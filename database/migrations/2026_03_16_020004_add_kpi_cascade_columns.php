<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_targets', function (Blueprint $table) {
            $table->uuid('parent_target_id')->nullable()->after('period_value');
            $table->decimal('weight_pct', 5, 2)->default(100)->after('parent_target_id');
            $table->boolean('is_cascaded')->default(false)->after('weight_pct');
        });
    }

    public function down(): void
    {
        Schema::table('kpi_targets', function (Blueprint $table) {
            $table->dropColumn(['parent_target_id', 'weight_pct', 'is_cascaded']);
        });
    }
};
