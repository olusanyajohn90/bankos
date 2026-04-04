<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Compliance Frameworks ─────────────────────────────────────────
        Schema::create('compliance_frameworks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 100); // CBN MFB Guidelines, NDIC Regulations, NFIU AML/CFT, NDPR, BOFIA
            $table->string('code', 30); // cbn_mfb, ndic, nfiu, ndpr, bofia
            $table->text('description')->nullable();
            $table->integer('total_controls')->default(0);
            $table->integer('compliant_controls')->default(0);
            $table->integer('non_compliant_controls')->default(0);
            $table->integer('not_assessed_controls')->default(0);
            $table->decimal('compliance_score', 5, 2)->default(0); // 0-100%
            $table->timestamp('last_assessed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
        });

        // ── Compliance Controls ───────────────────────────────────────────
        Schema::create('compliance_controls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('framework_id');
            $table->foreign('framework_id')->references('id')->on('compliance_frameworks')->cascadeOnDelete();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('control_ref', 30); // CBN-001, NDIC-002
            $table->string('title', 300);
            $table->text('description')->nullable();
            $table->string('category', 100); // capital_adequacy, kyc_aml, reporting, governance, it_security
            $table->enum('status', ['compliant', 'non_compliant', 'partial', 'not_assessed'])->default('not_assessed');
            $table->text('evidence_notes')->nullable();
            $table->json('evidence_files')->nullable(); // [{file_path, uploaded_at, type}]
            $table->json('auto_check_config')->nullable(); // {type: "query", query: "...", threshold: ...}
            $table->timestamp('last_checked_at')->nullable();
            $table->text('remediation_plan')->nullable();
            $table->date('remediation_due')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->integer('priority')->default(0); // 1=critical, 2=high, 3=medium, 4=low
            $table->timestamps();
            $table->index(['framework_id', 'status']);
            $table->index(['tenant_id', 'category']);
        });

        // ── Compliance Evidence ────────────────────────────────────────────
        Schema::create('compliance_evidence', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('control_id');
            $table->foreign('control_id')->references('id')->on('compliance_controls')->cascadeOnDelete();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->enum('type', ['screenshot', 'document', 'query_result', 'api_response', 'manual_note', 'system_log']);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->json('data')->nullable(); // for query results, metrics
            $table->boolean('is_auto_collected')->default(false);
            $table->unsignedBigInteger('collected_by')->nullable();
            $table->foreign('collected_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('collected_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['control_id', 'type']);
        });

        // ── Continuous Monitoring Checks ───────────────────────────────────
        Schema::create('compliance_monitors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('check_type', 50); // capital_adequacy, liquidity_ratio, single_obligor, kyc_completion, aml_screening, transaction_limits, dormancy_check
            $table->json('config'); // {query, threshold, operator: ">", alert_on_breach: true}
            $table->enum('frequency', ['realtime', 'hourly', 'daily', 'weekly', 'monthly'])->default('daily');
            $table->decimal('current_value', 15, 4)->nullable();
            $table->decimal('threshold_value', 15, 4)->nullable();
            $table->enum('status', ['passing', 'warning', 'failing', 'not_checked'])->default('not_checked');
            $table->timestamp('last_checked_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('control_id')->nullable();
            $table->foreign('control_id')->references('id')->on('compliance_controls')->nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        // ── Compliance Audit Trail ────────────────────────────────────────
        Schema::create('compliance_audit_trail', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('event_type', 50); // control_updated, evidence_added, assessment_run, report_generated, breach_detected
            $table->string('entity_type', 50)->nullable();
            $table->string('entity_id', 50)->nullable();
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'event_type', 'created_at']);
        });

        // ── Trust Report (public compliance page) ─────────────────────────
        Schema::create('compliance_trust_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->unique();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('public_url_token', 64)->unique(); // for sharing
            $table->boolean('is_published')->default(false);
            $table->json('visible_frameworks')->nullable(); // which frameworks to show
            $table->json('custom_sections')->nullable(); // additional content
            $table->string('logo_path', 500)->nullable();
            $table->text('intro_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_trust_reports');
        Schema::dropIfExists('compliance_audit_trail');
        Schema::dropIfExists('compliance_monitors');
        Schema::dropIfExists('compliance_evidence');
        Schema::dropIfExists('compliance_controls');
        Schema::dropIfExists('compliance_frameworks');
    }
};
