<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('customer_id');
            $table->uuid('account_id')->nullable();
            $table->uuid('product_id');
            $table->uuid('group_id')->nullable();
            $table->string('loan_number')->nullable();
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('outstanding_balance', 15, 2)->default(0);
            $table->decimal('interest_rate', 6, 2);
            $table->enum('interest_method', ['reducing_balance', 'flat'])->default('reducing_balance');
            $table->enum('amortization', ['equal_installment', 'bullet', 'balloon'])->default('equal_installment');
            $table->integer('tenure_days');
            $table->enum('repayment_frequency', ['monthly', 'weekly', 'daily'])->default('monthly');
            $table->text('purpose')->nullable();
            $table->enum('source_channel', ['web', 'mobile', 'branch', 'ussd', 'agent'])->default('web');
            $table->text('collateral_desc')->nullable();
            $table->decimal('collateral_value', 15, 2)->nullable();
            $table->integer('ai_credit_score')->nullable();
            $table->uuid('bureau_report_id')->nullable();
            $table->enum('ifrs9_stage', ['stage_1', 'stage_2', 'stage_3'])->default('stage_1');
            $table->decimal('ecl_provision', 15, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'active', 'overdue', 'closed', 'written_off', 'rejected'])->default('pending');
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('product_id')->references('id')->on('loan_products')->onDelete('cascade');
            $table->unique(['tenant_id', 'loan_number']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
