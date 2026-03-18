<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('currency', 3)->default('NGN');
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->enum('interest_frequency', ['monthly', 'quarterly', 'annually'])->default('monthly');
            $table->decimal('min_balance', 15, 2)->default(0);
            $table->decimal('min_opening', 15, 2)->default(0);
            $table->decimal('max_withdrawal_daily', 15, 2)->nullable();
            $table->integer('max_withdrawals_monthly')->nullable();
            $table->integer('lock_in_period')->default(0);
            $table->decimal('early_withdrawal_penalty', 5, 2)->default(0);
            $table->decimal('monthly_fee', 15, 2)->default(0);
            $table->decimal('min_balance_penalty', 15, 2)->default(0);
            $table->enum('product_type', ['standard', 'goal_based', 'fixed_deposit'])->default('standard');
            $table->decimal('goal_target', 15, 2)->nullable();
            $table->date('maturity_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_products');
    }
};
