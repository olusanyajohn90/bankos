<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\KpiActual;
use App\Models\KpiDefinition;
use App\Models\KpiTarget;
use App\Models\StaffProfile;
use App\Models\Team;
use App\Models\User;
use App\Services\KpiAlertService;
use App\Services\KpiComputeService;
use Illuminate\Http\Request;

class KpiDashboardController extends Controller
{
    public function __construct(
        private KpiComputeService $kpiService,
        private KpiAlertService $alertService
    ) {}

    // ── My Performance ─────────────────────────────────────────────────────

    public function myPerformance(Request $request)
    {
        $user       = auth()->user();
        $tenantId   = $user->tenant_id;
        $periodType = $request->get('period_type', 'monthly');
        $period     = $request->get('period', $this->kpiService->currentPeriodValue($periodType));

        $profile = StaffProfile::where('user_id', $user->id)->with(['branch', 'team', 'manager'])->first();

        $kpiMatrix = $this->buildKpiMatrix($tenantId, 'individual', $profile?->id, $periodType, $period);

        // Trend: last 6 periods
        $trends = $this->buildTrend($tenantId, 'individual', $profile?->id ?? $user->id, $periodType, 6);

        // Recent alerts & notes for this user
        $alerts = $user->kpiAlerts()
            ->with('kpiTarget.kpiDefinition')
            ->whereIn('status', ['unread', 'read'])
            ->latest()
            ->take(10)
            ->get();

        $notes = \App\Models\KpiNote::where('subject_type', 'user')
            ->where('subject_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->with(['author', 'kpiDefinition'])
            ->latest()
            ->take(10)
            ->get();

        return view('kpi.me', compact(
            'user', 'profile', 'kpiMatrix', 'trends',
            'alerts', 'notes', 'periodType', 'period'
        ));
    }

    // ── Team Dashboard ─────────────────────────────────────────────────────

    public function teamDashboard(Request $request, Team $team)
    {
        $tenantId   = auth()->user()->tenant_id;
        $periodType = $request->get('period_type', 'monthly');
        $period     = $request->get('period', $this->kpiService->currentPeriodValue($periodType));

        $members = StaffProfile::where('team_id', $team->id)
            ->with('user')
            ->get();

        $kpis = KpiDefinition::where('is_active', true)->get();

        // Build matrix: member × KPI → actual + target + achievement_pct
        $matrix = [];
        foreach ($members as $profile) {
            $matrix[$profile->id] = $this->buildKpiMatrix($tenantId, 'individual', $profile->id, $periodType, $period);
        }

        $teamMatrix = $this->buildKpiMatrix($tenantId, 'team', $team->id, $periodType, $period);

        return view('kpi.team', compact(
            'team', 'members', 'kpis', 'matrix',
            'teamMatrix', 'periodType', 'period'
        ));
    }

    // ── Branch Dashboard ───────────────────────────────────────────────────

    public function branchDashboard(Request $request, Branch $branch)
    {
        $tenantId   = auth()->user()->tenant_id;
        $periodType = $request->get('period_type', 'monthly');
        $period     = $request->get('period', $this->kpiService->currentPeriodValue($periodType));

        $teams = Team::where('branch_id', $branch->id)
            ->where('status', 'active')
            ->with('teamLead')
            ->get();

        $staff = StaffProfile::where('branch_id', $branch->id)
            ->where('status', 'active')
            ->with('user')
            ->get();

        $branchMatrix = $this->buildKpiMatrix($tenantId, 'branch', $branch->id, $periodType, $period);

        // Top/bottom performers
        $staffScores = [];
        foreach ($staff as $profile) {
            $mat = $this->buildKpiMatrix($tenantId, 'individual', $profile->id, $periodType, $period);
            $staffScores[$profile->id] = [
                'profile'   => $profile,
                'composite' => $mat['composite_pct'],
                'matrix'    => $mat,
            ];
        }

        arsort($staffScores);
        $top5    = array_slice($staffScores, 0, 5, true);
        $bottom5 = array_slice(array_reverse($staffScores, true), 0, 5, true);

        return view('kpi.branch', compact(
            'branch', 'teams', 'staff', 'branchMatrix',
            'staffScores', 'top5', 'bottom5', 'periodType', 'period'
        ));
    }

    // ── HQ Overview ────────────────────────────────────────────────────────

    public function hqOverview(Request $request)
    {
        $tenantId   = auth()->user()->tenant_id;
        $periodType = $request->get('period_type', 'monthly');
        $period     = $request->get('period', $this->kpiService->currentPeriodValue($periodType));

        $branches = Branch::where('tenant_id', $tenantId)->with('manager')->get();

        $branchData = [];
        foreach ($branches as $branch) {
            $branchData[$branch->id] = [
                'branch' => $branch,
                'matrix' => $this->buildKpiMatrix($tenantId, 'branch', $branch->id, $periodType, $period),
            ];
        }

        // Alert summary by branch
        $alertSummary = \App\Models\KpiAlert::where('tenant_id', $tenantId)
            ->where('period_value', $period)
            ->whereIn('status', ['unread', 'read'])
            ->selectRaw('severity, COUNT(*) as cnt')
            ->groupBy('severity')
            ->pluck('cnt', 'severity')
            ->toArray();

        // Top/bottom performers across all branches
        $allStaff = StaffProfile::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with(['user', 'branch'])
            ->get();

        $staffScores = [];
        foreach ($allStaff as $profile) {
            $mat = $this->buildKpiMatrix($tenantId, 'individual', $profile->id, $periodType, $period);
            if ($mat['composite_pct'] !== null) {
                $staffScores[$profile->id] = [
                    'profile'   => $profile,
                    'composite' => $mat['composite_pct'],
                ];
            }
        }

        arsort($staffScores);
        $top5    = array_slice($staffScores, 0, 5, true);
        $bottom5 = array_slice(array_reverse($staffScores, true), 0, 5, true);

        return view('kpi.hq', compact(
            'branches', 'branchData', 'alertSummary',
            'top5', 'bottom5', 'periodType', 'period'
        ));
    }

    // ── Individual Staff Dashboard (HR/Manager view) ───────────────────────

    public function staffDashboard(Request $request, StaffProfile $staffProfile)
    {
        $tenantId   = auth()->user()->tenant_id;
        abort_unless($staffProfile->tenant_id === $tenantId, 403);
        $this->authorize('viewStaffKpi', $staffProfile);

        $periodType = $request->get('period_type', 'monthly');
        $period     = $request->get('period', $this->kpiService->currentPeriodValue($periodType));

        $staffProfile->load(['branch', 'team', 'manager', 'user', 'orgDepartment']);

        $kpiMatrix = $this->buildKpiMatrix($tenantId, 'individual', $staffProfile->id, $periodType, $period);
        $trends    = $this->buildTrend($tenantId, 'individual', $staffProfile->id, $periodType, 6);

        $notes = \App\Models\KpiNote::where('subject_type', 'staff_profile')
            ->where('subject_id', $staffProfile->id)
            ->where('tenant_id', $tenantId)
            ->with(['author', 'kpiDefinition'])
            ->latest()
            ->take(10)
            ->get();

        $kpis = KpiDefinition::where('is_active', true)->get();

        return view('kpi.staff', compact(
            'staffProfile', 'kpiMatrix', 'trends',
            'notes', 'kpis', 'periodType', 'period'
        ));
    }

    // ── Manual trigger ─────────────────────────────────────────────────────

    public function triggerCompute(Request $request)
    {
        $tenantId   = auth()->user()->tenant_id;
        $periodType = $request->get('period_type', 'monthly');
        $period     = $request->get('period', $this->kpiService->currentPeriodValue($periodType));

        $result = $this->kpiService->computeAll($tenantId, $periodType, $period);
        $alerts = $this->alertService->checkAndFire($tenantId, $periodType, $period);

        return back()->with('success', "KPI computation complete: {$result['computed']} values updated, {$alerts} alerts fired.");
    }

    // ── Shared helper ──────────────────────────────────────────────────────

    public function buildKpiMatrix(string $tenantId, string $subjectType, ?string $subjectRefId, string $periodType, string $periodValue): array
    {
        $kpis = KpiDefinition::where('is_active', true)->get()->keyBy('id');

        $actuals = KpiActual::where('tenant_id', $tenantId)
            ->where('subject_type', $subjectType)
            ->where('subject_ref_id', $subjectRefId)
            ->where('period_type', $periodType)
            ->where('period_value', $periodValue)
            ->get()
            ->keyBy('kpi_id');

        $targets = KpiTarget::where('tenant_id', $tenantId)
            ->where('target_type', $subjectType)
            ->where('target_ref_id', $subjectRefId)
            ->where('period_type', $periodType)
            ->where('period_value', $periodValue)
            ->get()
            ->keyBy('kpi_id');

        $rows           = [];
        $totalPct       = 0.0;
        $countWithTarget = 0;

        foreach ($kpis as $kpi) {
            $actual  = $actuals->get($kpi->id);
            $target  = $targets->get($kpi->id);
            $pct     = null;
            $severity = 'gray';

            if ($actual && $target) {
                $pct = $this->alertService->computeAchievement($actual, $target, $kpi);
                $severity = $this->alertService->determineSeverity($pct, $target->alert_threshold_pct);
                $totalPct += $pct;
                $countWithTarget++;
            }

            $rows[$kpi->id] = [
                'kpi'           => $kpi,
                'actual'        => $actual?->value,
                'target'        => $target?->target_value,
                'achievement'   => $pct,
                'severity'      => $severity,
                'threshold'     => $target?->alert_threshold_pct ?? 70,
            ];
        }

        $compositePct = $countWithTarget > 0 ? round($totalPct / $countWithTarget, 1) : null;

        return [
            'rows'          => $rows,
            'composite_pct' => $compositePct,
            'green_count'   => count(array_filter($rows, fn($r) => $r['severity'] === 'green')),
            'yellow_count'  => count(array_filter($rows, fn($r) => $r['severity'] === 'yellow')),
            'red_count'     => count(array_filter($rows, fn($r) => $r['severity'] === 'red')),
        ];
    }

    private function buildTrend(string $tenantId, string $subjectType, string $subjectRefId, string $periodType, int $count): array
    {
        $periods = [];
        $current = now();

        for ($i = $count - 1; $i >= 0; $i--) {
            $date = $current->copy()->subMonths($i);
            $periods[] = $date->format('Y-m');
        }

        $actuals = KpiActual::where('tenant_id', $tenantId)
            ->where('subject_type', $subjectType)
            ->where('subject_ref_id', $subjectRefId)
            ->where('period_type', $periodType)
            ->whereIn('period_value', $periods)
            ->with('kpiDefinition')
            ->get()
            ->groupBy('period_value');

        $data = [];
        foreach ($periods as $p) {
            $data[$p] = $actuals->get($p, collect())->keyBy('kpi_id');
        }

        return ['periods' => $periods, 'data' => $data];
    }
}
