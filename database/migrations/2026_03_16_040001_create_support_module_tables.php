<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Support Teams (IT, Card Ops, Customer Service, Fraud, Settlements, etc.)
        Schema::create('support_teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name');
            $table->string('code')->nullable();           // e.g. IT, CARD, CS
            $table->string('division')->nullable();        // Operations, Technology, etc.
            $table->text('description')->nullable();
            $table->string('email')->nullable();           // team inbox email
            $table->foreignId('team_lead_id')->nullable()->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->json('working_hours')->nullable();     // {mon:{start,end}, ...}
            $table->timestamps();
        });

        // Team members
        Schema::create('support_team_members', function (Blueprint $table) {
            $table->id();
            $table->uuid('team_id');
            $table->foreignId('user_id')->constrained('users');
            $table->string('role')->default('agent');      // agent, supervisor, team_lead
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['team_id', 'user_id']);
        });

        // SLA Policies
        Schema::create('support_sla_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name');
            $table->string('priority');                    // low, medium, high, critical
            $table->unsignedInteger('response_minutes');   // time to first response
            $table->unsignedInteger('resolution_minutes'); // time to resolution
            $table->boolean('business_hours_only')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Ticket categories
        Schema::create('support_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('team_id')->nullable();           // default routing team
            $table->string('name');
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tickets
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('ticket_number')->unique();     // TKT-2026-00001
            $table->string('subject');
            $table->text('description');
            $table->string('channel')->default('web');     // web, email, phone, walk_in, portal
            $table->string('priority')->default('medium'); // low, medium, high, critical
            $table->string('status')->default('open');     // open, pending, in_progress, resolved, closed, cancelled
            $table->uuid('category_id')->nullable();
            $table->uuid('team_id')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            // Subject — can be a customer or an account
            $table->string('requester_type')->nullable();  // customer, staff, walk_in
            $table->string('requester_name')->nullable();
            $table->string('requester_email')->nullable();
            $table->string('requester_phone')->nullable();
            $table->uuid('customer_id')->nullable();
            $table->string('account_number')->nullable();
            // SLA tracking
            $table->uuid('sla_policy_id')->nullable();
            $table->timestamp('sla_response_due_at')->nullable();
            $table->timestamp('sla_resolution_due_at')->nullable();
            $table->timestamp('first_responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->boolean('sla_breached')->default(false);
            // Escalation
            $table->unsignedInteger('escalation_level')->default(0);
            $table->timestamp('escalated_at')->nullable();
            $table->foreignId('escalated_to')->nullable()->constrained('users');
            // Rating
            $table->unsignedTinyInteger('satisfaction_rating')->nullable(); // 1-5
            $table->text('satisfaction_comment')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });

        // Ticket replies / thread
        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ticket_id');
            $table->foreignId('author_id')->constrained('users');
            $table->text('body');
            $table->string('type')->default('reply');      // reply, internal_note, status_change, assignment
            $table->boolean('is_internal')->default(false); // internal notes invisible to customer
            $table->string('attachment_path')->nullable();
            $table->timestamps();
            $table->index('ticket_id');
        });

        // Knowledge base articles
        Schema::create('support_kb_articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->foreignId('created_by')->constrained('users');
            $table->string('title');
            $table->text('body');
            $table->string('category')->nullable();        // e.g. Account Issues, Cards, Loans
            $table->string('status')->default('draft');    // draft, published
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_kb_articles');
        Schema::dropIfExists('support_ticket_replies');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('support_categories');
        Schema::dropIfExists('support_sla_policies');
        Schema::dropIfExists('support_team_members');
        Schema::dropIfExists('support_teams');
    }
};
