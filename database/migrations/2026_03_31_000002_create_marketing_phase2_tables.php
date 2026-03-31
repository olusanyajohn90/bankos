<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Loyalty Program ───────────────────────────────────────────────
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('tiers')->nullable(); // [{name: "Bronze", min_points: 0, perks: [...]}, ...]
            $table->json('earning_rules')->nullable(); // [{event: "deposit", points_per_unit: 1, unit: 1000}, ...]
            $table->json('redemption_options')->nullable(); // [{name: "Fee Waiver", points_cost: 500, value: 1000}, ...]
            $table->integer('points_expiry_months')->nullable();
            $table->timestamps();
        });

        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->uuid('program_id');
            $table->foreign('program_id')->references('id')->on('loyalty_programs')->cascadeOnDelete();
            $table->integer('total_earned')->default(0);
            $table->integer('total_redeemed')->default(0);
            $table->integer('current_balance')->default(0);
            $table->string('current_tier', 50)->default('Bronze');
            $table->timestamps();

            $table->unique(['customer_id', 'program_id']);
        });

        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->uuid('program_id');
            $table->foreign('program_id')->references('id')->on('loyalty_programs')->cascadeOnDelete();
            $table->enum('type', ['earned', 'redeemed', 'expired', 'adjusted']);
            $table->integer('points');
            $table->string('description', 255);
            $table->string('source', 50)->nullable(); // deposit, loan_repayment, referral, manual, redemption
            $table->string('source_id', 50)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'program_id', 'type']);
        });

        // ── Promotional Offers & Coupons ──────────────────────────────────
        Schema::create('marketing_offers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('offer_type', 30); // rate_discount, fee_waiver, cashback, bonus_interest, free_service
            $table->json('offer_config')->nullable(); // {discount_percent: 50, applies_to: "processing_fee"}
            $table->string('coupon_code', 30)->nullable();
            $table->uuid('segment_id')->nullable();
            $table->foreign('segment_id')->references('id')->on('marketing_segments')->nullOnDelete();
            $table->integer('max_redemptions')->nullable();
            $table->integer('redemption_count')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
            $table->index(['coupon_code']);
        });

        Schema::create('marketing_offer_redemptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('offer_id');
            $table->foreign('offer_id')->references('id')->on('marketing_offers')->cascadeOnDelete();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->string('applied_to', 100)->nullable(); // loan_id, account_id, etc.
            $table->timestamps();

            $table->unique(['offer_id', 'customer_id']);
        });

        // ── Customer Feedback & NPS ───────────────────────────────────────
        Schema::create('marketing_surveys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('type', ['nps', 'csat', 'custom'])->default('custom');
            $table->json('questions'); // [{id, text, type: "rating|text|multiple_choice", options: [...]}]
            $table->boolean('is_active')->default(true);
            $table->uuid('segment_id')->nullable();
            $table->foreign('segment_id')->references('id')->on('marketing_segments')->nullOnDelete();
            $table->integer('response_count')->default(0);
            $table->decimal('average_score', 4, 2)->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('marketing_survey_responses', function (Blueprint $table) {
            $table->id();
            $table->uuid('survey_id');
            $table->foreign('survey_id')->references('id')->on('marketing_surveys')->cascadeOnDelete();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->json('answers'); // [{question_id, value}]
            $table->integer('nps_score')->nullable(); // 0-10 for NPS surveys
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->unique(['survey_id', 'customer_id']);
        });

        // ── Marketing Automation Workflows ────────────────────────────────
        Schema::create('marketing_automations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('trigger'); // {type: "event", event: "account_opened"} or {type: "schedule", cron: "0 9 * * 1"}
            $table->json('conditions')->nullable(); // [{field, operator, value}] — filter before action
            $table->json('actions'); // [{type: "send_sms", template_id, delay_minutes}, {type: "send_email"}, ...]
            $table->integer('enrolled_count')->default(0);
            $table->integer('completed_count')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('marketing_automation_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('automation_id');
            $table->foreign('automation_id')->references('id')->on('marketing_automations')->cascadeOnDelete();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->integer('step_index')->default(0);
            $table->string('action_type', 50);
            $table->enum('status', ['pending', 'sent', 'completed', 'failed', 'skipped'])->default('pending');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->text('result')->nullable();
            $table->timestamps();

            $table->index(['automation_id', 'status']);
            $table->index(['customer_id']);
        });

        // ── Product Recommendations ───────────────────────────────────────
        Schema::create('marketing_recommendations', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->string('product_type', 50); // savings_product, loan_product, insurance, investment
            $table->string('product_id', 50)->nullable();
            $table->string('product_name', 150);
            $table->text('reason');
            $table->decimal('confidence_score', 4, 2)->default(0); // 0.00-1.00
            $table->enum('status', ['active', 'accepted', 'dismissed'])->default('active');
            $table->timestamps();

            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_recommendations');
        Schema::dropIfExists('marketing_automation_logs');
        Schema::dropIfExists('marketing_automations');
        Schema::dropIfExists('marketing_survey_responses');
        Schema::dropIfExists('marketing_surveys');
        Schema::dropIfExists('marketing_offer_redemptions');
        Schema::dropIfExists('marketing_offers');
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('loyalty_points');
        Schema::dropIfExists('loyalty_programs');
    }
};
