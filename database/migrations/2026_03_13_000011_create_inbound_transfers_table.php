<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbound_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('account_id')->nullable(); // resolved destination account
            $table->string('session_id')->unique(); // NIBSS/switch session ID
            $table->string('sender_name')->nullable();
            $table->string('sender_account')->nullable();
            $table->string('sender_bank')->nullable();
            $table->string('destination_account'); // account number at our institution
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('NGN');
            $table->enum('channel', ['branch', 'mobile', 'pos', 'internet_banking', 'nibss', 'other'])->default('nibss');
            $table->string('narration')->nullable();
            $table->string('source')->nullable(); // payment switch name
            $table->enum('posting_type', ['manual', 'auto'])->default('auto');
            $table->enum('status', ['pending', 'posted', 'failed', 'reversed'])->default('pending');
            $table->uuid('transaction_id')->nullable(); // FK once posted
            $table->text('raw_payload')->nullable(); // original webhook JSON
            $table->timestamp('received_at');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_transfers');
    }
};
