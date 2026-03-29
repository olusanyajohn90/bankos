<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // ── Channels: extend conversations ────────────────────────────────
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->string('topic', 255)->nullable()->after('description');
            $table->boolean('is_private')->default(true)->after('is_archived');
            // type already has 'direct','group' — we add 'channel' below
        });

        // Add 'channel' to conversation type CHECK constraint
        DB::statement('ALTER TABLE chat_conversations DROP CONSTRAINT IF EXISTS chat_conversations_type_check');
        DB::statement("ALTER TABLE chat_conversations ADD CONSTRAINT chat_conversations_type_check CHECK (type::text = ANY (ARRAY['direct','group','channel']::text[]))");

        // ── Threads: add thread_id to messages ────────────────────────────
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->uuid('thread_id')->nullable()->after('reply_to_id');
            $table->integer('thread_reply_count')->default(0)->after('thread_id');
            $table->timestamp('thread_last_reply_at')->nullable()->after('thread_reply_count');
            $table->timestamp('scheduled_at')->nullable()->after('disappear_at');
            $table->boolean('is_scheduled')->default(false)->after('scheduled_at');
            $table->index(['thread_id']);
        });

        // Self-referencing FK for thread parent
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreign('thread_id')->references('id')->on('chat_messages')->nullOnDelete();
        });

        // ── User status ───────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->string('chat_status_emoji', 10)->nullable()->after('status');
            $table->string('chat_status_text', 100)->nullable()->after('chat_status_emoji');
            $table->timestamp('chat_status_until')->nullable()->after('chat_status_text');
            $table->timestamp('chat_dnd_until')->nullable()->after('chat_status_until');
        });

        // ── Mentions tracking ─────────────────────────────────────────────
        Schema::create('chat_mentions', function (Blueprint $table) {
            $table->id();
            $table->uuid('message_id');
            $table->foreign('message_id')->references('id')->on('chat_messages')->cascadeOnDelete();
            $table->uuid('conversation_id');
            $table->foreign('conversation_id')->references('id')->on('chat_conversations')->cascadeOnDelete();
            $table->unsignedBigInteger('mentioned_user_id')->nullable();
            $table->foreign('mentioned_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('mention_type', 20)->default('user'); // user, here, channel, group
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['mentioned_user_id', 'is_read']);
            $table->index(['conversation_id']);
        });

        // ── User groups (for @mentions) ───────────────────────────────────
        Schema::create('chat_user_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('handle', 50); // e.g. @managers, @tellers
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'handle']);
        });

        Schema::create('chat_user_group_members', function (Blueprint $table) {
            $table->id();
            $table->uuid('group_id');
            $table->foreign('group_id')->references('id')->on('chat_user_groups')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamp('added_at')->useCurrent();

            $table->unique(['group_id', 'user_id']);
        });

        // ── Reminders ─────────────────────────────────────────────────────
        Schema::create('chat_reminders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->uuid('conversation_id')->nullable();
            $table->foreign('conversation_id')->references('id')->on('chat_conversations')->nullOnDelete();
            $table->uuid('message_id')->nullable();
            $table->foreign('message_id')->references('id')->on('chat_messages')->nullOnDelete();
            $table->text('note');
            $table->timestamp('remind_at');
            $table->boolean('is_fired')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_fired', 'remind_at']);
        });

        // ── Bookmarks (per conversation) ──────────────────────────────────
        Schema::create('chat_bookmarks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id');
            $table->foreign('conversation_id')->references('id')->on('chat_conversations')->cascadeOnDelete();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('url', 2000)->nullable();
            $table->uuid('message_id')->nullable();
            $table->foreign('message_id')->references('id')->on('chat_messages')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['conversation_id', 'sort_order']);
        });

        // ── Custom emoji ──────────────────────────────────────────────────
        Schema::create('chat_custom_emoji', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('shortcode', 50); // :bankos:
            $table->string('image_path', 500);
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'shortcode']);
        });

        // ── Notification preferences per conversation ─────────────────────
        Schema::table('chat_participants', function (Blueprint $table) {
            $table->string('notify_level', 20)->default('all')->after('muted_until');
            // all, mentions, none
        });
    }

    public function down(): void
    {
        Schema::table('chat_participants', function (Blueprint $table) {
            $table->dropColumn('notify_level');
        });

        Schema::dropIfExists('chat_custom_emoji');
        Schema::dropIfExists('chat_bookmarks');
        Schema::dropIfExists('chat_reminders');
        Schema::dropIfExists('chat_user_group_members');
        Schema::dropIfExists('chat_user_groups');
        Schema::dropIfExists('chat_mentions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['chat_status_emoji', 'chat_status_text', 'chat_status_until', 'chat_dnd_until']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign(['thread_id']);
            $table->dropIndex(['thread_id']);
            $table->dropColumn(['thread_id', 'thread_reply_count', 'thread_last_reply_at', 'scheduled_at', 'is_scheduled']);
        });

        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropColumn(['topic', 'is_private']);
        });

        DB::statement('ALTER TABLE chat_conversations DROP CONSTRAINT IF EXISTS chat_conversations_type_check');
        DB::statement("ALTER TABLE chat_conversations ADD CONSTRAINT chat_conversations_type_check CHECK (type::text = ANY (ARRAY['direct','group']::text[]))");
    }
};
