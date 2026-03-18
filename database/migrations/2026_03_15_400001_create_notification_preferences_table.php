<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notification preferences table — shared by both bankOS admin and portal.
 * This migration is idempotent: it only creates the table if it does not already exist.
 * If the portal has already migrated this table, ALTER statements add any missing columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->uuid('customer_id')->index();
                $table->boolean('transaction_alerts')->default(true);
                $table->boolean('low_balance_alerts')->default(true);
                $table->boolean('weekly_statements')->default(true);
                $table->boolean('loan_reminders')->default(true);
                $table->boolean('marketing')->default(false);
                $table->timestamps();
                $table->unique(['tenant_id', 'customer_id']);
            });
        } else {
            // Table exists (created by the portal migration) — add any missing columns
            Schema::table('notification_preferences', function (Blueprint $table) {
                if (!Schema::hasColumn('notification_preferences', 'weekly_statements')) {
                    $table->boolean('weekly_statements')->default(true)->after('monthly_summary');
                }
                if (!Schema::hasColumn('notification_preferences', 'transaction_alerts')) {
                    $table->boolean('transaction_alerts')->default(true)->after('statement_ready');
                }
                if (!Schema::hasColumn('notification_preferences', 'low_balance_alerts')) {
                    $table->boolean('low_balance_alerts')->default(true)->after('transaction_alerts');
                }
                if (!Schema::hasColumn('notification_preferences', 'loan_reminders')) {
                    $table->boolean('loan_reminders')->default(true)->after('low_balance_alerts');
                }
                if (!Schema::hasColumn('notification_preferences', 'marketing')) {
                    $table->boolean('marketing')->default(false)->after('loan_reminders');
                }
            });
        }
    }

    public function down(): void
    {
        // Only drop columns that this migration added; never drop the full table
        // as the portal migration owns the base table definition.
        if (Schema::hasTable('notification_preferences')) {
            Schema::table('notification_preferences', function (Blueprint $table) {
                $cols = ['weekly_statements', 'transaction_alerts', 'low_balance_alerts', 'loan_reminders', 'marketing'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('notification_preferences', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
