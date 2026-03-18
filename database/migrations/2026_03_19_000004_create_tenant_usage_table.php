<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_usage', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('tenant_id');
            $table->string('period', 7); // e.g. '2026-03'
            $table->integer('customer_count')->default(0);
            $table->integer('staff_count')->default(0);
            $table->integer('branch_count')->default(0);
            $table->integer('transaction_count')->default(0);
            $table->integer('api_call_count')->default(0);
            $table->timestamp('recorded_at')->nullable();

            $table->unique(['tenant_id', 'period']);
            $table->foreign('tenant_id')->references('id')->on('tenants');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_usage');
    }
};
