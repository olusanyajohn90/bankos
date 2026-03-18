<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_job_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('job_name');
            $table->enum('status', ['success', 'failed', 'running'])->default('running');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->integer('records')->default(0);
            $table->integer('errors')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['job_name', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_job_runs');
    }
};
