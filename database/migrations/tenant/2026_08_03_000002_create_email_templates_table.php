<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('identifier')->unique();
            $table->string('subject');
            $table->string('role')->index();
            $table->string('type')->index();
            $table->string('category')->nullable();
            $table->text('description');
            $table->longText('message_content')->nullable();
            $table->json('accepted_inputs')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['role', 'type']);
        });

        DB::table('email_templates')->insert([
            'title' => 'Verification code',
            'identifier' => 'user-verification-code-email',
            'subject' => 'Your verification code',
            'role' => 'user',
            'type' => 'verification_code_email',
            'category' => 'security',
            'description' => '<p>Hello {{ $first_name }},</p><p>Use the verification code below to continue signing in.</p>',
            'accepted_inputs' => json_encode(['first_name', 'verification_code'], JSON_THROW_ON_ERROR),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
