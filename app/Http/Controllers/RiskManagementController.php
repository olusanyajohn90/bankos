<?php

namespace App\Http\Controllers;

use App\Models\RiskAssessment;
use App\Models\RiskLimit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiskManagementController extends Controller
{
    public function dashboard()
    {
        try {
            $totalAssessments = RiskAssessment::count();
            $openAssessments = RiskAssessment::where('status', 'open')->count();
            $criticalAssessments = RiskAssessment::where('severity', 'critical')->where('status', 'open')->count();
            $highAssessments = RiskAssessment::where('severity', 'high')->where('status', 'open')->count();
            $totalExposure = RiskAssessment::where('status', 'open')->sum('exposure_amount');
            $mitigatedCount = RiskAssessment::where('status', 'mitigated')->count();

            // Risk heatmap data (type x severity)
            $heatmapData = RiskAssessment::where('status', 'open')
                ->select('risk_type', 'severity', DB::raw('COUNT(*) as count'))
                ->groupBy('risk_type', 'severity')
                ->get();

            // By type
            $byType = RiskAssessment::select('risk_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(exposure_amount) as exposure'))
                ->where('status', 'open')
                ->groupBy('risk_type')
                ->get();

            // By severity
            $bySeverity = RiskAssessment::select('severity', DB::raw('COUNT(*) as count'))
                ->where('status', 'open')
                ->groupBy('severity')
                ->get();

            // Limits
            $totalLimits = RiskLimit::count();
            $breachedLimits = RiskLimit::where('status', 'breached')->count();
            $warningLimits = RiskLimit::where('status', 'warning')->count();
            $withinLimits = RiskLimit::where('status', 'within_limit')->count();

            // Limits utilization summary
            $limitsUtilization = RiskLimit::select('name', 'utilization_pct', 'status')
                ->orderByDesc('utilization_pct')
                ->limit(10)
                ->get();

            // Recent assessments
            $recentAssessments = RiskAssessment::with('creator')
                ->latest()
                ->limit(5)
                ->get();

        } catch (\Exception $e) {
            return view('risk-management.dashboard', [
                'error' => $e->getMessage(),
                'totalAssessments' => 0, 'openAssessments' => 0, 'criticalAssessments' => 0,
                'highAssessments' => 0, 'totalExposure' => 0, 'mitigatedCount' => 0,
                'heatmapData' => collect(), 'byType' => collect(), 'bySeverity' => collect(),
                'totalLimits' => 0, 'breachedLimits' => 0, 'warningLimits' => 0,
                'withinLimits' => 0, 'limitsUtilization' => collect(), 'recentAssessments' => collect(),
            ]);
        }

        return view('risk-management.dashboard', compact(
            'totalAssessments', 'openAssessments', 'criticalAssessments', 'highAssessments',
            'totalExposure', 'mitigatedCount', 'heatmapData', 'byType', 'bySeverity',
            'totalLimits', 'breachedLimits', 'warningLimits', 'withinLimits',
            'limitsUtilization', 'recentAssessments'
        ));
    }

    public function assessments(Request $request)
    {
        try {
            $query = RiskAssessment::with('creator', 'assignee')->latest();
            if ($request->filled('risk_type')) $query->where('risk_type', $request->risk_type);
            if ($request->filled('severity')) $query->where('severity', $request->severity);
            if ($request->filled('status')) $query->where('status', $request->status);
            $assessments = $query->paginate(20)->withQueryString();
        } catch (\Exception $e) {
            $assessments = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('risk-management.assessments.index', compact('assessments'));
    }

    public function createAssessment()
    {
        try {
            $users = User::select('id', 'name')->where('tenant_id', auth()->user()->tenant_id)->get();
        } catch (\Exception $e) {
            $users = collect();
        }
        return view('risk-management.assessments.create', compact('users'));
    }

    public function storeAssessment(Request $request)
    {
        $request->validate([
            'risk_type'        => 'required|in:credit,liquidity,market,operational,concentration',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'severity'         => 'required|in:low,medium,high,critical',
            'exposure_amount'  => 'nullable|numeric|min:0',
            'mitigation_plan'  => 'nullable|string',
            'assigned_to'      => 'nullable|exists:users,id',
        ]);

        try {
            RiskAssessment::create([
                'risk_type'       => $request->risk_type,
                'title'           => $request->title,
                'description'     => $request->description,
                'severity'        => $request->severity,
                'exposure_amount' => $request->exposure_amount,
                'mitigation_plan' => $request->mitigation_plan,
                'assigned_to'     => $request->assigned_to,
                'created_by'      => auth()->id(),
            ]);

            return redirect()->route('risk-management.assessments')->with('success', 'Risk assessment created.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function showAssessment($id)
    {
        try {
            $assessment = RiskAssessment::with('creator', 'assignee')->findOrFail($id);
        } catch (\Exception $e) {
            return redirect()->route('risk-management.assessments')->with('error', 'Not found.');
        }
        return view('risk-management.assessments.show', compact('assessment'));
    }

    public function limits(Request $request)
    {
        try {
            $query = RiskLimit::orderByDesc('utilization_pct');
            if ($request->filled('status')) $query->where('status', $request->status);
            if ($request->filled('limit_type')) $query->where('limit_type', $request->limit_type);
            $limits = $query->paginate(20)->withQueryString();
        } catch (\Exception $e) {
            $limits = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('risk-management.limits.index', compact('limits'));
    }

    public function createLimit()
    {
        return view('risk-management.limits.create');
    }

    public function storeLimit(Request $request)
    {
        $request->validate([
            'limit_type'        => 'required|string|max:50',
            'name'              => 'required|string|max:200',
            'limit_value'       => 'required|numeric|min:0.01',
            'current_value'     => 'nullable|numeric|min:0',
            'warning_threshold' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $currentVal = $request->current_value ?? 0;
            $limitVal = $request->limit_value;
            $utilPct = $limitVal > 0 ? ($currentVal / $limitVal) * 100 : 0;
            $warnThreshold = $request->warning_threshold ?? 80;
            $status = 'within_limit';
            if ($utilPct >= 100) $status = 'breached';
            elseif ($utilPct >= $warnThreshold) $status = 'warning';

            RiskLimit::create([
                'limit_type'        => $request->limit_type,
                'name'              => $request->name,
                'limit_value'       => $limitVal,
                'current_value'     => $currentVal,
                'utilization_pct'   => $utilPct,
                'status'            => $status,
                'warning_threshold' => $warnThreshold,
            ]);

            return redirect()->route('risk-management.limits')->with('success', 'Risk limit created.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function updateLimit(Request $request, $id)
    {
        $request->validate([
            'current_value' => 'required|numeric|min:0',
        ]);

        try {
            $limit = RiskLimit::findOrFail($id);
            $utilPct = $limit->limit_value > 0 ? ($request->current_value / $limit->limit_value) * 100 : 0;
            $status = 'within_limit';
            if ($utilPct >= 100) $status = 'breached';
            elseif ($utilPct >= $limit->warning_threshold) $status = 'warning';

            $limit->update([
                'current_value'   => $request->current_value,
                'utilization_pct' => $utilPct,
                'status'          => $status,
            ]);

            return back()->with('success', 'Limit updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function breachAlerts()
    {
        try {
            $breached = RiskLimit::whereIn('status', ['warning', 'breached'])
                ->orderByDesc('utilization_pct')
                ->get();
        } catch (\Exception $e) {
            $breached = collect();
        }

        return view('risk-management.breach-alerts', compact('breached'));
    }
}
