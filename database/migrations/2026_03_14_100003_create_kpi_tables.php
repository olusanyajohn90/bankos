<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // KPI Definitions — master catalog
        Schema::create('kpi_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable(); // null = system/global; set = tenant-custom
            $table->string('code', 60)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', [
                'business_development',
                'credit_lending',
                'operations',
                'customer_service',
                'branch',
            ]);
            $table->string('unit', 30);  // count, ngn, percent, days, score
            $table->enum('direction', ['higher_better', 'lower_better', 'target_exact'])->default('higher_better');
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->json('department_applicable')->nullable(); // ['credit','operations'] or null = all
            $table->enum('computation_type', ['auto', 'manual'])->default('auto');
            $table->string('auto_compute_method', 60)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->index(['tenant_id', 'is_active']);
        });

        // KPI Targets — set by HQ per subject/period
        Schema::create('kpi_targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('kpi_id');
            $table->enum('target_type', ['individual', 'team', 'branch', 'department', 'tenant']);
            $table->uuid('target_ref_id')->nullable();
            $table->string('target_ref_type', 30)->nullable(); // user, team, branch
            $table->string('department', 50)->nullable();
            $table->enum('period_type', ['monthly', 'quarterly', 'yearly']);
            $table->string('period_value', 10);   // '2025-03', '2025-Q1', '2025'
            $table->decimal('target_value', 20, 4);
            $table->unsignedTinyInteger('alert_threshold_pct')->default(70);
            $table->unsignedBigInteger('set_by'); // FK → users.id (bigint)
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('kpi_id')->references('id')->on('kpi_definitions')->onDelete('cascade');
            $table->foreign('set_by')->references('id')->on('users')->onDelete('cascade');
            $table->unique(
                ['tenant_id', 'kpi_id', 'target_type', 'target_ref_id', 'period_type', 'period_value'],
                'kpi_targets_unique'
            );
            $table->index(['tenant_id', 'period_type', 'period_value']);
            $table->index(['tenant_id', 'target_type', 'target_ref_id']);
        });

        // KPI Actuals — computed or manually entered values
        Schema::create('kpi_actuals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('kpi_id');
            $table->enum('subject_type', ['individual', 'team', 'branch', 'department', 'tenant']);
            $table->uuid('subject_ref_id')->nullable();
            $table->string('subject_ref_type', 30)->nullable();
            $table->string('department', 50)->nullable();
            $table->enum('period_type', ['monthly', 'quarterly', 'yearly']);
            $table->string('period_value', 10);
            $table->decimal('value', 20, 4)->default(0);
            $table->enum('source', ['auto', 'manual'])->default('auto');
            $table->unsignedBigInteger('entered_by')->nullable(); // FK → users.id (bigint)
            $table->timestamp('computed_at')->nullable();
            $table->text('computation_notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('kpi_id')->references('id')->on('kpi_definitions')->onDelete('cascade');
            $table->foreign('entered_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(
                ['tenant_id', 'kpi_id', 'subject_type', 'subject_ref_id', 'period_type', 'period_value'],
                'kpi_actuals_unique'
            );
            $table->index(['tenant_id', 'period_type', 'period_value']);
            $table->index(['tenant_id', 'subject_type', 'subject_ref_id']);
        });

        // KPI Notes — manager comments on staff/team KPIs
        Schema::create('kpi_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('author_id'); // FK → users.id (bigint)
            $table->string('subject_type', 30); // user, team, branch
            $table->uuid('subject_id');
            $table->uuid('kpi_id')->nullable();
            $table->string('period_value', 10)->nullable();
            $table->text('body');
            $table->boolean('is_alert')->default(false);
            $table->boolean('is_private')->default(false);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('kpi_id')->references('id')->on('kpi_definitions')->nullOnDelete();
            $table->index(['tenant_id', 'subject_type', 'subject_id']);
            $table->index(['tenant_id', 'author_id']);
        });

        // KPI Alerts — triggered when actual falls below threshold
        Schema::create('kpi_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('kpi_target_id');
            $table->uuid('kpi_actual_id')->nullable();
            $table->unsignedBigInteger('recipient_id'); // FK → users.id (bigint)
            $table->enum('severity', ['green', 'yellow', 'red']);
            $table->decimal('achievement_pct', 8, 2);
            $table->decimal('target_value', 20, 4);
            $table->decimal('actual_value', 20, 4);
            $table->string('period_value', 10);
            $table->enum('status', ['unread', 'read', 'dismissed'])->default('unread');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->uuid('note_id')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('kpi_target_id')->references('id')->on('kpi_targets')->onDelete('cascade');
            $table->foreign('kpi_actual_id')->references('id')->on('kpi_actuals')->nullOnDelete();
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('note_id')->references('id')->on('kpi_notes')->nullOnDelete();
            $table->index(['tenant_id', 'recipient_id', 'status']);
            $table->index(['tenant_id', 'severity', 'status']);
            $table->index(['period_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_alerts');
        Schema::dropIfExists('kpi_notes');
        Schema::dropIfExists('kpi_actuals');
        Schema::dropIfExists('kpi_targets');
        Schema::dropIfExists('kpi_definitions');
    }
};
