<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'duration_months',
                'late_payment_charge',
                'repayment_schedule',
            ]);

            $table->integer('duration')->after('base_rate');
            $table->enum('payment_type', [
                'monthly',
                'quarterly',
                'yearly'
            ])->after('duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->integer('duration_months');
            $table->decimal('late_payment_charge', 10, 2)->nullable();

            $table->enum('repayment_schedule', ['monthly', 'quarterly', 'yearly']);

            $table->dropColumn([
                'duration',
                'payment_type'
            ]);
        });
    }
};
