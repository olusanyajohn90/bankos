<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Schema;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Return whether a portal-side table exists in the current DB.
     * Portal tables (kyc_upgrade_requests, loan_applications, portal_disputes, etc.)
     * are created by the bankos-portal project and may be absent when portal hasn't
     * been migrated to the same DB engine yet.
     */
    protected function portalTableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    /**
     * Abort with a user-friendly 503 if a portal table is missing.
     * Use at the top of controller actions that are entirely portal-dependent.
     */
    protected function requirePortalTable(string $table, string $label = 'This feature'): void
    {
        if (! Schema::hasTable($table)) {
            abort(503, "{$label} requires the customer portal database tables, which are not yet available in this environment.");
        }
    }
}
