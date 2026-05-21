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
        Schema::create('schemes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('image')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('start_miti');
            $table->string('end_miti');
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('issued_amount', 15, 2)->nullable();
            $table->enum('scheme_type', ['monthly', 'quarterly', '6_monthly', 'annually'])
                ->default('monthly');
            $table->enum('status', ['pending', 'completed'])
                ->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schemes');
    }
};
