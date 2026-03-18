<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fixed_deposits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('product_id');
            $table->foreign('product_id')->references('id')->on('fixed_deposit_products');
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->uuid('source_account_id');
            $table->foreign('source_account_id')->references('id')->on('accounts');
            $table->string('fd_number', 30)->unique();
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_rate', 6, 3);
            $table->unsignedSmallInteger('tenure_days');
            $table->date('start_date');
            $table->date('maturity_date');
            $table->decimal('expected_interest', 15, 2)->default(0);
            $table->decimal('accrued_interest', 15, 2)->default(0);
            $table->decimal('paid_interest', 15, 2)->default(0);
            $table->enum('status', ['active', 'matured', 'liquidated', 'rolled_over'])->default('active');
            $table->boolean('auto_rollover')->default(false);
            $table->timestamp('liquidated_at')->nullable();
            $table->decimal('liquidation_amount', 15, 2)->nullable();
            $table->decimal('penalty_amount', 15, 2)->nullable();
            $table->text('liquidation_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->uuid('branch_id')->nullable();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['maturity_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('fixed_deposits'); }
};
