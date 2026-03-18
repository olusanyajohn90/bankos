<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Agent;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        $today    = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();

        // --- KPI Cards ---
        $totalCustomers  = Customer::where('tenant_id', $tenantId)->count();
        $totalDeposits   = Account::where('tenant_id', $tenantId)
                                  ->whereIn('type', ['savings', 'current', 'fixed'])
                                  ->sum('available_balance');
        $totalAccounts   = Account::where('tenant_id', $tenantId)
                                  ->where('status', 'active')
                                  ->count();

        $loanPortfolio   = Loan::where('tenant_id', $tenantId)
                               ->whereIn('status', ['active', 'overdue', 'defaulted'])
                               ->sum('outstanding_balance');

        // NPL = (overdue + defaulted loans) / total active portfolio
        $nplBalance = Loan::where('tenant_id', $tenantId)
                          ->whereIn('status', ['defaulted'])
                          ->sum('outstanding_balance');
        $nplRatio = $loanPortfolio > 0 ? ($nplBalance / $loanPortfolio) * 100 : 0;

        // PAR > 30 = overdue + defaulted / total portfolio
        $parBalance = Loan::where('tenant_id', $tenantId)
                          ->whereIn('status', ['overdue', 'defaulted'])
                          ->sum('outstanding_balance');
        $par30 = $loanPortfolio > 0 ? ($parBalance / $loanPortfolio) * 100 : 0;

        // --- Secondary KPIs ---
        $todayTxnCount  = Transaction::where('tenant_id', $tenantId)
                                     ->whereDate('created_at', $today)
                                     ->count();
        $todayTxnVolume = Transaction::where('tenant_id', $tenantId)
                                     ->whereDate('created_at', $today)
                                     ->sum('amount');

        $pendingKyc      = Customer::where('tenant_id', $tenantId)
                                   ->where('kyc_status', 'manual_review')
                                   ->count();
        $pendingLoans    = Loan::where('tenant_id', $tenantId)
                               ->where('status', 'pending')
                               ->count();
        $activeAgents    = Agent::where('tenant_id', $tenantId)
                                ->where('status', 'active')
                                ->count();

        // --- Cashflow chart: deposits vs loan disbursals by week this month ---
        $cashflowData = $this->getCashflowByWeek($tenantId, $monthStart, $today);

        // --- PAR trend: last 6 months ---
        $parTrend = $this->getParTrend($tenantId);

        // --- Branch performance: deposit volume by branch ---
        $branchPerformance = $this->getBranchPerformance($tenantId);

        // --- Recent transactions ---
        $recentTransactions = Transaction::where('tenant_id', $tenantId)
            ->with('account.customer')
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard', compact(
            'totalCustomers', 'totalDeposits', 'totalAccounts',
            'loanPortfolio', 'nplRatio', 'par30',
            'todayTxnCount', 'todayTxnVolume',
            'pendingKyc', 'pendingLoans', 'activeAgents',
            'cashflowData', 'parTrend', 'branchPerformance',
            'recentTransactions'
        ));
    }

    private function getCashflowByWeek(string $tenantId, Carbon $monthStart, Carbon $today): array
    {
        $labels   = [];
        $deposits = [];
        $disbursals = [];

        // Build 4-week buckets within the current month
        $weekStart = $monthStart->copy();
        $weekNum   = 1;

        while ($weekStart->lte($today) && $weekNum <= 4) {
            $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();
            if ($weekEnd->gt($today)) $weekEnd = $today->copy()->endOfDay();

            $labels[] = 'Wk ' . $weekNum;

            $deposits[] = (float) Transaction::where('tenant_id', $tenantId)
                ->where('type', 'deposit')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->sum('amount') / 1_000_000; // in millions

            $disbursals[] = (float) Transaction::where('tenant_id', $tenantId)
                ->where('type', 'disbursement')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->sum('amount') / 1_000_000;

            $weekStart->addDays(7);
            $weekNum++;
        }

        return compact('labels', 'deposits', 'disbursals');
    }

    private function getParTrend(string $tenantId): array
    {
        $labels = [];
        $par30  = [];
        $par90  = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::today()->subMonths($i);
            $labels[] = $month->format('M');

            $total = Loan::where('tenant_id', $tenantId)
                ->whereDate('created_at', '<=', $month->endOfMonth())
                ->sum('outstanding_balance');

            // Approximate PAR using status (a full solution would use schedule snapshots)
            $overdue = Loan::where('tenant_id', $tenantId)
                ->whereIn('status', ['overdue', 'defaulted'])
                ->whereDate('created_at', '<=', $month->endOfMonth())
                ->sum('outstanding_balance');

            $par30[] = $total > 0 ? round(($overdue / $total) * 100, 2) : 0;

            $npl = Loan::where('tenant_id', $tenantId)
                ->where('status', 'defaulted')
                ->whereDate('created_at', '<=', $month->endOfMonth())
                ->sum('outstanding_balance');

            $par90[] = $total > 0 ? round(($npl / $total) * 100, 2) : 0;
        }

        return compact('labels', 'par30', 'par90');
    }

    private function getBranchPerformance(string $tenantId): array
    {
        // Loan portfolio outstanding grouped by branch (via users.branch_id → loans created_by)
        // We use agents.branch_id as they directly link to branches
        $rows = DB::table('branches')
            ->where('branches.tenant_id', $tenantId)
            ->where('branches.status', 'active')
            ->leftJoin('agents', function ($join) use ($tenantId) {
                $join->on('agents.branch_id', '=', 'branches.id')
                     ->where('agents.tenant_id', $tenantId);
            })
            ->select(
                'branches.name',
                DB::raw('COUNT(agents.id) as agent_count'),
                DB::raw('COALESCE(SUM(agents.float_balance), 0) as total_float')
            )
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('total_float')
            ->limit(6)
            ->get();

        $labels  = $rows->pluck('name')->toArray();
        $volumes = $rows->map(fn($r) => round($r->total_float / 1_000_000, 4))->toArray();

        return compact('labels', 'volumes');
    }
}
