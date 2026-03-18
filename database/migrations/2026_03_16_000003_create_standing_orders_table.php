<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('standing_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('source_account_id');
            $table->foreign('source_account_id')->references('id')->on('accounts');
            $table->string('beneficiary_account_number', 20)->nullable();
            $table->string('beneficiary_bank_code', 10)->nullable();
            $table->string('beneficiary_name', 150)->nullable();
            $table->uuid('internal_dest_account_id')->nullable();
            $table->foreign('internal_dest_account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->enum('transfer_type', ['internal', 'external'])->default('internal');
            $table->decimal('amount', 15, 2);
            $table->string('narration', 255)->nullable();
            $table->enum('frequency', ['daily','weekly','monthly','quarterly','yearly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_run_date');
            $table->unsignedSmallInteger('max_runs')->nullable();
            $table->unsignedSmallInteger('runs_completed')->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->enum('status', ['active','paused','completed','cancelled','failed'])->default('active');
            $table->text('last_failure_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'status', 'next_run_date']);
            $table->index(['source_account_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('standing_orders'); }
};
