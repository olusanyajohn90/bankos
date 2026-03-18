<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'portal_password')) {
                $table->string('portal_password')->nullable()->after('portal_active');
            }
            if (!Schema::hasColumn('customers', 'portal_pin')) {
                $table->string('portal_pin', 64)->nullable()->after('portal_password');
            }
            if (!Schema::hasColumn('customers', 'portal_last_login_at')) {
                $table->timestamp('portal_last_login_at')->nullable()->after('portal_pin');
            }
            if (!Schema::hasColumn('customers', 'remember_token')) {
                $table->rememberToken()->nullable()->after('portal_last_login_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $drop = array_filter([
                Schema::hasColumn('customers', 'portal_password')      ? 'portal_password'      : null,
                Schema::hasColumn('customers', 'portal_pin')           ? 'portal_pin'           : null,
                Schema::hasColumn('customers', 'portal_last_login_at') ? 'portal_last_login_at' : null,
                Schema::hasColumn('customers', 'remember_token')       ? 'remember_token'       : null,
            ]);
            if ($drop) $table->dropColumn(array_values($drop));
        });
    }
};
