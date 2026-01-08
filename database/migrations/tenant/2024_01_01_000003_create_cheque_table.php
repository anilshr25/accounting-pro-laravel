<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheque', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('type')->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('pay_to')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->date('date')->nullable();
            $table->string('miti')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->nullable();
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
