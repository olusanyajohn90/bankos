<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('customer_number')->nullable();
            $table->enum('type', ['individual', 'corporate'])->default('individual');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('occupation')->nullable();
            $table->string('marital_status')->nullable();
            $table->json('address')->nullable();
            $table->string('bvn', 64)->nullable();
            $table->string('nin', 64)->nullable();
            $table->boolean('bvn_verified')->default(false);
            $table->boolean('nin_verified')->default(false);
            $table->enum('kyc_tier', ['level_1', 'level_2', 'level_3'])->default('level_1');
            $table->enum('kyc_status', ['auto_approved', 'manual_review', 'approved', 'rejected'])->default('manual_review');
            $table->enum('status', ['active', 'inactive', 'pending', 'suspended'])->default('pending');
            $table->boolean('portal_active')->default(false);
            $table->string('referral_code', 20)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id', 'customer_number']);
            $table->unique(['tenant_id', 'email']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
