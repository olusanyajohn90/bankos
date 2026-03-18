<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name', 100);
            $table->string('code', 20)->nullable();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('depreciation_years')->default(5);
            $table->enum('depreciation_method', ['straight_line', 'reducing_balance', 'none'])->default('straight_line');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('category_id');
            $table->string('name', 200);
            $table->string('asset_tag', 50)->nullable();       // e.g. AST-2026-00001
            $table->string('serial_number', 100)->nullable();
            $table->string('model', 150)->nullable();
            $table->string('manufacturer', 100)->nullable();
            $table->string('vendor', 150)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('current_value', 15, 2)->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->enum('condition', ['new', 'good', 'fair', 'poor', 'beyond_repair'])->default('new');
            $table->enum('status', ['available', 'assigned', 'under_maintenance', 'disposed', 'lost'])->default('available');
            $table->string('location', 200)->nullable();
            $table->uuid('branch_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('invoice_number', 100)->nullable();
            $table->string('photo_path', 500)->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'category_id']);
            $table->index(['asset_tag']);
        });

        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('asset_id');
            $table->uuid('staff_profile_id');
            $table->date('assigned_date');
            $table->date('returned_date')->nullable();
            $table->enum('condition_at_assignment', ['new', 'good', 'fair', 'poor'])->default('good');
            $table->enum('condition_at_return', ['good', 'fair', 'poor', 'damaged'])->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('assigned_by');
            $table->unsignedBigInteger('received_by')->nullable();
            $table->timestamps();
            $table->index(['asset_id', 'returned_date'], 'asset_assign_active_idx');
            $table->index(['staff_profile_id', 'returned_date'], 'asset_staff_active_idx');
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
            $table->foreign('staff_profile_id')->references('id')->on('staff_profiles')->cascadeOnDelete();
        });

        Schema::create('asset_maintenance_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('asset_id');
            $table->enum('maintenance_type', ['routine', 'repair', 'upgrade', 'inspection', 'disposal_prep'])->default('routine');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('vendor', 150)->nullable();
            $table->text('description');
            $table->text('findings')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->unsignedBigInteger('logged_by');
            $table->timestamps();
            $table->index(['asset_id', 'status']);
            $table->index(['tenant_id', 'scheduled_date']);
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
        });

        Schema::create('procurement_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('category_id')->nullable();
            $table->string('item_name', 200);
            $table->text('justification');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->string('vendor_name', 150)->nullable();
            $table->string('vendor_quote_ref', 100)->nullable();
            $table->string('urgency', 20)->default('normal'); // normal, urgent, critical
            $table->enum('status', ['draft','pending','approved','rejected','ordered','received','cancelled'])->default('draft');
            $table->uuid('approval_request_id')->nullable(); // links to approval_requests
            $table->uuid('asset_id')->nullable();             // filled once asset is registered post-delivery
            $table->date('required_by_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('requested_by');
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index(['requested_by', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_requests');
        Schema::dropIfExists('asset_maintenance_logs');
        Schema::dropIfExists('asset_assignments');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
    }
};
