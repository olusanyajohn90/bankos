<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('data_source', 50);
            $table->json('selected_columns');
            $table->json('filters')->nullable();
            $table->string('sort_column', 50)->nullable();
            $table->enum('sort_direction', ['asc', 'desc'])->default('asc');
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->timestamp('last_run_at')->nullable();
            $table->integer('last_run_row_count')->nullable();
            $table->timestamps();
        });

        Schema::create('custom_report_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->foreign('report_id')->references('id')->on('custom_reports')->onDelete('cascade');
            $table->uuid('tenant_id');
            $table->enum('frequency', ['daily', 'weekly', 'monthly']);
            $table->tinyInteger('day_of_week')->nullable();
            $table->tinyInteger('day_of_month')->nullable();
            $table->string('time', 5)->default('08:00');
            $table->json('recipients');
            $table->enum('format', ['csv', 'pdf']);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_report_schedules');
        Schema::dropIfExists('custom_reports');
    }
};
