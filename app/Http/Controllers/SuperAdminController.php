<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    // ── Platform Overview ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        // Log access
        DB::table('super_admin_access_log')->insert([
            'user_id'         => auth()->id(),
            'tenant_accessed' => null,
            'action'          => 'platform_overview',
            'ip_address'      => $request->ip(),
            'created_at'      => now(),
        ]);

        // Per-tenant scorecard
        $tenantStats = DB::table('tenants')
            ->leftJoin('customers', 'customers.tenant_id', '=', 'tenants.id')
            ->leftJoin('accounts', 'accounts.tenant_id', '=', 'tenants.id')
            ->leftJoin('loans', 'loans.tenant_id', '=', 'tenants.id')
            ->select(
                'tenants.id',
                'tenants.name',
                'tenants.short_name',
                'tenants.status',
                'tenants.type',
                'tenants.domain',
                DB::raw('COUNT(DISTINCT customers.id) as customer_count'),
                DB::raw('COUNT(DISTINCT CASE WHEN customers.portal_active=true THEN customers.id END) as portal_active'),
                DB::raw("COALESCE(SUM(CASE WHEN accounts.type='savings' THEN accounts.available_balance END),0) as savings_book"),
                DB::raw("COALESCE(SUM(CASE WHEN loans.status='active' THEN loans.outstanding_balance END),0) as loan_book"),
                DB::raw("COALESCE(SUM(CASE WHEN loans.status='written_off' THEN loans.outstanding_balance END),0) as npl_book")
            )
            ->groupBy('tenants.id', 'tenants.name', 'tenants.short_name', 'tenants.status', 'tenants.type', 'tenants.domain')
            ->get();

        // Platform totals
        $totalTenants     = $tenantStats->count();
        $activeTenants    = $tenantStats->where('status', 'active')->count();
        $totalCustomers   = $tenantStats->sum('customer_count');
        $totalPortalActive = $tenantStats->sum('portal_active');
        $platformSavings  = $tenantStats->sum('savings_book');
        $platformLoanBook = $tenantStats->sum('loan_book');
        $platformNplBook  = $tenantStats->sum('npl_book');
        $platformNplRatio = $platformLoanBook > 0
            ? round(($platformNplBook / $platformLoanBook) * 100, 2)
            : 0;

        $transactionsToday = DB::table('transactions')
            ->whereDate('created_at', today())
            ->count();

        $transactionsThisMonthVolume = DB::table('transactions')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        // Recent platform events (last 20)
        $recentEvents = DB::table('platform_events')
            ->join('tenants', 'tenants.id', '=', 'platform_events.tenant_id')
            ->select(
                'platform_events.*',
                'tenants.name as tenant_name',
                'tenants.short_name as tenant_short_name'
            )
            ->orderByDesc('platform_events.created_at')
            ->limit(20)
            ->get();

        // Leaderboard — top 5 by customer count
        $topByCustomers = $tenantStats->sortByDesc('customer_count')->take(5)->values();

        // Top 5 by savings book
        $topByDeposits = $tenantStats->sortByDesc('savings_book')->take(5)->values();

        // Alert banks: NPL > 10% or status != active
        $alertBanks = $tenantStats->filter(function ($t) {
            $npl = $t->loan_book > 0
                ? ($t->npl_book / $t->loan_book) * 100
                : 0;
            return $npl > 10 || $t->status !== 'active';
        })->values();

        return view('super-admin.index', compact(
            'tenantStats',
            'totalTenants',
            'activeTenants',
            'totalCustomers',
            'totalPortalActive',
            'platformSavings',
            'platformLoanBook',
            'platformNplBook',
            'platformNplRatio',
            'transactionsToday',
            'transactionsThisMonthVolume',
            'recentEvents',
            'topByCustomers',
            'topByDeposits',
            'alertBanks'
        ));
    }

    // ── Tenant Drill (JSON) ────────────────────────────────────────────────────
    public function tenantDrill(Request $request, $tenantId)
    {
        // Log access
        DB::table('super_admin_access_log')->insert([
            'user_id'         => auth()->id(),
            'tenant_accessed' => $tenantId,
            'action'          => 'tenant_drill',
            'ip_address'      => $request->ip(),
            'created_at'      => now(),
        ]);

        $tenant = DB::table('tenants')->where('id', $tenantId)->first();
        if (! $tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        // Customers
        $customers = DB::table('customers')->where('tenant_id', $tenantId);
        $totalCustomers   = $customers->count();
        $portalActive     = (clone $customers)->where('portal_active', true)->count();
        $kycVerified      = (clone $customers)->where('kyc_status', 'verified')->count();

        // Accounts / deposits
        $accounts    = DB::table('accounts')->where('tenant_id', $tenantId);
        $savingsBook = (clone $accounts)->where('type', 'savings')->sum('available_balance') ?? 0;
        $currentBook = (clone $accounts)->where('type', 'current')->sum('available_balance') ?? 0;
        $totalDeposits = $savingsBook + $currentBook;

        // Loans
        $loans     = DB::table('loans')->where('tenant_id', $tenantId);
        $loanBook  = (clone $loans)->where('status', 'active')->sum('outstanding_balance') ?? 0;
        $activeCount = (clone $loans)->where('status', 'active')->count();
        $nplBook   = (clone $loans)->where('status', 'written_off')->sum('outstanding_balance') ?? 0;
        $nplRatio  = $loanBook > 0 ? round(($nplBook / $loanBook) * 100, 2) : 0;

        // Transactions (30d)
        $txn30d    = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(30));
        $txnCount  = (clone $txn30d)->count();
        $txnVolume = (clone $txn30d)->sum('amount') ?? 0;
        $feeRevenue = (clone $txn30d)->sum('fee') ?? 0;

        // Daily series (30 days)
        $dailySeries = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $newCustomers = DB::table('customers')
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', $date)
                ->count();
            $dayTxnCount = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', $date)
                ->count();
            $dayTxnVolume = DB::table('transactions')
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', $date)
                ->sum('amount') ?? 0;
            $dailySeries[] = [
                'date'          => $date,
                'new_customers' => $newCustomers,
                'txn_count'     => $dayTxnCount,
                'txn_volume'    => (float) $dayTxnVolume,
            ];
        }

        return response()->json([
            'tenant' => [
                'id'         => $tenant->id,
                'name'       => $tenant->name,
                'short_name' => $tenant->short_name,
                'type'       => $tenant->type,
                'status'     => $tenant->status,
                'domain'     => $tenant->domain,
                'cbn_license'=> $tenant->cbn_license_number ?? null,
                'joined'     => $tenant->created_at,
            ],
            'metrics' => [
                'customers'       => [
                    'total'        => $totalCustomers,
                    'portal_active' => $portalActive,
                    'kyc_verified' => $kycVerified,
                ],
                'deposits'        => [
                    'savings' => (float) $savingsBook,
                    'current' => (float) $currentBook,
                    'total'   => (float) $totalDeposits,
                ],
                'loans'           => [
                    'book'         => (float) $loanBook,
                    'active_count' => $activeCount,
                    'npl'          => (float) $nplBook,
                    'npl_ratio'    => $nplRatio,
                ],
                'transactions_30d' => [
                    'count'       => $txnCount,
                    'volume'      => (float) $txnVolume,
                    'fee_revenue' => (float) $feeRevenue,
                ],
            ],
            'daily_series_30d' => $dailySeries,
        ]);
    }

    // ── Export All (JSON stream for AI/analytics) ──────────────────────────────
    public function exportAll(Request $request)
    {
        // Log access
        DB::table('super_admin_access_log')->insert([
            'user_id'         => auth()->id(),
            'tenant_accessed' => null,
            'action'          => 'export_all',
            'ip_address'      => $request->ip(),
            'created_at'      => now(),
        ]);

        $tenants = DB::table('tenants')->orderBy('name')->get();

        $export = [
            'export_date' => now()->toIso8601String(),
            'platform'    => 'bankOS',
            'version'     => '1.0',
            'tenants'     => [],
        ];

        foreach ($tenants as $tenant) {
            $tid = $tenant->id;

            // Customers
            $totalCustomers = DB::table('customers')->where('tenant_id', $tid)->count();
            $portalActive   = DB::table('customers')->where('tenant_id', $tid)->where('portal_active', true)->count();
            $kycVerified    = DB::table('customers')->where('tenant_id', $tid)->where('kyc_status', 'verified')->count();

            // Deposits
            $savingsBook  = DB::table('accounts')->where('tenant_id', $tid)->where('type', 'savings')->sum('available_balance') ?? 0;
            $currentBook  = DB::table('accounts')->where('tenant_id', $tid)->where('type', 'current')->sum('available_balance') ?? 0;

            // Loans
            $loanBook    = DB::table('loans')->where('tenant_id', $tid)->where('status', 'active')->sum('outstanding_balance') ?? 0;
            $activeCount = DB::table('loans')->where('tenant_id', $tid)->where('status', 'active')->count();
            $nplBook     = DB::table('loans')->where('tenant_id', $tid)->where('status', 'written_off')->sum('outstanding_balance') ?? 0;
            $nplRatio    = $loanBook > 0 ? round(($nplBook / $loanBook) * 100, 2) : 0;

            // Transactions 30d
            $txn30dBase  = DB::table('transactions')->where('tenant_id', $tid)->where('created_at', '>=', now()->subDays(30));
            $txnCount    = (clone $txn30dBase)->count();
            $txnVolume   = (clone $txn30dBase)->sum('amount') ?? 0;
            $feeRevenue  = (clone $txn30dBase)->sum('fee') ?? 0;

            // Daily series
            $dailySeries = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $dailySeries[] = [
                    'date'          => $date,
                    'new_customers' => DB::table('customers')->where('tenant_id', $tid)->whereDate('created_at', $date)->count(),
                    'txn_count'     => DB::table('transactions')->where('tenant_id', $tid)->whereDate('created_at', $date)->count(),
                    'txn_volume'    => (float) (DB::table('transactions')->where('tenant_id', $tid)->whereDate('created_at', $date)->sum('amount') ?? 0),
                ];
            }

            $export['tenants'][] = [
                'tenant_id' => $tid,
                'name'      => $tenant->name,
                'status'    => $tenant->status,
                'type'      => $tenant->type,
                'metrics'   => [
                    'customers'        => [
                        'total'        => $totalCustomers,
                        'portal_active' => $portalActive,
                        'kyc_verified' => $kycVerified,
                    ],
                    'deposits'         => [
                        'savings' => (float) $savingsBook,
                        'current' => (float) $currentBook,
                        'total'   => (float) ($savingsBook + $currentBook),
                    ],
                    'loans'            => [
                        'book'         => (float) $loanBook,
                        'active_count' => $activeCount,
                        'npl'          => (float) $nplBook,
                        'npl_ratio'    => $nplRatio,
                    ],
                    'transactions_30d' => [
                        'count'       => $txnCount,
                        'volume'      => (float) $txnVolume,
                        'fee_revenue' => (float) $feeRevenue,
                    ],
                ],
                'daily_series_30d' => $dailySeries,
            ];
        }

        $filename = 'bankos-platform-export-' . now()->format('Y-m-d') . '.json';

        return response()->json($export)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }
}
