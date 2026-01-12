<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('owner_users', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('phone');
            $table->string('company_address')->nullable()->after('company_name');
            $table->string('company_email')->nullable()->after('company_address');
            $table->string('company_pan_no')->nullable()->after('company_address');
            $table->string('company_registration_no')->nullable()->after('company_pan_no');
            $table->string('doc_one')->nullable()->after('company_registration_no');
            $table->string('doc_two')->nullable()->after('doc_one');
            $table->string('doc_three')->nullable()->after('doc_two');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('doc_three');
            $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            $table->foreign('approved_by')->references('id')->on('admin_users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('remarks')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('owner_users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'company_name',
                'company_address',
                'company_pan_no',
                'company_registration_no',
                'doc_one',
                'doc_two',
                'doc_three',
                'status',
                'approved_by',
                'remarks',
            ]);
        });
    }
};
