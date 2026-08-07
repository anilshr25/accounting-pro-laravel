<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->encryptCredentials();
    }

    public function down(): void
    {
        // Credential encryption is intentionally irreversible during rollback.
    }

    private function encryptCredentials(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table): void {
            $table->text('recaptcha_secret_key')->nullable()->change();
            $table->text('mail_password')->nullable()->change();
            $table->text('storage_access_key')->nullable()->change();
            $table->text('storage_secret_key')->nullable()->change();
        });

        $fields = ['recaptcha_secret_key', 'mail_password', 'storage_access_key', 'storage_secret_key'];

        DB::table('site_settings')->orderBy('id')->each(function (object $setting) use ($fields): void {
            $updates = [];

            foreach ($fields as $field) {
                $value = $setting->{$field} ?? null;
                if (! is_string($value) || $value === '' || $this->isEncrypted($value)) {
                    continue;
                }

                $updates[$field] = Crypt::encryptString($value);
            }

            if ($updates !== []) {
                DB::table('site_settings')->where('id', $setting->id)->update($updates);
            }
        });
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (Throwable) {
            return false;
        }
    }
};
