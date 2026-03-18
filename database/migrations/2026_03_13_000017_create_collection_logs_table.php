<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('loan_id');
            $table->uuid('customer_id');
            $table->unsignedBigInteger('officer_id')->nullable(); // users.id
            $table->integer('days_past_due')->default(0);
            $table->integer('overdue_score')->default(0); // 0-100 composite risk score
            $table->enum('action', ['call', 'sms', 'visit', 'demand_letter', 'legal', 'write_off', 'restructure'])->default('call');
            $table->enum('outcome', ['contacted', 'promised_to_pay', 'paid', 'unreachable', 'disputed', 'escalated'])->default('contacted');
            $table->decimal('promise_amount', 15, 2)->nullable();
            $table->date('promise_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('actioned_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('officer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_logs');
    }
};
