<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pay_grades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('code', 10);
            $table->string('name');
            $table->tinyInteger('level')->unsigned();
            $table->decimal('basic_min', 15, 2);
            $table->decimal('basic_max', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('pay_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->enum('type', ['earning', 'deduction']);
            $table->boolean('is_statutory')->default(false);
            $table->boolean('is_taxable')->default(true);
            $table->enum('computation_type', ['fixed', 'percentage_of_basic', 'percentage_of_gross', 'formula']);
            $table->decimal('value', 10, 4)->nullable();
            $table->string('formula_key', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('staff_pay_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('staff_profile_id')->unique();
            $table->foreign('staff_profile_id')->references('id')->on('staff_profiles')->cascadeOnDelete();
            $table->uuid('pay_grade_id')->nullable();
            $table->foreign('pay_grade_id')->references('id')->on('pay_grades')->nullOnDelete();
            $table->decimal('basic_salary', 15, 2);
            $table->decimal('housing_allowance', 15, 2)->default(0);
            $table->decimal('transport_allowance', 15, 2)->default(0);
            $table->decimal('meal_allowance', 15, 2)->default(0);
            $table->json('other_allowances')->nullable();
            $table->string('pension_fund_administrator', 100)->nullable();
            $table->string('pension_account_number', 30)->nullable();
            $table->string('tax_id', 20)->nullable();
            $table->string('nhf_number', 20)->nullable();
            $table->date('effective_date');
            $table->timestamps();
        });

        Schema::create('staff_bank_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('staff_profile_id');
            $table->foreign('staff_profile_id')->references('id')->on('staff_profiles')->cascadeOnDelete();
            $table->string('bank_name', 100);
            $table->string('bank_code', 10);
            $table->string('account_number', 10);
            $table->string('account_name', 100);
            $table->boolean('is_primary')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->index(['staff_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_bank_details');
        Schema::dropIfExists('staff_pay_configs');
        Schema::dropIfExists('pay_components');
        Schema::dropIfExists('pay_grades');
    }
};
