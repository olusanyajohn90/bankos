<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Document folders / filing structure
        Schema::create('document_folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name');
            $table->uuid('parent_id')->nullable();        // nested folders
            $table->string('icon')->nullable()->default('folder');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false); // system folders can't be deleted
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Document tags
        Schema::create('document_tags', function (Blueprint $table) {
            $table->id();
            $table->uuid('document_id');
            $table->string('tag');
            $table->index(['document_id', 'tag']);
        });

        // Add folder_id and is_confidential to documents table if not present
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'folder_id')) {
                $table->uuid('folder_id')->nullable()->after('tenant_id');
            }
            if (! Schema::hasColumn('documents', 'is_confidential')) {
                $table->boolean('is_confidential')->default(false)->after('is_required');
            }
            if (! Schema::hasColumn('documents', 'source')) {
                $table->string('source')->nullable()->after('is_confidential'); // internal, external, email, portal
            }
            if (! Schema::hasColumn('documents', 'ref_number')) {
                $table->string('ref_number')->nullable()->after('source'); // bank's own reference number
            }
            if (! Schema::hasColumn('documents', 'direction')) {
                $table->string('direction')->default('internal')->after('ref_number'); // internal, inbound, outbound
            }
        });

        // Document notes / threaded comments
        Schema::create('document_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('document_id')->index();
            $table->foreignId('author_id')->constrained('users');
            $table->text('body');
            $table->boolean('is_internal')->default(false); // internal staff note vs. external comment
            $table->uuid('parent_id')->nullable();          // reply to another note
            $table->timestamps();
        });

        // Workflow definitions (reusable templates)
        Schema::create('document_workflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_category')->nullable(); // auto-trigger for this doc category
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_all_signatures')->default(false);
            $table->timestamps();
        });

        // Workflow step templates
        Schema::create('document_workflow_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workflow_id');
            $table->integer('step_order');
            $table->string('name');
            $table->string('action_type')->default('approve'); // approve, sign, review, acknowledge
            $table->string('assignee_type')->default('user'); // user, role
            $table->foreignId('assignee_user_id')->nullable()->constrained('users');
            $table->string('assignee_role')->nullable();
            $table->unsignedInteger('deadline_hours')->nullable();
            $table->boolean('is_optional')->default(false);
            $table->timestamps();
        });

        // Active workflow instances per document
        Schema::create('document_workflow_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('document_id')->index();
            $table->uuid('workflow_id');
            $table->foreignId('initiated_by')->constrained('users');
            $table->string('status')->default('in_progress'); // in_progress, completed, rejected, cancelled
            $table->integer('current_step_order')->default(1);
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['document_id', 'status']);
        });

        // Actions taken on each step instance
        Schema::create('document_workflow_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('instance_id')->index();
            $table->uuid('step_id');
            $table->foreignId('assignee_id')->constrained('users');  // who is assigned
            $table->foreignId('actor_id')->nullable()->constrained('users');  // who actually acted
            $table->string('status')->default('pending'); // pending, approved, rejected, signed, acknowledged
            $table->text('notes')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();
            $table->index(['assignee_id', 'status']);
        });

        // Digital signatures
        Schema::create('document_signatures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('document_id')->index();
            $table->foreignId('signer_id')->constrained('users');
            $table->text('signature_data');           // base64 canvas drawing OR typed name
            $table->string('signature_type')->default('drawn'); // drawn, typed, uploaded
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_valid')->default(true);
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('signed_at');
            $table->timestamps();
        });

        // Document shares (controlled access)
        Schema::create('document_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('document_id')->index();
            $table->foreignId('shared_by')->constrained('users');
            $table->foreignId('shared_with')->nullable()->constrained('users');
            $table->string('share_token')->unique()->nullable(); // for external link sharing
            $table->string('permission')->default('view');       // view, download, sign, comment
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_shares');
        Schema::dropIfExists('document_signatures');
        Schema::dropIfExists('document_workflow_actions');
        Schema::dropIfExists('document_workflow_instances');
        Schema::dropIfExists('document_workflow_steps');
        Schema::dropIfExists('document_workflows');
        Schema::dropIfExists('document_notes');
        Schema::dropIfExists('document_tags');
        Schema::dropIfExists('document_folders');
    }
};
