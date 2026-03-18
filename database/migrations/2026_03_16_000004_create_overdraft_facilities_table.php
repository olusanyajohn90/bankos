<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('overdraft_facilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('account_id');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->decimal('limit_amount', 15, 2);
            $table->decimal('used_amount', 15, 2)->default(0);
            $table->decimal('interest_rate', 6, 3); // annual %
            $table->decimal('accrued_interest', 15, 2)->default(0);
            $table->date('approved_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'suspended', 'expired', 'closed'])->default('active');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['account_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['expiry_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('overdraft_facilities'); }
};
