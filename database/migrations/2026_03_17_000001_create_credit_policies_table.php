<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── credit_policies ─────────────────────────────────────────────────
        Schema::create('credit_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->foreignUuid('loan_product_id')->nullable()->constrained('loan_products')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->integer('auto_approve_above')->nullable();
            $table->integer('auto_decline_below')->nullable();
            $table->boolean('require_bureau_report')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        // ── credit_policy_rules ──────────────────────────────────────────────
        Schema::create('credit_policy_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('policy_id')->constrained('credit_policies')->cascadeOnDelete();
            $table->enum('rule_type', [
                'min_bureau_score',
                'max_dti_ratio',
                'max_loan_to_income',
                'min_customer_age',
                'max_active_loans',
                'min_bvn_verified',
                'max_delinquency_count',
                'max_outstanding_ratio',
                'collateral_required',
                'min_kyc_tier',
            ]);
            $table->enum('operator', ['gte', 'lte', 'eq', 'neq']);
            $table->decimal('threshold_value', 10, 4);
            $table->enum('action_on_fail', ['decline', 'refer', 'flag', 'reduce_amount']);
            $table->string('action_param', 50)->nullable();
            $table->enum('severity', ['hard', 'soft'])->default('hard');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── credit_decisions ─────────────────────────────────────────────────
        Schema::create('credit_decisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->foreignUuid('policy_id')->nullable()->constrained('credit_policies')->nullOnDelete();
            $table->integer('bureau_score')->nullable();
            $table->integer('internal_score')->nullable();
            $table->integer('final_score')->nullable();
            $table->enum('recommendation', ['approve', 'conditional', 'refer', 'decline']);
            $table->boolean('auto_decided')->default(false);
            $table->json('rules_passed')->nullable();
            $table->json('rules_failed')->nullable();
            $table->json('conditions')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('decided_by')->nullable();
            $table->foreign('decided_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->unique('loan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_decisions');
        Schema::dropIfExists('credit_policy_rules');
        Schema::dropIfExists('credit_policies');
    }
};
