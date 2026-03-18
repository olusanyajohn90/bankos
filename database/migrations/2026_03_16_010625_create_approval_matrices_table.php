<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Configurable approval matrix rules per action type
        Schema::create('approval_matrices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name', 150);                          // e.g. "Loan Disbursement > ₦5M"
            $table->string('action_type', 80);                    // e.g. loan_disbursal, expense_claim, leave_request, asset_purchase
            $table->string('description', 300)->nullable();
            $table->decimal('min_amount', 20, 2)->nullable();     // trigger condition: min amount
            $table->decimal('max_amount', 20, 2)->nullable();     // trigger condition: max amount (null = no limit)
            $table->string('condition_field', 80)->nullable();    // e.g. 'amount', 'days', 'grade_level'
            $table->string('condition_operator', 10)->nullable(); // >, >=, <, <=, =
            $table->string('condition_value', 80)->nullable();
            $table->tinyInteger('total_steps')->default(1);       // how many approval tiers
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_checker')->default(true);   // maker-checker enforcement
            $table->unsignedInteger('escalation_hours')->default(48); // auto-escalate after N hours
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index(['tenant_id', 'action_type', 'is_active']);
        });

        // Steps within each approval matrix
        Schema::create('approval_matrix_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('matrix_id');
            $table->tinyInteger('step_number');                   // 1 = first approver, 2 = second, etc.
            $table->string('step_name', 100);                     // e.g. "Branch Manager", "Head of Credit"
            $table->enum('approver_type', ['role', 'user', 'department_head', 'grade_level_above', 'any_manager']);
            $table->string('approver_value', 150)->nullable();    // role name, user id, dept name, or grade level
            $table->boolean('is_mandatory')->default(true);
            $table->unsignedInteger('timeout_hours')->default(48);
            $table->enum('on_timeout', ['escalate', 'auto_approve', 'auto_reject'])->default('escalate');
            $table->timestamps();

            $table->index(['matrix_id', 'step_number']);
            $table->foreign('matrix_id')->references('id')->on('approval_matrices')->cascadeOnDelete();
        });

        // Individual approval request instances
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('matrix_id');
            $table->string('action_type', 80);                    // mirrors matrix.action_type
            $table->string('subject_type', 80);                   // e.g. App\Models\Loan
            $table->uuid('subject_id');                           // the record being approved
            $table->string('reference', 50)->nullable();          // human-readable ref e.g. REQ-2026-0001
            $table->text('summary');                              // what is being approved
            $table->decimal('amount', 20, 2)->nullable();
            $table->enum('status', ['pending', 'in_review', 'approved', 'rejected', 'cancelled', 'escalated'])->default('pending');
            $table->tinyInteger('current_step')->default(1);
            $table->tinyInteger('total_steps')->default(1);
            $table->unsignedBigInteger('initiated_by');           // maker
            $table->unsignedBigInteger('final_actioned_by')->nullable();
            $table->timestamp('final_actioned_at')->nullable();
            $table->text('final_notes')->nullable();
            $table->json('metadata')->nullable();                 // extra context
            $table->timestamp('due_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'action_type', 'status']);
            $table->index(['subject_type', 'subject_id']);
            $table->foreign('matrix_id')->references('id')->on('approval_matrices');
        });

        // Decision log for each step of each request
        Schema::create('approval_request_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('request_id');
            $table->tinyInteger('step_number');
            $table->string('step_name', 100);
            $table->unsignedBigInteger('assigned_to')->nullable();  // specific user assigned
            $table->string('assigned_role', 150)->nullable();       // or by role
            $table->enum('status', ['pending', 'approved', 'rejected', 'escalated', 'skipped'])->default('pending');
            $table->unsignedBigInteger('actioned_by')->nullable();
            $table->timestamp('actioned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();

            $table->index(['request_id', 'step_number']);
            $table->foreign('request_id')->references('id')->on('approval_requests')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_request_steps');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_matrix_steps');
        Schema::dropIfExists('approval_matrices');
    }
};
