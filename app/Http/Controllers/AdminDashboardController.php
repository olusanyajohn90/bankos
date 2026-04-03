<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        $today    = now()->toDateString();

        try {
            // ── System Overview ──
            $totalUsers = DB::table('users')
                ->where('tenant_id', $tenantId)
                ->count();

            $activeUsers = DB::table('users')
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->count();

            $totalRoles = DB::table('roles')
                ->where('tenant_id', $tenantId)
                ->count();

            $totalPermissions = DB::table('permissions')->count();

            // ── User Activity ──
            $loginsToday = DB::table('audit_logs')
                ->where('tenant_id', $tenantId)
                ->where('action', 'login')
                ->whereDate('created_at', $today)
                ->count();

            $activeSessions = DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subMinutes(30)->timestamp)
                ->count();

            $uniqueLoginsThisWeek = DB::table('audit_logs')
                ->where('tenant_id', $tenantId)
                ->where('action', 'login')
                ->whereDate('created_at', '>=', now()->startOfWeek())
                ->distinct('user_id')
                ->count('user_id');

            // ── Audit Log Summary ──
            $auditActionsToday = DB::table('audit_logs')
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', $today)
                ->count();

            $recentAuditLogs = DB::table('audit_logs')
                ->where('tenant_id', $tenantId)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            $auditByAction = DB::table('audit_logs')
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->select('action', DB::raw("count(*) as total"))
                ->groupBy('action')
                ->orderByDesc('total')
                ->limit(10)
                ->pluck('total', 'action');

            // ── Feature Flags Status ──
            $featureFlagsTotal = DB::table('feature_flags')
                ->where('tenant_id', $tenantId)
                ->count();

            $featureFlagsEnabled = DB::table('feature_flags')
                ->where('tenant_id', $tenantId)
                ->where('is_enabled', true)
                ->count();

            // ── Subscription / Tenant Info ──
            $tenant = DB::table('tenants')
                ->where('id', $tenantId)
                ->first();

            $subscription = DB::table('subscriptions')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->first();

            // ── Database Stats (key table row counts) ──
            $dbStats = [];
            $keyTables = ['customers', 'accounts', 'transactions', 'loans', 'users', 'documents', 'aml_alerts', 'support_tickets'];
            foreach ($keyTables as $table) {
                try {
                    $dbStats[$table] = DB::table($table)->where('tenant_id', $tenantId)->count();
                } catch (\Exception $e) {
                    $dbStats[$table] = 0;
                }
            }

            // ── Storage Usage ──
            $storageBytes = DB::table('documents')
                ->where('tenant_id', $tenantId)
                ->sum('file_size');
            $storageFormatted = $storageBytes > 0 ? round($storageBytes / (1024 * 1024), 1) : 0;

            // ── API Usage ──
            $apiCallsToday = DB::table('audit_logs')
                ->where('tenant_id', $tenantId)
                ->where('action', 'like', 'api.%')
                ->whereDate('created_at', $today)
                ->count();

            $apiCallsThisMonth = DB::table('audit_logs')
                ->where('tenant_id', $tenantId)
                ->where('action', 'like', 'api.%')
                ->whereDate('created_at', '>=', now()->startOfMonth())
                ->count();

            // ── Security Alerts ──
            $failedLoginsToday = DB::table('audit_logs')
                ->where('tenant_id', $tenantId)
                ->where('action', 'login_failed')
                ->whereDate('created_at', $today)
                ->count();

            $lockedAccounts = DB::table('users')
                ->where('tenant_id', $tenantId)
                ->where('is_active', false)
                ->count();

            $failedLoginsWeek = DB::table('audit_logs')
                ->where('tenant_id', $tenantId)
                ->where('action', 'login_failed')
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->count();

            $usersWithout2fa = DB::table('users')
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->whereNull('two_factor_confirmed_at')
                ->count();

            // ── Charts: Login trend (last 14 days) ──
            $loginTrend = DB::table('audit_logs')
                ->where('tenant_id', $tenantId)
                ->where('action', 'login')
                ->whereDate('created_at', '>=', now()->subDays(14))
                ->select(DB::raw("DATE(created_at) as date"), DB::raw("count(*) as total"))
                ->groupBy(DB::raw("DATE(created_at)"))
                ->orderBy('date')
                ->get();

            // ── Charts: Users by role ──
            $usersByRole = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('roles.tenant_id', $tenantId)
                ->select('roles.name as role_name', DB::raw("count(*) as total"))
                ->groupBy('roles.name')
                ->pluck('total', 'role_name');

            // ── Charts: Audit actions (last 7 days trend) ──
            $auditTrend = DB::table('audit_logs')
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->select(DB::raw("DATE(created_at) as date"), DB::raw("count(*) as total"))
                ->groupBy(DB::raw("DATE(created_at)"))
                ->orderBy('date')
                ->get();

        } catch (\Exception $e) {
            $totalUsers = $activeUsers = $totalRoles = $totalPermissions = 0;
            $loginsToday = $activeSessions = $uniqueLoginsThisWeek = 0;
            $auditActionsToday = 0;
            $recentAuditLogs = collect();
            $auditByAction = collect();
            $featureFlagsTotal = $featureFlagsEnabled = 0;
            $tenant = null;
            $subscription = null;
            $dbStats = [];
            $storageFormatted = 0;
            $apiCallsToday = $apiCallsThisMonth = 0;
            $failedLoginsToday = $lockedAccounts = $failedLoginsWeek = $usersWithout2fa = 0;
            $loginTrend = collect();
            $usersByRole = collect();
            $auditTrend = collect();
        }

        return view('admin.dashboard', compact(
            'totalUsers', 'activeUsers', 'totalRoles', 'totalPermissions',
            'loginsToday', 'activeSessions', 'uniqueLoginsThisWeek',
            'auditActionsToday', 'recentAuditLogs', 'auditByAction',
            'featureFlagsTotal', 'featureFlagsEnabled',
            'tenant', 'subscription',
            'dbStats', 'storageFormatted',
            'apiCallsToday', 'apiCallsThisMonth',
            'failedLoginsToday', 'lockedAccounts', 'failedLoginsWeek', 'usersWithout2fa',
            'loginTrend', 'usersByRole', 'auditTrend'
        ));
    }
}
