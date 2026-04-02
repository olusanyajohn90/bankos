<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Projects ──────────────────────────────────────────────────────
        Schema::create('pm_projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('code', 20)->nullable(); // PRJ-001
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3B82F6');
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();
            $table->enum('status', ['planning', 'active', 'on_hold', 'completed', 'archived'])->default('planning');
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('progress')->default(0); // 0-100
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // ── Project Members ───────────────────────────────────────────────
        Schema::create('pm_project_members', function (Blueprint $table) {
            $table->id();
            $table->uuid('project_id');
            $table->foreign('project_id')->references('id')->on('pm_projects')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('role', 20)->default('member'); // owner, admin, member, viewer
            $table->timestamp('joined_at')->useCurrent();

            $table->unique(['project_id', 'user_id']);
        });

        // ── Boards (Kanban boards per project) ────────────────────────────
        Schema::create('pm_boards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->foreign('project_id')->references('id')->on('pm_projects')->cascadeOnDelete();
            $table->string('name', 100);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // ── Board Columns ─────────────────────────────────────────────────
        Schema::create('pm_columns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('board_id');
            $table->foreign('board_id')->references('id')->on('pm_boards')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('color', 7)->default('#6B7280');
            $table->integer('position')->default(0);
            $table->integer('wip_limit')->nullable(); // work-in-progress limit
            $table->boolean('is_done_column')->default(false);
            $table->timestamps();

            $table->index(['board_id', 'position']);
        });

        // ── Tasks ─────────────────────────────────────────────────────────
        Schema::create('pm_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->foreign('project_id')->references('id')->on('pm_projects')->cascadeOnDelete();
            $table->uuid('column_id')->nullable();
            $table->foreign('column_id')->references('id')->on('pm_columns')->nullOnDelete();
            $table->uuid('parent_id')->nullable(); // subtask support
            $table->string('task_number', 20)->nullable(); // TSK-001
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['todo', 'in_progress', 'review', 'done', 'blocked', 'cancelled'])->default('todo');
            $table->unsignedBigInteger('assignee_id')->nullable();
            $table->foreign('assignee_id')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('reporter_id');
            $table->foreign('reporter_id')->references('id')->on('users')->cascadeOnDelete();
            $table->date('due_date')->nullable();
            $table->date('start_date')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->decimal('logged_hours', 8, 2)->default(0);
            $table->integer('position')->default(0); // order within column
            $table->json('labels')->nullable(); // ["bug", "feature", "urgent"]
            $table->uuid('sprint_id')->nullable();
            $table->integer('story_points')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['column_id', 'position']);
            $table->index(['assignee_id']);
            $table->index(['sprint_id']);
        });

        // Self-referencing FK added after table creation
        Schema::table('pm_tasks', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('pm_tasks')->nullOnDelete();
        });

        // ── Task Comments ─────────────────────────────────────────────────
        Schema::create('pm_task_comments', function (Blueprint $table) {
            $table->id();
            $table->uuid('task_id');
            $table->foreign('task_id')->references('id')->on('pm_tasks')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['task_id', 'created_at']);
        });

        // ── Task Attachments ──────────────────────────────────────────────
        Schema::create('pm_task_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id');
            $table->foreign('task_id')->references('id')->on('pm_tasks')->cascadeOnDelete();
            $table->unsignedBigInteger('uploaded_by');
            $table->foreign('uploaded_by')->references('id')->on('users')->cascadeOnDelete();
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->string('mime_type', 100)->nullable();
            $table->integer('file_size_kb')->nullable();
            $table->timestamps();
        });

        // ── Task Activity Log ─────────────────────────────────────────────
        Schema::create('pm_task_activities', function (Blueprint $table) {
            $table->id();
            $table->uuid('task_id');
            $table->foreign('task_id')->references('id')->on('pm_tasks')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('action', 50); // created, assigned, status_changed, priority_changed, commented, moved, etc.
            $table->string('old_value', 255)->nullable();
            $table->string('new_value', 255)->nullable();
            $table->timestamps();
        });

        // ── Time Tracking ─────────────────────────────────────────────────
        Schema::create('pm_time_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('task_id');
            $table->foreign('task_id')->references('id')->on('pm_tasks')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->decimal('hours', 6, 2);
            $table->text('note')->nullable();
            $table->date('logged_date');
            $table->timestamps();

            $table->index(['task_id']);
            $table->index(['user_id', 'logged_date']);
        });

        // ── Sprints / Milestones ──────────────────────────────────────────
        Schema::create('pm_sprints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->foreign('project_id')->references('id')->on('pm_projects')->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('goal')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['planning', 'active', 'completed'])->default('planning');
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });

        // ── Labels ────────────────────────────────────────────────────────
        Schema::create('pm_labels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->foreign('project_id')->references('id')->on('pm_projects')->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('color', 7)->default('#6B7280');

            $table->unique(['project_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_labels');
        Schema::dropIfExists('pm_sprints');
        Schema::dropIfExists('pm_time_entries');
        Schema::dropIfExists('pm_task_activities');
        Schema::dropIfExists('pm_task_attachments');
        Schema::dropIfExists('pm_task_comments');
        Schema::dropIfExists('pm_tasks');
        Schema::dropIfExists('pm_columns');
        Schema::dropIfExists('pm_boards');
        Schema::dropIfExists('pm_project_members');
        Schema::dropIfExists('pm_projects');
    }
};
