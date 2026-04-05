<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ══════════════════════════════════════════════════════════════════
        // PHASE 1: Dynamic Risk Scoring, Screening, SAR/STR, Perpetual KYC
        // ══════════════════════════════════════════════════════════════════

        // Dynamic Customer Risk Scores (continuously updated)
        Schema::create('customer_risk_scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->decimal('overall_score', 5, 2)->default(0); // 0-100 (higher = riskier)
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical', 'pep'])->default('low');
            $table->json('score_breakdown')->nullable(); // {transaction: 20, kyc: 10, geography: 5, product: 15, behavior: 10}
            $table->json('risk_factors')->nullable(); // [{factor, weight, score, description}]
            $table->timestamp('last_assessed_at')->nullable();
            $table->string('assessed_by', 20)->default('system'); // system, manual, ai
            $table->text('ai_narrative')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'customer_id']);
            $table->index(['risk_level']);
        });

        // Real-time Transaction Screening Results
        Schema::create('transaction_screenings', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('transaction_id')->nullable();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->string('screening_type', 30); // sanctions, pep, adverse_media, threshold, pattern, velocity
            $table->enum('result', ['clear', 'match', 'potential_match', 'flagged']);
            $table->decimal('confidence', 5, 2)->default(0); // 0-100%
            $table->json('match_details')->nullable(); // {matched_name, list_source, match_score}
            $table->json('reason_codes')->nullable(); // explainable AI codes
            $table->enum('disposition', ['pending', 'true_positive', 'false_positive', 'escalated'])->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'result', 'disposition']);
            $table->index(['customer_id']);
        });

        // SAR/STR Auto-generated Reports
        Schema::create('suspicious_activity_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('report_type', 10); // SAR, STR, CTR
            $table->string('reference', 30)->unique();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->text('narrative'); // AI-generated narrative
            $table->json('transactions_involved')->nullable(); // transaction IDs
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('suspicion_category', 100)->nullable(); // structuring, layering, unusual_pattern, pep_activity
            $table->enum('status', ['draft', 'pending_review', 'approved', 'filed', 'rejected'])->default('draft');
            $table->string('filing_reference', 50)->nullable(); // NFIU reference after filing
            $table->unsignedBigInteger('prepared_by')->nullable();
            $table->foreign('prepared_by')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('filed_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        // Perpetual KYC Events (continuous monitoring triggers)
        Schema::create('perpetual_kyc_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->string('event_type', 50); // address_change, occupation_change, transaction_pattern_shift, document_expiry, adverse_media_hit, sanctions_hit, pep_status_change, risk_score_change
            $table->text('description');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->enum('action_required', ['none', 'review', 'enhanced_due_diligence', 'account_restriction', 'sar_filing'])->default('review');
            $table->enum('status', ['open', 'in_review', 'resolved', 'escalated'])->default('open');
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->foreign('resolved_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index(['customer_id']);
        });

        // ══════════════════════════════════════════════════════════════════
        // PHASE 2: Behavioral Analytics, Network Analysis, Beneficial Ownership
        // ══════════════════════════════════════════════════════════════════

        // Customer Behavioral Profiles (learned patterns)
        Schema::create('customer_behavior_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->json('transaction_patterns')->nullable(); // {avg_monthly_volume, avg_txn_size, peak_hours, preferred_channels, common_recipients}
            $table->json('baseline_metrics')->nullable(); // {avg_balance, income_estimate, expense_ratio, savings_rate}
            $table->json('anomaly_thresholds')->nullable(); // {volume_3sigma, size_3sigma, frequency_max}
            $table->integer('anomaly_count_30d')->default(0);
            $table->decimal('behavior_risk_score', 5, 2)->default(0);
            $table->timestamp('profile_computed_at')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'customer_id']);
        });

        // Network/Link Analysis (relationships between entities)
        Schema::create('entity_relationships', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('entity_a_id');
            $table->string('entity_a_type', 30); // customer, account, company
            $table->uuid('entity_b_id');
            $table->string('entity_b_type', 30);
            $table->string('relationship_type', 50); // transacts_with, guarantor_for, director_of, related_to, shares_address, shares_phone, shares_employer
            $table->decimal('strength', 5, 2)->default(0); // 0-100 based on transaction frequency/volume
            $table->integer('transaction_count')->default(0);
            $table->decimal('total_volume', 15, 2)->default(0);
            $table->boolean('is_suspicious')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'entity_a_id']);
            $table->index(['tenant_id', 'entity_b_id']);
            $table->index(['is_suspicious']);
        });

        // Beneficial Ownership
        Schema::create('beneficial_owners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('customer_id'); // the corporate customer
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->string('owner_name', 200);
            $table->string('nationality', 100)->nullable();
            $table->string('id_type', 50)->nullable();
            $table->string('id_number', 50)->nullable();
            $table->decimal('ownership_percentage', 5, 2);
            $table->boolean('is_pep')->default(false);
            $table->boolean('is_sanctioned')->default(false);
            $table->enum('verification_status', ['pending', 'verified', 'failed'])->default('pending');
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'customer_id']);
        });

        // Adverse Media Screening Results
        Schema::create('adverse_media_results', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->string('source', 200); // news outlet or database
            $table->string('headline', 500);
            $table->text('summary')->nullable();
            $table->string('url', 1000)->nullable();
            $table->date('published_date')->nullable();
            $table->string('category', 50); // fraud, corruption, money_laundering, terrorism, sanctions, crime
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->enum('disposition', ['pending', 'relevant', 'irrelevant', 'escalated'])->default('pending');
            $table->timestamps();
            $table->index(['customer_id', 'disposition']);
        });

        // ══════════════════════════════════════════════════════════════════
        // PHASE 3: Predictive Compliance, Reg Change, Scenarios, Chatbot
        // ══════════════════════════════════════════════════════════════════

        // Predictive Compliance Alerts
        Schema::create('predictive_compliance_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('alert_type', 50); // predicted_breach, trend_warning, anomaly_cluster, regulatory_risk
            $table->string('title', 300);
            $table->text('description');
            $table->json('prediction_data')->nullable(); // {metric, current_trend, predicted_value, predicted_date, confidence}
            $table->enum('severity', ['info', 'warning', 'critical']);
            $table->enum('status', ['active', 'acknowledged', 'resolved', 'false_alarm'])->default('active');
            $table->text('recommended_action')->nullable();
            $table->text('ai_analysis')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status', 'severity']);
        });

        // Regulatory Change Tracker
        Schema::create('regulatory_changes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('regulator', 100); // CBN, NDIC, NFIU, SEC, FCCPC
            $table->string('title', 500);
            $table->text('summary');
            $table->text('full_text')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->date('effective_date')->nullable();
            $table->date('published_date')->nullable();
            $table->enum('impact_level', ['low', 'medium', 'high', 'critical']);
            $table->json('affected_areas')->nullable(); // ["kyc", "aml", "capital", "reporting"]
            $table->enum('status', ['new', 'under_review', 'impact_assessed', 'implemented', 'not_applicable'])->default('new');
            $table->text('implementation_plan')->nullable();
            $table->json('affected_controls')->nullable(); // control IDs that need updating
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        // Compliance Scenario Tests
        Schema::create('compliance_scenarios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->enum('category', ['aml', 'fraud', 'sanctions', 'kyc', 'regulatory', 'stress_test']);
            $table->json('test_config'); // {type: "transaction_simulation", params: {...}}
            $table->json('expected_outcome')->nullable();
            $table->json('actual_outcome')->nullable();
            $table->enum('result', ['not_run', 'passed', 'failed', 'partial'])->default('not_run');
            $table->timestamp('last_run_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Compliance Chatbot Conversations
        Schema::create('compliance_chat_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->json('messages'); // [{role: "user"|"assistant", content, timestamp}]
            $table->string('topic', 200)->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════════════════
        // PHASE 4: Autonomous Agents, Cross-border, Consortium, Digital Twins
        // ══════════════════════════════════════════════════════════════════

        // Autonomous Compliance Agent Tasks
        Schema::create('compliance_agent_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('agent_type', 50); // kyc_refresher, sanctions_scanner, risk_scorer, report_filer, evidence_collector
            $table->string('description', 500);
            $table->json('config')->nullable();
            $table->enum('status', ['queued', 'running', 'completed', 'failed'])->default('queued');
            $table->json('result')->nullable();
            $table->integer('items_processed')->default(0);
            $table->integer('issues_found')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        // Cross-border Compliance Rules
        Schema::create('cross_border_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('country_code', 3);
            $table->string('country_name', 100);
            $table->json('requirements')->nullable(); // {reporting_threshold, restricted_entities, required_documents}
            $table->json('restrictions')->nullable(); // {max_transfer, prohibited_purposes, sanctions_programs}
            $table->enum('risk_category', ['low', 'medium', 'high', 'prohibited'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'country_code']);
        });

        // Regulatory Digital Twin Simulations
        Schema::create('regulatory_simulations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->json('scenario_params'); // {regulation_change: "increase_car_to_15%", impact_on: ["capital", "lending"]}
            $table->json('baseline_metrics')->nullable(); // current state before simulation
            $table->json('simulated_metrics')->nullable(); // projected state after regulation change
            $table->json('impact_analysis')->nullable(); // {affected_products, capital_gap, timeline, cost}
            $table->text('ai_recommendation')->nullable();
            $table->enum('status', ['draft', 'running', 'completed'])->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regulatory_simulations');
        Schema::dropIfExists('cross_border_rules');
        Schema::dropIfExists('compliance_agent_tasks');
        Schema::dropIfExists('compliance_chat_sessions');
        Schema::dropIfExists('compliance_scenarios');
        Schema::dropIfExists('regulatory_changes');
        Schema::dropIfExists('predictive_compliance_alerts');
        Schema::dropIfExists('adverse_media_results');
        Schema::dropIfExists('beneficial_owners');
        Schema::dropIfExists('entity_relationships');
        Schema::dropIfExists('customer_behavior_profiles');
        Schema::dropIfExists('perpetual_kyc_events');
        Schema::dropIfExists('suspicious_activity_reports');
        Schema::dropIfExists('transaction_screenings');
        Schema::dropIfExists('customer_risk_scores');
    }
};
