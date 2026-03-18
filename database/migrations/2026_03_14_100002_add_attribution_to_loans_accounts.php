<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->unsignedBigInteger('officer_id')->nullable()->after('customer_id'); // FK → users.id
            $table->string('referral_code', 20)->nullable()->after('officer_id');
            $table->foreign('officer_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['tenant_id', 'officer_id']);
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('opened_by')->nullable()->after('customer_id'); // FK → users.id
            $table->string('referral_code', 20)->nullable()->after('opened_by');
            $table->foreign('opened_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['tenant_id', 'opened_by']);
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['officer_id']);
            $table->dropIndex(['tenant_id', 'officer_id']);
            $table->dropColumn(['officer_id', 'referral_code']);
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['opened_by']);
            $table->dropIndex(['tenant_id', 'opened_by']);
            $table->dropColumn(['opened_by', 'referral_code']);
        });
    }
};
