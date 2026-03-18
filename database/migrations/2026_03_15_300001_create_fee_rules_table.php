<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->string('name', 100);
            $table->string('transaction_type', 50); // transfer, withdrawal, bill_payment, airtime, loan_repayment, fee, deposit
            $table->string('account_type', 30)->nullable(); // savings, current, domiciliary, kids — null = all types

            $table->enum('fee_type', ['flat', 'percentage']);
            $table->decimal('amount', 15, 2)->default(0.00); // flat amount OR percentage value (e.g. 1.5 = 1.5%)

            $table->decimal('min_fee', 15, 2)->nullable();   // percentage: minimum fee floor
            $table->decimal('max_fee', 15, 2)->nullable();   // percentage: maximum fee cap

            $table->decimal('min_transaction_amount', 15, 2)->nullable(); // apply fee only when txn >= this
            $table->decimal('max_transaction_amount', 15, 2)->nullable(); // apply fee only when txn <= this (null = unlimited)

            $table->boolean('is_active')->default(true);
            $table->boolean('waivable')->default(true); // can this fee be waived by admin?

            $table->timestamps();

            $table->index(['tenant_id', 'transaction_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_rules');
    }
};
