<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id')->nullable();
            $table->unsignedBigInteger('team_lead_id')->nullable(); // FK → users.id (bigint)
            $table->string('name');
            $table->string('department', 50);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('team_lead_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['tenant_id', 'branch_id']);
            $table->index(['tenant_id', 'department']);
        });

        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('user_id')->unique(); // FK → users.id (bigint)
            $table->uuid('branch_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable(); // FK → users.id (bigint)
            $table->uuid('team_id')->nullable();
            $table->string('department', 50)->nullable();
            $table->string('job_title')->nullable();
            $table->string('staff_code', 30)->nullable();
            $table->string('referral_code', 20)->nullable();
            $table->date('joined_date')->nullable();
            $table->enum('employment_type', ['full_time', 'contract', 'intern'])->default('full_time');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->unique(['tenant_id', 'referral_code']);
            $table->unique(['tenant_id', 'staff_code']);
            $table->index(['tenant_id', 'department']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('teams');
    }
};
