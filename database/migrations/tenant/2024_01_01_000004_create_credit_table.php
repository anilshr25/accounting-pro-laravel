<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->decimal('return_amount', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->string('miti')->nullable();
            $table->string('shift')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit');
    }
};
