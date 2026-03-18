<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name', 100);
            $table->time('work_start_time')->default('08:00:00');
            $table->time('work_end_time')->default('17:00:00');
            $table->unsignedSmallInteger('grace_minutes')->default(15);
            $table->decimal('daily_work_hours', 4, 2)->default(8.00);
            $table->unsignedSmallInteger('half_day_hours')->default(4);
            $table->boolean('allow_overtime')->default(true);
            $table->boolean('is_default')->default(false);
            $table->json('working_days')->nullable(); // [1,2,3,4,5] = Mon-Fri
            $table->timestamps();
            $table->index(['tenant_id', 'is_default']);
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('staff_profile_id');
            $table->date('date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->time('expected_in')->nullable();
            $table->time('expected_out')->nullable();
            $table->enum('status', [
                'present', 'absent', 'late', 'half_day',
                'excused', 'on_leave', 'public_holiday', 'weekend'
            ])->default('present');
            $table->unsignedSmallInteger('minutes_late')->default(0);
            $table->decimal('hours_worked', 5, 2)->nullable();
            $table->decimal('overtime_hours', 5, 2)->nullable();
            $table->boolean('is_manually_adjusted')->default(false);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('marked_by')->nullable();
            $table->timestamps();
            $table->unique(['staff_profile_id', 'date'], 'attendance_profile_date_unique');
            $table->index(['tenant_id', 'date']);
            $table->index(['staff_profile_id', 'date']);
            $table->foreign('staff_profile_id')->references('id')->on('staff_profiles')->cascadeOnDelete();
        });

        Schema::create('shift_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('staff_profile_id');
            $table->uuid('policy_id');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->index(['staff_profile_id', 'effective_from'], 'shift_profile_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_schedules');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_policies');
    }
};
