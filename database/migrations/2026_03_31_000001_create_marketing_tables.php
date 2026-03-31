<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Marketing Templates ───────────────────────────────────────────
        Schema::create('marketing_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('channel', 20); // sms, email, whatsapp, push
            $table->string('subject', 255)->nullable(); // for email
            $table->text('body'); // supports placeholders: {first_name}, {account_number}, etc.
            $table->json('placeholders')->nullable(); // list of available placeholders
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'channel']);
        });

        // ── Customer Segments ─────────────────────────────────────────────
        Schema::create('marketing_segments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->json('rules'); // [{field, operator, value}, ...]
            $table->boolean('is_system')->default(false); // system-defined segments
            $table->integer('cached_count')->default(0); // last computed count
            $table->timestamp('count_computed_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tenant_id']);
        });

        // ── Marketing Campaigns ───────────────────────────────────────────
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('type', 30)->default('broadcast');
            // broadcast, drip, event_triggered, cross_sell
            $table->string('channel', 20); // sms, email, whatsapp
            $table->uuid('template_id')->nullable();
            $table->foreign('template_id')->references('id')->on('marketing_templates')->nullOnDelete();
            $table->uuid('segment_id')->nullable();
            $table->foreign('segment_id')->references('id')->on('marketing_segments')->nullOnDelete();
            $table->string('custom_message', 2000)->nullable(); // override template body
            $table->string('custom_subject', 255)->nullable(); // override template subject
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'paused', 'cancelled'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            // Stats
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->integer('converted_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->integer('unsubscribed_count')->default(0);
            $table->decimal('cost', 10, 2)->default(0); // total SMS/email cost
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['scheduled_at']);
        });

        // ── Campaign Recipients ───────────────────────────────────────────
        Schema::create('marketing_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->uuid('campaign_id');
            $table->foreign('campaign_id')->references('id')->on('marketing_campaigns')->cascadeOnDelete();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->string('channel_address', 255)->nullable(); // phone or email used
            $table->enum('status', ['queued', 'sent', 'delivered', 'opened', 'clicked', 'converted', 'failed', 'unsubscribed'])->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('provider_message_id', 100)->nullable(); // SMS/email provider reference

            $table->index(['campaign_id', 'status']);
            $table->index(['customer_id']);
            $table->unique(['campaign_id', 'customer_id']);
        });

        // ── Customer Unsubscribe List ─────────────────────────────────────
        Schema::create('marketing_unsubscribes', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->string('channel', 20); // sms, email, whatsapp
            $table->timestamp('unsubscribed_at')->useCurrent();
            $table->string('reason', 255)->nullable();

            $table->unique(['tenant_id', 'customer_id', 'channel']);
        });

        // ── Cross-sell Opportunities ──────────────────────────────────────
        Schema::create('marketing_cross_sells', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->string('opportunity_type', 50);
            // loan_to_savings, savings_to_loan, loan_to_insurance, active_to_investment, dormant_reactivation
            $table->string('recommended_product', 100);
            $table->text('reason')->nullable();
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->enum('status', ['identified', 'contacted', 'interested', 'converted', 'declined'])->default('identified');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['customer_id']);
            $table->index(['assigned_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_cross_sells');
        Schema::dropIfExists('marketing_unsubscribes');
        Schema::dropIfExists('marketing_campaign_recipients');
        Schema::dropIfExists('marketing_campaigns');
        Schema::dropIfExists('marketing_segments');
        Schema::dropIfExists('marketing_templates');
    }
};
