<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contribution_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name'); // e.g. "Monthly Dues", "Building Levy", "Welfare Fund"
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('frequency'); // monthly, weekly, quarterly, annual, one_time
            $table->boolean('mandatory')->default(true);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants');
        });

        Schema::create('member_contributions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('customer_id');
            $table->uuid('contribution_schedule_id');
            $table->decimal('amount', 15, 2);
            $table->string('period'); // e.g. "2026-03", "2026-Q1", "2026"
            $table->string('payment_method')->default('cash'); // cash, transfer, deduction
            $table->string('reference')->nullable();
            $table->string('status')->default('paid'); // paid, pending, waived, refunded
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('contribution_schedule_id')->references('id')->on('contribution_schedules');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_contributions');
        Schema::dropIfExists('contribution_schedules');
    }
};
