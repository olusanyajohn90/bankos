<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->date('next_due_date')->nullable()->after('disbursed_at');
            $table->unsignedInteger('days_in_arrears')->default(0)->after('next_due_date');
        });

        // Backfill next_due_date and days_in_arrears from disbursed_at
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("
                UPDATE loans
                SET next_due_date = CASE
                    WHEN status = 'active' AND disbursed_at IS NOT NULL THEN
                        (disbursed_at + (
                            GREATEST(1,
                                (EXTRACT(YEAR FROM AGE(NOW(), disbursed_at)) * 12 +
                                 EXTRACT(MONTH FROM AGE(NOW(), disbursed_at)))::int + 1
                            )::text || ' months'
                        )::interval)::date
                    WHEN status = 'overdue' AND disbursed_at IS NOT NULL THEN
                        (disbursed_at + (
                            GREATEST(1,
                                (EXTRACT(YEAR FROM AGE(NOW(), disbursed_at)) * 12 +
                                 EXTRACT(MONTH FROM AGE(NOW(), disbursed_at)))::int
                            )::text || ' months'
                        )::interval)::date
                    ELSE disbursed_at::date
                END,
                days_in_arrears = CASE
                    WHEN status = 'overdue' AND disbursed_at IS NOT NULL THEN
                        GREATEST(0,
                            (NOW()::date - (
                                disbursed_at + (
                                    GREATEST(1,
                                        (EXTRACT(YEAR FROM AGE(NOW(), disbursed_at)) * 12 +
                                         EXTRACT(MONTH FROM AGE(NOW(), disbursed_at)))::int
                                    )::text || ' months'
                                )::interval
                            )::date)
                        )
                    ELSE 0
                END
                WHERE disbursed_at IS NOT NULL
            ");
        } else {
            DB::statement("
                UPDATE loans
                SET next_due_date = CASE
                    WHEN status = 'active' AND disbursed_at IS NOT NULL THEN
                        DATE_ADD(disbursed_at, INTERVAL
                            GREATEST(1, TIMESTAMPDIFF(MONTH, disbursed_at, NOW()) + 1) MONTH)
                    WHEN status = 'overdue' AND disbursed_at IS NOT NULL THEN
                        DATE_ADD(disbursed_at, INTERVAL
                            GREATEST(1, TIMESTAMPDIFF(MONTH, disbursed_at, NOW())) MONTH)
                    ELSE disbursed_at
                END,
                days_in_arrears = CASE
                    WHEN status = 'overdue' AND disbursed_at IS NOT NULL THEN
                        GREATEST(0, DATEDIFF(NOW(),
                            DATE_ADD(disbursed_at, INTERVAL
                                GREATEST(1, TIMESTAMPDIFF(MONTH, disbursed_at, NOW())) MONTH)
                        ))
                    ELSE 0
                END
                WHERE disbursed_at IS NOT NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['next_due_date', 'days_in_arrears']);
        });
    }
};
