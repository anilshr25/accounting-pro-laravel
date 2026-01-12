<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credits', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->decimal('amount', 15, 2);
            $table->decimal('return_amount', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->date('date');
            $table->string('miti');
            $table->enum('shift', ['morning', 'evening']);
            $table->enum('status', [
                'pending',
                'completed',
            ])->default('pending');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credits');
    }
};
