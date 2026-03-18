<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_cycles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->enum('period_type', ['annual', 'semi_annual', 'quarterly']);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'active', 'closed', 'archived'])->default('draft');
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('review_cycle_id');
            $table->foreign('review_cycle_id')->references('id')->on('review_cycles')->cascadeOnDelete();
            $table->uuid('staff_profile_id');
            $table->foreign('staff_profile_id')->references('id')->on('staff_profiles')->cascadeOnDelete();
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->foreign('reviewer_id')->references('id')->on('users')->nullOnDelete();
            $table->enum('status', ['pending', 'self_assessed', 'manager_reviewed', 'hr_approved'])->default('pending');
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->enum('rating', ['exceptional', 'exceeds_expectations', 'meets_expectations', 'below_expectations', 'unsatisfactory'])->nullable();
            $table->text('staff_comments')->nullable();
            $table->text('manager_comments')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['review_cycle_id', 'staff_profile_id']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('performance_review_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('review_id');
            $table->foreign('review_id')->references('id')->on('performance_reviews')->cascadeOnDelete();
            $table->text('criterion');
            $table->decimal('weight', 5, 2)->default(1);
            $table->decimal('self_score', 4, 2)->nullable();
            $table->decimal('manager_score', 4, 2)->nullable();
            $table->decimal('max_score', 4, 2)->default(5);
            $table->text('target_description')->nullable();
            $table->text('achievement_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_review_items');
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('review_cycles');
    }
};
