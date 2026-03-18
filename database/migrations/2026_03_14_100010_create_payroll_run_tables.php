<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->tinyInteger('period_month')->unsigned();
            $table->smallInteger('period_year')->unsigned();
            $table->enum('status', ['draft', 'processing', 'approved', 'paid', 'cancelled'])->default('draft');
            $table->decimal('total_gross', 20, 2)->default(0);
            $table->decimal('total_deductions', 20, 2)->default(0);
            $table->decimal('total_net', 20, 2)->default(0);
            $table->decimal('total_paye', 20, 2)->default(0);
            $table->decimal('total_pension_employee', 20, 2)->default(0);
            $table->decimal('total_pension_employer', 20, 2)->default(0);
            $table->decimal('total_nhf', 20, 2)->default(0);
            $table->decimal('total_nsitf', 20, 2)->default(0);
            $table->integer('staff_count')->default(0);
            $table->unsignedBigInteger('run_by');
            $table->foreign('run_by')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'period_month', 'period_year']);
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('payroll_run_id');
            $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->cascadeOnDelete();
            $table->uuid('staff_profile_id');
            $table->foreign('staff_profile_id')->references('id')->on('staff_profiles')->cascadeOnDelete();
            $table->decimal('gross_salary', 15, 2);
            $table->decimal('taxable_income', 15, 2);
            $table->decimal('total_deductions', 15, 2);
            $table->decimal('paye', 15, 2)->default(0);
            $table->decimal('employee_pension', 15, 2)->default(0);
            $table->decimal('employer_pension', 15, 2)->default(0);
            $table->decimal('nhf', 15, 2)->default(0);
            $table->decimal('nsitf', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2);
            $table->uuid('bank_detail_id')->nullable();
            $table->foreign('bank_detail_id')->references('id')->on('staff_bank_details')->nullOnDelete();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->timestamp('payment_date')->nullable();
            $table->timestamps();
            $table->unique(['payroll_run_id', 'staff_profile_id']);
            $table->index(['payroll_run_id', 'payment_status']);
        });

        Schema::create('payroll_item_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('payroll_item_id');
            $table->foreign('payroll_item_id')->references('id')->on('payroll_items')->cascadeOnDelete();
            $table->uuid('pay_component_id')->nullable();
            $table->foreign('pay_component_id')->references('id')->on('pay_components')->nullOnDelete();
            $table->string('component_name', 100);
            $table->enum('component_type', ['earning', 'deduction']);
            $table->boolean('is_statutory')->default(false);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
            $table->index(['payroll_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_item_lines');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_runs');
    }
};
