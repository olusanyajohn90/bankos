<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create regions
        Schema::create('regions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 10);
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        // 2. Create divisions
        Schema::create('divisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['tenant_id']);
        });

        // 3. Create departments
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->uuid('division_id')->nullable();
            $table->foreign('division_id')->references('id')->on('divisions')->nullOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->unsignedBigInteger('head_id')->nullable();
            $table->foreign('head_id')->references('id')->on('users')->nullOnDelete();
            $table->string('cost_centre_code', 20)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'division_id']);
        });

        // 4. Alter branches table
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('manager_id');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->unsignedBigInteger('manager_id')->nullable()->after('state');
            $table->uuid('region_id')->nullable()->after('manager_id');
            $table->foreign('region_id')->references('id')->on('regions')->nullOnDelete();
            $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
        });

        // 5. Alter staff_profiles table
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->uuid('region_id')->nullable()->after('branch_id');
            $table->uuid('department_id')->nullable()->after('region_id');
            $table->string('grade_level', 10)->nullable()->after('department');
            $table->string('cost_centre_code', 20)->nullable()->after('grade_level');
            $table->string('employee_number', 20)->nullable()->after('staff_code');
            $table->date('confirmation_date')->nullable()->after('joined_date');
            $table->date('exit_date')->nullable()->after('confirmation_date');
            $table->enum('exit_reason', ['resigned', 'terminated', 'retired', 'deceased', 'transferred'])->nullable()->after('exit_date');
            $table->foreign('region_id')->references('id')->on('regions')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->unique(['tenant_id', 'employee_number'], 'staff_profiles_tenant_employee_unique');
        });

        // 6. Alter teams table
        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('is_cross_branch')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        // Reverse step 6
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('is_cross_branch');
        });

        // Reverse step 5
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropForeign(['department_id']);
            $table->dropUnique('staff_profiles_tenant_employee_unique');
            $table->dropColumn([
                'region_id', 'department_id', 'grade_level', 'cost_centre_code',
                'employee_number', 'confirmation_date', 'exit_date', 'exit_reason',
            ]);
        });

        // Reverse step 4
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropForeign(['region_id']);
            $table->dropColumn(['manager_id', 'region_id']);
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->uuid('manager_id')->nullable();
        });

        // Reverse steps 3, 2, 1
        Schema::dropIfExists('departments');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('regions');
    }
};
