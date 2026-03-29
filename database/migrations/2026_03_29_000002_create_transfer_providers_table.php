<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 30);
            $table->string('provider_class', 255);
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('flat_fee', 10, 2)->default(0);
            $table->decimal('percentage_fee', 5, 4)->default(0);
            $table->decimal('fee_cap', 10, 2)->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_providers');
    }
};
