<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('short_name')->unique();
            $table->enum('type', ['bank', 'lender', 'cooperative']);
            $table->string('account_prefix', 3);
            $table->string('primary_currency', 3)->default('NGN');
            $table->json('supported_currencies')->nullable();
            $table->string('domain')->nullable();
            $table->string('cbn_license_number')->nullable();
            $table->string('nibss_institution_code')->nullable();
            $table->string('routing_number')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->json('address')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
