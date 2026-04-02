<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenantId  = Auth::user()->tenant_id;
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));
        $filterGender = $request->input('gender');
        $filterBranch = $request->input('branch_id');
        $filterKycTier = $request->input('kyc_tier');

        try {
            // ── Branches for filter dropdown ──
            $branches = DB::table('branches')
                ->where('tenant_id', $tenantId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            // ── KPI: Total Customers ──
            $totalCustomers = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->count();

            // ── KPI: Monthly Growth ──
            $customersThisMonth = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->whereRaw("created_at >= ?", [Carbon::now()->startOfMonth()])
                ->count();
            $customersLastMonth = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->whereRaw("created_at >= ?", [Carbon::now()->subMonth()->startOfMonth()])
                ->whereRaw("created_at < ?", [Carbon::now()->startOfMonth()])
                ->count();
            $monthlyGrowth = $customersLastMonth > 0
                ? round((($customersThisMonth - $customersLastMonth) / $customersLastMonth) * 100, 1) : 0;

            // ── KPI: Customers by Status ──
            $customersByStatus = DB::table('customers')
                ->select('status', DB::raw('COUNT(*) as count'))
                ->where('tenant_id', $tenantId)
                ->groupBy('status')
                ->pluck('count', 'status');

            // ── KPI: Active Customers ──
            $activeCustomers = $customersByStatus->get('active', 0);
            $inactiveCustomers = $customersByStatus->get('inactive', 0);
            $dormantCustomers = $customersByStatus->get('dormant', 0);
            $blacklistedCustomers = $customersByStatus->get('blacklisted', 0);

            // ── CHART: Customers by Gender (pie) ──
            $customersByGender = DB::table('customers')
                ->select('gender', DB::raw('COUNT(*) as count'))
                ->where('tenant_id', $tenantId)
                ->whereNotNull('gender')
                ->groupBy('gender')
                ->pluck('count', 'gender');

            // ── CHART: Customers by Age Group (bar) ──
            $customersByAge = DB::table('customers')
                ->select(DB::raw("
                    CASE
                        WHEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, date_of_birth)) BETWEEN 18 AND 25 THEN '18-25'
                        WHEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, date_of_birth)) BETWEEN 26 AND 35 THEN '26-35'
                        WHEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, date_of_birth)) BETWEEN 36 AND 45 THEN '36-45'
                        WHEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, date_of_birth)) BETWEEN 46 AND 55 THEN '46-55'
                        WHEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, date_of_birth)) BETWEEN 56 AND 65 THEN '56-65'
                        WHEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, date_of_birth)) > 65 THEN '65+'
                        ELSE 'Unknown'
                    END as age_group
                "), DB::raw('COUNT(*) as count'))
                ->where('tenant_id', $tenantId)
                ->whereNotNull('date_of_birth')
                ->groupBy('age_group')
                ->pluck('count', 'age_group');

            // Sort age groups properly
            $ageOrder = ['18-25', '26-35', '36-45', '46-55', '56-65', '65+', 'Unknown'];
            $sortedAgeGroups = collect($ageOrder)->mapWithKeys(function ($group) use ($customersByAge) {
                return [$group => $customersByAge->get($group, 0)];
            })->filter(fn($v) => $v > 0);

            // ── CHART: Customers by KYC Tier ──
            $customersByKyc = DB::table('customers')
                ->select('kyc_tier', DB::raw('COUNT(*) as count'))
                ->where('tenant_id', $tenantId)
                ->whereNotNull('kyc_tier')
                ->groupBy('kyc_tier')
                ->pluck('count', 'kyc_tier');

            // ── CHART: Customers by Branch (bar) ──
            $customersByBranch = DB::table('customers')
                ->join('branches', 'customers.branch_id', '=', 'branches.id')
                ->select('branches.name', DB::raw('COUNT(*) as count'))
                ->where('customers.tenant_id', $tenantId)
                ->groupBy('branches.name')
                ->orderByDesc('count')
                ->limit(15)
                ->pluck('count', 'name');

            // ── CHART: New Customer Acquisition Trend (last 12 months) ──
            $acquisitionTrend = DB::table('customers')
                ->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                    DB::raw('COUNT(*) as count')
                )
                ->where('tenant_id', $tenantId)
                ->whereRaw("created_at >= ?", [Carbon::now()->subMonths(12)->startOfMonth()])
                ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM')"))
                ->orderBy('month')
                ->pluck('count', 'month');

            // ── KPI: Average Account Balance per Customer ──
            $avgBalancePerCustomer = DB::table('accounts')
                ->join('customers', 'accounts.customer_id', '=', 'customers.id')
                ->where('accounts.tenant_id', $tenantId)
                ->where('accounts.status', 'active')
                ->avg('accounts.available_balance') ?? 0;

            // ── TABLE: Top 10 Customers by Total Balance ──
            $topCustomers = DB::table('customers')
                ->join('accounts', 'customers.id', '=', 'accounts.customer_id')
                ->select(
                    'customers.id',
                    DB::raw("CONCAT(customers.first_name, ' ', customers.last_name) as full_name"),
                    'customers.customer_number',
                    'customers.status',
                    DB::raw('SUM(accounts.available_balance) as total_balance'),
                    DB::raw('COUNT(accounts.id) as account_count')
                )
                ->where('customers.tenant_id', $tenantId)
                ->where('accounts.status', 'active')
                ->groupBy('customers.id', 'customers.first_name', 'customers.last_name', 'customers.customer_number', 'customers.status')
                ->orderByDesc('total_balance')
                ->limit(10)
                ->get();

            // ── KPI: Customer Type Distribution ──
            $customersByType = DB::table('customers')
                ->select('type', DB::raw('COUNT(*) as count'))
                ->where('tenant_id', $tenantId)
                ->groupBy('type')
                ->pluck('count', 'type');

            // ── KPI: KYC Completion Rate ──
            $kycCompleted = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->whereIn('kyc_tier', ['level_2', 'level_3'])
                ->count();
            $kycCompletionRate = $totalCustomers > 0
                ? round(($kycCompleted / $totalCustomers) * 100, 1) : 0;

            // ── KPI: New Customers Today ──
            $newCustomersToday = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->whereRaw("created_at >= ?", [Carbon::today()])
                ->count();

        } catch (\Exception $e) {
            report($e);
            $totalCustomers = $customersThisMonth = $monthlyGrowth = 0;
            $activeCustomers = $inactiveCustomers = $dormantCustomers = $blacklistedCustomers = 0;
            $customersByGender = $customersByKyc = $customersByBranch = collect();
            $sortedAgeGroups = collect();
            $acquisitionTrend = collect();
            $avgBalancePerCustomer = 0;
            $topCustomers = collect();
            $customersByType = collect();
            $kycCompletionRate = $newCustomersToday = 0;
            $customersByStatus = collect();
            $branches = collect();
        }

        return view('customers.dashboard', compact(
            'totalCustomers', 'customersThisMonth', 'monthlyGrowth',
            'activeCustomers', 'inactiveCustomers', 'dormantCustomers', 'blacklistedCustomers',
            'customersByGender', 'sortedAgeGroups', 'customersByKyc', 'customersByBranch',
            'acquisitionTrend', 'avgBalancePerCustomer', 'topCustomers',
            'customersByType', 'kycCompletionRate', 'newCustomersToday', 'customersByStatus',
            'branches', 'startDate', 'endDate', 'filterGender', 'filterBranch', 'filterKycTier'
        ));
    }
}
