<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->enum('category', ['compliance', 'technical', 'soft_skills', 'leadership', 'regulatory', 'product']);
            $table->string('provider', 100)->nullable();
            $table->decimal('duration_hours', 6, 1);
            $table->boolean('is_mandatory')->default(false);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('training_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('program_id');
            $table->foreign('program_id')->references('id')->on('training_programs')->cascadeOnDelete();
            $table->uuid('staff_profile_id');
            $table->foreign('staff_profile_id')->references('id')->on('staff_profiles')->cascadeOnDelete();
            $table->timestamp('enrolled_at')->nullable();
            $table->enum('status', ['enrolled', 'attended', 'completed', 'failed', 'excused'])->default('enrolled');
            $table->decimal('score', 5, 2)->nullable();
            $table->boolean('certificate_issued')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['program_id', 'staff_profile_id']);
            $table->index(['tenant_id', 'staff_profile_id']);
        });

        Schema::create('staff_certifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('staff_profile_id');
            $table->foreign('staff_profile_id')->references('id')->on('staff_profiles')->cascadeOnDelete();
            $table->string('name');
            $table->string('issuing_body');
            $table->string('cert_number', 50)->nullable();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'staff_profile_id']);
        });

        Schema::create('staff_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('staff_profile_id');
            $table->foreign('staff_profile_id')->references('id')->on('staff_profiles')->cascadeOnDelete();
            $table->enum('document_type', ['bvn', 'nin', 'passport', 'drivers_license', 'cibn', 'ican', 'work_permit', 'tax_id', 'pension_id', 'nhf_id', 'other']);
            $table->string('document_number', 50)->nullable();
            $table->text('file_url')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->unique(['staff_profile_id', 'document_type']);
            $table->index(['tenant_id', 'staff_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_documents');
        Schema::dropIfExists('staff_certifications');
        Schema::dropIfExists('training_attendances');
        Schema::dropIfExists('training_programs');
    }
};
