<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecl_provisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('loan_id');
            $table->uuid('customer_id');
            $table->integer('days_past_due')->default(0);
            $table->tinyInteger('stage')->default(1)->comment('1=performing, 2=underperforming, 3=non-performing');
            $table->decimal('outstanding_balance', 15, 2)->default(0);
            $table->decimal('probability_of_default', 8, 6)->default(0); // PD
            $table->decimal('loss_given_default', 8, 6)->default(0.45);  // LGD
            $table->decimal('exposure_at_default', 15, 2)->default(0);   // EAD
            $table->decimal('ecl_amount', 15, 2)->default(0);            // PD × LGD × EAD
            $table->date('reporting_date');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            $table->unique(['loan_id', 'reporting_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecl_provisions');
    }
};
