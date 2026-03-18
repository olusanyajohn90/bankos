<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            $table->string('documentable_type', 255);
            $table->string('documentable_id', 36);

            $table->string('document_type', 80);
            $table->enum('document_category', ['identity', 'financial', 'legal', 'compliance', 'collateral', 'hr', 'other']);

            $table->string('title', 255);
            $table->text('description')->nullable();

            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size_kb')->nullable();

            $table->tinyInteger('version')->default(1);
            $table->boolean('is_current_version')->default(true);

            $table->uuid('parent_id')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'expired', 'archived'])->default('pending');

            $table->date('expiry_date')->nullable();
            $table->tinyInteger('alert_days_before')->default(30);
            $table->boolean('is_required')->default(false);

            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['tenant_id', 'documentable_type', 'documentable_id']);
            $table->index(['tenant_id', 'document_category']);
            $table->index(['tenant_id', 'status']);
            $table->index(['expiry_date']);
            $table->index(['documentable_type', 'documentable_id', 'document_type', 'is_current_version'], 'docs_polymorphic_type_version_idx');
        });

        // Self-referencing FK added separately so PostgreSQL sees the primary key first
        Schema::table('documents', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('documents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
