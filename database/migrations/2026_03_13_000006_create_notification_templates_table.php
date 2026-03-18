<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('event'); // loan_disbursed, repayment_received, overdue, kyc_approved, etc.
            $table->enum('channel', ['sms', 'whatsapp', 'email', 'push']);
            $table->string('subject')->nullable(); // email subject
            $table->text('body'); // message body, supports {{variables}}
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'event', 'channel']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
