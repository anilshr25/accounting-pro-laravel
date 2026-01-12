<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('owner_users', function (Blueprint $table) {
            $table->string('company_industry')->nullable()->after('company_registration_no');
            $table->string('company_website')->nullable()->after('company_industry');
            $table->string('company_country')->nullable()->after('company_website');
        });
    }

    public function down(): void
    {
        Schema::table('owner_users', function (Blueprint $table) {
            $table->dropColumn(['company_industry', 'company_website', 'company_country']);
        });
    }
};
