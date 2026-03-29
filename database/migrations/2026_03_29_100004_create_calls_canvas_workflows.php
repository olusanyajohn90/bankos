<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Calls / Huddles ───────────────────────────────────────────────
        Schema::create('chat_calls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('conversation_id');
            $table->foreign('conversation_id')->references('id')->on('chat_conversations')->cascadeOnDelete();
            $table->unsignedBigInteger('initiated_by');
            $table->foreign('initiated_by')->references('id')->on('users')->cascadeOnDelete();
            $table->string('livekit_room_name', 100);
            $table->enum('type', ['audio', 'video', 'screen_share'])->default('audio');
            $table->enum('status', ['ringing', 'active', 'ended', 'missed', 'declined'])->default('ringing');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'status']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('chat_call_participants', function (Blueprint $table) {
            $table->id();
            $table->uuid('call_id');
            $table->foreign('call_id')->references('id')->on('chat_calls')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_video_on')->default(false);
            $table->boolean('is_screen_sharing')->default(false);

            $table->unique(['call_id', 'user_id']);
        });

        // ── Canvas / Docs ─────────────────────────────────────────────────
        Schema::create('chat_canvas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('conversation_id')->nullable();
            $table->foreign('conversation_id')->references('id')->on('chat_conversations')->nullOnDelete();
            $table->string('title', 255);
            $table->json('content')->nullable(); // Tiptap JSON
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('last_edited_by')->nullable();
            $table->foreign('last_edited_by')->references('id')->on('users')->nullOnDelete();
            $table->boolean('is_shared')->default(false);
            $table->timestamps();

            $table->index(['conversation_id']);
            $table->index(['tenant_id', 'created_by']);
        });

        // ── Workflow Builder ──────────────────────────────────────────────
        Schema::create('chat_workflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->json('trigger')->nullable(); // {type: 'message_contains', value: 'help'}
            $table->json('steps')->nullable();    // [{action: 'send_message', config: {...}}, ...]
            $table->uuid('conversation_id')->nullable(); // If scoped to a conversation
            $table->foreign('conversation_id')->references('id')->on('chat_conversations')->nullOnDelete();
            $table->integer('run_count')->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('chat_workflow_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workflow_id');
            $table->foreign('workflow_id')->references('id')->on('chat_workflows')->cascadeOnDelete();
            $table->uuid('triggered_by_message_id')->nullable();
            $table->foreign('triggered_by_message_id')->references('id')->on('chat_messages')->nullOnDelete();
            $table->unsignedBigInteger('triggered_by_user_id')->nullable();
            $table->foreign('triggered_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->json('step_results')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_workflow_runs');
        Schema::dropIfExists('chat_workflows');
        Schema::dropIfExists('chat_canvas');
        Schema::dropIfExists('chat_call_participants');
        Schema::dropIfExists('chat_calls');
    }
};
