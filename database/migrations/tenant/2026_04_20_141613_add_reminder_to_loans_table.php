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
        Schema::table('loans', function (Blueprint $table) {
            $table->date('next_due_date')->nullable()->after('end_miti');

            $table->decimal('paid_amount', 15, 2)->default(0)->after('remaining_amount');

            $table->integer('current_month')->default(1)->after('paid_amount');

            $table->date('last_paid_date')->nullable()->after('current_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'next_due_date',
                'paid_amount',
                'current_month',
                'last_paid_date',
            ]);
        });
    }
};
