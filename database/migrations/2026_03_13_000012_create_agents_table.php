<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('branch_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // linked system user
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->string('bvn', 11)->nullable();
            $table->string('nin', 11)->nullable();
            $table->text('address')->nullable();
            $table->decimal('float_balance', 15, 2)->default(0);
            $table->decimal('daily_cash_in_limit', 15, 2)->default(500000);
            $table->decimal('daily_cash_out_limit', 15, 2)->default(200000);
            $table->decimal('daily_transfer_limit', 15, 2)->default(100000);
            $table->decimal('commission_rate', 5, 4)->default(0.005); // 0.5%
            $table->decimal('home_latitude', 10, 7)->nullable();
            $table->decimal('home_longitude', 10, 7)->nullable();
            $table->decimal('total_commission_earned', 15, 2)->default(0);
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
