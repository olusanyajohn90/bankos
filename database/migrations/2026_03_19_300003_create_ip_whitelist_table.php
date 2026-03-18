<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_ip_whitelist', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('ip_address', 45);
            $table->string('label', 100);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable()->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['tenant_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_ip_whitelist');
    }
};
