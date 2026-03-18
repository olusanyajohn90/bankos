<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('card_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name', 100);
            $table->string('primary_color', 7)->default('#1e40af');
            $table->string('secondary_color', 7)->default('#1d4ed8');
            $table->string('text_color', 7)->default('#ffffff');
            $table->string('background_color', 7)->default('#f1f5f9');
            $table->string('logo_path', 500)->nullable();
            $table->string('background_image_path', 500)->nullable();
            $table->boolean('show_qr')->default(true);
            $table->boolean('show_photo')->default(true);
            $table->boolean('show_department')->default(true);
            $table->boolean('show_grade')->default(true);
            $table->boolean('show_blood_group')->default(false);
            $table->boolean('show_emergency_contact')->default(false);
            $table->tinyInteger('expiry_years')->default(2);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'is_default']);
        });

        Schema::create('id_card_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name', 100);
            $table->integer('total_count')->default(0);
            $table->integer('generated_count')->default(0);
            $table->enum('status', ['draft', 'generating', 'ready', 'printing', 'distributed'])->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('staff_id_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('staff_profile_id');
            $table->uuid('template_id')->nullable();
            $table->uuid('batch_id')->nullable();
            $table->string('card_number', 30)->unique();
            $table->date('issued_date');
            $table->date('expiry_date');
            $table->enum('status', ['active', 'expired', 'lost', 'replaced', 'cancelled'])->default('active');
            $table->string('photo_path', 500)->nullable();
            $table->string('qr_payload', 500)->nullable();
            $table->string('pdf_path', 500)->nullable();
            $table->uuid('replaced_by')->nullable();          // self-referential for replacements
            $table->date('loss_report_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['staff_profile_id', 'status']);
            $table->foreign('staff_profile_id')->references('id')->on('staff_profiles')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_id_cards');
        Schema::dropIfExists('id_card_batches');
        Schema::dropIfExists('card_templates');
    }
};
