<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('owner_users', function (Blueprint $table) {
            $table->string('workplace')->nullable()->unique()->after('company_address');
        });
    }

    public function down(): void
    {
        Schema::table('owner_users', function (Blueprint $table) {
            $table->dropColumn('workplace');
        });
    }
};
