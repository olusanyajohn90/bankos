<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('logo_url');
            $table->string('primary_color', 7)->default('#2563eb')->after('logo_path');
            $table->string('secondary_color', 7)->default('#0c2461')->after('primary_color');
            $table->string('portal_domain')->nullable()->after('secondary_color');
            $table->string('subscription_plan', 20)->default('trial')->after('portal_domain');
            $table->timestamp('onboarding_completed_at')->nullable()->after('subscription_plan');
            $table->tinyInteger('onboarding_step')->default(0)->after('onboarding_completed_at');
            $table->timestamp('suspended_at')->nullable()->after('onboarding_step');
            $table->text('suspension_reason')->nullable()->after('suspended_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path',
                'primary_color',
                'secondary_color',
                'portal_domain',
                'subscription_plan',
                'onboarding_completed_at',
                'onboarding_step',
                'suspended_at',
                'suspension_reason',
            ]);
        });
    }
};
