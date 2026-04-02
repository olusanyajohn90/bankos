<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionDashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenantId  = Auth::user()->tenant_id;
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));
        $filterType = $request->input('type');
        $filterStatus = $request->input('status');

        try {
            $today      = Carbon::today();
            $weekStart  = Carbon::now()->startOfWeek();
            $monthStart = Carbon::now()->startOfMonth();

            // ── KPI: Total Transactions Today ──
            $txnCountToday = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->whereRaw("created_at >= ?", [$today])
                ->count();

            // ── KPI: Total Transactions This Week ──
            $txnCountWeek = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->whereRaw("created_at >= ?", [$weekStart])
                ->count();

            // ── KPI: Total Transactions This Month ──
            $txnCountMonth = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->whereRaw("created_at >= ?", [$monthStart])
                ->count();

            // ── KPI: Volume Today ──
            $volumeToday = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('status', 'success')
                ->whereRaw("created_at >= ?", [$today])
                ->sum('amount');

            // ── KPI: Volume This Week ──
            $volumeWeek = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('status', 'success')
                ->whereRaw("created_at >= ?", [$weekStart])
                ->sum('amount');

            // ── KPI: Volume This Month ──
            $volumeMonth = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('status', 'success')
                ->whereRaw("created_at >= ?", [$monthStart])
                ->sum('amount');

            // ── KPI: Average Transaction Size ──
            $avgTxnSize = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('status', 'success')
                ->whereRaw("created_at >= ?", [$monthStart])
                ->avg('amount') ?? 0;

            // ── KPI: Failed Transaction Rate ──
            $failedCount = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('status', 'failed')
                ->whereRaw("created_at >= ?", [$monthStart])
                ->count();
            $failedRate = $txnCountMonth > 0
                ? round(($failedCount / $txnCountMonth) * 100, 2) : 0;

            // ── KPI: Pending Transactions ──
            $pendingTxns = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->count();

            // ── KPI: Reversed Transactions This Month ──
            $reversedTxns = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('status', 'reversed')
                ->whereRaw("created_at >= ?", [$monthStart])
                ->count();

            // ── KPI: Success Rate ──
            $successCount = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('status', 'success')
                ->whereRaw("created_at >= ?", [$monthStart])
                ->count();
            $successRate = $txnCountMonth > 0
                ? round(($successCount / $txnCountMonth) * 100, 1) : 0;

            // ── CHART: Transactions by Type (pie) ──
            $txnByType = DB::table('transactions')
                ->select('type', DB::raw('COUNT(*) as count'))
                ->where('tenant_id', $tenantId)
                ->whereRaw("created_at >= ?", [$monthStart])
                ->groupBy('type')
                ->pluck('count', 'type');

            // ── CHART: Transaction by Status ──
            $txnByStatus = DB::table('transactions')
                ->select('status', DB::raw('COUNT(*) as count'))
                ->where('tenant_id', $tenantId)
                ->whereRaw("created_at >= ?", [$monthStart])
                ->groupBy('status')
                ->pluck('count', 'status');

            // ── CHART: Transaction Trend (last 30 days line chart) ──
            $txnTrend = DB::table('transactions')
                ->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD') as day"),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('COALESCE(SUM(amount),0) as volume')
                )
                ->where('tenant_id', $tenantId)
                ->whereRaw("created_at >= ?", [Carbon::now()->subDays(30)])
                ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD')"))
                ->orderBy('day')
                ->get();

            // ── CHART: Hourly Distribution (today) ──
            $hourlyDist = DB::table('transactions')
                ->select(
                    DB::raw("EXTRACT(HOUR FROM created_at)::int as hour"),
                    DB::raw('COUNT(*) as count')
                )
                ->where('tenant_id', $tenantId)
                ->whereRaw("created_at >= ?", [$today])
                ->groupBy(DB::raw("EXTRACT(HOUR FROM created_at)::int"))
                ->orderBy('hour')
                ->pluck('count', 'hour');

            // Fill all 24 hours
            $hourlyData = collect(range(0, 23))->mapWithKeys(function ($h) use ($hourlyDist) {
                return [$h => $hourlyDist->get($h, 0)];
            });

            // ── CHART: Transaction Volume by Type (bar) ──
            $volumeByType = DB::table('transactions')
                ->select('type', DB::raw('COALESCE(SUM(amount),0) as volume'))
                ->where('tenant_id', $tenantId)
                ->where('status', 'success')
                ->whereRaw("created_at >= ?", [$monthStart])
                ->groupBy('type')
                ->pluck('volume', 'type');

            // ── TABLE: Top 10 Largest Transactions Today ──
            $topTxnsToday = DB::table('transactions')
                ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                ->join('customers', 'accounts.customer_id', '=', 'customers.id')
                ->select(
                    'transactions.reference',
                    'transactions.type',
                    'transactions.amount',
                    'transactions.status',
                    'transactions.created_at',
                    'accounts.account_number',
                    DB::raw("CONCAT(customers.first_name, ' ', customers.last_name) as customer_name")
                )
                ->where('transactions.tenant_id', $tenantId)
                ->whereRaw("transactions.created_at >= ?", [$today])
                ->orderByDesc('transactions.amount')
                ->limit(10)
                ->get();

        } catch (\Exception $e) {
            report($e);
            $txnCountToday = $txnCountWeek = $txnCountMonth = 0;
            $volumeToday = $volumeWeek = $volumeMonth = $avgTxnSize = 0;
            $failedRate = $pendingTxns = $reversedTxns = $successRate = 0;
            $txnByType = $txnByStatus = collect();
            $txnTrend = collect();
            $hourlyData = collect(range(0, 23))->mapWithKeys(fn($h) => [$h => 0]);
            $volumeByType = collect();
            $topTxnsToday = collect();
        }

        return view('transactions.dashboard', compact(
            'txnCountToday', 'txnCountWeek', 'txnCountMonth',
            'volumeToday', 'volumeWeek', 'volumeMonth', 'avgTxnSize',
            'failedRate', 'pendingTxns', 'reversedTxns', 'successRate',
            'txnByType', 'txnByStatus', 'txnTrend', 'hourlyData', 'volumeByType',
            'topTxnsToday',
            'startDate', 'endDate', 'filterType', 'filterStatus'
        ));
    }
}
