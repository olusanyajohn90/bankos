<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Points to the original loan this one was restructured from
            $table->uuid('parent_loan_id')->nullable()->after('id');
            $table->foreign('parent_loan_id')->references('id')->on('loans')->onDelete('set null');
        });

        Schema::table('loan_restructures', function (Blueprint $table) {
            // After approval, stores the new loan that was created
            $table->uuid('new_loan_id')->nullable()->after('loan_id');
            $table->foreign('new_loan_id')->references('id')->on('loans')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('loan_restructures', function (Blueprint $table) {
            $table->dropForeign(['new_loan_id']);
            $table->dropColumn('new_loan_id');
        });
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['parent_loan_id']);
            $table->dropColumn('parent_loan_id');
        });
    }
};
