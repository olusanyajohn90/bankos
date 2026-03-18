<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bureau_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('customer_id');
            $table->uuid('loan_id')->nullable();
            $table->enum('bureau', ['crc', 'xds', 'firstcentral'])->default('crc');
            $table->string('reference')->unique();
            $table->integer('credit_score')->nullable();
            $table->integer('active_loans_count')->default(0);
            $table->decimal('total_outstanding', 15, 2)->default(0);
            $table->integer('delinquency_count')->default(0);
            $table->enum('status', ['pending', 'retrieved', 'failed'])->default('pending');
            $table->text('raw_response')->nullable(); // JSON
            $table->timestamp('retrieved_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bureau_reports');
    }
};
