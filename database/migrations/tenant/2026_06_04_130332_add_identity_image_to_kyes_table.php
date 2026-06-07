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
        Schema::table('kyes', function (Blueprint $table) {
            $table->string('front_image')->after('issue_district');
            $table->string('back_image')->nullable()->after('front_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kyes', function (Blueprint $table) {
            $table->dropColumn(['front_image', 'back_image']);
        });
    }
};
