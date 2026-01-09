<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_account')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('supplier')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customer')->nullOnDelete();
            $table->string('type');
            $table->string('cheque_number');
            $table->string('pay_to')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->string('miti');
            $table->text('remarks')->nullable();
            $table->enum('status', [
                'pending',
                'cleared',
                'cancelled'
            ])->default('pending');
            $table->string('bank_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheque');
    }
};
