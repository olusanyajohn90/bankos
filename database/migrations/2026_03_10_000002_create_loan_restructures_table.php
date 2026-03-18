<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_restructures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('loan_id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->decimal('previous_outstanding', 15, 2);
            $table->integer('previous_tenure');
            $table->decimal('previous_rate', 8, 4);
            $table->integer('new_tenure');
            $table->decimal('new_rate', 8, 4);
            $table->text('reason');
            $table->text('officer_notes')->nullable();
            $table->uuid('requested_by');
            $table->unsignedBigInteger('reviewed_by')->nullable()->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_restructures');
    }
};
