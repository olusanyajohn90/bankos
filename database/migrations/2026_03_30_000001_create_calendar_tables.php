<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calendars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('color', 7)->default('#3B82F6');
            $table->enum('type', ['personal', 'shared', 'system'])->default('personal');
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'type']);
            $table->index(['owner_id']);
        });

        Schema::create('calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('calendar_id')->nullable();
            $table->foreign('calendar_id')->references('id')->on('calendars')->nullOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('type', 30)->default('meeting');
            // meeting, appointment, reminder, task, leave, holiday, loan_maturity, custom
            $table->string('source', 30)->default('manual');
            // manual, chat_task, chat_reminder, leave_request, loan, holiday
            $table->string('source_id', 50)->nullable();
            $table->string('color', 7)->nullable();
            $table->boolean('all_day')->default(false);
            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();
            $table->string('location', 255)->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule', 255)->nullable();
            $table->date('recurrence_end')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->string('visibility', 10)->default('public'); // public, private
            $table->string('status', 15)->default('confirmed'); // confirmed, tentative, cancelled
            $table->timestamps();

            $table->index(['tenant_id', 'start_at', 'end_at']);
            $table->index(['source', 'source_id']);
            $table->index(['calendar_id']);
            $table->index(['created_by']);
        });

        Schema::create('calendar_event_attendees', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id');
            $table->foreign('event_id')->references('id')->on('calendar_events')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('status', 15)->default('pending'); // pending, accepted, declined, tentative
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('responded_at')->nullable();

            $table->unique(['event_id', 'user_id']);
        });

        Schema::create('calendar_event_reminders', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id');
            $table->foreign('event_id')->references('id')->on('calendar_events')->cascadeOnDelete();
            $table->integer('minutes_before')->default(15);
            $table->boolean('is_sent')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_event_reminders');
        Schema::dropIfExists('calendar_event_attendees');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('calendars');
    }
};
