<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_instances', function (Blueprint $table) {
            if (!Schema::hasColumn('workflow_instances', 'step')) {
                $table->unsignedSmallInteger('step')->default(1)->after('assigned_role');
            }
            if (!Schema::hasColumn('workflow_instances', 'total_steps')) {
                $table->unsignedSmallInteger('total_steps')->default(1)->after('step');
            }
            if (!Schema::hasColumn('workflow_instances', 'actioned_by')) {
                $table->unsignedBigInteger('actioned_by')->nullable()->after('total_steps');
                $table->foreign('actioned_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('workflow_instances', 'notes')) {
                $table->text('notes')->nullable()->after('actioned_by');
            }
            if (!Schema::hasColumn('workflow_instances', 'due_at')) {
                $table->timestamp('due_at')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('workflow_instances', 'metadata')) {
                $table->json('metadata')->nullable()->after('due_at');
            }
        });

        // Add FK separately in case columns already existed without the constraint
        try {
            Schema::table('workflow_instances', function (Blueprint $table) {
                $table->foreign('actioned_by')->references('id')->on('users')->nullOnDelete();
            });
        } catch (\Throwable) {
            // FK already exists — safe to ignore
        }
    }

    public function down(): void
    {
        Schema::table('workflow_instances', function (Blueprint $table) {
            try { $table->dropForeign(['actioned_by']); } catch (\Throwable) {}
            $cols = ['step', 'total_steps', 'actioned_by', 'notes', 'due_at', 'metadata'];
            $existing = array_filter($cols, fn($c) => Schema::hasColumn('workflow_instances', $c));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};
