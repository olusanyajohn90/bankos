<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. account_mandates
        Schema::create('account_mandates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('account_id');
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->enum('mandate_class', ['A', 'B', 'C', 'sole'])->default('sole');
            $table->enum('signing_rule', ['sole', 'any_one', 'any_two', 'a_and_b', 'a_and_any_b', 'all'])->default('sole');
            $table->tinyInteger('min_signatories')->unsigned()->default(1);
            $table->decimal('max_amount_sole', 15, 2)->nullable()->comment('Below this amount sole signing applies');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['account_id']);
            $table->index(['tenant_id']);
        });

        // 2. mandate_signatories
        Schema::create('mandate_signatories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mandate_id');
            $table->foreign('mandate_id')->references('id')->on('account_mandates')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('signatory_name', 150);
            $table->enum('signatory_class', ['A', 'B', 'C'])->default('A');
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['mandate_id']);
        });

        // 3. mandate_approvals
        Schema::create('mandate_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('account_id');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->uuid('mandate_id');
            $table->foreign('mandate_id')->references('id')->on('account_mandates');
            $table->string('description', 500);
            $table->decimal('amount', 15, 2);
            $table->string('reference', 100)->unique();
            $table->tinyInteger('required_approvals')->unsigned()->default(1);
            $table->tinyInteger('approvals_received')->unsigned()->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->foreign('requested_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['reference']);
        });

        // 4. mandate_approval_actions
        Schema::create('mandate_approval_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('approval_id');
            $table->foreign('approval_id')->references('id')->on('mandate_approvals')->cascadeOnDelete();
            $table->uuid('signatory_id')->nullable();
            $table->foreign('signatory_id')->references('id')->on('mandate_signatories')->nullOnDelete();
            $table->enum('action', ['approved', 'rejected']);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('actioned_by')->nullable();
            $table->foreign('actioned_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('actioned_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mandate_approval_actions');
        Schema::dropIfExists('mandate_approvals');
        Schema::dropIfExists('mandate_signatories');
        Schema::dropIfExists('account_mandates');
    }
};
