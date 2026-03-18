<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_pipeline_stages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name', 80);
            $table->string('color', 7)->default('#3b82f6');
            $table->unsignedTinyInteger('position')->default(1);
            $table->boolean('is_closed_won')->default(false);
            $table->boolean('is_closed_lost')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->timestamps();
            $table->index(['tenant_id', 'position']);
        });

        Schema::create('crm_leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('stage_id')->nullable();
            $table->string('title', 200);
            $table->string('contact_name', 150);
            $table->string('contact_phone', 30)->nullable();
            $table->string('contact_email', 150)->nullable();
            $table->string('company', 150)->nullable();
            $table->string('source', 50)->nullable(); // walk-in, referral, social, direct
            $table->string('product_interest', 100)->nullable(); // savings, loan, sme, etc.
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->unsignedTinyInteger('probability_pct')->default(50);
            $table->enum('status', ['new', 'in_progress', 'converted', 'lost', 'on_hold'])->default('new');
            $table->unsignedBigInteger('assigned_to')->nullable(); // user_id
            $table->uuid('converted_account_id')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->date('closed_date')->nullable();
            $table->text('lost_reason')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'stage_id']);
            $table->index(['assigned_to', 'status']);
        });

        Schema::create('crm_interactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->enum('subject_type', ['account', 'lead', 'customer'])->default('account');
            $table->string('subject_id', 36); // UUID or id of the related record
            $table->uuid('lead_id')->nullable();
            $table->string('account_id', 36)->nullable();
            $table->enum('interaction_type', ['call', 'meeting', 'email', 'whatsapp', 'visit', 'sms', 'note'])->default('call');
            $table->enum('direction', ['inbound', 'outbound', 'internal'])->default('outbound');
            $table->string('subject', 200)->nullable();
            $table->text('summary');
            $table->text('outcome')->nullable();
            $table->string('next_action', 200)->nullable();
            $table->date('next_action_date')->nullable();
            $table->unsignedSmallInteger('duration_mins')->nullable();
            $table->timestamp('interacted_at');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->index(['tenant_id', 'subject_type', 'subject_id'], 'crm_int_subject_idx');
            $table->index(['tenant_id', 'interacted_at']);
            $table->index(['lead_id']);
        });

        Schema::create('crm_follow_ups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->enum('subject_type', ['lead', 'account'])->default('lead');
            $table->string('subject_id', 36);
            $table->string('title', 200);
            $table->text('notes')->nullable();
            $table->dateTime('due_at');
            $table->enum('status', ['pending', 'completed', 'snoozed', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('assigned_to');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->index(['assigned_to', 'status', 'due_at'], 'crm_follow_ups_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_follow_ups');
        Schema::dropIfExists('crm_interactions');
        Schema::dropIfExists('crm_leads');
        Schema::dropIfExists('crm_pipeline_stages');
    }
};
