<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheque_books', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('account_id');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->string('series_start', 20);
            $table->string('series_end', 20);
            $table->unsignedInteger('leaves');
            $table->unsignedInteger('leaves_used')->default(0);
            $table->date('issued_date');
            $table->enum('status', ['active', 'exhausted', 'cancelled'])->default('active');
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->foreign('issued_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'account_id']);
        });

        Schema::create('cheque_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('cheque_book_id');
            $table->foreign('cheque_book_id')->references('id')->on('cheque_books')->cascadeOnDelete();
            $table->uuid('account_id');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->string('cheque_number', 20);
            $table->string('payee_name', 150)->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('presented_date')->nullable();
            $table->date('cleared_date')->nullable();
            $table->date('bounced_date')->nullable();
            $table->enum('status', ['issued', 'presented', 'cleared', 'bounced', 'cancelled'])->default('issued');
            $table->string('drawer_reference', 100)->nullable();
            $table->string('bank_reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'account_id']);
            $table->unique(['cheque_book_id', 'cheque_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheque_transactions');
        Schema::dropIfExists('cheque_books');
    }
};
