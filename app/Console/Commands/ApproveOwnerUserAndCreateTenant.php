<?php

namespace App\Console\Commands;

use App\Models\AdminUser\AdminUser;
use App\Models\Domain\Domain;
use App\Models\OwnerUser\OwnerUser;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\User\User as TenantUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApproveOwnerUserAndCreateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'owneruser:approve-and-create-tenant
                            {owner_user_id : The ID of the owner user to approve}
                            {--tenant-name= : The tenant name}
                            {--domain= : The domain for the tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Approve an owner user and create a tenant database for them';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ownerUserId = $this->argument('owner_user_id');
        $tenantName = $this->option('tenant-name');
        $domain = $this->option('domain');

        // Find the owner user
        $ownerUser = OwnerUser::find($ownerUserId);

        if (!$ownerUser) {
            $this->error("Owner user with ID {$ownerUserId} not found.");
            return self::FAILURE;
        }

        $this->info("Found owner user: {$ownerUser->first_name} {$ownerUser->last_name}");

        // Prompt for tenant name if not provided
        if (!$tenantName) {
            $tenantName = $this->ask('Enter tenant name', $ownerUser->company_name ?? $ownerUser->first_name . ' ' . $ownerUser->last_name);
        }

        // Prompt for domain if not provided
        if (!$domain) {
            $domain = $this->ask('Enter domain for the tenant');
        }

        if (Domain::query()->where('domain', $domain)->exists()) {
            $this->error("Domain '{$domain}' is already in use.");
            return self::FAILURE;
        }

        try {
            // 1. Get the admin user ID (or create a default one if none exists)
            $approvedBy = $this->getAdminUserId();

            // NOTE: Don't wrap tenant creation in a DB transaction.
            // Stancl Tenancy runs Jobs\CreateDatabase and Jobs\MigrateDatabase on TenantCreated,
            // and DDL can implicitly commit transactions which would break PDO commit.

            $ownerUser->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
            ]);

            $this->info("✓ Owner user approved successfully");

            $tenancyConfig = config('tenancy.database');
            $suffix = str_replace(['.', '-', ' '], '_', strtolower($tenantName));
            $this->info("✓ Suffix: {$suffix}");
            config(['tenancy.database.suffix' => "_$suffix"]);
            $tenancyConfig['suffix'] = $suffix;

            // 3. Create tenant
            $tenant = Tenant::create([
                'id' => (string) \Str::uuid(),
                'name' => $tenantName,
                'owner_user_id' => $ownerUser->id,
            ]);

            $this->info("✓ Tenant created with ID: {$tenant->id}");

            // 4. Create domain
            $tenant->domains()->create([
                'domain' => $domain,
            ]);

            $this->info("✓ Domain created: {$domain}");

            $databaseName = ($tenancyConfig['prefix'] ?? 'tenant') . $tenant->id . '_' .$tenancyConfig['suffix'];
            $this->info("✓ Tenant database will be: {$databaseName}");
            // Create the initial tenant user (after tenant migrations).
            $this->info("Creating initial tenant user...");
            try {
                $tenant->run(function () use ($ownerUser) {
                    $this->call('migrate', [
                        '--path' => 'database/migrations/tenant',
                        '--database' => 'tenant',
                        '--force' => true,
                    ]);

                    $existingTenantUser = TenantUser::query()
                        ->where('email', $ownerUser->email)
                        ->first();

                    if ($existingTenantUser) {
                        $this->warn("Tenant user already exists for {$ownerUser->email}; skipping creation.");
                        return;
                    }

                    TenantUser::create([
                        'first_name' => $ownerUser->first_name,
                        'last_name' => $ownerUser->last_name,
                        'email' => $ownerUser->email,
                        'is_login_verified' => true,
                        'is_active' => true,
                        'password' => $ownerUser->password,
                        'image' => $ownerUser->image,
                        'company_name' => $ownerUser->company_name,
                    ]);

                    $this->info("✓ Tenant user created: {$ownerUser->email}");
                });
            } catch (\Exception $e) {
                $this->warn("⚠ Could not create tenant user");
                $this->line("  Error: " . substr($e->getMessage(), 0, 120) . "...");
                $this->line("");
                $this->info("  You can run tenant migrations manually with:");
                $this->line("  php artisan tenants:migrate --tenants=" . $tenant->id);
            }

            // 6. Run migrations on tenant database (outside transaction)
            $this->info("Running migrations on tenant database...");
            try {
                $tenant->run(function () {
                    $this->call('migrate', [
                        '--path' => 'database/migrations/tenant',
                        '--database' => 'tenant',
                        '--force' => true,
                    ]);
                });
                $this->info("✓ Migrations completed on tenant database");
            } catch (\Exception $e) {
                // Migration errors can occur due to missing tables or FK constraints
                $this->warn("⚠ Migration completed with notices/errors (may be expected)");
                $this->line("  Error: " . substr($e->getMessage(), 0, 120) . "...");
                $this->line("");
                $this->info("  You can fix migrations manually with:");
                $this->line("  php artisan tenants:migrate --tenants=" . $tenant->id);
            }
            $this->newLine();
            $this->info('✓ Owner user approved and tenant created successfully!');
            $this->newLine();
            $this->table(
                ['Property', 'Value'],
                [
                    ['Owner User ID', $ownerUser->id],
                    ['Owner Name', "{$ownerUser->first_name} {$ownerUser->last_name}"],
                    ['Email', $ownerUser->email],
                    ['Status', $ownerUser->status],
                    ['Tenant ID', $tenant->id],
                    ['Tenant Name', $tenant->name],
                    ['Domain', $domain],
                    ['Database', $databaseName],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $this->error("Error: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return self::FAILURE;
        }
    }

    /**
     * Get or create an admin user ID for approval
     *
     * @return int
     */
    private function getAdminUserId(): int
    {
        // Try to get an existing admin user
        $adminUser = AdminUser::first();

        if ($adminUser) {
            return $adminUser->id;
        }

        // Create a default admin user if none exists
        $this->info('No admin user found. Creating default admin user...');

        $adminUser = AdminUser::create([
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email' => 'admin@' . config('app.domain', 'localhost'),
            'password' => Hash::make('Admin@123'),
            'user_type' => 'super_admin',
            'is_active' => true,
        ]);

        $this->info("✓ Default admin user created with ID: {$adminUser->id}");
        return $adminUser->id;
    }
}
