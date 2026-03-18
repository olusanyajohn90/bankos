<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── platform_events ────────────────────────────────────────────────────
        if (! Schema::hasTable('platform_events')) {
            Schema::create('platform_events', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('event_type')->index();   // e.g. 'loan.approved'
                $table->string('entity_type');            // 'loan', 'account', 'transaction', 'customer'
                $table->uuid('entity_id');
                $table->string('actor_type');             // 'customer', 'staff', 'system'
                $table->unsignedBigInteger('actor_id')->nullable()->nullable();
                $table->json('metadata')->nullable();
                $table->decimal('amount', 15, 2)->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'event_type']);
                $table->index(['tenant_id', 'created_at']);
                $table->index(['event_type', 'created_at']);
            });
        }

        // ── super_admin_access_log ─────────────────────────────────────────────
        if (! Schema::hasTable('super_admin_access_log')) {
            Schema::create('super_admin_access_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                $table->uuid('tenant_accessed')->nullable();
                $table->string('action');
                $table->string('ip_address')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('super_admin_access_log');
        Schema::dropIfExists('platform_events');
    }
};
