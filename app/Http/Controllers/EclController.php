<?php

namespace App\Http\Controllers;

use App\Models\EclProvision;
use App\Services\EclService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EclController extends Controller
{
    public function __construct(private EclService $eclService) {}

    public function index(Request $request)
    {
        $reportingDate = $request->date ?? Carbon::today()->toDateString();
        $tenantId = Auth::user()->tenant_id;

        $provisions = EclProvision::with(['loan', 'customer'])
            ->where('reporting_date', $reportingDate)
            ->orderBy('stage', 'desc')
            ->orderBy('ecl_amount', 'desc')
            ->paginate(30);

        $summary = EclProvision::where('reporting_date', $reportingDate)
            ->selectRaw('stage, COUNT(*) as loan_count, SUM(ecl_amount) as total_ecl, SUM(outstanding_balance) as total_exposure')
            ->groupBy('stage')
            ->get()
            ->keyBy('stage');

        $totalEcl = EclProvision::where('reporting_date', $reportingDate)->sum('ecl_amount');

        return view('ecl.index', compact('provisions', 'summary', 'totalEcl', 'reportingDate'));
    }

    public function run(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $provisions = $this->eclService->computeForTenant($tenantId);

        return redirect()->route('ecl.index')
            ->with('success', 'ECL computed for ' . count($provisions) . ' loans as at ' . Carbon::today()->format('d M Y'));
    }
}
