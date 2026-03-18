<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bureau_reports', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('reference');
            $table->string('original_filename')->nullable()->after('file_path');
            $table->longText('raw_text')->nullable()->after('raw_response');
            $table->longText('parsed_data')->nullable()->after('raw_text'); // JSON
            $table->timestamp('uploaded_at')->nullable()->after('retrieved_at');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE bureau_reports DROP CONSTRAINT IF EXISTS bureau_reports_status_check");
            DB::statement("ALTER TABLE bureau_reports ADD CONSTRAINT bureau_reports_status_check CHECK (status IN ('pending','retrieved','failed','uploaded','parsed'))");
        } else {
            DB::statement("ALTER TABLE bureau_reports MODIFY COLUMN status ENUM('pending','retrieved','failed','uploaded','parsed') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        Schema::table('bureau_reports', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'original_filename', 'raw_text', 'parsed_data', 'uploaded_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE bureau_reports DROP CONSTRAINT IF EXISTS bureau_reports_status_check");
            DB::statement("ALTER TABLE bureau_reports ADD CONSTRAINT bureau_reports_status_check CHECK (status IN ('pending','retrieved','failed'))");
        } else {
            DB::statement("ALTER TABLE bureau_reports MODIFY COLUMN status ENUM('pending','retrieved','failed') DEFAULT 'pending'");
        }
    }
};
