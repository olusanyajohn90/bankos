<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PortalAnalyticsController extends Controller
{
    /**
     * Resolve the date range from request params.
     */
    private function resolveDateRange(Request $request): array
    {
        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->get('from'))->startOfDay();
            $to   = Carbon::parse($request->get('to'))->endOfDay();
        } else {
            $period = $request->get('period', '30d');
            $days   = match($period) {
                '7d'   => 7,
                '90d'  => 90,
                '365d' => 365,
                default => 30,
            };
            $to   = Carbon::now()->endOfDay();
            $from = Carbon::now()->subDays($days - 1)->startOfDay();
        }
        return [$from, $to];
    }

    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        [$from, $to] = $this->resolveDateRange($request);
        $period = $request->get('period', '30d');

        // ── CSV Export ────────────────────────────────────────────────────────
        if ($request->get('export') === 'csv') {
            return $this->exportCsv($tenantId, $from, $to);
        }

        $creditTypes = ['deposit', 'disbursement', 'interest'];
        $debitTypes  = ['withdrawal', 'transfer', 'fee', 'loan_repayment', 'bill_payment', 'airtime'];
        $successStatuses = ['success', 'completed'];

        // ── Customer Metrics ─────────────────────────────────────────────────
        $totalCustomers = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->count();

        $newCustomers = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $portalActive = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->where('portal_active', 1)
            ->count();

        $portalActivationRate = $totalCustomers > 0
            ? round(($portalActive / $totalCustomers) * 100, 1)
            : 0;

        $kycRows = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->selectRaw('kyc_tier, COUNT(*) as cnt')
            ->groupBy('kyc_tier')
            ->get()
            ->keyBy('kyc_tier');

        $kycDistribution = [
            'level_1' => $kycRows->get('level_1')->cnt ?? 0,
            'level_2' => $kycRows->get('level_2')->cnt ?? 0,
            'level_3' => $kycRows->get('level_3')->cnt ?? 0,
        ];

        $kycPending = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->where('kyc_status', 'manual_review')
            ->count();

        // ── Transaction Metrics ───────────────────────────────────────────────
        $totalTxnCount = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', $successStatuses)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $totalTxnVolume = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', $successStatuses)
            ->whereIn('type', $creditTypes)
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        $totalTxnDebit = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', $successStatuses)
            ->whereIn('type', $debitTypes)
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        // Daily volumes for chart
        $dailyRaw = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', $successStatuses)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date, type, SUM(amount) as total')
            ->groupByRaw('DATE(created_at), type')
            ->get();

        $dailyMap = [];
        foreach ($dailyRaw as $row) {
            $d = $row->date;
            if (!isset($dailyMap[$d])) {
                $dailyMap[$d] = ['date' => $d, 'credits' => 0, 'debits' => 0];
            }
            if (in_array($row->type, $creditTypes)) {
                $dailyMap[$d]['credits'] += $row->total;
            } else {
                $dailyMap[$d]['debits'] += $row->total;
            }
        }
        ksort($dailyMap);
        $dailyVolumes = array_values($dailyMap);

        // Type breakdown
        $typeBreakdown = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', $successStatuses)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('type, COUNT(*) as cnt, SUM(amount) as total')
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        $feeRevenue = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', $successStatuses)
            ->where('type', 'fee')
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        // ── Deposit / Balance Metrics ─────────────────────────────────────────
        $totalDeposits = DB::table('accounts')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('type', 'savings')
            ->sum('available_balance');

        $totalCurrentDeposits = DB::table('accounts')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('type', 'current')
            ->sum('available_balance');

        $totalDomiciliaryDeposits = DB::table('accounts')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('type', 'domiciliary')
            ->sum('available_balance');

        $totalSavingsCount = DB::table('accounts')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('type', 'savings')
            ->count();

        $totalCurrentCount = DB::table('accounts')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('type', 'current')
            ->count();

        $depositGrowth = DB::table('accounts')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereBetween('created_at', [$from, $to])
            ->sum('available_balance');

        // ── Lending Metrics ────────────────────────────────────────────────────
        $loanBook = DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->sum('outstanding_balance');

        $totalDisbursed = DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->whereBetween('disbursed_at', [$from, $to])
            ->sum('principal_amount');

        $activeLoans = DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->count();

        $settledLoans = DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->where('status', 'settled')
            ->count();

        $writtenOffLoans = DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->where('status', 'written_off')
            ->count();

        $pendingLoans = DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        $nplAmount = DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->where('status', 'written_off')
            ->sum('outstanding_balance');

        $nplRatio = $loanBook > 0
            ? round(($nplAmount / $loanBook) * 100, 2)
            : 0;

        $repaymentThisPeriod = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', $successStatuses)
            ->where('type', 'loan_repayment')
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        $loanApplications = DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $approvedApplications = DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['approved', 'active', 'settled', 'written_off'])
            ->count();

        $approvalRate = $loanApplications > 0
            ? round(($approvedApplications / $loanApplications) * 100, 1)
            : 0;

        // ── Portal Engagement ─────────────────────────────────────────────────
        // These tables are created by bankos-portal; default to 0 if absent.
        try {
            $disputesOpen = \Illuminate\Support\Facades\Schema::hasTable('portal_disputes')
                ? DB::table('portal_disputes')->join('customers', 'portal_disputes.customer_id', '=', 'customers.id')->where('customers.tenant_id', $tenantId)->whereIn('portal_disputes.status', ['open', 'under_review'])->count()
                : 0;
            $disputesResolved = \Illuminate\Support\Facades\Schema::hasTable('portal_disputes')
                ? DB::table('portal_disputes')->join('customers', 'portal_disputes.customer_id', '=', 'customers.id')->where('customers.tenant_id', $tenantId)->where('portal_disputes.status', 'resolved')->whereBetween('portal_disputes.created_at', [$from, $to])->count()
                : 0;
        } catch (\Exception $e) { $disputesOpen = 0; $disputesResolved = 0; }

        try {
            $referralsTotal   = \Illuminate\Support\Facades\Schema::hasTable('portal_referrals') ? DB::table('portal_referrals')->where('tenant_id', $tenantId)->count() : 0;
            $referralsPending = \Illuminate\Support\Facades\Schema::hasTable('portal_referrals') ? DB::table('portal_referrals')->where('tenant_id', $tenantId)->where('status', 'pending')->count() : 0;
        } catch (\Exception $e) { $referralsTotal = 0; $referralsPending = 0; }

        try {
            $investmentsActive = \Illuminate\Support\Facades\Schema::hasTable('portal_investments') ? DB::table('portal_investments')->where('tenant_id', $tenantId)->where('status', 'active')->count() : 0;
            $investmentsBook   = \Illuminate\Support\Facades\Schema::hasTable('portal_investments') ? DB::table('portal_investments')->where('tenant_id', $tenantId)->where('status', 'active')->sum('principal') : 0;
        } catch (\Exception $e) { $investmentsActive = 0; $investmentsBook = 0; }

        return view('portal-analytics.index', compact(
            'period', 'from', 'to',
            // Customer
            'totalCustomers', 'newCustomers', 'portalActive',
            'portalActivationRate', 'kycDistribution', 'kycPending',
            // Transactions
            'totalTxnCount', 'totalTxnVolume', 'totalTxnDebit',
            'dailyVolumes', 'typeBreakdown', 'feeRevenue',
            // Deposits
            'totalDeposits', 'totalCurrentDeposits', 'totalDomiciliaryDeposits',
            'totalSavingsCount', 'totalCurrentCount', 'depositGrowth',
            // Lending
            'loanBook', 'totalDisbursed', 'activeLoans', 'settledLoans',
            'writtenOffLoans', 'pendingLoans', 'nplAmount', 'nplRatio',
            'repaymentThisPeriod', 'loanApplications', 'approvedApplications', 'approvalRate',
            // Engagement
            'disputesOpen', 'disputesResolved', 'referralsTotal', 'referralsPending',
            'investmentsActive', 'investmentsBook'
        ));
    }

    /**
     * AJAX endpoint: return daily_volumes JSON for the selected period.
     */
    public function data(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        [$from, $to] = $this->resolveDateRange($request);

        $creditTypes     = ['deposit', 'disbursement', 'interest'];
        $successStatuses = ['success', 'completed'];

        $rows = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', $successStatuses)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date, type, SUM(amount) as total')
            ->groupByRaw('DATE(created_at), type')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $d = $row->date;
            if (!isset($map[$d])) {
                $map[$d] = ['date' => $d, 'credits' => 0, 'debits' => 0];
            }
            if (in_array($row->type, $creditTypes)) {
                $map[$d]['credits'] += $row->total;
            } else {
                $map[$d]['debits'] += $row->total;
            }
        }
        ksort($map);

        return response()->json([
            'daily_volumes' => array_values($map),
            'from'          => $from->toDateString(),
            'to'            => $to->toDateString(),
        ]);
    }

    /**
     * Stream a CSV export of all key metrics.
     */
    private function exportCsv($tenantId, Carbon $from, Carbon $to)
    {
        $creditTypes     = ['deposit', 'disbursement', 'interest'];
        $debitTypes      = ['withdrawal', 'transfer', 'fee', 'loan_repayment', 'bill_payment', 'airtime'];
        $successStatuses = ['success', 'completed'];

        $filename = 'portal-analytics-' . $from->format('Y-m-d') . '-to-' . $to->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($tenantId, $from, $to, $creditTypes, $debitTypes, $successStatuses) {
            $handle = fopen('php://output', 'w');

            // Period info
            fputcsv($handle, ['Bank Performance Analytics Export']);
            fputcsv($handle, ['Period', $from->toDateString() . ' to ' . $to->toDateString()]);
            fputcsv($handle, ['Generated At', now()->toDateTimeString()]);
            fputcsv($handle, []);

            // ── Customer Metrics
            fputcsv($handle, ['=== CUSTOMER METRICS ===']);
            fputcsv($handle, ['Metric', 'Value']);

            $totalCustomers = DB::table('customers')->where('tenant_id', $tenantId)->where('status', 'active')->count();
            $newCustomers   = DB::table('customers')->where('tenant_id', $tenantId)->whereBetween('created_at', [$from, $to])->count();
            $portalActive   = DB::table('customers')->where('tenant_id', $tenantId)->where('portal_active', 1)->count();
            $activationRate = $totalCustomers > 0 ? round(($portalActive / $totalCustomers) * 100, 1) : 0;
            $kycPending     = DB::table('customers')->where('tenant_id', $tenantId)->where('kyc_status', 'manual_review')->count();

            fputcsv($handle, ['Total Active Customers', $totalCustomers]);
            fputcsv($handle, ['New Customers (period)', $newCustomers]);
            fputcsv($handle, ['Portal Active Customers', $portalActive]);
            fputcsv($handle, ['Portal Activation Rate (%)', $activationRate]);
            fputcsv($handle, ['KYC Pending (Manual Review)', $kycPending]);
            fputcsv($handle, []);

            // ── Transaction Metrics
            fputcsv($handle, ['=== TRANSACTION METRICS ===']);
            fputcsv($handle, ['Metric', 'Value']);

            $txnCount  = DB::table('transactions')->where('tenant_id', $tenantId)->whereIn('status', $successStatuses)->whereBetween('created_at', [$from, $to])->count();
            $txnCredit = DB::table('transactions')->where('tenant_id', $tenantId)->whereIn('status', $successStatuses)->whereIn('type', $creditTypes)->whereBetween('created_at', [$from, $to])->sum('amount');
            $txnDebit  = DB::table('transactions')->where('tenant_id', $tenantId)->whereIn('status', $successStatuses)->whereIn('type', $debitTypes)->whereBetween('created_at', [$from, $to])->sum('amount');
            $feeRev    = DB::table('transactions')->where('tenant_id', $tenantId)->whereIn('status', $successStatuses)->where('type', 'fee')->whereBetween('created_at', [$from, $to])->sum('amount');

            fputcsv($handle, ['Total Transactions (count)', $txnCount]);
            fputcsv($handle, ['Total Credit Volume (NGN)', number_format($txnCredit, 2)]);
            fputcsv($handle, ['Total Debit Volume (NGN)', number_format($txnDebit, 2)]);
            fputcsv($handle, ['Fee Revenue (NGN)', number_format($feeRev, 2)]);
            fputcsv($handle, []);

            // Type breakdown
            fputcsv($handle, ['--- Transaction Type Breakdown ---']);
            fputcsv($handle, ['Type', 'Count', 'Total Amount (NGN)']);
            $typeBreakdown = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', $successStatuses)
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('type, COUNT(*) as cnt, SUM(amount) as total')
                ->groupBy('type')
                ->orderByDesc('total')
                ->get();
            foreach ($typeBreakdown as $row) {
                fputcsv($handle, [$row->type, $row->cnt, number_format($row->total, 2)]);
            }
            fputcsv($handle, []);

            // ── Lending Metrics
            fputcsv($handle, ['=== LENDING METRICS ===']);
            fputcsv($handle, ['Metric', 'Value']);

            $loanBook      = DB::table('loans')->where('tenant_id', $tenantId)->where('status', 'active')->sum('outstanding_balance');
            $disbursed     = DB::table('loans')->where('tenant_id', $tenantId)->whereBetween('disbursed_at', [$from, $to])->sum('principal_amount');
            $activeLoans   = DB::table('loans')->where('tenant_id', $tenantId)->where('status', 'active')->count();
            $settledLoans  = DB::table('loans')->where('tenant_id', $tenantId)->where('status', 'settled')->count();
            $writtenOff    = DB::table('loans')->where('tenant_id', $tenantId)->where('status', 'written_off')->count();
            $nplAmount     = DB::table('loans')->where('tenant_id', $tenantId)->where('status', 'written_off')->sum('outstanding_balance');
            $nplRatio      = $loanBook > 0 ? round(($nplAmount / $loanBook) * 100, 2) : 0;
            $repayment     = DB::table('transactions')->where('tenant_id', $tenantId)->whereIn('status', $successStatuses)->where('type', 'loan_repayment')->whereBetween('created_at', [$from, $to])->sum('amount');
            $applications  = DB::table('loans')->where('tenant_id', $tenantId)->whereBetween('created_at', [$from, $to])->count();
            $approved      = DB::table('loans')->where('tenant_id', $tenantId)->whereBetween('created_at', [$from, $to])->whereIn('status', ['approved', 'active', 'settled', 'written_off'])->count();
            $approvalRate  = $applications > 0 ? round(($approved / $applications) * 100, 1) : 0;

            fputcsv($handle, ['Active Loan Book (NGN)', number_format($loanBook, 2)]);
            fputcsv($handle, ['Total Disbursed this Period (NGN)', number_format($disbursed, 2)]);
            fputcsv($handle, ['Active Loans (count)', $activeLoans]);
            fputcsv($handle, ['Settled Loans (count)', $settledLoans]);
            fputcsv($handle, ['Written-Off Loans (count)', $writtenOff]);
            fputcsv($handle, ['NPL Amount (NGN)', number_format($nplAmount, 2)]);
            fputcsv($handle, ['NPL Ratio (%)', $nplRatio]);
            fputcsv($handle, ['Repayments this Period (NGN)', number_format($repayment, 2)]);
            fputcsv($handle, ['Loan Applications (period)', $applications]);
            fputcsv($handle, ['Approval Rate (%)', $approvalRate]);
            fputcsv($handle, []);

            // ── Portal Engagement
            fputcsv($handle, ['=== PORTAL ENGAGEMENT ===']);
            fputcsv($handle, ['Metric', 'Value']);

            try {
                $disputesOpen     = \Illuminate\Support\Facades\Schema::hasTable('portal_disputes') ? DB::table('portal_disputes')->join('customers', 'portal_disputes.customer_id', '=', 'customers.id')->where('customers.tenant_id', $tenantId)->whereIn('portal_disputes.status', ['open', 'under_review'])->count() : 0;
                $disputesResolved = \Illuminate\Support\Facades\Schema::hasTable('portal_disputes') ? DB::table('portal_disputes')->join('customers', 'portal_disputes.customer_id', '=', 'customers.id')->where('customers.tenant_id', $tenantId)->where('portal_disputes.status', 'resolved')->whereBetween('portal_disputes.created_at', [$from, $to])->count() : 0;
                $referralsTotal   = \Illuminate\Support\Facades\Schema::hasTable('portal_referrals') ? DB::table('portal_referrals')->where('tenant_id', $tenantId)->count() : 0;
                $referralsPending = \Illuminate\Support\Facades\Schema::hasTable('portal_referrals') ? DB::table('portal_referrals')->where('tenant_id', $tenantId)->where('status', 'pending')->count() : 0;
                $investmentsActive = \Illuminate\Support\Facades\Schema::hasTable('portal_investments') ? DB::table('portal_investments')->where('tenant_id', $tenantId)->where('status', 'active')->count() : 0;
                $investmentsBook   = \Illuminate\Support\Facades\Schema::hasTable('portal_investments') ? DB::table('portal_investments')->where('tenant_id', $tenantId)->where('status', 'active')->sum('principal') : 0;
            } catch (\Exception $e) { $disputesOpen = $disputesResolved = $referralsTotal = $referralsPending = $investmentsActive = $investmentsBook = 0; }

            fputcsv($handle, ['Open Disputes', $disputesOpen]);
            fputcsv($handle, ['Disputes Resolved (period)', $disputesResolved]);
            fputcsv($handle, ['Total Referrals', $referralsTotal]);
            fputcsv($handle, ['Pending Referrals', $referralsPending]);
            fputcsv($handle, ['Active Investments (count)', $investmentsActive]);
            fputcsv($handle, ['Active Investments Book (NGN)', number_format($investmentsBook, 2)]);
            fputcsv($handle, []);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
