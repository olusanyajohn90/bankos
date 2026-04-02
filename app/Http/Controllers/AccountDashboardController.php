<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountDashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenantId  = Auth::user()->tenant_id;
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));
        $filterType = $request->input('account_type');
        $filterBranch = $request->input('branch_id');

        try {
            // ── Branches for filter dropdown ──
            $branches = DB::table('branches')
                ->where('tenant_id', $tenantId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            // ── KPI: Total Accounts ──
            $totalAccounts = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->count();

            // ── KPI: Total Deposits ──
            $totalDeposits = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->sum('available_balance');

            // ── KPI: Active Accounts ──
            $activeAccounts = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->count();

            // ── KPI: Average Balance ──
            $avgBalance = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->avg('available_balance') ?? 0;

            // ── KPI: Accounts Opened This Month ──
            $openedThisMonth = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->whereRaw("created_at >= ?", [Carbon::now()->startOfMonth()])
                ->count();

            // ── KPI: Dormant Accounts (no transaction in 90 days) ──
            $dormantAccounts = DB::table('accounts')
                ->where('accounts.tenant_id', $tenantId)
                ->where('accounts.status', 'active')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('transactions')
                        ->whereColumn('transactions.account_id', 'accounts.id')
                        ->whereRaw("transactions.created_at >= ?", [Carbon::now()->subDays(90)]);
                })
                ->count();

            // ── KPI: Frozen Accounts ──
            $frozenAccounts = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->where('status', 'frozen')
                ->count();

            // ── KPI: Closed Accounts This Month ──
            $closedThisMonth = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->where('status', 'closed')
                ->whereRaw("closed_at >= ?", [Carbon::now()->startOfMonth()])
                ->count();

            // ── KPI: Total Ledger Balance ──
            $totalLedgerBalance = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->sum('ledger_balance');

            // ── KPI: PND Accounts ──
            $pndAccounts = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->where('pnd_active', true)
                ->count();

            // ── CHART: Accounts by Type (pie) ──
            $accountsByType = DB::table('accounts')
                ->select('type', DB::raw('COUNT(*) as count'))
                ->where('tenant_id', $tenantId)
                ->groupBy('type')
                ->pluck('count', 'type');

            // ── CHART: Accounts by Status (bar) ──
            $accountsByStatus = DB::table('accounts')
                ->select('status', DB::raw('COUNT(*) as count'))
                ->where('tenant_id', $tenantId)
                ->groupBy('status')
                ->pluck('count', 'status');

            // ── CHART: Account Opening Trend (last 12 months) ──
            $openingTrend = DB::table('accounts')
                ->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                    DB::raw('COUNT(*) as count')
                )
                ->where('tenant_id', $tenantId)
                ->whereRaw("created_at >= ?", [Carbon::now()->subMonths(12)->startOfMonth()])
                ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM')"))
                ->orderBy('month')
                ->pluck('count', 'month');

            // ── TABLE: Top 10 Accounts by Balance ──
            $topAccounts = DB::table('accounts')
                ->join('customers', 'accounts.customer_id', '=', 'customers.id')
                ->select(
                    'accounts.id',
                    'accounts.account_number',
                    'accounts.type',
                    'accounts.available_balance',
                    DB::raw("CONCAT(customers.first_name, ' ', customers.last_name) as customer_name")
                )
                ->where('accounts.tenant_id', $tenantId)
                ->where('accounts.status', 'active')
                ->orderByDesc('accounts.available_balance')
                ->limit(10)
                ->get();

            // ── CHART: Accounts by Branch ──
            $accountsByBranch = DB::table('accounts')
                ->join('branches', 'accounts.branch_id', '=', 'branches.id')
                ->select('branches.name', DB::raw('COUNT(*) as count'))
                ->where('accounts.tenant_id', $tenantId)
                ->groupBy('branches.name')
                ->orderByDesc('count')
                ->limit(15)
                ->pluck('count', 'name');

            // ── CHART: Balance Distribution ──
            $balanceDistribution = DB::table('accounts')
                ->select(DB::raw("
                    CASE
                        WHEN available_balance < 10000 THEN 'Below 10K'
                        WHEN available_balance >= 10000 AND available_balance < 100000 THEN '10K - 100K'
                        WHEN available_balance >= 100000 AND available_balance < 500000 THEN '100K - 500K'
                        WHEN available_balance >= 500000 AND available_balance < 1000000 THEN '500K - 1M'
                        ELSE 'Above 1M'
                    END as range_label
                "), DB::raw('COUNT(*) as count'))
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->groupBy('range_label')
                ->pluck('count', 'range_label');

            // Sort balance ranges
            $rangeOrder = ['Below 10K', '10K - 100K', '100K - 500K', '500K - 1M', 'Above 1M'];
            $sortedBalanceDistribution = collect($rangeOrder)->mapWithKeys(function ($range) use ($balanceDistribution) {
                return [$range => $balanceDistribution->get($range, 0)];
            });

        } catch (\Exception $e) {
            report($e);
            $totalAccounts = $totalDeposits = $activeAccounts = $avgBalance = 0;
            $openedThisMonth = $dormantAccounts = $frozenAccounts = $closedThisMonth = 0;
            $totalLedgerBalance = $pndAccounts = 0;
            $accountsByType = $accountsByStatus = $openingTrend = collect();
            $topAccounts = collect();
            $accountsByBranch = collect();
            $sortedBalanceDistribution = collect();
            $branches = collect();
        }

        return view('accounts.dashboard', compact(
            'totalAccounts', 'totalDeposits', 'activeAccounts', 'avgBalance',
            'openedThisMonth', 'dormantAccounts', 'frozenAccounts', 'closedThisMonth',
            'totalLedgerBalance', 'pndAccounts',
            'accountsByType', 'accountsByStatus', 'openingTrend',
            'topAccounts', 'accountsByBranch', 'sortedBalanceDistribution',
            'branches', 'startDate', 'endDate', 'filterType', 'filterBranch'
        ));
    }
}
