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
        Schema::create('scheme_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->date('date');
            $table->string('miti');
            $table->decimal('amount', 15, 2);
            $table->string('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheme_payments');
    }
};
