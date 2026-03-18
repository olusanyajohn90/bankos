<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comms_recipients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('tenant_id');

            $table->uuid('message_id');
            $table->foreign('message_id')->references('id')->on('comms_messages')->cascadeOnDelete();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->timestamp('read_at')->nullable();
            $table->timestamp('ack_at')->nullable();
            $table->string('ack_note', 500)->nullable();

            $table->timestamps();

            $table->unique(['message_id', 'user_id']);
            $table->index(['tenant_id', 'user_id', 'read_at']);
            $table->index(['tenant_id', 'message_id', 'ack_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comms_recipients');
    }
};
