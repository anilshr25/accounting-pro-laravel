<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customer')->nullOnDelete();
            $table->string('invoice_miti');
            $table->date('invoice_date');
            $table->decimal('tax', 15, 2)->nullable();
            $table->decimal('sub_total', 15, 2)->nullable();
            $table->decimal('total', 15, 2);
            $table->enum('payment_type', ['cash', 'fonepay', 'cardpay']);
            $table->string('status')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('shift', ['morning', 'evening']);
            $table->boolean('sale_return')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
