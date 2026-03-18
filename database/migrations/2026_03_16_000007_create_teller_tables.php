<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Teller sessions (daily drawer)
        Schema::create('teller_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->unsignedBigInteger('teller_id');
            $table->foreign('teller_id')->references('id')->on('users');
            $table->date('session_date');
            $table->decimal('opening_cash', 15, 2)->default(0);
            $table->decimal('cash_in', 15, 2)->default(0);     // deposits received
            $table->decimal('cash_out', 15, 2)->default(0);    // withdrawals paid
            $table->decimal('closing_cash', 15, 2)->nullable();
            $table->decimal('expected_closing', 15, 2)->nullable();
            $table->decimal('variance', 15, 2)->nullable();
            $table->enum('status', ['open', 'closed', 'balanced', 'unbalanced'])->default('open');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('supervised_by')->nullable();
            $table->foreign('supervised_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['teller_id', 'session_date']);
            $table->index(['tenant_id', 'session_date']);
            $table->index(['branch_id', 'session_date']);
        });

        // Vault (branch-level cash vault)
        Schema::create('vault_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->enum('entry_type', ['open_balance','deposit_to_vault','withdrawal_from_vault','teller_replenish','teller_return','adjustment']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('reference', 100)->nullable();
            $table->text('narration')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->foreign('performed_by')->references('id')->on('users')->nullOnDelete();
            $table->uuid('teller_session_id')->nullable();
            $table->foreign('teller_session_id')->references('id')->on('teller_sessions')->nullOnDelete();
            $table->timestamps();
            $table->index(['branch_id', 'created_at']);
        });

        // Denomination tracking for cash counts
        Schema::create('cash_counts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('teller_session_id');
            $table->foreign('teller_session_id')->references('id')->on('teller_sessions')->cascadeOnDelete();
            $table->enum('count_type', ['opening', 'closing']);
            $table->json('denominations'); // {1000:x, 500:x, 200:x, 100:x, 50:x, 20:x, 10:x, 5:x}
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('cash_counts');
        Schema::dropIfExists('vault_entries');
        Schema::dropIfExists('teller_sessions');
    }
};
