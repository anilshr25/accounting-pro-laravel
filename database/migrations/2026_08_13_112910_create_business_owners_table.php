<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_owners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();
            $table->foreignId('owner_user_id')
                ->constrained('owner_users')
                ->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique([
                'business_id',
                'owner_user_id',
            ]);

            $table->index('business_id');
            $table->index('owner_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_owners');
    }
};
