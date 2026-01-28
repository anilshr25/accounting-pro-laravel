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
        Schema::create('invoice_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_return_id')->constrained('invoice_returns')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('description');
            $table->integer('quantity');
            $table->decimal('rate', 15, 2);
            $table->decimal('amount', 15, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_return_items');
    }
};
