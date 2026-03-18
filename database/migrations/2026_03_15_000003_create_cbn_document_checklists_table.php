<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbn_document_checklists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            $table->enum('entity_type', ['customer', 'loan', 'account', 'staff_profile', 'branch']);
            $table->string('document_type', 80);
            $table->string('document_label', 255);

            $table->boolean('is_required')->default(true);
            $table->string('applies_to', 50)->nullable();

            $table->tinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['tenant_id', 'entity_type', 'document_type', 'applies_to'], 'cbn_checklist_unique');
            $table->index(['tenant_id', 'entity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbn_document_checklists');
    }
};
