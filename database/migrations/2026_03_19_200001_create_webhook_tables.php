<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('url', 500);
            $table->string('secret', 100)->comment('Random 32-char string, shown once on creation');
            $table->json('events')->comment('Array of subscribed event names');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('failure_count')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants')
                  ->cascadeOnDelete();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('webhook_delivery_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('tenant_id');
            $table->uuid('endpoint_id');
            $table->string('event', 100);
            $table->json('payload');
            $table->smallInteger('response_code')->unsigned()->nullable();
            $table->text('response_body')->nullable();
            $table->tinyInteger('attempt_count')->unsigned()->default(1);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('endpoint_id')
                  ->references('id')
                  ->on('webhook_endpoints')
                  ->cascadeOnDelete();

            $table->index(['tenant_id', 'event']);
            $table->index(['endpoint_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_delivery_logs');
        Schema::dropIfExists('webhook_endpoints');
    }
};
