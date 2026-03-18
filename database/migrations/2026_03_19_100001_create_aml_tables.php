<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // AML Alerts
        Schema::create('aml_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->enum('alert_type', [
                'velocity', 'large_amount', 'structuring', 'sanctions_match',
                'pep_match', 'unusual_pattern', 'round_amount', 'rapid_movement'
            ]);
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['open', 'under_review', 'escalated', 'dismissed', 'reported'])->default('open');
            $table->enum('entity_type', ['customer', 'transaction', 'account']);
            $table->uuid('entity_id');
            $table->uuid('customer_id')->nullable();
            $table->uuid('transaction_id')->nullable();
            $table->uuid('account_id')->nullable();
            $table->tinyInteger('score')->unsigned()->default(0);
            $table->json('details')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable()->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable()->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'created_at']);
        });

        // Sanctions List
        Schema::create('sanctions_list', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('list_source', ['OFAC', 'UN', 'EU', 'CBN', 'CUSTOM']);
            $table->enum('entity_type', ['individual', 'entity', 'vessel', 'aircraft']);
            $table->string('full_name', 200);
            $table->json('aliases')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->json('id_numbers')->nullable();
            $table->json('programs')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('last_updated');

            $table->index('full_name');
            $table->index(['list_source', 'is_active']);
        });

        // AML Rules
        Schema::create('aml_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('rule_code', 30);
            $table->string('rule_name', 100);
            $table->enum('rule_type', [
                'velocity', 'amount_threshold', 'structuring',
                'round_amount', 'dormancy_reactivation'
            ]);
            $table->boolean('is_active')->default(true);
            $table->decimal('threshold_amount', 15, 2)->nullable();
            $table->integer('threshold_count')->nullable();
            $table->integer('time_window_hours')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->boolean('auto_block')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id', 'rule_code']);
        });

        // Transaction Limits
        Schema::create('transaction_limits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->enum('kyc_tier', ['level_1', 'level_2', 'level_3']);
            $table->enum('channel', ['portal', 'api', 'ussd', 'agent', 'teller', 'all']);
            $table->enum('transaction_type', ['transfer', 'withdrawal', 'bill_payment', 'airtime', 'all']);
            $table->decimal('single_limit', 15, 2);
            $table->decimal('daily_limit', 15, 2);
            $table->decimal('monthly_limit', 15, 2)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id', 'kyc_tier', 'channel', 'transaction_type'], 'txn_limits_unique');
        });

        // Suspicious Transaction Reports (STR)
        Schema::create('suspicious_transaction_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('report_number', 20);
            $table->unsignedBigInteger('reporting_officer')->nullable();
            $table->uuid('customer_id');
            $table->json('transaction_ids');
            $table->json('alert_ids');
            $table->text('summary');
            $table->enum('status', ['draft', 'submitted', 'acknowledged'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->string('nfiu_reference', 50)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('reporting_officer')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suspicious_transaction_reports');
        Schema::dropIfExists('transaction_limits');
        Schema::dropIfExists('aml_rules');
        Schema::dropIfExists('sanctions_list');
        Schema::dropIfExists('aml_alerts');
    }
};
