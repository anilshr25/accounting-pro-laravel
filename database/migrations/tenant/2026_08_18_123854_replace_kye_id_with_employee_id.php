<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // kye_addresses
        Schema::table('kye_addresses', function (Blueprint $table) {
            $table->dropForeign(['kye_id']);
            $table->renameColumn('kye_id', 'employee_id');
        });

        Schema::table('kye_addresses', function (Blueprint $table) {
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // kye_educations
        Schema::table('kye_educations', function (Blueprint $table) {
            $table->dropForeign(['kye_id']);
            $table->renameColumn('kye_id', 'employee_id');
        });

        Schema::table('kye_educations', function (Blueprint $table) {
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // kye_experiences
        Schema::table('kye_experiences', function (Blueprint $table) {
            $table->dropForeign(['kye_id']);
            $table->renameColumn('kye_id', 'employee_id');
        });

        Schema::table('kye_experiences', function (Blueprint $table) {
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // kye_services
        Schema::table('kye_services', function (Blueprint $table) {
            $table->dropForeign(['kye_id']);
            $table->renameColumn('kye_id', 'employee_id');
        });

        Schema::table('kye_services', function (Blueprint $table) {
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // kye_emergency_contacts
        Schema::table('kye_emergency_contacts', function (Blueprint $table) {
            $table->dropForeign(['kye_id']);
            $table->renameColumn('kye_id', 'employee_id');
        });

        Schema::table('kye_emergency_contacts', function (Blueprint $table) {
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // kye_addresses
        Schema::table('kye_addresses', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->renameColumn('employee_id', 'kye_id');
        });

        Schema::table('kye_addresses', function (Blueprint $table) {
            $table->foreign('kye_id')
                ->references('id')
                ->on('kyes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // kye_educations
        Schema::table('kye_educations', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->renameColumn('employee_id', 'kye_id');
        });

        Schema::table('kye_educations', function (Blueprint $table) {
            $table->foreign('kye_id')
                ->references('id')
                ->on('kyes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // kye_experiences
        Schema::table('kye_experiences', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->renameColumn('employee_id', 'kye_id');
        });

        Schema::table('kye_experiences', function (Blueprint $table) {
            $table->foreign('kye_id')
                ->references('id')
                ->on('kyes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // kye_services
        Schema::table('kye_services', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->renameColumn('employee_id', 'kye_id');
        });

        Schema::table('kye_services', function (Blueprint $table) {
            $table->foreign('kye_id')
                ->references('id')
                ->on('kyes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // kye_emergency_contacts
        Schema::table('kye_emergency_contacts', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->renameColumn('employee_id', 'kye_id');
        });

        Schema::table('kye_emergency_contacts', function (Blueprint $table) {
            $table->foreign('kye_id')
                ->references('id')
                ->on('kyes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};
