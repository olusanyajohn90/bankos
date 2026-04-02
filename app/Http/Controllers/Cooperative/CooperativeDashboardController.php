<?php

namespace App\Http\Controllers\Cooperative;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CooperativeDashboardController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        // Members with shares
        $totalMembers = DB::table('member_shares')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->distinct('customer_id')
            ->count('customer_id');

        // Total shares value
        $totalSharesValue = (float) DB::table('member_shares')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->sum('total_value');

        // Dividends distributed
        $dividendDistributed = (float) DB::table('cooperative_dividends')
            ->where('tenant_id', $tenantId)
            ->where('status', 'processed')
            ->sum('total_amount');

        // Contribution compliance rate (% of on-time contributions this month)
        $monthStart = Carbon::today()->startOfMonth();
        $totalContribDue = DB::table('contribution_collections')
            ->where('tenant_id', $tenantId)
            ->where('collection_date', '>=', $monthStart)
            ->count();
        $contribOnTime = DB::table('contribution_collections')
            ->where('tenant_id', $tenantId)
            ->where('collection_date', '>=', $monthStart)
            ->where('status', 'paid')
            ->count();
        $complianceRate = $totalContribDue > 0 ? round(($contribOnTime / $totalContribDue) * 100, 1) : 100;

        // Share distribution by product (pie)
        $sharesByProduct = DB::table('member_shares')
            ->join('share_products', 'member_shares.product_id', '=', 'share_products.id')
            ->where('member_shares.tenant_id', $tenantId)
            ->where('member_shares.status', 'active')
            ->select('share_products.name', DB::raw('SUM(member_shares.total_value) as total'))
            ->groupBy('share_products.name')
            ->pluck('total', 'name')
            ->toArray();

        // Monthly contributions trend (last 6 months)
        $contribTrend = ['labels' => [], 'data' => []];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::today()->subMonths($i);
            $contribTrend['labels'][] = $month->format('M Y');
            $contribTrend['data'][] = (float) DB::table('contribution_collections')
                ->where('tenant_id', $tenantId)
                ->where('status', 'paid')
                ->whereRaw("TO_CHAR(collection_date, 'YYYY-MM') = ?", [$month->format('Y-m')])
                ->sum('amount') / 1_000_000;
        }

        // Pending exits
        $pendingExits = DB::table('cooperative_member_exits')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        // Recent dividends
        $recentDividends = DB::table('cooperative_dividends')
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('cooperative.dashboard', compact(
            'totalMembers', 'totalSharesValue', 'dividendDistributed', 'complianceRate',
            'sharesByProduct', 'contribTrend', 'pendingExits', 'recentDividends'
        ));
    }
}
