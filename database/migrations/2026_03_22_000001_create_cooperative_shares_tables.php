<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('share_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name'); // e.g. "Ordinary Shares", "Preference Shares"
            $table->text('description')->nullable();
            $table->decimal('par_value', 15, 2); // price per share
            $table->integer('min_shares')->default(1);
            $table->integer('max_shares')->nullable();
            $table->decimal('dividend_rate', 8, 4)->nullable(); // annual % for preference shares
            $table->boolean('transferable')->default(false);
            $table->boolean('redeemable')->default(true);
            $table->string('status')->default('active'); // active, suspended, closed
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants');
        });

        Schema::create('member_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('customer_id');
            $table->uuid('share_product_id');
            $table->integer('quantity');
            $table->decimal('total_value', 15, 2);
            $table->string('certificate_number')->nullable();
            $table->date('purchase_date');
            $table->string('status')->default('active'); // active, redeemed, transferred
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('share_product_id')->references('id')->on('share_products');
        });

        Schema::create('share_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('customer_id');
            $table->uuid('share_product_id');
            $table->uuid('member_share_id')->nullable();
            $table->string('type'); // purchase, redemption, transfer_in, transfer_out, dividend
            $table->integer('quantity');
            $table->decimal('amount', 15, 2);
            $table->decimal('unit_price', 15, 2);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('completed');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('share_product_id')->references('id')->on('share_products');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_transactions');
        Schema::dropIfExists('member_shares');
        Schema::dropIfExists('share_products');
    }
};
