<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('notify_sms')->default(true)->after('status');
            $table->boolean('notify_whatsapp')->default(false)->after('notify_sms');
            $table->boolean('notify_email')->default(true)->after('notify_whatsapp');
            $table->boolean('notify_push')->default(false)->after('notify_email');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['notify_sms', 'notify_whatsapp', 'notify_email', 'notify_push']);
        });
    }
};
