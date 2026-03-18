<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('customers', function (Blueprint $table) {
            $table->uuid('branch_id')->nullable()->after('tenant_id');
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });
        Schema::table('accounts', function (Blueprint $table) {
            $table->uuid('branch_id')->nullable()->after('tenant_id');
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });
        Schema::table('loans', function (Blueprint $table) {
            $table->uuid('branch_id')->nullable()->after('tenant_id');
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('customers', fn($t) => $t->dropForeign(['branch_id']));
        Schema::table('customers', fn($t) => $t->dropColumn('branch_id'));
        Schema::table('accounts', fn($t) => $t->dropForeign(['branch_id']));
        Schema::table('accounts', fn($t) => $t->dropColumn('branch_id'));
        Schema::table('loans', fn($t) => $t->dropForeign(['branch_id']));
        Schema::table('loans', fn($t) => $t->dropColumn('branch_id'));
    }
};
