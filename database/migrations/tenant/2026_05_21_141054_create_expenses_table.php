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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->enum('expense_type', [
                'utilities',
                'insurance',
                'rent',
                'salary',
                'maintenance',
                'supplies',
                'travel',
                'marketing',
                'other'
            ]);
            $table->string('title');
            $table->date('expense_date');
            $table->string('expense_miti');
            $table->decimal('amount', 5, 2);
            $table->string('description');
            $table->enum('payment_method', ['cash', 'fonepay',]);
            $table->enum('status', ['paid', 'cancelled'])->default('paid');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
