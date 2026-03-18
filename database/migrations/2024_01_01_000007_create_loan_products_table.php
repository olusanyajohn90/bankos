<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('currency', 3)->default('NGN');
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->enum('interest_method', ['reducing_balance', 'flat'])->default('reducing_balance');
            $table->enum('amortization', ['equal_installment', 'bullet', 'balloon'])->default('equal_installment');
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('max_amount', 15, 2)->default(0);
            $table->integer('min_tenure')->default(1);
            $table->integer('max_tenure')->default(365);
            $table->decimal('max_dti', 5, 2)->default(0.40);
            $table->decimal('processing_fee', 5, 2)->default(0);
            $table->decimal('insurance_fee', 5, 2)->default(0);
            $table->integer('grace_period')->default(0);
            $table->boolean('group_lending')->default(false);
            $table->boolean('ai_assessment')->default(false);
            $table->boolean('early_repayment')->default(true);
            $table->decimal('early_repayment_penalty', 5, 2)->default(0);
            $table->json('collateral_types')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_products');
    }
};
