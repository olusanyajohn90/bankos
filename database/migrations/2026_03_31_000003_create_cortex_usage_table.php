<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cortex_usage', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('analysis_type', 50); // profile_review, loan_scoring, fraud_detection, churn_prediction, clv, recommendations, batch
            $table->string('engine', 20); // standard, extended
            $table->uuid('subject_id')->nullable(); // customer_id, loan_id, etc.
            $table->string('subject_type', 50)->nullable(); // customer, loan, portfolio
            $table->integer('tokens_used')->default(0);
            $table->decimal('cost', 8, 4)->default(0); // USD cost
            $table->decimal('charge', 10, 2)->default(0); // NGN charge to tenant
            $table->integer('response_time_ms')->nullable();
            $table->boolean('success')->default(true);
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['user_id']);
            $table->index(['engine']);
        });

        // Cortex pricing config per tenant
        Schema::create('cortex_pricing', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->unique();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->decimal('price_per_extended_call', 10, 2)->default(500); // NGN per Cortex Extended call
            $table->integer('free_monthly_calls')->default(10); // free extended calls per month
            $table->integer('monthly_call_limit')->default(100); // max extended calls per month
            $table->boolean('extended_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cortex_pricing');
        Schema::dropIfExists('cortex_usage');
    }
};
