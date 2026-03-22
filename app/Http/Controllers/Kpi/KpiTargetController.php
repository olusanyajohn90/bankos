<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\KpiDefinition;
use App\Models\KpiTarget;
use App\Models\StaffProfile;
use App\Models\Team;
use App\Services\KpiComputeService;
use Illuminate\Http\Request;

class KpiTargetController extends Controller
{
    public function __construct(private KpiComputeService $kpiService) {}

    public function index(Request $request)
    {
        $tenantId   = auth()->user()->tenant_id;
        $periodType = $request->get('period_type', 'monthly');
        $period     = $request->get('period', $this->kpiService->currentPeriodValue($periodType));

        $targets = KpiTarget::where('tenant_id', $tenantId)
            ->where('period_type', $periodType)
            ->where('period_value', $period)
            ->with(['kpiDefinition', 'setBy'])
            ->orderBy('target_type')
            ->paginate(50)
            ->withQueryString();

        $kpis     = KpiDefinition::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('tenant_id', $tenantId)->orderBy('name')->get();
        $teams    = Team::where('tenant_id', $tenantId)->orderBy('name')->get();
        $staff    = StaffProfile::where('tenant_id', $tenantId)
            ->with('user')
            ->orderBy('created_at')
            ->get();

        return view('kpi.setup.targets', compact(
            'targets', 'kpis', 'branches', 'teams', 'staff', 'periodType', 'period'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kpi_id'             => 'required|exists:kpi_definitions,id',
            'target_type'        => 'required|in:individual,team,branch,department,tenant',
            'target_ref_id'      => 'nullable|string',
            'department'         => 'nullable|string|max:50',
            'period_type'        => 'required|in:monthly,quarterly,yearly',
            'period_value'       => 'required|string|max:10',
            'target_value'       => 'required|numeric|min:0',
            'alert_threshold_pct'=> 'required|integer|min:1|max:99',
            'notes'              => 'nullable|string|max:500',
        ]);

        KpiTarget::updateOrCreate(
            [
                'tenant_id'      => auth()->user()->tenant_id,
                'kpi_id'         => $data['kpi_id'],
                'target_type'    => $data['target_type'],
                'target_ref_id'  => $data['target_ref_id'] ?? null,
                'period_type'    => $data['period_type'],
                'period_value'   => $data['period_value'],
            ],
            [
                'target_value'        => $data['target_value'],
                'alert_threshold_pct' => $data['alert_threshold_pct'],
                'department'          => $data['department'] ?? null,
                'notes'               => $data['notes'] ?? null,
                'set_by'              => auth()->id(),
            ]
        );

        return back()->with('success', 'Target saved.');
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'targets'              => 'required|array',
            'targets.*.kpi_id'     => 'required|exists:kpi_definitions,id',
            'targets.*.ref_id'     => 'required|string',
            'targets.*.target_type'=> 'required|in:individual,team,branch',
            'targets.*.value'      => 'required|numeric|min:0',
            'period_type'          => 'required|in:monthly,quarterly,yearly',
            'period_value'         => 'required|string|max:10',
            'alert_threshold_pct'  => 'required|integer|min:1|max:99',
        ]);

        $tenantId   = auth()->user()->tenant_id;
        $periodType = $request->period_type;
        $period     = $request->period_value;
        $threshold  = $request->alert_threshold_pct;
        $count      = 0;

        foreach ($request->targets as $t) {
            KpiTarget::updateOrCreate(
                [
                    'tenant_id'     => $tenantId,
                    'kpi_id'        => $t['kpi_id'],
                    'target_type'   => $t['target_type'],
                    'target_ref_id' => $t['ref_id'],
                    'period_type'   => $periodType,
                    'period_value'  => $period,
                ],
                [
                    'target_value'        => $t['value'],
                    'alert_threshold_pct' => $threshold,
                    'set_by'              => auth()->id(),
                ]
            );
            $count++;
        }

        return back()->with('success', "{$count} targets saved.");
    }

    public function destroy(KpiTarget $kpiTarget)
    {
        $kpiTarget->delete();
        return back()->with('success', 'Target removed.');
    }
}
