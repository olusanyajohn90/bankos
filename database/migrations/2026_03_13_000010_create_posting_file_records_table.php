<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posting_file_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('posting_file_id');
            $table->integer('row_number');
            $table->string('identifier_type'); // BVN, NIN, LOAN_ACCOUNT_NUMBER
            $table->string('identifier_value');
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->string('payment_channel')->nullable();
            $table->string('narration')->nullable();
            $table->enum('status', ['pending', 'valid', 'invalid', 'posted', 'duplicate', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->uuid('transaction_id')->nullable(); // FK to transactions once posted
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('posting_file_id')->references('id')->on('posting_files')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posting_file_records');
    }
};
