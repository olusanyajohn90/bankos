<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_liquidations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('loan_id');
            $table->enum('type', ['partial', 'full'])->default('partial');
            $table->decimal('gross_amount', 15, 2);        // Amount before discount
            $table->decimal('discount_amount', 15, 2)->default(0); // Early settlement rebate
            $table->decimal('net_amount', 15, 2);          // Actual collected
            $table->string('reference')->unique();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamps();

            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_liquidations');
    }
};
