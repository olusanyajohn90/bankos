<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 100);
            $table->enum('depreciation_method', ['straight_line', 'declining_balance'])->default('straight_line');
            $table->unsignedInteger('useful_life_years');
            $table->decimal('residual_rate', 5, 2)->default(0.00); // % of cost
            $table->string('gl_asset_code', 20)->nullable();
            $table->string('gl_depreciation_code', 20)->nullable();
            $table->timestamps();

            $table->index(['tenant_id']);
        });

        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('fixed_asset_categories')->nullOnDelete();
            $table->string('name', 150);
            $table->string('asset_tag', 50)->nullable();
            $table->text('description')->nullable();
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 15, 2);
            $table->decimal('current_book_value', 15, 2);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->enum('depreciation_method', ['straight_line', 'declining_balance']);
            $table->unsignedInteger('useful_life_years');
            $table->decimal('residual_value', 15, 2)->default(0);
            $table->date('last_depreciation_date')->nullable();
            $table->enum('status', ['active', 'disposed', 'fully_depreciated'])->default('active');
            $table->date('disposed_at')->nullable();
            $table->decimal('disposal_value', 15, 2)->nullable();
            $table->text('disposal_notes')->nullable();
            $table->uuid('branch_id')->nullable();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->unsignedBigInteger('purchased_by')->nullable();
            $table->foreign('purchased_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('fixed_asset_categories');
    }
};
