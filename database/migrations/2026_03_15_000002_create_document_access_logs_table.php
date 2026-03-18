<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_access_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            $table->uuid('document_id');
            $table->foreign('document_id')->references('id')->on('documents')->cascadeOnDelete();

            $table->unsignedBigInteger('accessed_by')->nullable();
            $table->foreign('accessed_by')->references('id')->on('users')->nullOnDelete();

            $table->enum('action', ['viewed', 'downloaded', 'printed']);

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->timestamp('accessed_at')->useCurrent();

            $table->timestamps();

            $table->index(['document_id', 'accessed_at']);
            $table->index(['tenant_id', 'accessed_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_access_logs');
    }
};
