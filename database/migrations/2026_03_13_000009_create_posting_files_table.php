<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posting_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->unsignedBigInteger('uploaded_by');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('reference')->unique();
            $table->enum('type', ['repayment', 'deposit', 'disbursement'])->default('repayment');
            $table->enum('status', ['pending', 'validating', 'validated', 'posting', 'posted', 'failed'])->default('pending');
            $table->integer('total_records')->default(0);
            $table->integer('valid_records')->default(0);
            $table->integer('invalid_records')->default(0);
            $table->integer('posted_records')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('validation_errors')->nullable(); // JSON
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posting_files');
    }
};
