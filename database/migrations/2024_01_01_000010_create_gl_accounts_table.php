<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gl_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('account_number');
            $table->string('name');
            $table->enum('category', ['asset', 'liability', 'equity', 'income', 'expense']);
            $table->integer('level')->default(1);
            $table->uuid('parent_id')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->uuid('branch_id')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id', 'account_number']);
        });

        // Self-referencing FK added separately so PostgreSQL sees the primary key first
        Schema::table('gl_accounts', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('gl_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gl_accounts');
    }
};
