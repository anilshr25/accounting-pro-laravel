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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('loan_number')->unique();
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('premium_rate', 5, 2);
            $table->decimal('base_rate', 5, 2);
            $table->integer('duration_months');
            $table->enum('loan_type', ['od', 'home', 'business', 'term']);
            $table->decimal('emi_amount', 15, 2);
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->decimal('remaining_amount', 15, 2)->nullable();
            $table->string('collateral')->nullable();
            $table->enum('repayment_schedule', ['monthly', 'quarterly', 'yearly']);
            $table->decimal('late_payment_charge', 10, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('start_miti');
            $table->string('end_miti');
            $table->enum('status', ['active', 'closed'])
                ->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
