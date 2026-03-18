<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable()->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->uuid('branch_id')->nullable()->after('phone');
            $table->boolean('must_change_password')->default(false)->after('password');
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('active')->after('must_change_password');

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'phone', 'branch_id', 'must_change_password', 'status']);
        });
    }
};
