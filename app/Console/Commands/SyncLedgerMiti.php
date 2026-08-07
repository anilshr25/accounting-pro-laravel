<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\Ledger\Ledger;

class SyncLedgerMiti extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ledger:sync-miti';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync ledger miti from reference tables for all tenants';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Tenant::all()->each(function ($tenant) {

            tenancy()->initialize($tenant);

            $this->info("Syncing tenant: {$tenant->id}");

            Ledger::with('reference')
                ->chunkById(500, function ($ledgers) {

                    foreach ($ledgers as $ledger) {

                        if (!$ledger->reference) {
                            $this->error("Reference not found");
                            continue;
                        }

                        $miti = match ($ledger->reference_type) {
                            'invoice'         => $ledger->reference->invoice_miti ?? null,
                            'invoice_return'  => $ledger->reference->return_miti ?? null,
                            'purchase_order'  => $ledger->reference->received_date_miti ?? null,
                            'purchase_return' => $ledger->reference->return_miti ?? null,
                            'payment'         => $ledger->reference->miti ?? null,
                            'credit'          => $ledger->reference->miti ?? null,
                            'cheque'          => $ledger->reference->miti ?? null,
                            default           => null,
                        };

                        $this->info("Miti: " . ($miti ?? 'NULL'));

                        if ($miti) {
                            $ledger->miti = $miti;
                            $ledger->save();

                            $this->info("Updated ledger {$ledger->id}");
                        }
                    }
                });

            tenancy()->end();
        });

        $this->info('Ledger miti synchronized successfully.');

        return self::SUCCESS;
    }
}
