<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daybook', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->string('type')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->text('remarks')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daybook');
    }
};
