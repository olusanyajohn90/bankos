<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class BoardPackController extends Controller
{
    public function index()
    {
        $months = [];
        for ($i = 0; $i < 24; $i++) {
            $d        = Carbon::now()->startOfMonth()->subMonths($i);
            $months[] = ['value' => $d->format('Y-m'), 'label' => $d->format('F Y')];
        }

        $years = [];
        for ($y = now()->year; $y >= now()->year - 5; $y--) {
            $years[] = $y;
        }

        $quarters = [
            1 => 'Q1 (Jan – Mar)',
            2 => 'Q2 (Apr – Jun)',
            3 => 'Q3 (Jul – Sep)',
            4 => 'Q4 (Oct – Dec)',
        ];

        $sections = $this->allSections();

        return view('board-pack.generate', compact('months', 'years', 'quarters', 'sections'));
    }

    public function generate(Request $r)
    {
        $r->validate([
            'period_type' => 'required|in:monthly,quarterly,annual,range',
            'period'      => 'nullable|regex:/^\d{4}-\d{2}$/',
            'year'        => 'nullable|integer|min:2015|max:2040',
            'annual_year' => 'nullable|integer|min:2015|max:2040',
            'quarter'     => 'nullable|in:1,2,3,4',
            'from_month'  => 'nullable|regex:/^\d{4}-\d{2}$/',
            'to_month'    => 'nullable|regex:/^\d{4}-\d{2}$/',
            'sections'    => 'nullable|array',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $sections = $r->sections ?? array_keys($this->allSections());

        // ── Compute date range from period type ──────────────────────────────
        switch ($r->period_type) {
            case 'quarterly':
                $year       = (int) $r->year;
                $q          = (int) $r->quarter;
                $startMonth = ($q - 1) * 3 + 1;
                $startDate  = Carbon::createFromDate($year, $startMonth, 1)->startOfMonth();
                $endDate    = $startDate->copy()->addMonths(3)->subSecond();
                $periodLabel = "Q{$q} {$year}";
                break;

            case 'annual':
                $year        = (int) $r->annual_year;
                $startDate   = Carbon::createFromDate($year, 1, 1)->startOfDay();
                $endDate     = Carbon::createFromDate($year, 12, 31)->endOfDay();
                $periodLabel = "Full Year {$year}";
                break;

            case 'range':
                [$fy, $fm] = explode('-', $r->from_month);
                [$ty, $tm] = explode('-', $r->to_month ?? $r->from_month);
                $startDate  = Carbon::createFromDate($fy, $fm, 1)->startOfMonth();
                $endDate    = Carbon::createFromDate($ty, $tm, 1)->endOfMonth();
                $periodLabel = $startDate->format('M Y') . ' – ' . $endDate->format('M Y');
                break;

            default: // monthly
                $period = $r->period ?? now()->format('Y-m');
                [$year, $month] = explode('-', $period);
                $startDate   = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                $endDate     = $startDate->copy()->endOfMonth();
                $periodLabel = $startDate->format('F Y');
                break;
        }

        $spanMonths = max(1, (int) $startDate->diffInMonths($endDate) + 1);
        $prevStart  = $startDate->copy()->subMonth()->startOfMonth();
        $prevEnd    = $startDate->copy()->subMonth()->endOfMonth();
        $tenant     = DB::table('tenants')->where('id', $tenantId)->first();

        $data = [
            'tenant'      => $tenant,
            'periodLabel' => $periodLabel,
            'periodType'  => $r->period_type,
            'startDate'   => $startDate,
            'endDate'     => $endDate,
            'spanMonths'  => $spanMonths,
            'sections'    => $sections,
        ];

        // ── Executive Summary ────────────────────────────────────────────────
        if (in_array('executive_summary', $sections)) {
            $data['totalDeposits'] = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->whereIn('type', ['savings', 'current', 'fixed'])
                ->sum('ledger_balance');

            $data['loanBook'] = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['active', 'overdue'])
                ->sum('outstanding_balance');

            $totalActivePrincipal = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['active', 'overdue'])
                ->sum('principal_amount') ?: 1;

            $nplBalance = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->where('status', 'overdue')
                ->sum('outstanding_balance');

            $data['nplRatio'] = round(($nplBalance / $totalActivePrincipal) * 100, 2);

            $data['newCustomers'] = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $data['totalCustomers'] = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->count();

            $data['feeRevenue'] = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('type', 'fee')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');

            $data['netProfit'] = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('type', ['interest_income', 'fee'])
                ->sum('amount');

            $data['loanDisbursements'] = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('principal_amount');

            $data['newLoanCount'] = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $data['repaymentCollections'] = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('type', 'repayment')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');
        }

        // ── Balance Sheet ────────────────────────────────────────────────────
        if (in_array('balance_sheet', $sections)) {
            $data['totalAssets']     = DB::table('accounts')->where('tenant_id', $tenantId)->sum('ledger_balance');
            $data['totalSavings']    = DB::table('accounts')->where('tenant_id', $tenantId)->where('type', 'savings')->sum('ledger_balance');
            $data['totalCurrent']    = DB::table('accounts')->where('tenant_id', $tenantId)->where('type', 'current')->sum('ledger_balance');
            $data['totalFixed']      = DB::table('accounts')->where('tenant_id', $tenantId)->where('type', 'fixed')->sum('ledger_balance');
            $data['totalLoanAssets'] = DB::table('loans')->where('tenant_id', $tenantId)->whereIn('status', ['active', 'overdue'])->sum('outstanding_balance');
        }

        // ── Loan Portfolio ───────────────────────────────────────────────────
        if (in_array('loan_portfolio', $sections)) {
            $data['loansByStatus'] = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->selectRaw('status, COUNT(*) as count, SUM(outstanding_balance) as total')
                ->groupBy('status')
                ->get();

            $data['par30'] = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->where('status', 'overdue')
                ->sum('outstanding_balance');

            $data['loansByProduct'] = DB::table('loans')
                ->where('loans.tenant_id', $tenantId)
                ->whereIn('loans.status', ['active', 'overdue'])
                ->leftJoin('loan_products', 'loans.product_id', '=', 'loan_products.id')
                ->selectRaw('COALESCE(loan_products.name, "Unknown") as product_name, COUNT(*) as count, SUM(loans.outstanding_balance) as total')
                ->groupBy('loan_products.name')
                ->get();
        }

        // ── Deposit Analysis ─────────────────────────────────────────────────
        if (in_array('deposit_analysis', $sections)) {
            $data['depositsByType'] = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->selectRaw('type, COUNT(*) as count, SUM(ledger_balance) as total')
                ->groupBy('type')
                ->get();

            $data['top10Depositors'] = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->whereIn('type', ['savings', 'current', 'fixed'])
                ->orderByDesc('ledger_balance')
                ->limit(10)
                ->get(['account_number', 'account_name', 'type', 'ledger_balance']);

            $data['prevMonthDeposits'] = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->whereIn('type', ['savings', 'current', 'fixed'])
                ->where('created_at', '<=', $prevEnd)
                ->sum('ledger_balance');
        }

        // ── Transaction Activity ─────────────────────────────────────────────
        if (in_array('transaction_activity', $sections)) {
            $data['txnVolume'] = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');

            $data['txnByType'] = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('type, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('type')
                ->orderByDesc('total')
                ->get();

            $data['txnCount'] = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $days = max(1, $startDate->diffInDays($endDate) + 1);
            $data['dailyAvg'] = round($data['txnCount'] / $days, 1);

            $data['busiestDay'] = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as txn_date, COUNT(*) as cnt')
                ->groupBy('txn_date')
                ->orderByDesc('cnt')
                ->first();
        }

        // ── Customer Growth ──────────────────────────────────────────────────
        if (in_array('customer_growth', $sections)) {
            $data['newRegistrations'] = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $data['portalActivations'] = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('portal_active', true)
                ->count();

            $data['kycDistribution'] = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->selectRaw('kyc_tier, COUNT(*) as count')
                ->groupBy('kyc_tier')
                ->orderBy('kyc_tier')
                ->get();
        }

        // ── Branch Performance ───────────────────────────────────────────────
        if (in_array('branch_performance', $sections)) {
            $data['branchStats'] = DB::table('branches')
                ->where('branches.tenant_id', $tenantId)
                ->leftJoin('customers', 'customers.branch_id', '=', 'branches.id')
                ->selectRaw('branches.id, branches.name, COUNT(DISTINCT customers.id) as customer_count')
                ->groupBy('branches.id', 'branches.name')
                ->orderByDesc('customer_count')
                ->get();
        }

        // ── Compliance Summary ───────────────────────────────────────────────
        if (in_array('compliance_summary', $sections)) {
            $data['openAmlAlerts'] = DB::table('aml_alerts')
                ->where('tenant_id', $tenantId)
                ->where('status', 'open')
                ->count();

            $data['amlInPeriod'] = DB::table('aml_alerts')
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $data['amlByType'] = DB::table('aml_alerts')
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('alert_type, COUNT(*) as count')
                ->groupBy('alert_type')
                ->orderByDesc('count')
                ->get();

            $data['kycPending'] = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->where('kyc_status', 'manual_review')
                ->count();

            try {
                $data['disputesOpen'] = \Illuminate\Support\Facades\Schema::hasTable('portal_disputes')
                    ? DB::table('portal_disputes')->where('tenant_id', $tenantId)->where('status', 'open')->count()
                    : 0;
                $data['disputesResolved'] = \Illuminate\Support\Facades\Schema::hasTable('portal_disputes')
                    ? DB::table('portal_disputes')->where('tenant_id', $tenantId)->where('status', 'resolved')->whereBetween('updated_at', [$startDate, $endDate])->count()
                    : 0;
            } catch (\Exception $e) { $data['disputesOpen'] = 0; $data['disputesResolved'] = 0; }

            try {
                $data['strCount'] = DB::table('aml_str_reports')
                    ->where('tenant_id', $tenantId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();
            } catch (\Exception $e) {
                $data['strCount'] = 0;
            }
        }

        // ── Monthly Trends (for multi-month periods) ─────────────────────────
        $data['monthlyTrends'] = [];
        if ($spanMonths > 1) {
            $trends = [];
            $cursor = $startDate->copy()->startOfMonth();
            while ($cursor->lte($endDate)) {
                $mStart = $cursor->copy()->startOfMonth();
                $mEnd   = $cursor->copy()->endOfMonth();
                $trends[] = [
                    'label'         => $cursor->format('M Y'),
                    'customers'     => DB::table('customers')->where('tenant_id', $tenantId)->whereBetween('created_at', [$mStart, $mEnd])->count(),
                    'txn_volume'    => (float) DB::table('transactions')->where('tenant_id', $tenantId)->whereBetween('created_at', [$mStart, $mEnd])->sum('amount'),
                    'txn_count'     => DB::table('transactions')->where('tenant_id', $tenantId)->whereBetween('created_at', [$mStart, $mEnd])->count(),
                    'disbursements' => (float) DB::table('loans')->where('tenant_id', $tenantId)->whereBetween('created_at', [$mStart, $mEnd])->sum('principal_amount'),
                    'repayments'    => (float) DB::table('transactions')->where('tenant_id', $tenantId)->where('type', 'repayment')->whereBetween('created_at', [$mStart, $mEnd])->sum('amount'),
                ];
                $cursor->addMonth();
            }
            $data['monthlyTrends'] = $trends;
        }

        $pdf = Pdf::loadView('board-pack.pdf', $data)
            ->setPaper('a4', 'portrait');

        $safeLabel   = preg_replace('/[^A-Za-z0-9\-]/', '-', strtolower($periodLabel));
        $filename    = 'board-pack-' . $safeLabel . '.pdf';
        return $pdf->download($filename);
    }

    private function allSections(): array
    {
        return [
            'executive_summary'    => 'Executive Summary',
            'balance_sheet'        => 'Balance Sheet Snapshot',
            'loan_portfolio'       => 'Loan Portfolio',
            'deposit_analysis'     => 'Deposit Analysis',
            'transaction_activity' => 'Transaction Activity',
            'customer_growth'      => 'Customer Growth',
            'branch_performance'   => 'Branch Performance',
            'compliance_summary'   => 'Compliance & Risk',
            'monthly_trends'       => 'Monthly Trend Analysis',
        ];
    }
}
