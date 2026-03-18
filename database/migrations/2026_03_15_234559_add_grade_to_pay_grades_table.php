<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pay_grades', function (Blueprint $table) {
            // Grade within a level: Level 1 Grade 1, Level 1 Grade 2, Level 1 Grade 3
            $table->unsignedTinyInteger('grade')->default(1)->after('level');

            // Step increment % applied at annual review
            $table->decimal('annual_increment_pct', 5, 2)->default(0)->after('basic_max')
                  ->comment('% pay increase at annual step review');

            // Annual leave allowance paid as a % of basic salary
            $table->decimal('leave_allowance_pct', 5, 2)->default(10)->after('annual_increment_pct')
                  ->comment('Leave allowance paid as % of basic salary');

            // Typical job title at this level/grade
            $table->string('typical_title', 100)->nullable()->after('leave_allowance_pct');

            // Drop old tenant+code unique index (will be re-added after adding grade column)
            $table->dropUnique('pay_grades_tenant_id_code_unique');
        });

        Schema::table('pay_grades', function (Blueprint $table) {
            $table->unique(['tenant_id', 'code'],           'pay_grades_tenant_id_code_unique');
            $table->unique(['tenant_id', 'level', 'grade'], 'pay_grades_tenant_level_grade_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pay_grades', function (Blueprint $table) {
            $table->dropUnique('pay_grades_tenant_level_grade_unique');
            $table->dropUnique('pay_grades_tenant_id_code_unique');
            $table->dropColumn(['grade', 'annual_increment_pct', 'leave_allowance_pct', 'typical_title']);
        });

        Schema::table('pay_grades', function (Blueprint $table) {
            $table->unique(['tenant_id', 'code'], 'pay_grades_tenant_id_code_unique');
        });
    }
};
