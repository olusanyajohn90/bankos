<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditDashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenantId  = Auth::user()->tenant_id;
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));

        try {
            // ── KPI: Total Loan Portfolio ──
            $totalPortfolio = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['active', 'overdue'])
                ->sum('outstanding_balance');

            // ── KPI: Total Active Loans Count ──
            $activeLoansCount = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->count();

            // ── KPI: Overdue Loans Count ──
            $overdueLoansCount = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->where('status', 'overdue')
                ->count();

            // ── KPI: Average Loan Size ──
            $avgLoanSize = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['active', 'overdue', 'closed'])
                ->avg('principal_amount') ?? 0;

            // ── KPI: Total Disbursements This Month ──
            $disbursementsThisMonth = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->whereNotNull('disbursed_at')
                ->whereRaw("disbursed_at >= ?", [Carbon::now()->startOfMonth()])
                ->whereRaw("disbursed_at <= ?", [Carbon::now()->endOfMonth()])
                ->sum('principal_amount');

            // ── KPI: Repayment Collection Rate (this month) ──
            $expectedRepayments = DB::table('transactions')
                ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                ->join('loans', 'accounts.id', '=', 'loans.account_id')
                ->where('transactions.tenant_id', $tenantId)
                ->where('transactions.type', 'repayment')
                ->where('transactions.status', 'success')
                ->whereRaw("transactions.created_at >= ?", [Carbon::now()->startOfMonth()])
                ->sum('transactions.amount');

            $totalDueThisMonth = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['active', 'overdue'])
                ->sum('outstanding_balance');

            $collectionRate = $totalDueThisMonth > 0
                ? round(($expectedRepayments / $totalDueThisMonth) * 100, 1) : 0;

            // ── KPI: NPL Ratio ──
            $nplBalance = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['overdue', 'written_off'])
                ->sum('outstanding_balance');
            $nplRatio = $totalPortfolio > 0
                ? round(($nplBalance / $totalPortfolio) * 100, 2) : 0;

            // ── KPI: Total Borrowers ──
            $totalBorrowers = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['active', 'overdue'])
                ->distinct('customer_id')
                ->count('customer_id');

            // ── KPI: Loan Applications Pipeline ──
            $pendingApps = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->count();
            $approvedApps = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->count();
            $rejectedApps = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->whereNotNull('rejected_at')
                ->count();

            // ── KPI: IFRS9 Staging Breakdown ──
            $ifrs9Stages = DB::table('loans')
                ->select('ifrs9_stage', DB::raw('COUNT(*) as count'), DB::raw('COALESCE(SUM(outstanding_balance),0) as total'))
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['active', 'overdue'])
                ->groupBy('ifrs9_stage')
                ->get()
                ->keyBy('ifrs9_stage');

            // ── KPI: Total ECL Provision ──
            $totalEclProvision = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['active', 'overdue'])
                ->sum('ecl_provision');

            // ── CHART: Loans by Status (pie) ──
            $loansByStatus = DB::table('loans')
                ->select('status', DB::raw('COUNT(*) as count'))
                ->where('tenant_id', $tenantId)
                ->groupBy('status')
                ->pluck('count', 'status');

            // ── CHART: Loans by Product (bar) ──
            $loansByProduct = DB::table('loans')
                ->join('loan_products', 'loans.product_id', '=', 'loan_products.id')
                ->select('loan_products.name', DB::raw('COUNT(*) as count'))
                ->where('loans.tenant_id', $tenantId)
                ->groupBy('loan_products.name')
                ->pluck('count', 'name');

            // ── CHART: Disbursement Trend (last 12 months) ──
            $disbursementTrend = DB::table('loans')
                ->select(
                    DB::raw("TO_CHAR(disbursed_at, 'YYYY-MM') as month"),
                    DB::raw('COALESCE(SUM(principal_amount),0) as total')
                )
                ->where('tenant_id', $tenantId)
                ->whereNotNull('disbursed_at')
                ->whereRaw("disbursed_at >= ?", [Carbon::now()->subMonths(12)->startOfMonth()])
                ->groupBy(DB::raw("TO_CHAR(disbursed_at, 'YYYY-MM')"))
                ->orderBy('month')
                ->pluck('total', 'month');

            // ── CHART: PAR Analysis (DPD Buckets) ──
            // We use loans with overdue status and calculate approximate DPD from expected_maturity_date
            $parBuckets = [
                '1-30'  => 0,
                '31-60' => 0,
                '61-90' => 0,
                '91-180' => 0,
                '180+'  => 0,
            ];
            $overdueLoans = DB::table('loans')
                ->select('outstanding_balance', 'expected_maturity_date', 'disbursed_at', 'tenure_days', 'principal_amount')
                ->where('tenant_id', $tenantId)
                ->where('status', 'overdue')
                ->get();

            foreach ($overdueLoans as $loan) {
                $maturity = $loan->expected_maturity_date
                    ? Carbon::parse($loan->expected_maturity_date)
                    : ($loan->disbursed_at ? Carbon::parse($loan->disbursed_at)->addMonths((int)$loan->tenure_days) : null);
                if (!$maturity) continue;
                $dpd = max(0, (int) $maturity->diffInDays(now(), false));
                $bal = (float) $loan->outstanding_balance;

                if ($dpd <= 30) $parBuckets['1-30'] += $bal;
                elseif ($dpd <= 60) $parBuckets['31-60'] += $bal;
                elseif ($dpd <= 90) $parBuckets['61-90'] += $bal;
                elseif ($dpd <= 180) $parBuckets['91-180'] += $bal;
                else $parBuckets['180+'] += $bal;
            }

            // ── TABLE: Top 10 Borrowers by Outstanding Balance ──
            $topBorrowers = DB::table('loans')
                ->join('customers', 'loans.customer_id', '=', 'customers.id')
                ->select(
                    'customers.id',
                    DB::raw("CONCAT(customers.first_name, ' ', customers.last_name) as full_name"),
                    'customers.customer_number',
                    DB::raw('SUM(loans.outstanding_balance) as total_outstanding'),
                    DB::raw('COUNT(loans.id) as loan_count')
                )
                ->where('loans.tenant_id', $tenantId)
                ->whereIn('loans.status', ['active', 'overdue'])
                ->groupBy('customers.id', 'customers.first_name', 'customers.last_name', 'customers.customer_number')
                ->orderByDesc('total_outstanding')
                ->limit(10)
                ->get();

            // ── CHART: Loan Officer Performance ──
            $officerPerformance = DB::table('loans')
                ->join('users', 'loans.officer_id', '=', 'users.id')
                ->select('users.name', DB::raw('COUNT(*) as loan_count'), DB::raw('SUM(loans.principal_amount) as total_disbursed'))
                ->where('loans.tenant_id', $tenantId)
                ->whereIn('loans.status', ['active', 'overdue', 'closed'])
                ->groupBy('users.name')
                ->orderByDesc('loan_count')
                ->limit(10)
                ->get();

        } catch (\Exception $e) {
            report($e);
            $totalPortfolio = $activeLoansCount = $overdueLoansCount = $avgLoanSize = 0;
            $disbursementsThisMonth = $collectionRate = $nplRatio = $totalBorrowers = 0;
            $pendingApps = $approvedApps = $rejectedApps = $totalEclProvision = 0;
            $loansByStatus = collect();
            $loansByProduct = collect();
            $disbursementTrend = collect();
            $parBuckets = ['1-30' => 0, '31-60' => 0, '61-90' => 0, '91-180' => 0, '180+' => 0];
            $topBorrowers = collect();
            $officerPerformance = collect();
            $ifrs9Stages = collect();
        }

        return view('credit.dashboard', compact(
            'totalPortfolio', 'activeLoansCount', 'overdueLoansCount', 'avgLoanSize',
            'disbursementsThisMonth', 'collectionRate', 'nplRatio', 'totalBorrowers',
            'pendingApps', 'approvedApps', 'rejectedApps', 'totalEclProvision',
            'loansByStatus', 'loansByProduct', 'disbursementTrend', 'parBuckets',
            'topBorrowers', 'officerPerformance', 'ifrs9Stages',
            'startDate', 'endDate'
        ));
    }
}
