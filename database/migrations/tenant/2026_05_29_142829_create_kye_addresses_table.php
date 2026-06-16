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
        Schema::create('kye_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kye_id')
                ->constrained('kyes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->enum('type', [
                'permanent',
                'temporary'
            ]);
            $table->string('zone');
            $table->string('district');
            $table->string('municipality');
            $table->string('ward_number');
            $table->string('plus_code')->nullable();
            $table->string('locality')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kye_addresses');
    }
};
