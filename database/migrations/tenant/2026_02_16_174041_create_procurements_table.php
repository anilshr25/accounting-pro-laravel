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
        Schema::create('procurements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('code')->unique();
            $table->string('name');
            $table->enum('unit', ['gram', 'kilogram']);
            $table->decimal('price', 15, 2)->nullable();
            $table->enum('category', ['food','non-food']);
            $table->string('remarks')->nullable();
            $table->enum('status', ['received','dispatch'])->default('received');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurements');
    }
};
