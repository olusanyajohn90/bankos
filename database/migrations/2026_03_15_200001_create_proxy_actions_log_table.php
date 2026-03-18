<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('proxy_actions_log')) {
            return;
        }
        Schema::create('proxy_actions_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->uuid('customer_id');
            $table->string('action');
            $table->json('payload')->nullable();
            $table->string('reason', 500);
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->foreign('actor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            $table->index(['tenant_id', 'customer_id']);
            $table->index('actor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proxy_actions_log');
    }
};
