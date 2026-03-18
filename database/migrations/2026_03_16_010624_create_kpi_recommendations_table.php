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
        Schema::create('kpi_recommendations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('kpi_definition_id');
            $table->enum('subject_type', ['individual', 'team', 'branch', 'tenant']);
            $table->uuid('subject_ref_id');
            $table->string('period_value', 10);
            $table->enum('period_type', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->decimal('achievement_pct', 8, 2)->nullable();
            $table->enum('severity', ['green', 'yellow', 'red', 'info'])->default('info');
            $table->enum('recommendation_type', [
                'below_target', 'improving', 'declining', 'consistent_underperformer',
                'top_performer', 'milestone_reached', 'custom'
            ])->default('custom');
            $table->string('title', 200);
            $table->text('body');
            $table->json('action_steps')->nullable();
            $table->boolean('is_system_generated')->default(true);
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->boolean('is_acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'subject_type', 'subject_ref_id', 'period_value'], 'kpi_recs_subject_period_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_recommendations');
    }
};
