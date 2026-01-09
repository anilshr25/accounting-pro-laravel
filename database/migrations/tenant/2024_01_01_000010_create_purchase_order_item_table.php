<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_order')->nullOnDelete();
            $table->text('description');
            $table->integer('quantity');
            $table->decimal('rate', 15, 2);
            $table->decimal('amount', 15, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_item');
    }
};
