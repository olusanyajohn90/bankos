<?php

namespace App\Http\Controllers;

use App\Models\ComplianceFramework;
use App\Models\ComplianceControl;
use App\Models\ComplianceEvidence;
use App\Models\ComplianceMonitor;
use App\Models\ComplianceAuditTrail;
use App\Models\ComplianceTrustReport;
use App\Services\ComplianceAutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComplianceAutomationController extends Controller
{
    private ComplianceAutomationService $service;

    public function __construct(ComplianceAutomationService $service)
    {
        $this->service = $service;
    }

    private function tenantId(): string
    {
        return Auth::user()->tenant_id;
    }

    // ── DASHBOARD (Command Center) ───────────────────────────────────────────

    public function dashboard()
    {
        $tenantId = $this->tenantId();

        try {
            $frameworks = ComplianceFramework::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            $monitors = ComplianceMonitor::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            $recentTrail = ComplianceAuditTrail::where('tenant_id', $tenantId)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            $overallScore = $frameworks->count() > 0
                ? round($frameworks->avg('compliance_score'), 1)
                : 0;

            $totalControls = ComplianceControl::where('tenant_id', $tenantId)->count();
            $compliantControls = ComplianceControl::where('tenant_id', $tenantId)->where('status', 'compliant')->count();
            $evidenceCount = ComplianceEvidence::where('tenant_id', $tenantId)->count();
            $passingMonitors = $monitors->where('status', 'passing')->count();
            $warningMonitors = $monitors->where('status', 'warning')->count();
            $failingMonitors = $monitors->where('status', 'failing')->count();

            $narrative = $this->service->generateComplianceNarrative($tenantId);

            $alerts = ComplianceAuditTrail::where('tenant_id', $tenantId)
                ->whereIn('event_type', ['breach', 'warning', 'check_error'])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            return view('compliance-automation.dashboard', compact(
                'frameworks', 'monitors', 'recentTrail', 'overallScore',
                'totalControls', 'compliantControls', 'evidenceCount',
                'passingMonitors', 'warningMonitors', 'failingMonitors',
                'narrative', 'alerts'
            ));
        } catch (\Exception $e) {
            return view('compliance-automation.dashboard', [
                'frameworks' => collect(),
                'monitors' => collect(),
                'recentTrail' => collect(),
                'overallScore' => 0,
                'totalControls' => 0,
                'compliantControls' => 0,
                'evidenceCount' => 0,
                'passingMonitors' => 0,
                'warningMonitors' => 0,
                'failingMonitors' => 0,
                'narrative' => 'Unable to load compliance data.',
                'alerts' => collect(),
            ]);
        }
    }

    // ── FRAMEWORKS ───────────────────────────────────────────────────────────

    public function frameworks()
    {
        $tenantId = $this->tenantId();

        try {
            $frameworks = ComplianceFramework::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->withCount(['controls', 'controls as compliant_count' => function ($q) {
                    $q->where('status', 'compliant');
                }, 'controls as partial_count' => function ($q) {
                    $q->where('status', 'partial');
                }, 'controls as non_compliant_count' => function ($q) {
                    $q->where('status', 'non_compliant');
                }, 'controls as not_assessed_count' => function ($q) {
                    $q->where('status', 'not_assessed');
                }])
                ->orderBy('name')
                ->get();

            return view('compliance-automation.frameworks', compact('frameworks'));
        } catch (\Exception $e) {
            return view('compliance-automation.frameworks', ['frameworks' => collect()]);
        }
    }

    public function showFramework(string $id)
    {
        $tenantId = $this->tenantId();

        try {
            $framework = ComplianceFramework::where('tenant_id', $tenantId)->findOrFail($id);

            $controls = ComplianceControl::where('framework_id', $id)
                ->where('tenant_id', $tenantId)
                ->when(request('status'), fn($q, $s) => $q->where('status', $s))
                ->when(request('category'), fn($q, $c) => $q->where('category', $c))
                ->orderBy('control_ref')
                ->get();

            $categories = ComplianceControl::where('framework_id', $id)
                ->where('tenant_id', $tenantId)
                ->select('category', DB::raw('count(*) as total'))
                ->groupBy('category')
                ->pluck('total', 'category');

            $statusCounts = ComplianceControl::where('framework_id', $id)
                ->where('tenant_id', $tenantId)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            return view('compliance-automation.framework-show', compact(
                'framework', 'controls', 'categories', 'statusCounts'
            ));
        } catch (\Exception $e) {
            return redirect()->route('compliance-auto.frameworks')
                ->with('error', 'Framework not found.');
        }
    }

    // ── CONTROLS ─────────────────────────────────────────────────────────────

    public function controls(Request $request)
    {
        $tenantId = $this->tenantId();

        try {
            $query = ComplianceControl::where('tenant_id', $tenantId)
                ->with('framework');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }
            if ($request->filled('framework_id')) {
                $query->where('framework_id', $request->framework_id);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'ilike', "%{$search}%")
                      ->orWhere('control_ref', 'ilike', "%{$search}%");
                });
            }

            $controls = $query->orderBy('control_ref')->paginate(25)->withQueryString();

            $frameworks = ComplianceFramework::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->pluck('name', 'id');

            return view('compliance-automation.controls', compact('controls', 'frameworks'));
        } catch (\Exception $e) {
            return view('compliance-automation.controls', [
                'controls' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25),
                'frameworks' => collect(),
            ]);
        }
    }

    public function showControl(string $id)
    {
        $tenantId = $this->tenantId();

        try {
            $control = ComplianceControl::where('tenant_id', $tenantId)
                ->with(['framework', 'evidence' => function ($q) {
                    $q->orderByDesc('collected_at');
                }, 'monitors'])
                ->findOrFail($id);

            $auditTrail = ComplianceAuditTrail::where('tenant_id', $tenantId)
                ->where('entity_type', 'control')
                ->where('entity_id', $id)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();

            $users = DB::table('users')
                ->where('tenant_id', $tenantId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            return view('compliance-automation.control-show', compact('control', 'auditTrail', 'users'));
        } catch (\Exception $e) {
            return redirect()->route('compliance-auto.controls')
                ->with('error', 'Control not found.');
        }
    }

    public function updateControl(Request $request, string $id)
    {
        $tenantId = $this->tenantId();

        try {
            $control = ComplianceControl::where('tenant_id', $tenantId)->findOrFail($id);

            $validated = $request->validate([
                'status'           => 'nullable|in:compliant,partial,non_compliant,not_assessed',
                'evidence_notes'   => 'nullable|string',
                'remediation_plan' => 'nullable|string',
                'remediation_due'  => 'nullable|date',
                'assigned_to'      => 'nullable|integer',
                'priority'         => 'nullable|integer|in:1,2,3,4',
            ]);

            $oldStatus = $control->status;
            $control->update(array_filter($validated, fn($v) => $v !== null));

            if (isset($validated['status']) && $oldStatus !== $validated['status']) {
                ComplianceAuditTrail::create([
                    'tenant_id'   => $tenantId,
                    'event_type'  => 'status_changed',
                    'entity_type' => 'control',
                    'entity_id'   => $control->id,
                    'description' => "Control {$control->control_ref} status changed from {$oldStatus} to {$validated['status']}",
                    'metadata'    => ['old_status' => $oldStatus, 'new_status' => $validated['status']],
                    'user_id'     => Auth::id(),
                ]);

                // Recalculate framework score
                $this->service->calculateFrameworkScore($control->framework);
            }

            return redirect()->route('compliance-auto.controls.show', $id)
                ->with('success', 'Control updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update control: ' . $e->getMessage());
        }
    }

    public function uploadEvidence(Request $request, string $controlId)
    {
        $tenantId = $this->tenantId();

        try {
            $control = ComplianceControl::where('tenant_id', $tenantId)->findOrFail($controlId);

            $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'file'        => 'required|file|max:10240',
                'type'        => 'nullable|in:screenshot,document,query_result,api_response,manual_note,system_log',
            ]);

            $path = $request->file('file')->store("compliance-evidence/{$tenantId}", 'public');

            $evidence = ComplianceEvidence::create([
                'control_id'       => $control->id,
                'tenant_id'        => $tenantId,
                'type'             => $request->input('type', 'document'),
                'title'            => $request->title,
                'description'      => $request->description,
                'file_path'        => $path,
                'is_auto_collected' => false,
                'collected_by'     => Auth::id(),
                'collected_at'     => now(),
            ]);

            ComplianceAuditTrail::create([
                'tenant_id'   => $tenantId,
                'event_type'  => 'evidence_added',
                'entity_type' => 'control',
                'entity_id'   => $control->id,
                'description' => "Evidence uploaded: {$request->title}",
                'metadata'    => ['evidence_id' => $evidence->id, 'file_path' => $path],
                'user_id'     => Auth::id(),
            ]);

            return redirect()->route('compliance-auto.controls.show', $controlId)
                ->with('success', 'Evidence uploaded successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload evidence: ' . $e->getMessage());
        }
    }

    // ── MONITORS ─────────────────────────────────────────────────────────────

    public function monitors()
    {
        $tenantId = $this->tenantId();

        try {
            $monitors = ComplianceMonitor::where('tenant_id', $tenantId)
                ->orderBy('name')
                ->get();

            return view('compliance-automation.monitors', compact('monitors'));
        } catch (\Exception $e) {
            return view('compliance-automation.monitors', ['monitors' => collect()]);
        }
    }

    // ── RUN CHECKS ───────────────────────────────────────────────────────────

    public function runChecks()
    {
        $tenantId = $this->tenantId();

        try {
            $results = $this->service->runAutomatedChecks($tenantId);

            return redirect()->route('compliance-auto.dashboard')
                ->with('success', "Compliance checks completed: {$results['checks_run']} run, {$results['passing']} passing, {$results['warning']} warning, {$results['failing']} failing.");
        } catch (\Exception $e) {
            return redirect()->route('compliance-auto.dashboard')
                ->with('error', 'Failed to run checks: ' . $e->getMessage());
        }
    }

    // ── AUDIT TRAIL ──────────────────────────────────────────────────────────

    public function auditTrail(Request $request)
    {
        $tenantId = $this->tenantId();

        try {
            $query = ComplianceAuditTrail::where('tenant_id', $tenantId)
                ->orderByDesc('created_at');

            if ($request->filled('event_type')) {
                $query->where('event_type', $request->event_type);
            }
            if ($request->filled('entity_type')) {
                $query->where('entity_type', $request->entity_type);
            }
            if ($request->filled('from')) {
                $query->whereDate('created_at', '>=', $request->from);
            }
            if ($request->filled('to')) {
                $query->whereDate('created_at', '<=', $request->to);
            }

            $trail = $query->paginate(30)->withQueryString();

            return view('compliance-automation.audit-trail', compact('trail'));
        } catch (\Exception $e) {
            return view('compliance-automation.audit-trail', [
                'trail' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 30),
            ]);
        }
    }

    // ── TRUST REPORT ─────────────────────────────────────────────────────────

    public function trustReport()
    {
        $tenantId = $this->tenantId();

        try {
            $report = ComplianceTrustReport::where('tenant_id', $tenantId)->first();

            if (!$report) {
                $report = ComplianceTrustReport::create([
                    'tenant_id'        => $tenantId,
                    'public_url_token' => Str::random(32),
                    'is_published'     => false,
                    'visible_frameworks' => [],
                    'custom_sections'  => [],
                ]);
            }

            $frameworks = ComplianceFramework::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->get();

            $publicUrl = route('public-trust-report', $report->public_url_token);

            return view('compliance-automation.trust-report-edit', compact('report', 'frameworks', 'publicUrl'));
        } catch (\Exception $e) {
            return view('compliance-automation.trust-report-edit', [
                'report' => null,
                'frameworks' => collect(),
                'publicUrl' => '#',
            ]);
        }
    }

    public function updateTrustReport(Request $request)
    {
        $tenantId = $this->tenantId();

        try {
            $report = ComplianceTrustReport::where('tenant_id', $tenantId)->firstOrFail();

            $validated = $request->validate([
                'is_published'       => 'nullable|boolean',
                'visible_frameworks' => 'nullable|array',
                'intro_text'         => 'nullable|string|max:2000',
            ]);

            $report->update([
                'is_published'       => $request->boolean('is_published'),
                'visible_frameworks' => $validated['visible_frameworks'] ?? [],
                'intro_text'         => $validated['intro_text'] ?? null,
            ]);

            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('compliance-logos', 'public');
                $report->update(['logo_path' => $path]);
            }

            return redirect()->route('compliance-auto.trust-report')
                ->with('success', 'Trust report updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update trust report: ' . $e->getMessage());
        }
    }

    public function publicTrustReport(string $token)
    {
        try {
            $report = ComplianceTrustReport::where('public_url_token', $token)
                ->where('is_published', true)
                ->firstOrFail();

            $tenant = DB::table('tenants')->find($report->tenant_id);

            $visibleIds = $report->visible_frameworks ?? [];

            $frameworks = ComplianceFramework::where('tenant_id', $report->tenant_id)
                ->where('is_active', true)
                ->when(!empty($visibleIds), fn($q) => $q->whereIn('id', $visibleIds))
                ->orderBy('name')
                ->get();

            return view('compliance-automation.trust-report-public', compact('report', 'tenant', 'frameworks'));
        } catch (\Exception $e) {
            abort(404, 'Trust report not found or not published.');
        }
    }

    // ── GENERATE REPORT ──────────────────────────────────────────────────────

    public function generateReport(Request $request)
    {
        $tenantId = $this->tenantId();

        try {
            $frameworks = ComplianceFramework::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->with('controls')
                ->get();

            $monitors = ComplianceMonitor::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->get();

            $overallScore = $frameworks->count() > 0
                ? round($frameworks->avg('compliance_score'), 1)
                : 0;

            $narrative = $this->service->generateComplianceNarrative($tenantId);

            $tenant = DB::table('tenants')->find($tenantId);

            return view('compliance-automation.report', compact(
                'frameworks', 'monitors', 'overallScore', 'narrative', 'tenant'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate report.');
        }
    }
}
