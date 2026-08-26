<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('type')->nullable()->after('name');

            $table->dropUnique(['name', 'guard_name']);

            $table->unique(['name', 'type', 'guard_name']);
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropUnique(['name', 'type', 'guard_name']);

            $table->unique(['name', 'guard_name']);

            $table->dropColumn('type');
        });
    }
};
