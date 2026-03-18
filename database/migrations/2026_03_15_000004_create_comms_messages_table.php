<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comms_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            $table->enum('type', ['memo', 'circular', 'announcement'])->default('memo');
            $table->string('subject', 255);
            $table->longText('body');

            $table->enum('priority', ['normal', 'urgent', 'critical'])->default('normal');
            $table->boolean('requires_ack')->default(false);
            $table->date('ack_deadline')->nullable();

            $table->unsignedBigInteger('sender_id')->nullable();
            $table->foreign('sender_id')->references('id')->on('users')->nullOnDelete();

            $table->enum('scope_type', ['all_staff', 'branch', 'department', 'team', 'role', 'individual'])->default('all_staff');
            $table->string('scope_id', 36)->nullable();

            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'status', 'published_at']);
            $table->index(['tenant_id', 'sender_id']);
            $table->index(['tenant_id', 'scope_type', 'scope_id']);
            $table->index(['tenant_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comms_messages');
    }
};
