<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_feature_flags', function (Blueprint $table) {
            if (!Schema::hasColumn('tenant_feature_flags', 'customer_id')) {
                $table->uuid('customer_id')->nullable()->after('tenant_id');
            }
        });

        // Check existing indexes in a cross-database way
        $indexes = $this->getIndexNames('tenant_feature_flags');

        Schema::table('tenant_feature_flags', function (Blueprint $table) use ($indexes) {
            if (!in_array('tenant_feature_flags_customer_id_index', $indexes)) {
                $table->index('customer_id');
            }

            if (in_array('tenant_feature_flags_tenant_id_feature_key_unique', $indexes)) {
                try { $table->dropForeign(['tenant_id']); } catch (\Exception $e) {}
                $table->dropUnique(['tenant_id', 'feature_key']);
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            }

            if (!in_array('tenant_feature_flags_tenant_id_customer_id_feature_key_unique', $indexes)) {
                $table->unique(['tenant_id', 'customer_id', 'feature_key']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenant_feature_flags', function (Blueprint $table) {
            try { $table->dropForeign(['tenant_id']); } catch (\Exception $e) {}
            try { $table->dropUnique(['tenant_id', 'customer_id', 'feature_key']); } catch (\Exception $e) {}
            try { $table->dropIndex(['customer_id']); } catch (\Exception $e) {}
            try { $table->dropColumn('customer_id'); } catch (\Exception $e) {}
            $table->unique(['tenant_id', 'feature_key']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    private function getIndexNames(string $table): array
    {
        if (DB::getDriverName() === 'pgsql') {
            return DB::table('pg_indexes')
                ->where('tablename', $table)
                ->pluck('indexname')
                ->toArray();
        }

        return collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->unique()
            ->values()
            ->toArray();
    }
};
