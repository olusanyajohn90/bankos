<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Portal investment products (configurable rates/durations per tenant)
        Schema::create('portal_investment_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->integer('duration_days');
            $table->decimal('interest_rate', 6, 2); // annual %
            $table->decimal('min_amount', 15, 2)->default(10000);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Admin responses to portal disputes (table lives in bankos-portal migrations)
        if (Schema::hasTable('portal_disputes')) {
            Schema::table('portal_disputes', function (Blueprint $table) {
                if (!Schema::hasColumn('portal_disputes', 'admin_response')) {
                    $table->text('admin_response')->nullable()->after('resolution_notes');
                    $table->foreignId('admin_responded_by')->nullable()->constrained('users')->nullOnDelete()->after('admin_response');
                    $table->timestamp('admin_responded_at')->nullable()->after('admin_responded_by');
                }
            });
        }

        // Referral payout tracking (table lives in bankos-portal migrations)
        if (Schema::hasTable('portal_referrals')) {
            Schema::table('portal_referrals', function (Blueprint $table) {
                if (!Schema::hasColumn('portal_referrals', 'payout_processed_by')) {
                    $table->foreignId('payout_processed_by')->nullable()->constrained('users')->nullOnDelete()->after('rewarded_at');
                    $table->timestamp('payout_processed_at')->nullable()->after('payout_processed_by');
                    $table->text('payout_notes')->nullable()->after('payout_processed_at');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_investment_products');
    }
};
