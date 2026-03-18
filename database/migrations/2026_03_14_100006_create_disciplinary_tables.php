<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplinary_cases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('staff_profile_id');
            $table->foreign('staff_profile_id')->references('id')->on('staff_profiles')->cascadeOnDelete();
            $table->string('case_number', 30)->unique();
            $table->enum('type', ['query', 'warning', 'suspension', 'demotion', 'termination']);
            $table->text('description');
            $table->date('incident_date');
            $table->unsignedBigInteger('raised_by');
            $table->foreign('raised_by')->references('id')->on('users')->cascadeOnDelete();
            $table->enum('status', ['open', 'awaiting_response', 'responded', 'closed', 'appealed'])->default('open');
            $table->timestamps();
            $table->index(['tenant_id', 'staff_profile_id', 'status']);
        });

        Schema::create('disciplinary_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('case_id');
            $table->foreign('case_id')->references('id')->on('disciplinary_cases')->cascadeOnDelete();
            $table->text('staff_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->enum('outcome', ['warning_issued', 'suspended', 'dismissed', 'cleared', 'no_action'])->nullable();
            $table->unsignedBigInteger('decided_by')->nullable();
            $table->foreign('decided_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_responses');
        Schema::dropIfExists('disciplinary_cases');
    }
};
