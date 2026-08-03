<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->string('mail_fallback_host')->nullable()->after('mail_sender_address');
            $table->unsignedSmallInteger('mail_fallback_port')->nullable()->after('mail_fallback_host');
            $table->string('mail_fallback_user_name')->nullable()->after('mail_fallback_port');
            $table->text('mail_fallback_password')->nullable()->after('mail_fallback_user_name');
            $table->string('mail_fallback_encryption')->nullable()->after('mail_fallback_password');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'mail_fallback_host',
                'mail_fallback_port',
                'mail_fallback_user_name',
                'mail_fallback_password',
                'mail_fallback_encryption',
            ]);
        });
    }
};
