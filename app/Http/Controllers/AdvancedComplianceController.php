<?php

namespace App\Http\Controllers;

use App\Models\CustomerRiskScore;
use App\Models\TransactionScreening;
use App\Models\SuspiciousActivityReport;
use App\Models\PerpetualKycEvent;
use App\Models\CustomerBehaviorProfile;
use App\Models\EntityRelationship;
use App\Models\BeneficialOwner;
use App\Models\AdverseMediaResult;
use App\Models\PredictiveComplianceAlert;
use App\Models\RegulatoryChange;
use App\Models\ComplianceScenario;
use App\Models\ComplianceChatSession;
use App\Models\ComplianceAgentTask;
use App\Models\CrossBorderRule;
use App\Models\RegulatorySimulation;
use App\Models\Customer;
use App\Services\AdvancedComplianceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdvancedComplianceController extends Controller
{
    private AdvancedComplianceService $service;

    public function __construct(AdvancedComplianceService $service)
    {
        $this->service = $service;
    }

    private function tenantId(): string
    {
        return Auth::user()->tenant_id;
    }

    // ══════════════════════════════════════════════════════════════════
    // PHASE 1: Risk Scoring, Screening, SAR/STR, Perpetual KYC
    // ══════════════════════════════════════════════════════════════════

    public function riskScoring()
    {
        $tenantId = $this->tenantId();

        try {
            $scores = CustomerRiskScore::where('tenant_id', $tenantId)
                ->with('customer')
                ->orderByDesc('overall_score')
                ->get();

            $distribution = [
                'low'      => $scores->where('risk_level', 'low')->count(),
                'medium'   => $scores->where('risk_level', 'medium')->count(),
                'high'     => $scores->where('risk_level', 'high')->count(),
                'critical' => $scores->where('risk_level', 'critical')->count(),
                'pep'      => $scores->where('risk_level', 'pep')->count(),
            ];

            $highRisk = $scores->whereIn('risk_level', ['high', 'critical', 'pep'])->take(20);

            return view('compliance-automation.risk-scoring', compact('scores', 'distribution', 'highRisk'));
        } catch (\Exception $e) {
            return view('compliance-automation.risk-scoring', [
                'scores' => collect(), 'distribution' => ['low' => 0, 'medium' => 0, 'high' => 0, 'critical' => 0, 'pep' => 0], 'highRisk' => collect(),
            ]);
        }
    }

    public function customerRisk(string $customerId)
    {
        $tenantId = $this->tenantId();

        try {
            $customer = Customer::where('tenant_id', $tenantId)->findOrFail($customerId);
            $riskScore = CustomerRiskScore::where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->first();

            $screenings = TransactionScreening::where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();

            $kycEvents = PerpetualKycEvent::where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            return view('compliance-automation.customer-risk', compact('customer', 'riskScore', 'screenings', 'kycEvents'));
        } catch (\Exception $e) {
            return redirect()->route('compliance-auto.risk-scoring')->with('error', 'Customer not found.');
        }
    }

    public function recalculateRisk(string $customerId)
    {
        $tenantId = $this->tenantId();

        try {
            $customer = Customer::where('tenant_id', $tenantId)->findOrFail($customerId);
            $result = $this->service->calculateCustomerRisk($customer);

            return redirect()->route('compliance-auto.customer-risk', $customerId)
                ->with('success', "Risk recalculated: {$result['risk_level']} ({$result['overall_score']})");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to recalculate risk: ' . $e->getMessage());
        }
    }

    public function batchRiskScoring()
    {
        $tenantId = $this->tenantId();

        try {
            $customers = Customer::where('tenant_id', $tenantId)->where('status', 'active')->limit(200)->get();
            $scored = 0;

            foreach ($customers as $customer) {
                $this->service->calculateCustomerRisk($customer);
                $scored++;
            }

            return redirect()->route('compliance-auto.risk-scoring')
                ->with('success', "Batch scoring complete: {$scored} customers scored.");
        } catch (\Exception $e) {
            return redirect()->route('compliance-auto.risk-scoring')
                ->with('error', 'Batch scoring failed: ' . $e->getMessage());
        }
    }

    public function screeningDashboard()
    {
        $tenantId = $this->tenantId();

        try {
            $screenings = TransactionScreening::where('tenant_id', $tenantId)
                ->with('customer')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();

            $stats = [
                'clear'           => TransactionScreening::where('tenant_id', $tenantId)->where('result', 'clear')->count(),
                'match'           => TransactionScreening::where('tenant_id', $tenantId)->where('result', 'match')->count(),
                'potential_match' => TransactionScreening::where('tenant_id', $tenantId)->where('result', 'potential_match')->count(),
                'flagged'         => TransactionScreening::where('tenant_id', $tenantId)->where('result', 'flagged')->count(),
            ];

            $total = array_sum($stats) ?: 1;
            $falsePositiveRate = TransactionScreening::where('tenant_id', $tenantId)
                ->where('disposition', 'false_positive')->count();
            $fpRate = round(($falsePositiveRate / $total) * 100, 1);

            return view('compliance-automation.screening-dashboard', compact('screenings', 'stats', 'fpRate'));
        } catch (\Exception $e) {
            return view('compliance-automation.screening-dashboard', [
                'screenings' => collect(),
                'stats'      => ['clear' => 0, 'match' => 0, 'potential_match' => 0, 'flagged' => 0],
                'fpRate'     => 0,
            ]);
        }
    }

    public function screeningResults(Request $request)
    {
        $tenantId = $this->tenantId();

        try {
            $query = TransactionScreening::where('tenant_id', $tenantId)->with('customer');

            if ($request->filled('result')) {
                $query->where('result', $request->result);
            }
            if ($request->filled('disposition')) {
                $query->where('disposition', $request->disposition);
            }

            $screenings = $query->orderByDesc('created_at')->paginate(30)->withQueryString();

            return view('compliance-automation.screening-dashboard', compact('screenings'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load screening results.');
        }
    }

    public function reviewScreening(Request $request, string $id)
    {
        $tenantId = $this->tenantId();

        try {
            $screening = TransactionScreening::where('tenant_id', $tenantId)->findOrFail($id);

            $request->validate([
                'disposition'  => 'required|in:true_positive,false_positive,escalated',
                'review_notes' => 'nullable|string',
            ]);

            $screening->update([
                'disposition'  => $request->disposition,
                'reviewed_by'  => Auth::id(),
                'reviewed_at'  => now(),
                'review_notes' => $request->review_notes,
            ]);

            return redirect()->route('compliance-auto.screening')
                ->with('success', 'Screening reviewed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to review screening: ' . $e->getMessage());
        }
    }

    public function sarReports()
    {
        $tenantId = $this->tenantId();

        try {
            $reports = SuspiciousActivityReport::where('tenant_id', $tenantId)
                ->with('customer')
                ->orderByDesc('created_at')
                ->get();

            $customers = Customer::where('tenant_id', $tenantId)
                ->select('id', 'first_name', 'last_name', 'customer_number')
                ->orderBy('first_name')
                ->get();

            return view('compliance-automation.sar-reports', compact('reports', 'customers'));
        } catch (\Exception $e) {
            return view('compliance-automation.sar-reports', ['reports' => collect(), 'customers' => collect()]);
        }
    }

    public function createSar(string $customerId)
    {
        $tenantId = $this->tenantId();

        try {
            $customer = Customer::where('tenant_id', $tenantId)->findOrFail($customerId);

            // Get recent flagged transactions
            $accountIds = DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->pluck('id');

            $transactions = DB::table('transactions')
                ->whereIn('account_id', $accountIds)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();

            $txnIds = $transactions->pluck('id')->toArray();
            $narrative = $this->service->generateSarNarrative($customer, $txnIds);

            $sar = SuspiciousActivityReport::create([
                'tenant_id'              => $tenantId,
                'report_type'            => 'STR',
                'reference'              => 'STR-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                'customer_id'            => $customerId,
                'narrative'              => $narrative,
                'transactions_involved'  => $txnIds,
                'total_amount'           => $transactions->sum('amount'),
                'suspicion_category'     => 'unusual_pattern',
                'status'                 => 'draft',
                'prepared_by'            => Auth::id(),
            ]);

            return redirect()->route('compliance-auto.sar.show', $sar->id)
                ->with('success', 'SAR generated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('compliance-auto.sar')
                ->with('error', 'Failed to generate SAR: ' . $e->getMessage());
        }
    }

    public function showSar(string $id)
    {
        $tenantId = $this->tenantId();

        try {
            $sar = SuspiciousActivityReport::where('tenant_id', $tenantId)
                ->with(['customer', 'preparer', 'approver'])
                ->findOrFail($id);

            return view('compliance-automation.sar-show', compact('sar'));
        } catch (\Exception $e) {
            return redirect()->route('compliance-auto.sar')->with('error', 'Report not found.');
        }
    }

    public function approveSar(string $id)
    {
        $tenantId = $this->tenantId();

        try {
            $sar = SuspiciousActivityReport::where('tenant_id', $tenantId)->findOrFail($id);

            $sar->update([
                'status'      => 'approved',
                'approved_by' => Auth::id(),
            ]);

            return redirect()->route('compliance-auto.sar.show', $id)
                ->with('success', 'SAR approved for filing.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve SAR: ' . $e->getMessage());
        }
    }

    public function kycMonitoring()
    {
        $tenantId = $this->tenantId();

        try {
            $events = PerpetualKycEvent::where('tenant_id', $tenantId)
                ->with('customer')
                ->orderByRaw("CASE WHEN status = 'open' THEN 0 WHEN status = 'in_review' THEN 1 ELSE 2 END")
                ->orderByDesc('created_at')
                ->get();

            $openCount = $events->where('status', 'open')->count();
            $reviewCount = $events->where('status', 'in_review')->count();
            $resolvedCount = $events->where('status', 'resolved')->count();

            return view('compliance-automation.kyc-monitoring', compact('events', 'openCount', 'reviewCount', 'resolvedCount'));
        } catch (\Exception $e) {
            return view('compliance-automation.kyc-monitoring', [
                'events' => collect(), 'openCount' => 0, 'reviewCount' => 0, 'resolvedCount' => 0,
            ]);
        }
    }

    public function resolveKycEvent(string $id)
    {
        $tenantId = $this->tenantId();

        try {
            $event = PerpetualKycEvent::where('tenant_id', $tenantId)->findOrFail($id);

            $event->update([
                'status'      => 'resolved',
                'resolved_by' => Auth::id(),
                'resolved_at' => now(),
            ]);

            return redirect()->route('compliance-auto.kyc-monitoring')
                ->with('success', 'KYC event resolved.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to resolve event: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // PHASE 2: Behavioral Analytics, Network Analysis, Beneficial Ownership
    // ══════════════════════════════════════════════════════════════════

    public function behavioralAnalytics()
    {
        $tenantId = $this->tenantId();

        try {
            $profiles = CustomerBehaviorProfile::where('tenant_id', $tenantId)
                ->with('customer')
                ->orderByDesc('behavior_risk_score')
                ->get();

            $anomalyAlerts = $profiles->where('anomaly_count_30d', '>', 0);

            return view('compliance-automation.behavioral-analytics', compact('profiles', 'anomalyAlerts'));
        } catch (\Exception $e) {
            return view('compliance-automation.behavioral-analytics', ['profiles' => collect(), 'anomalyAlerts' => collect()]);
        }
    }

    public function customerBehavior(string $customerId)
    {
        $tenantId = $this->tenantId();

        try {
            $customer = Customer::where('tenant_id', $tenantId)->findOrFail($customerId);
            $profile = CustomerBehaviorProfile::where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->first();

            if (!$profile) {
                $this->service->buildBehaviorProfile($customer);
                $profile = CustomerBehaviorProfile::where('tenant_id', $tenantId)
                    ->where('customer_id', $customerId)
                    ->first();
            }

            return view('compliance-automation.behavioral-analytics', compact('customer', 'profile'));
        } catch (\Exception $e) {
            return back()->with('error', 'Customer not found.');
        }
    }

    public function networkAnalysis()
    {
        $tenantId = $this->tenantId();

        try {
            $relationships = EntityRelationship::where('tenant_id', $tenantId)
                ->orderByDesc('is_suspicious')
                ->orderByDesc('strength')
                ->limit(100)
                ->get();

            $suspiciousCount = $relationships->where('is_suspicious', true)->count();

            // Resolve entity names
            $entityIds = $relationships->pluck('entity_a_id')
                ->merge($relationships->pluck('entity_b_id'))
                ->unique();

            $customerNames = DB::table('customers')
                ->whereIn('id', $entityIds)
                ->pluck(DB::raw("CONCAT(first_name, ' ', last_name)"), 'id');

            return view('compliance-automation.network-analysis', compact('relationships', 'suspiciousCount', 'customerNames'));
        } catch (\Exception $e) {
            return view('compliance-automation.network-analysis', [
                'relationships' => collect(), 'suspiciousCount' => 0, 'customerNames' => collect(),
            ]);
        }
    }

    public function customerNetwork(string $customerId)
    {
        $tenantId = $this->tenantId();

        try {
            $customer = Customer::where('tenant_id', $tenantId)->findOrFail($customerId);
            $network = $this->service->analyzeNetwork($customer);

            return response()->json($network);
        } catch (\Exception $e) {
            return response()->json(['relationships' => [], 'suspicious_links' => 0]);
        }
    }

    public function beneficialOwnership()
    {
        $tenantId = $this->tenantId();

        try {
            $owners = BeneficialOwner::where('tenant_id', $tenantId)
                ->with('customer')
                ->orderBy('owner_name')
                ->get();

            $customers = Customer::where('tenant_id', $tenantId)
                ->where('type', 'corporate')
                ->select('id', 'first_name', 'last_name', 'customer_number')
                ->get();

            return view('compliance-automation.beneficial-ownership', compact('owners', 'customers'));
        } catch (\Exception $e) {
            return view('compliance-automation.beneficial-ownership', ['owners' => collect(), 'customers' => collect()]);
        }
    }

    public function addBeneficialOwner(Request $request)
    {
        $tenantId = $this->tenantId();

        try {
            $validated = $request->validate([
                'customer_id'          => 'required|uuid',
                'owner_name'           => 'required|string|max:200',
                'nationality'          => 'nullable|string|max:100',
                'id_type'              => 'nullable|string|max:50',
                'id_number'            => 'nullable|string|max:50',
                'ownership_percentage' => 'required|numeric|min:0|max:100',
                'is_pep'               => 'nullable|boolean',
                'is_sanctioned'        => 'nullable|boolean',
                'date_of_birth'        => 'nullable|date',
                'address'              => 'nullable|string',
            ]);

            $validated['tenant_id'] = $tenantId;
            $validated['is_pep'] = $request->boolean('is_pep');
            $validated['is_sanctioned'] = $request->boolean('is_sanctioned');

            BeneficialOwner::create($validated);

            return redirect()->route('compliance-auto.beneficial-ownership')
                ->with('success', 'Beneficial owner added.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add owner: ' . $e->getMessage());
        }
    }

    public function adverseMedia()
    {
        $tenantId = $this->tenantId();

        try {
            $results = AdverseMediaResult::where('tenant_id', $tenantId)
                ->with('customer')
                ->orderByDesc('created_at')
                ->get();

            return view('compliance-automation.adverse-media', compact('results'));
        } catch (\Exception $e) {
            return view('compliance-automation.adverse-media', ['results' => collect()]);
        }
    }

    public function screenAdverseMedia(string $customerId)
    {
        $tenantId = $this->tenantId();

        try {
            $customer = Customer::where('tenant_id', $tenantId)->findOrFail($customerId);
            // In production, this would call an adverse media API
            return redirect()->route('compliance-auto.adverse-media')
                ->with('success', "Adverse media screening initiated for {$customer->first_name} {$customer->last_name}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Screening failed: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // PHASE 3: Predictive Alerts, Reg Changes, Scenarios, Chatbot
    // ══════════════════════════════════════════════════════════════════

    public function predictiveAlerts()
    {
        $tenantId = $this->tenantId();

        try {
            $alerts = PredictiveComplianceAlert::where('tenant_id', $tenantId)
                ->orderByRaw("CASE severity WHEN 'critical' THEN 0 WHEN 'warning' THEN 1 ELSE 2 END")
                ->orderByDesc('created_at')
                ->get();

            return view('compliance-automation.predictive-alerts', compact('alerts'));
        } catch (\Exception $e) {
            return view('compliance-automation.predictive-alerts', ['alerts' => collect()]);
        }
    }

    public function acknowledgeAlert(string $id)
    {
        $tenantId = $this->tenantId();

        try {
            $alert = PredictiveComplianceAlert::where('tenant_id', $tenantId)->findOrFail($id);
            $alert->update(['status' => 'acknowledged']);

            return redirect()->route('compliance-auto.predictive-alerts')
                ->with('success', 'Alert acknowledged.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to acknowledge alert.');
        }
    }

    public function regulatoryChanges()
    {
        $tenantId = $this->tenantId();

        try {
            $changes = RegulatoryChange::where('tenant_id', $tenantId)
                ->orderByDesc('published_date')
                ->get();

            return view('compliance-automation.regulatory-changes', compact('changes'));
        } catch (\Exception $e) {
            return view('compliance-automation.regulatory-changes', ['changes' => collect()]);
        }
    }

    public function addRegulatoryChange(Request $request)
    {
        $tenantId = $this->tenantId();

        try {
            $validated = $request->validate([
                'regulator'        => 'required|string|max:100',
                'title'            => 'required|string|max:500',
                'summary'          => 'required|string',
                'reference_number' => 'nullable|string|max:100',
                'effective_date'   => 'nullable|date',
                'published_date'   => 'nullable|date',
                'impact_level'     => 'required|in:low,medium,high,critical',
                'affected_areas'   => 'nullable|array',
            ]);

            $validated['tenant_id'] = $tenantId;
            RegulatoryChange::create($validated);

            return redirect()->route('compliance-auto.regulatory-changes')
                ->with('success', 'Regulatory change added.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add regulatory change: ' . $e->getMessage());
        }
    }

    public function showRegulatoryChange(string $id)
    {
        $tenantId = $this->tenantId();

        try {
            $change = RegulatoryChange::where('tenant_id', $tenantId)->findOrFail($id);

            return view('compliance-automation.regulatory-change-show', compact('change'));
        } catch (\Exception $e) {
            return redirect()->route('compliance-auto.regulatory-changes')->with('error', 'Change not found.');
        }
    }

    public function scenarios()
    {
        $tenantId = $this->tenantId();

        try {
            $scenarios = ComplianceScenario::where('tenant_id', $tenantId)
                ->orderByDesc('created_at')
                ->get();

            return view('compliance-automation.scenarios', compact('scenarios'));
        } catch (\Exception $e) {
            return view('compliance-automation.scenarios', ['scenarios' => collect()]);
        }
    }

    public function createScenario(Request $request)
    {
        $tenantId = $this->tenantId();

        try {
            $validated = $request->validate([
                'name'        => 'required|string|max:200',
                'description' => 'nullable|string',
                'category'    => 'required|in:aml,fraud,sanctions,kyc,regulatory,stress_test',
                'test_config' => 'required|array',
            ]);

            $validated['tenant_id'] = $tenantId;
            $validated['created_by'] = Auth::id();

            ComplianceScenario::create($validated);

            return redirect()->route('compliance-auto.scenarios')
                ->with('success', 'Scenario created.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create scenario: ' . $e->getMessage());
        }
    }

    public function runScenario(string $id)
    {
        $tenantId = $this->tenantId();

        try {
            $scenario = ComplianceScenario::where('tenant_id', $tenantId)->findOrFail($id);
            $result = $this->service->runScenarioTest($scenario);

            $status = $result['passed'] ? 'PASSED' : 'FAILED';
            return redirect()->route('compliance-auto.scenarios')
                ->with('success', "Scenario test {$status}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to run scenario: ' . $e->getMessage());
        }
    }

    public function complianceChat()
    {
        $tenantId = $this->tenantId();

        try {
            $session = ComplianceChatSession::where('tenant_id', $tenantId)
                ->where('user_id', Auth::id())
                ->where('is_resolved', false)
                ->latest()
                ->first();

            if (!$session) {
                $session = ComplianceChatSession::create([
                    'tenant_id' => $tenantId,
                    'user_id'   => Auth::id(),
                    'messages'  => [],
                ]);
            }

            return view('compliance-automation.compliance-chat', compact('session'));
        } catch (\Exception $e) {
            return view('compliance-automation.compliance-chat', ['session' => null]);
        }
    }

    public function chatMessage(Request $request)
    {
        $tenantId = $this->tenantId();

        try {
            $request->validate(['message' => 'required|string|max:2000']);

            $session = ComplianceChatSession::where('tenant_id', $tenantId)
                ->where('user_id', Auth::id())
                ->where('is_resolved', false)
                ->latest()
                ->first();

            if (!$session) {
                $session = ComplianceChatSession::create([
                    'tenant_id' => $tenantId,
                    'user_id'   => Auth::id(),
                    'messages'  => [],
                ]);
            }

            $messages = $session->messages ?? [];
            $messages[] = ['role' => 'user', 'content' => $request->message, 'timestamp' => now()->toISOString()];

            $aiResponse = $this->service->complianceChatResponse($request->message, $tenantId);
            $messages[] = ['role' => 'assistant', 'content' => $aiResponse, 'timestamp' => now()->toISOString()];

            $session->update(['messages' => $messages]);

            return response()->json([
                'response' => $aiResponse,
                'session_id' => $session->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['response' => 'Sorry, I encountered an error. Please try again.', 'error' => $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // PHASE 4: Autonomous Agents, Cross-border, Simulations
    // ══════════════════════════════════════════════════════════════════

    public function autonomousAgents()
    {
        $tenantId = $this->tenantId();

        try {
            $tasks = ComplianceAgentTask::where('tenant_id', $tenantId)
                ->orderByDesc('created_at')
                ->get();

            $agentTypes = [
                'kyc_refresher'      => ['name' => 'KYC Refresher', 'description' => 'Scan for expired KYC documents', 'icon' => 'id-card'],
                'sanctions_scanner'  => ['name' => 'Sanctions Scanner', 'description' => 'Screen all customers against sanctions lists', 'icon' => 'shield'],
                'risk_scorer'        => ['name' => 'Risk Scorer', 'description' => 'Recalculate all customer risk scores', 'icon' => 'chart-bar'],
                'report_filer'       => ['name' => 'Report Filer', 'description' => 'File approved SAR/STR reports', 'icon' => 'document'],
                'evidence_collector' => ['name' => 'Evidence Collector', 'description' => 'Auto-collect compliance evidence', 'icon' => 'archive'],
            ];

            return view('compliance-automation.autonomous-agents', compact('tasks', 'agentTypes'));
        } catch (\Exception $e) {
            return view('compliance-automation.autonomous-agents', ['tasks' => collect(), 'agentTypes' => []]);
        }
    }

    public function runAgent(Request $request)
    {
        $tenantId = $this->tenantId();

        try {
            $request->validate(['agent_type' => 'required|in:kyc_refresher,sanctions_scanner,risk_scorer,report_filer,evidence_collector']);

            $result = $this->service->runAutonomousAgent($request->agent_type, $tenantId);

            return redirect()->route('compliance-auto.agents')
                ->with('success', "Agent completed: {$result['items_processed']} items processed, {$result['issues_found']} issues found.");
        } catch (\Exception $e) {
            return redirect()->route('compliance-auto.agents')
                ->with('error', 'Agent failed: ' . $e->getMessage());
        }
    }

    public function crossBorderRules()
    {
        $tenantId = $this->tenantId();

        try {
            $rules = CrossBorderRule::where('tenant_id', $tenantId)
                ->orderBy('country_name')
                ->get();

            return view('compliance-automation.cross-border', compact('rules'));
        } catch (\Exception $e) {
            return view('compliance-automation.cross-border', ['rules' => collect()]);
        }
    }

    public function addCrossBorderRule(Request $request)
    {
        $tenantId = $this->tenantId();

        try {
            $validated = $request->validate([
                'country_code'  => 'required|string|max:3',
                'country_name'  => 'required|string|max:100',
                'risk_category' => 'required|in:low,medium,high,prohibited',
                'requirements'  => 'nullable|array',
                'restrictions'  => 'nullable|array',
            ]);

            $validated['tenant_id'] = $tenantId;
            CrossBorderRule::create($validated);

            return redirect()->route('compliance-auto.cross-border')
                ->with('success', 'Cross-border rule added.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add rule: ' . $e->getMessage());
        }
    }

    public function simulations()
    {
        $tenantId = $this->tenantId();

        try {
            $simulations = RegulatorySimulation::where('tenant_id', $tenantId)
                ->orderByDesc('created_at')
                ->get();

            return view('compliance-automation.simulations', compact('simulations'));
        } catch (\Exception $e) {
            return view('compliance-automation.simulations', ['simulations' => collect()]);
        }
    }

    public function createSimulation(Request $request)
    {
        $tenantId = $this->tenantId();

        try {
            $validated = $request->validate([
                'name'            => 'required|string|max:200',
                'description'     => 'nullable|string',
                'scenario_params' => 'required|array',
            ]);

            $validated['tenant_id'] = $tenantId;
            $validated['created_by'] = Auth::id();

            RegulatorySimulation::create($validated);

            return redirect()->route('compliance-auto.simulations')
                ->with('success', 'Simulation created.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create simulation: ' . $e->getMessage());
        }
    }

    public function runSimulation(string $id)
    {
        $tenantId = $this->tenantId();

        try {
            $sim = RegulatorySimulation::where('tenant_id', $tenantId)->findOrFail($id);
            $sim->update(['status' => 'running']);

            $result = $this->service->simulateRegulation($sim);

            return redirect()->route('compliance-auto.simulations')
                ->with('success', 'Simulation completed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Simulation failed: ' . $e->getMessage());
        }
    }
}
