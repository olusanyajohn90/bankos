<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_revaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('fixed_asset_id');
            $table->decimal('previous_book_value', 15, 2);
            $table->decimal('new_book_value', 15, 2);
            $table->decimal('revaluation_amount', 15, 2);
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('revalued_by')->nullable();
            $table->timestamp('revalued_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('fixed_asset_id')->references('id')->on('fixed_assets')->onDelete('cascade');
            $table->foreign('revalued_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['fixed_asset_id', 'revalued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_asset_revaluations');
    }
};
