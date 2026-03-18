<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('account_liens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('account_id');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->decimal('amount', 15, 2);
            $table->string('reason', 500);
            $table->enum('lien_type', ['loan_collateral','court_order','regulatory','internal'])->default('internal');
            $table->string('reference', 100)->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('placed_by')->nullable();
            $table->foreign('placed_by')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('lifted_by')->nullable();
            $table->foreign('lifted_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('lifted_at')->nullable();
            $table->timestamps();
            $table->index(['account_id', 'is_active']);
            $table->index(['tenant_id']);
        });

        // Add PND flag to accounts
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('pnd_active')->default(false)->after('status');
            $table->string('pnd_reason', 255)->nullable()->after('pnd_active');
            $table->unsignedBigInteger('pnd_placed_by')->nullable()->after('pnd_reason');
            $table->foreign('pnd_placed_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('pnd_placed_at')->nullable()->after('pnd_placed_by');
        });
    }
    public function down(): void {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['pnd_active','pnd_reason','pnd_placed_by','pnd_placed_at']);
        });
        Schema::dropIfExists('account_liens');
    }
};
