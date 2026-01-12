<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('purchase_invoice_number');
            $table->date('order_date');
            $table->date('received_date');
            $table->decimal('tax', 15, 2);
            $table->decimal('sub_total', 15, 2);
            $table->decimal('total', 15, 2);
            $table->enum('status', ['invoiced', 'received'])->default('received');
            $table->string('received_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
