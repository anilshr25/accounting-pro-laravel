<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->text('description')->nullable();
            $table->string('mobile')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('slogan')->nullable();
            $table->string('tagline')->nullable();
            $table->string('website')->nullable();
            $table->string('ledger_closing_date')->nullable();
            $table->string('date_format')->default('d/m/Y');
            $table->string('logo')->nullable();
            $table->string('footer_logo')->nullable();
            $table->string('recaptcha_site_key')->nullable();
            $table->string('recaptcha_secret_key')->nullable();
            $table->string('mail_driver')->nullable();
            $table->string('mail_host')->nullable();
            $table->string('mail_port')->nullable();
            $table->string('mail_user_name')->nullable();
            $table->string('mail_password')->nullable();
            $table->string('mail_encryption')->nullable();
            $table->string('mail_sender_name')->nullable();
            $table->string('mail_sender_address')->nullable();
            $table->string('storage_url')->nullable();
            $table->string('storage_type')->default('local');
            $table->string('storage_access_key')->nullable();
            $table->string('storage_secret_key')->nullable();
            $table->string('storage_region')->nullable();
            $table->string('storage_endpoint')->nullable();
            $table->string('storage_bucket_name')->nullable();
            $table->string('tax_percentage')->nullable();
            $table->string('pan_no')->nullable();
            $table->string('vat_no')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
