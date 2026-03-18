<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('customer_id');
            $table->uuid('loan_id')->nullable();
            $table->string('policy_number')->unique();
            $table->string('provider')->default('leadway'); // insurer name
            $table->enum('product', ['credit_life', 'health', 'asset'])->default('credit_life');
            $table->decimal('sum_assured', 15, 2);
            $table->decimal('premium', 15, 2);
            $table->enum('premium_frequency', ['monthly', 'quarterly', 'annual', 'single'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'lapsed', 'claimed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_policies');
    }
};
