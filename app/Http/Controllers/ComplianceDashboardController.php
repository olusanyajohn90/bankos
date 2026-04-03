<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComplianceDashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenantId  = Auth::user()->tenant_id;
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));

        try {
            // ── AML Alerts (suspicious transactions) ──
            $amlAlertsOpen = DB::table('aml_alerts')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['open', 'escalated'])
                ->count();

            $amlAlertsCritical = DB::table('aml_alerts')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['open', 'escalated'])
                ->where('severity', 'critical')
                ->count();

            $amlAlertsThisMonth = DB::table('aml_alerts')
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count();

            // ── KYC Compliance Rate ──
            $totalCustomers = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->count();

            $approvedKyc = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->where('kyc_level', '>=', 2)
                ->count();

            $kycComplianceRate = $totalCustomers > 0
                ? round(($approvedKyc / $totalCustomers) * 100, 1)
                : 0;

            $pendingKyc = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->where('kyc_level', '<', 2)
                ->count();

            // ── PEP (Politically Exposed Persons) ──
            $pepCount = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->where('is_pep', true)
                ->count();

            // ── Sanctions Screening ──
            $sanctionsHits = DB::table('aml_alerts')
                ->where('tenant_id', $tenantId)
                ->where('alert_type', 'sanctions_match')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count();

            $sanctionsPending = DB::table('aml_alerts')
                ->where('tenant_id', $tenantId)
                ->where('alert_type', 'sanctions_match')
                ->where('status', 'open')
                ->count();

            // ── Transaction Monitoring (large transactions >5M today) ──
            $largeTransactionsToday = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('amount', '>=', 5000000)
                ->whereDate('created_at', now()->toDateString())
                ->where('status', 'success')
                ->count();

            $largeTransactionsValue = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('amount', '>=', 5000000)
                ->whereDate('created_at', now()->toDateString())
                ->where('status', 'success')
                ->sum('amount');

            $largeTransactionsPeriod = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('amount', '>=', 5000000)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->where('status', 'success')
                ->count();

            // ── NDIC Deposit Coverage ──
            $totalDeposits = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->sum('balance');

            $totalDepositors = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->distinct('customer_id')
                ->count('customer_id');

            $coveredDepositors = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->where('balance', '<=', 500000)
                ->distinct('customer_id')
                ->count('customer_id');

            $ndicCoverageRate = $totalDepositors > 0
                ? round(($coveredDepositors / $totalDepositors) * 100, 1)
                : 0;

            // ── Document Expiry Alerts (documents expiring in 30 days) ──
            $expiringDocuments = DB::table('documents')
                ->where('tenant_id', $tenantId)
                ->whereNotNull('expires_at')
                ->whereDate('expires_at', '>=', now()->toDateString())
                ->whereDate('expires_at', '<=', now()->addDays(30)->toDateString())
                ->count();

            $expiredDocuments = DB::table('documents')
                ->where('tenant_id', $tenantId)
                ->whereNotNull('expires_at')
                ->whereDate('expires_at', '<', now()->toDateString())
                ->count();

            // ── Regulatory Report Status ──
            $reportsSubmitted = DB::table('regulatory_reports')
                ->where('tenant_id', $tenantId)
                ->where('status', 'submitted')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count();

            $reportsPending = DB::table('regulatory_reports')
                ->where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->count();

            $reportsOverdue = DB::table('regulatory_reports')
                ->where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->whereDate('due_date', '<', now()->toDateString())
                ->count();

            // ── Risk-Rated Customer Breakdown ──
            $riskLow = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->where('risk_rating', 'low')
                ->count();

            $riskMedium = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->where('risk_rating', 'medium')
                ->count();

            $riskHigh = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->where('risk_rating', 'high')
                ->count();

            // ── CBN Reporting Compliance ──
            $cbnReportsdue = DB::table('regulatory_reports')
                ->where('tenant_id', $tenantId)
                ->where('regulator', 'CBN')
                ->where('status', 'pending')
                ->count();

            $cbnReportsSubmitted = DB::table('regulatory_reports')
                ->where('tenant_id', $tenantId)
                ->where('regulator', 'CBN')
                ->where('status', 'submitted')
                ->whereDate('created_at', '>=', now()->startOfYear())
                ->count();

            // ── Charts: AML Alert Trend (last 30 days) ──
            $amlTrend = DB::table('aml_alerts')
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->select(DB::raw("DATE(created_at) as date"), DB::raw("count(*) as total"))
                ->groupBy(DB::raw("DATE(created_at)"))
                ->orderBy('date')
                ->get();

            // ── Charts: Large Transaction Trend (last 30 days) ──
            $largeTxnTrend = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->where('amount', '>=', 5000000)
                ->where('status', 'success')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->select(DB::raw("DATE(created_at) as date"), DB::raw("count(*) as total"), DB::raw("SUM(amount) as volume"))
                ->groupBy(DB::raw("DATE(created_at)"))
                ->orderBy('date')
                ->get();

            // ── Charts: KYC Level Breakdown ──
            $kycBreakdown = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->select('kyc_level', DB::raw("count(*) as total"))
                ->groupBy('kyc_level')
                ->pluck('total', 'kyc_level');

            // ── Recent AML Alerts ──
            $recentAlerts = DB::table('aml_alerts')
                ->where('tenant_id', $tenantId)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

        } catch (\Exception $e) {
            // Graceful fallback for missing tables
            $amlAlertsOpen = $amlAlertsCritical = $amlAlertsThisMonth = 0;
            $totalCustomers = $approvedKyc = $kycComplianceRate = $pendingKyc = 0;
            $pepCount = $sanctionsHits = $sanctionsPending = 0;
            $largeTransactionsToday = $largeTransactionsValue = $largeTransactionsPeriod = 0;
            $totalDeposits = $totalDepositors = $coveredDepositors = $ndicCoverageRate = 0;
            $expiringDocuments = $expiredDocuments = 0;
            $reportsSubmitted = $reportsPending = $reportsOverdue = 0;
            $riskLow = $riskMedium = $riskHigh = 0;
            $cbnReportsdue = $cbnReportsSubmitted = 0;
            $amlTrend = collect();
            $largeTxnTrend = collect();
            $kycBreakdown = collect();
            $recentAlerts = collect();
        }

        return view('compliance.enhanced-dashboard', compact(
            'startDate', 'endDate',
            'amlAlertsOpen', 'amlAlertsCritical', 'amlAlertsThisMonth',
            'totalCustomers', 'approvedKyc', 'kycComplianceRate', 'pendingKyc',
            'pepCount', 'sanctionsHits', 'sanctionsPending',
            'largeTransactionsToday', 'largeTransactionsValue', 'largeTransactionsPeriod',
            'totalDeposits', 'totalDepositors', 'coveredDepositors', 'ndicCoverageRate',
            'expiringDocuments', 'expiredDocuments',
            'reportsSubmitted', 'reportsPending', 'reportsOverdue',
            'riskLow', 'riskMedium', 'riskHigh',
            'cbnReportsdue', 'cbnReportsSubmitted',
            'amlTrend', 'largeTxnTrend', 'kycBreakdown', 'recentAlerts'
        ));
    }
}
