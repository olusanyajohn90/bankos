<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Read receipts on messages ─────────────────────────────────────
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->enum('delivery_status', ['sent', 'delivered', 'read'])->default('sent')->after('type');
            $table->boolean('is_disappearing')->default(false)->after('is_deleted');
            $table->timestamp('disappear_at')->nullable()->after('is_disappearing');
        });

        // ── Mute + is_pinned on participants ──────────────────────────────
        Schema::table('chat_participants', function (Blueprint $table) {
            $table->boolean('is_muted')->default(false)->after('left_at');
            $table->timestamp('muted_until')->nullable()->after('is_muted');
        });

        // ── Group enhancements on conversations ───────────────────────────
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->string('invite_code', 20)->nullable()->unique()->after('is_archived');
            $table->integer('disappear_minutes')->nullable()->after('invite_code');
        });

        // ── Message reactions ─────────────────────────────────────────────
        Schema::create('chat_reactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('message_id');
            $table->foreign('message_id')->references('id')->on('chat_messages')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('emoji', 10);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['message_id', 'user_id', 'emoji']);
            $table->index(['message_id']);
        });

        // ── Pinned messages ───────────────────────────────────────────────
        Schema::create('chat_pinned_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('conversation_id');
            $table->foreign('conversation_id')->references('id')->on('chat_conversations')->cascadeOnDelete();
            $table->uuid('message_id');
            $table->foreign('message_id')->references('id')->on('chat_messages')->cascadeOnDelete();
            $table->unsignedBigInteger('pinned_by');
            $table->foreign('pinned_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamp('pinned_at')->useCurrent();

            $table->unique(['conversation_id', 'message_id']);
        });

        // ── Starred messages (per user) ───────────────────────────────────
        Schema::create('chat_starred_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('message_id');
            $table->foreign('message_id')->references('id')->on('chat_messages')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['message_id', 'user_id']);
        });

        // ── Chat polls ────────────────────────────────────────────────────
        Schema::create('chat_polls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('message_id');
            $table->foreign('message_id')->references('id')->on('chat_messages')->cascadeOnDelete();
            $table->uuid('conversation_id');
            $table->foreign('conversation_id')->references('id')->on('chat_conversations')->cascadeOnDelete();
            $table->string('question', 500);
            $table->boolean('allow_multiple')->default(false);
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_poll_options', function (Blueprint $table) {
            $table->id();
            $table->uuid('poll_id');
            $table->foreign('poll_id')->references('id')->on('chat_polls')->cascadeOnDelete();
            $table->string('text', 255);
            $table->integer('sort_order')->default(0);
        });

        Schema::create('chat_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->uuid('poll_id');
            $table->foreign('poll_id')->references('id')->on('chat_polls')->cascadeOnDelete();
            $table->unsignedBigInteger('option_id');
            $table->foreign('option_id')->references('id')->on('chat_poll_options')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['poll_id', 'option_id', 'user_id']);
        });

        // ── Chat tasks ────────────────────────────────────────────────────
        Schema::create('chat_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('message_id');
            $table->foreign('message_id')->references('id')->on('chat_messages')->cascadeOnDelete();
            $table->uuid('conversation_id');
            $table->foreign('conversation_id')->references('id')->on('chat_conversations')->cascadeOnDelete();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });

        // ── Read receipts detail (who read when) ──────────────────────────
        Schema::create('chat_read_receipts', function (Blueprint $table) {
            $table->id();
            $table->uuid('message_id');
            $table->foreign('message_id')->references('id')->on('chat_messages')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamp('read_at')->useCurrent();

            $table->unique(['message_id', 'user_id']);
            $table->index(['message_id']);
        });

        // ── User presence (typing / online) ───────────────────────────────
        Schema::create('chat_presence', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamp('last_seen_at')->nullable();
            $table->uuid('typing_in')->nullable(); // conversation_id
            $table->timestamp('typing_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_presence');
        Schema::dropIfExists('chat_read_receipts');
        Schema::dropIfExists('chat_tasks');
        Schema::dropIfExists('chat_poll_votes');
        Schema::dropIfExists('chat_poll_options');
        Schema::dropIfExists('chat_polls');
        Schema::dropIfExists('chat_starred_messages');
        Schema::dropIfExists('chat_pinned_messages');
        Schema::dropIfExists('chat_reactions');

        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropColumn(['invite_code', 'disappear_minutes']);
        });
        Schema::table('chat_participants', function (Blueprint $table) {
            $table->dropColumn(['is_muted', 'muted_until']);
        });
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['delivery_status', 'is_disappearing', 'disappear_at']);
        });
    }
};
