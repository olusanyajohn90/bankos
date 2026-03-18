<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('centre_id')->nullable();
            $table->uuid('branch_id')->nullable();
            $table->unsignedBigInteger('loan_officer_id')->nullable();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('solidarity_guarantee')->default(false);
            $table->enum('status', ['active', 'inactive', 'dissolved'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('centre_id')->references('id')->on('centres')->onDelete('set null');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('loan_officer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
