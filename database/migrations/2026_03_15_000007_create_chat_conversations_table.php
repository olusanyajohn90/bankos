<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            $table->enum('type', ['direct', 'group'])->default('direct');
            $table->string('name', 150)->nullable();
            $table->string('description', 500)->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamp('last_message_at')->nullable()->index('last_message_at');
            $table->string('last_message_preview', 120)->nullable();

            $table->boolean('is_archived')->default(false);

            $table->timestamps();

            $table->index(['tenant_id', 'type', 'is_archived']);
            $table->index(['tenant_id', 'created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
