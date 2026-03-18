<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fixed_deposit_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 30)->nullable();
            $table->text('description')->nullable();
            $table->decimal('interest_rate', 6, 3); // annual rate %
            $table->enum('interest_payment', ['on_maturity', 'monthly', 'quarterly'])->default('on_maturity');
            $table->unsignedSmallInteger('min_tenure_days')->default(30);
            $table->unsignedSmallInteger('max_tenure_days')->default(365);
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->decimal('early_liquidation_penalty', 5, 2)->default(0); // % of interest forfeited
            $table->boolean('allow_top_up')->default(false);
            $table->boolean('allow_early_liquidation')->default(true);
            $table->boolean('auto_rollover')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('fixed_deposit_products'); }
};
