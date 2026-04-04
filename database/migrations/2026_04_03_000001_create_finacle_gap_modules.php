<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ══════════════════════════════════════════════════════════════════
        // TREASURY LITE
        // ══════════════════════════════════════════════════════════════════
        Schema::create('treasury_placements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('reference', 30)->unique();
            $table->enum('type', ['placement', 'borrowing']); // money placed or borrowed
            $table->string('counterparty', 200); // bank or institution name
            $table->decimal('principal', 15, 2);
            $table->decimal('interest_rate', 6, 4); // annual rate
            $table->date('start_date');
            $table->date('maturity_date');
            $table->integer('tenor_days');
            $table->decimal('expected_interest', 15, 2)->default(0);
            $table->decimal('accrued_interest', 15, 2)->default(0);
            $table->enum('status', ['active', 'matured', 'liquidated', 'rolled_over'])->default('active');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('treasury_fx_deals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('reference', 30)->unique();
            $table->enum('deal_type', ['spot', 'forward', 'swap']);
            $table->enum('direction', ['buy', 'sell']);
            $table->string('currency_pair', 10); // e.g. USD/NGN
            $table->decimal('amount', 15, 2); // base currency amount
            $table->decimal('rate', 15, 6);
            $table->decimal('counter_amount', 15, 2); // settlement amount
            $table->date('trade_date');
            $table->date('settlement_date');
            $table->enum('status', ['pending', 'settled', 'cancelled'])->default('pending');
            $table->string('counterparty', 200)->nullable();
            $table->unsignedBigInteger('dealer_id');
            $table->foreign('dealer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        // ══════════════════════════════════════════════════════════════════
        // TRADE FINANCE LITE
        // ══════════════════════════════════════════════════════════════════
        Schema::create('trade_finance_instruments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->string('reference', 30)->unique();
            $table->enum('type', ['letter_of_credit', 'bank_guarantee', 'bill_for_collection', 'invoice_discounting']);
            $table->string('beneficiary_name', 200);
            $table->string('beneficiary_bank', 200)->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('NGN');
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->text('purpose')->nullable();
            $table->text('terms')->nullable();
            $table->decimal('commission_rate', 6, 4)->default(0);
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'issued', 'amended', 'utilized', 'expired', 'cancelled'])->default('draft');
            $table->json('documents')->nullable(); // attached document references
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'type', 'status']);
        });

        // ══════════════════════════════════════════════════════════════════
        // CASH MANAGEMENT
        // ══════════════════════════════════════════════════════════════════
        Schema::create('cash_positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->date('position_date');
            $table->string('currency', 3)->default('NGN');
            $table->decimal('opening_balance', 15, 2);
            $table->decimal('total_inflows', 15, 2)->default(0);
            $table->decimal('total_outflows', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2);
            $table->decimal('vault_cash', 15, 2)->default(0);
            $table->decimal('nostro_balance', 15, 2)->default(0);
            $table->json('breakdown')->nullable(); // {branch_cash: {}, atm_cash: {}, etc}
            $table->unsignedBigInteger('prepared_by')->nullable();
            $table->foreign('prepared_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['tenant_id', 'position_date', 'currency']);
        });

        // ══════════════════════════════════════════════════════════════════
        // WEALTH MANAGEMENT LITE
        // ══════════════════════════════════════════════════════════════════
        Schema::create('investment_portfolios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->string('portfolio_name', 150);
            $table->enum('risk_profile', ['conservative', 'moderate', 'aggressive'])->default('moderate');
            $table->decimal('total_value', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('unrealized_pnl', 15, 2)->default(0);
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->unsignedBigInteger('advisor_id')->nullable();
            $table->foreign('advisor_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'customer_id']);
        });

        Schema::create('investment_holdings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('portfolio_id');
            $table->foreign('portfolio_id')->references('id')->on('investment_portfolios')->cascadeOnDelete();
            $table->enum('asset_type', ['treasury_bill', 'bond', 'mutual_fund', 'equity', 'money_market', 'fixed_deposit']);
            $table->string('asset_name', 200);
            $table->string('asset_code', 50)->nullable();
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('cost_price', 15, 4);
            $table->decimal('current_price', 15, 4);
            $table->decimal('market_value', 15, 2)->default(0);
            $table->date('purchase_date');
            $table->date('maturity_date')->nullable();
            $table->decimal('yield_rate', 8, 4)->nullable();
            $table->enum('status', ['active', 'matured', 'sold'])->default('active');
            $table->timestamps();
            $table->index(['portfolio_id', 'asset_type']);
        });

        // ══════════════════════════════════════════════════════════════════
        // OPEN BANKING / API MARKETPLACE
        // ══════════════════════════════════════════════════════════════════
        Schema::create('api_clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('client_id', 64)->unique();
            $table->string('client_secret', 128);
            $table->string('webhook_url', 500)->nullable();
            $table->json('allowed_scopes')->nullable(); // ["accounts:read", "transactions:read", "transfers:write"]
            $table->json('ip_whitelist')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('rate_limit_per_minute')->default(60);
            $table->integer('total_requests')->default(0);
            $table->timestamp('last_request_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('api_clients')->cascadeOnDelete();
            $table->string('method', 10);
            $table->string('endpoint', 500);
            $table->integer('status_code');
            $table->integer('response_time_ms')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['client_id', 'created_at']);
        });

        // ══════════════════════════════════════════════════════════════════
        // ENHANCED RISK MANAGEMENT
        // ══════════════════════════════════════════════════════════════════
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->enum('risk_type', ['credit', 'liquidity', 'market', 'operational', 'concentration']);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->decimal('exposure_amount', 15, 2)->nullable();
            $table->json('metrics')->nullable(); // {ratio: 0.45, threshold: 0.5, breached: false}
            $table->enum('status', ['open', 'mitigated', 'accepted', 'closed'])->default('open');
            $table->text('mitigation_plan')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'risk_type', 'status']);
        });

        Schema::create('risk_limits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('limit_type', 50); // single_obligor, sector_concentration, currency_exposure, liquidity_ratio
            $table->string('name', 200);
            $table->decimal('limit_value', 15, 2);
            $table->decimal('current_value', 15, 2)->default(0);
            $table->decimal('utilization_pct', 6, 2)->default(0);
            $table->enum('status', ['within_limit', 'warning', 'breached'])->default('within_limit');
            $table->decimal('warning_threshold', 6, 2)->default(80); // % at which to warn
            $table->timestamps();
            $table->index(['tenant_id', 'limit_type']);
        });

        // ══════════════════════════════════════════════════════════════════
        // ENHANCED REGULATORY REPORTING
        // ══════════════════════════════════════════════════════════════════
        Schema::create('regulatory_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('report_type', 50); // cbn_returns, ndic_premium, nfiu_ctr, nfiu_str, prudential_guidelines
            $table->string('report_name', 200);
            $table->string('period', 20); // 2026-Q1, 2026-03, 2026
            $table->date('due_date');
            $table->date('submitted_date')->nullable();
            $table->enum('status', ['pending', 'draft', 'submitted', 'accepted', 'rejected'])->default('pending');
            $table->json('report_data')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('prepared_by')->nullable();
            $table->foreign('prepared_by')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'report_type', 'status']);
        });

        // ══════════════════════════════════════════════════════════════════
        // ENHANCED PROCESS AUTOMATION / BPM
        // ══════════════════════════════════════════════════════════════════
        Schema::create('bpm_processes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->enum('category', ['account_opening', 'loan_processing', 'kyc_verification', 'dispute_resolution', 'document_approval', 'custom']);
            $table->json('steps'); // [{name, type: "approval|task|notification|condition", config: {...}, next_step_id}]
            $table->boolean('is_active')->default(true);
            $table->integer('avg_completion_hours')->nullable();
            $table->integer('total_instances')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('bpm_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('process_id');
            $table->foreign('process_id')->references('id')->on('bpm_processes')->cascadeOnDelete();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('subject_type', 50)->nullable(); // customer, loan, account
            $table->string('subject_id', 50)->nullable();
            $table->integer('current_step')->default(0);
            $table->enum('status', ['active', 'completed', 'cancelled', 'on_hold'])->default('active');
            $table->json('step_history')->nullable(); // [{step, action, user_id, timestamp, notes}]
            $table->unsignedBigInteger('initiated_by');
            $table->foreign('initiated_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['process_id', 'status']);
            $table->index(['tenant_id', 'status']);
        });

        // ══════════════════════════════════════════════════════════════════
        // MOBILE BANKING API TOKENS
        // ══════════════════════════════════════════════════════════════════
        Schema::create('mobile_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->string('device_id', 200);
            $table->string('device_name', 200)->nullable();
            $table->enum('platform', ['ios', 'android', 'web']);
            $table->string('push_token', 500)->nullable();
            $table->string('app_version', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
            $table->unique(['customer_id', 'device_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_devices');
        Schema::dropIfExists('bpm_instances');
        Schema::dropIfExists('bpm_processes');
        Schema::dropIfExists('regulatory_reports');
        Schema::dropIfExists('risk_limits');
        Schema::dropIfExists('risk_assessments');
        Schema::dropIfExists('api_request_logs');
        Schema::dropIfExists('api_clients');
        Schema::dropIfExists('investment_holdings');
        Schema::dropIfExists('investment_portfolios');
        Schema::dropIfExists('cash_positions');
        Schema::dropIfExists('trade_finance_instruments');
        Schema::dropIfExists('treasury_fx_deals');
        Schema::dropIfExists('treasury_placements');
    }
};
