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
        Schema::create('kyes', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 255);
            $table->date('date_of_birth_ad');
            $table->string('date_of_birth_bs');
            $table->enum('marital_status',['single', 'married', 'divorced']);
            $table->string('blood_group')->nullable();
            $table->enum('gender', ['male', 'female', 'others']);
            $table->string('citizenship_number')->unique();
            $table->date('issue_date');
            $table->string('issue_district');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyes');
    }
};
