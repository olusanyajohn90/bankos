<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('customer_id');
            $table->string('account_number', 10)->nullable();
            $table->string('account_name');
            $table->enum('type', ['savings', 'current', 'loan', 'wallet'])->default('savings');
            $table->string('currency', 3)->default('NGN');
            $table->decimal('available_balance', 15, 2)->default(0);
            $table->decimal('ledger_balance', 15, 2)->default(0);
            $table->uuid('savings_product_id')->nullable();
            $table->enum('status', ['active', 'dormant', 'closed'])->default('active');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('savings_product_id')->references('id')->on('savings_products')->nullOnDelete();
            $table->unique(['tenant_id', 'account_number']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
