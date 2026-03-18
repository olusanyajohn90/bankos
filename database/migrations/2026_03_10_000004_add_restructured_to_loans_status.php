<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE loans DROP CONSTRAINT IF EXISTS loans_status_check");
            DB::statement("ALTER TABLE loans ADD CONSTRAINT loans_status_check CHECK (status IN ('pending','approved','active','overdue','closed','written_off','rejected','restructured'))");
        } else {
            DB::statement("ALTER TABLE loans MODIFY status ENUM('pending','approved','active','overdue','closed','written_off','rejected','restructured') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE loans DROP CONSTRAINT IF EXISTS loans_status_check");
            DB::statement("ALTER TABLE loans ADD CONSTRAINT loans_status_check CHECK (status IN ('pending','approved','active','overdue','closed','written_off','rejected'))");
            // Rows with 'restructured' will violate the constraint — handle manually if needed
        } else {
            DB::statement("ALTER TABLE loans MODIFY status ENUM('pending','approved','active','overdue','closed','written_off','rejected') DEFAULT 'pending'");
        }
    }
};
