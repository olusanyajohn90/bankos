<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('member_exits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('customer_id');
            $table->string('exit_type'); // voluntary, expelled, deceased, transferred
            $table->text('reason')->nullable();
            $table->decimal('share_refund', 15, 2)->default(0);
            $table->decimal('savings_balance', 15, 2)->default(0);
            $table->decimal('outstanding_loans', 15, 2)->default(0);
            $table->decimal('pending_contributions', 15, 2)->default(0);
            $table->decimal('net_settlement', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, approved, settled, rejected
            $table->date('exit_date')->nullable();
            $table->date('settlement_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->foreign('customer_id')->references('id')->on('customers');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_exits');
    }
};
