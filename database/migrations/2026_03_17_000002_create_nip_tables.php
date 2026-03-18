<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Bank List ──────────────────────────────────────────────────────────
        Schema::create('bank_list', function (Blueprint $table) {
            $table->id();
            $table->string('cbn_code', 10)->unique();
            $table->string('bank_name', 150);
            $table->string('nibss_code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_microfinance')->default(false);
            $table->timestamps();
        });

        // ── NIP Outward Transfers ──────────────────────────────────────────────
        Schema::create('nip_outward_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('initiated_by')->nullable();
            $table->uuid('source_account_id')->nullable();

            $table->string('session_id', 40)->unique();
            $table->string('name_enquiry_ref', 40)->nullable();

            // Sender
            $table->string('sender_account_number', 20);
            $table->string('sender_account_name', 150);
            $table->string('sender_bank_code', 10);

            // Beneficiary
            $table->string('beneficiary_account_number', 20);
            $table->string('beneficiary_account_name', 150)->nullable();
            $table->string('beneficiary_bank_code', 10);
            $table->string('beneficiary_bank_name', 100)->nullable();

            $table->decimal('amount', 15, 2);
            $table->string('narration', 255)->nullable();

            $table->enum('status', [
                'pending',
                'name_enquiry',
                'initiated',
                'successful',
                'failed',
                'reversed',
            ])->default('pending');

            $table->string('nibss_response_code', 10)->nullable();
            $table->string('nibss_session_id', 40)->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('tenant_id')
                  ->references('id')->on('tenants')
                  ->cascadeOnDelete();

            $table->foreign('initiated_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();

            $table->foreign('source_account_id')
                  ->references('id')->on('accounts')
                  ->nullOnDelete();

            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nip_outward_transfers');
        Schema::dropIfExists('bank_list');
    }
};
