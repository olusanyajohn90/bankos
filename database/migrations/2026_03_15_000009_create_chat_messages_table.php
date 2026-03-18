<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            $table->uuid('conversation_id');
            $table->foreign('conversation_id')->references('id')->on('chat_conversations')->cascadeOnDelete();

            $table->unsignedBigInteger('sender_id')->nullable();
            $table->foreign('sender_id')->references('id')->on('users')->nullOnDelete();

            $table->uuid('reply_to_id')->nullable();

            $table->text('body')->nullable();
            $table->enum('type', ['text', 'file', 'image', 'system'])->default('text');

            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();

            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();

            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index(['tenant_id', 'sender_id']);
            $table->index(['reply_to_id']);
        });

        // Self-referencing FK added separately so PostgreSQL sees the primary key first
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreign('reply_to_id')->references('id')->on('chat_messages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
