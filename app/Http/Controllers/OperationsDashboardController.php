<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OperationsDashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenantId  = Auth::user()->tenant_id;
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));

        try {
            $today      = Carbon::today();
            $monthStart = Carbon::now()->startOfMonth();

            // ── KPI: Daily Transaction Summary ──
            $dailyTxnCount = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->whereRaw("created_at >= ?", [$today])
                ->count();
            $dailyTxnVolume = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('status', 'success')
                ->whereRaw("created_at >= ?", [$today])
                ->sum('amount');

            // ── KPI: Cash Position (total available balance across all accounts) ──
            $cashPosition = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->sum('available_balance');

            // ── KPI: Fee Revenue This Month ──
            $feeRevenue = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('type', 'fee')
                ->where('status', 'success')
                ->whereRaw("created_at >= ?", [$monthStart])
                ->sum('amount');

            // ── KPI: Interest Income This Month ──
            $interestIncome = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('type', 'interest')
                ->where('status', 'success')
                ->whereRaw("created_at >= ?", [$monthStart])
                ->sum('amount');

            // ── KPI: Active Agents ──
            $activeAgents = DB::table('agents')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->count();

            // ── KPI: Total Agent Float Balance ──
            $agentFloat = DB::table('agents')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->sum('float_balance');

            // ── KPI: Total Customers ──
            $totalCustomers = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->count();

            // ── KPI: Total Loans Active ──
            $activeLoans = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->count();

            // ── KPI: Failed Jobs / Pending Queues ──
            $failedJobs = 0;
            try {
                $failedJobs = DB::table('failed_jobs')->count();
            } catch (\Exception $e) {
                // table may not exist
            }

            // ── KPI: Pending Transactions ──
            $pendingTxns = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->count();

            // ── KPI: Disbursements Today ──
            $disbursementsToday = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('type', 'disbursement')
                ->where('status', 'success')
                ->whereRaw("created_at >= ?", [$today])
                ->sum('amount');

            // ── CHART: GL Balance Overview (top 10 GL accounts) ──
            $glBalances = DB::table('gl_accounts')
                ->select('name', 'account_number', 'balance', 'category')
                ->where('tenant_id', $tenantId)
                ->orderByDesc(DB::raw('ABS(balance)'))
                ->limit(10)
                ->get();

            // ── CHART: Branch Performance Comparison ──
            $branchPerformance = DB::table('branches')
                ->leftJoin('accounts', function ($join) use ($tenantId) {
                    $join->on('branches.id', '=', 'accounts.branch_id')
                        ->where('accounts.tenant_id', $tenantId)
                        ->where('accounts.status', 'active');
                })
                ->leftJoin('customers', function ($join) use ($tenantId) {
                    $join->on('branches.id', '=', 'customers.branch_id')
                        ->where('customers.tenant_id', $tenantId);
                })
                ->select(
                    'branches.name',
                    DB::raw('COUNT(DISTINCT accounts.id) as account_count'),
                    DB::raw('COALESCE(SUM(accounts.available_balance),0) as total_deposits'),
                    DB::raw('COUNT(DISTINCT customers.id) as customer_count')
                )
                ->where('branches.tenant_id', $tenantId)
                ->groupBy('branches.id', 'branches.name')
                ->orderByDesc('total_deposits')
                ->get();

            // ── CHART: Teller Activity (transactions per performer today) ──
            $tellerActivity = DB::table('transactions')
                ->join('users', 'transactions.performed_by', '=', 'users.id')
                ->select('users.name', DB::raw('COUNT(*) as txn_count'), DB::raw('SUM(transactions.amount) as txn_volume'))
                ->where('transactions.tenant_id', $tenantId)
                ->whereRaw("transactions.created_at >= ?", [$today])
                ->whereNotNull('transactions.performed_by')
                ->groupBy('users.name')
                ->orderByDesc('txn_count')
                ->limit(10)
                ->get();

            // ── CHART: Daily Volume Trend (last 30 days) ──
            $dailyVolumeTrend = DB::table('transactions')
                ->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD') as day"),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('COALESCE(SUM(amount),0) as volume')
                )
                ->where('tenant_id', $tenantId)
                ->where('status', 'success')
                ->whereRaw("created_at >= ?", [Carbon::now()->subDays(30)])
                ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD')"))
                ->orderBy('day')
                ->get();

            // ── CHART: Revenue Breakdown (fees, interest, other) ──
            $revenueBreakdown = DB::table('transactions')
                ->select('type', DB::raw('COALESCE(SUM(amount),0) as total'))
                ->where('tenant_id', $tenantId)
                ->where('status', 'success')
                ->whereIn('type', ['fee', 'interest'])
                ->whereRaw("created_at >= ?", [$monthStart])
                ->groupBy('type')
                ->pluck('total', 'type');

        } catch (\Exception $e) {
            report($e);
            $dailyTxnCount = $dailyTxnVolume = $cashPosition = 0;
            $feeRevenue = $interestIncome = $activeAgents = $agentFloat = 0;
            $totalCustomers = $activeLoans = $failedJobs = $pendingTxns = 0;
            $disbursementsToday = 0;
            $glBalances = collect();
            $branchPerformance = collect();
            $tellerActivity = collect();
            $dailyVolumeTrend = collect();
            $revenueBreakdown = collect();
        }

        return view('operations.dashboard', compact(
            'dailyTxnCount', 'dailyTxnVolume', 'cashPosition',
            'feeRevenue', 'interestIncome', 'activeAgents', 'agentFloat',
            'totalCustomers', 'activeLoans', 'failedJobs', 'pendingTxns',
            'disbursementsToday',
            'glBalances', 'branchPerformance', 'tellerActivity',
            'dailyVolumeTrend', 'revenueBreakdown',
            'startDate', 'endDate'
        ));
    }
}
