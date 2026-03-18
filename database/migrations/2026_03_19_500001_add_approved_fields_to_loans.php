<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (!Schema::hasColumn('loans', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->nullable()->after('status');
            }
            if (!Schema::hasColumn('loans', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('loans', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('loans', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('loans', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumnIfExists('approved_by');
            $table->dropColumnIfExists('approved_at');
            $table->dropColumnIfExists('rejected_by');
            $table->dropColumnIfExists('rejected_at');
            $table->dropColumnIfExists('rejection_reason');
        });
    }
};
