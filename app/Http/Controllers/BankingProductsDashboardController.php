<?php

namespace App\Http\Controllers;

use App\Models\ChequeBook;
use App\Models\FixedDeposit;
use App\Models\OverdraftFacility;
use App\Models\StandingOrder;
use Illuminate\Support\Facades\DB;

class BankingProductsDashboardController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        // Fixed Deposits
        $fdCount      = FixedDeposit::where('tenant_id', $tenantId)->active()->count();
        $fdTotalValue = FixedDeposit::where('tenant_id', $tenantId)->active()->sum('principal_amount');
        $fdAvgRate    = FixedDeposit::where('tenant_id', $tenantId)->active()->avg('interest_rate') ?? 0;
        $fdMaturing   = FixedDeposit::where('tenant_id', $tenantId)
            ->active()
            ->where('maturity_date', '<=', now()->addDays(30))
            ->count();

        // Standing Orders
        $soActiveCount = StandingOrder::where('tenant_id', $tenantId)->where('status', 'active')->count();
        $soTotalValue  = StandingOrder::where('tenant_id', $tenantId)->where('status', 'active')->sum('amount');
        $soDueToday    = StandingOrder::where('tenant_id', $tenantId)->due()->count();

        // Overdrafts
        $odCount    = OverdraftFacility::where('tenant_id', $tenantId)->active()->count();
        $odLimit    = OverdraftFacility::where('tenant_id', $tenantId)->active()->sum('limit_amount');
        $odUsed     = OverdraftFacility::where('tenant_id', $tenantId)->active()->sum('used_amount');
        $odUtilRate = $odLimit > 0 ? round(($odUsed / $odLimit) * 100, 1) : 0;

        // Cheque Books
        $cbIssued       = ChequeBook::where('tenant_id', $tenantId)->count();
        $cbActive       = ChequeBook::where('tenant_id', $tenantId)->active()->count();
        $cbTotalLeaves  = ChequeBook::where('tenant_id', $tenantId)->active()->sum('leaves');
        $cbUsedLeaves   = ChequeBook::where('tenant_id', $tenantId)->active()->sum('leaves_used');

        // FD by status for pie
        $fdByStatus = FixedDeposit::where('tenant_id', $tenantId)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // SO by frequency for pie
        $soByFreq = StandingOrder::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->select('frequency', DB::raw('COUNT(*) as count'))
            ->groupBy('frequency')
            ->pluck('count', 'frequency')
            ->toArray();

        return view('banking-products.dashboard', compact(
            'fdCount', 'fdTotalValue', 'fdAvgRate', 'fdMaturing',
            'soActiveCount', 'soTotalValue', 'soDueToday',
            'odCount', 'odLimit', 'odUsed', 'odUtilRate',
            'cbIssued', 'cbActive', 'cbTotalLeaves', 'cbUsedLeaves',
            'fdByStatus', 'soByFreq'
        ));
    }
}
