<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dividend_declarations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('title'); // e.g. "2025 Annual Dividend"
            $table->string('financial_year'); // e.g. "2025"
            $table->decimal('total_surplus', 15, 2); // total profit to distribute
            $table->decimal('dividend_rate', 8, 4); // % per share
            $table->decimal('total_distributed', 15, 2)->default(0);
            $table->integer('eligible_members')->default(0);
            $table->date('declaration_date');
            $table->date('payment_date')->nullable();
            $table->string('status')->default('draft'); // draft, approved, processing, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants');
        });

        Schema::create('dividend_payouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('dividend_declaration_id');
            $table->uuid('customer_id');
            $table->integer('shares_held');
            $table->decimal('amount', 15, 2);
            $table->uuid('account_id')->nullable(); // account credited
            $table->string('status')->default('pending'); // pending, paid, failed
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->foreign('dividend_declaration_id')->references('id')->on('dividend_declarations');
            $table->foreign('customer_id')->references('id')->on('customers');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dividend_payouts');
        Schema::dropIfExists('dividend_declarations');
    }
};
