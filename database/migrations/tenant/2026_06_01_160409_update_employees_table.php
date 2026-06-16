<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['base_salary', 'employment_type', 'salary_type']);

            $table->string('pan_no')->nullable()->after('joining_date');
            $table->string('license_no')->nullable()->after('pan_no');
            $table->decimal('salary', 12, 2)->nullable()->after('license_no');
            $table->string('image')->after('salary');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('base_salary')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('salary_type');

            $table->dropColumn(['pan_no', 'license_no', 'salary', 'image']);
        });
    }
};
