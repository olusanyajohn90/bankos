<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CrmFollowUp;
use App\Models\CrmInteraction;
use App\Models\CrmLead;
use App\Models\CrmPipelineStage;
use App\Models\User;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    // ── Dashboard ────────────────────────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $stages = CrmPipelineStage::where('tenant_id', $tenantId)->orderBy('position')->get();

        // Pipeline summary
        $pipelineData = [];
        foreach ($stages as $stage) {
            $leads = CrmLead::where('tenant_id', $tenantId)
                ->where('stage_id', $stage->id)
                ->where('status', 'in_progress')
                ->get();
            $pipelineData[] = [
                'stage'       => $stage,
                'count'       => $leads->count(),
                'total_value' => $leads->sum('estimated_value'),
            ];
        }

        // Stats
        $totalLeads   = CrmLead::where('tenant_id', $tenantId)->count();
        $activeLeads  = CrmLead::where('tenant_id', $tenantId)->whereIn('status', ['new', 'in_progress'])->count();
        $converted    = CrmLead::where('tenant_id', $tenantId)->where('status', 'converted')->count();
        $recentInt    = CrmInteraction::where('tenant_id', $tenantId)->with('createdBy')->latest('interacted_at')->take(10)->get();

        // My follow-ups due today/overdue
        $myFollowUps  = CrmFollowUp::where('tenant_id', $tenantId)
            ->where('assigned_to', auth()->id())
            ->where('status', 'pending')
            ->orderBy('due_at')
            ->take(10)
            ->get();

        return view('crm.dashboard', compact(
            'stages', 'pipelineData', 'totalLeads', 'activeLeads',
            'converted', 'recentInt', 'myFollowUps'
        ));
    }

    // ── Leads ────────────────────────────────────────────────────────────────

    public function leads(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $user     = auth()->user();

        $query = CrmLead::where('tenant_id', $tenantId)
            ->with(['stage', 'assignedTo'])
            ->latest();

        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('stage_id')) $query->where('stage_id', $request->stage_id);
        if ($request->filled('assigned_to')) $query->where('assigned_to', $request->assigned_to);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('contact_name', 'like', "%{$s}%")
                  ->orWhere('company', 'like', "%{$s}%")
                  ->orWhere('contact_phone', 'like', "%{$s}%");
            });
        }

        $leads  = $query->paginate(25)->withQueryString();
        $stages = CrmPipelineStage::where('tenant_id', $tenantId)->orderBy('position')->get();
        $agents = User::where('tenant_id', $tenantId)->get(['id', 'name']);

        return view('crm.leads', compact('leads', 'stages', 'agents'));
    }

    public function showLead(CrmLead $lead)
    {
        abort_unless($lead->tenant_id === auth()->user()->tenant_id, 403);
        $lead->load(['stage', 'assignedTo', 'createdBy', 'interactions.createdBy', 'followUps.assignedTo']);
        $stages  = CrmPipelineStage::where('tenant_id', $lead->tenant_id)->orderBy('position')->get();
        $agents  = User::where('tenant_id', $lead->tenant_id)->get(['id', 'name']);
        return view('crm.lead-show', compact('lead', 'stages', 'agents'));
    }

    public function storeLead(Request $request)
    {
        $request->validate([
            'title'                => 'required|string|max:200',
            'contact_name'         => 'required|string|max:150',
            'contact_phone'        => 'nullable|string|max:30',
            'contact_email'        => 'nullable|email|max:150',
            'company'              => 'nullable|string|max:150',
            'source'               => 'nullable|string|max:50',
            'product_interest'     => 'nullable|string|max:100',
            'estimated_value'      => 'nullable|numeric|min:0',
            'probability_pct'      => 'nullable|integer|min:0|max:100',
            'assigned_to'          => 'nullable|exists:users,id',
            'expected_close_date'  => 'nullable|date',
            'stage_id'             => 'nullable|uuid',
            'notes'                => 'nullable|string|max:1000',
        ]);

        $tenantId = auth()->user()->tenant_id;

        // Default to first stage if none given
        if (!$request->stage_id) {
            $firstStage = CrmPipelineStage::where('tenant_id', $tenantId)->orderBy('position')->first();
            $request->merge(['stage_id' => $firstStage?->id]);
        }

        CrmLead::create(array_merge(
            $request->only([
                'title','contact_name','contact_phone','contact_email','company',
                'source','product_interest','estimated_value','probability_pct',
                'assigned_to','expected_close_date','stage_id','notes'
            ]),
            ['tenant_id' => $tenantId, 'status' => 'new', 'created_by' => auth()->id()]
        ));

        return back()->with('success', 'Lead created.');
    }

    public function updateLead(Request $request, CrmLead $lead)
    {
        abort_unless($lead->tenant_id === auth()->user()->tenant_id, 403);
        $request->validate([
            'stage_id'            => 'nullable|uuid',
            'status'              => 'nullable|in:new,in_progress,converted,lost,on_hold',
            'probability_pct'     => 'nullable|integer|min:0|max:100',
            'estimated_value'     => 'nullable|numeric|min:0',
            'assigned_to'         => 'nullable|exists:users,id',
            'expected_close_date' => 'nullable|date',
            'lost_reason'         => 'nullable|string|max:500',
            'notes'               => 'nullable|string|max:1000',
        ]);

        $data = $request->only([
            'stage_id','status','probability_pct','estimated_value',
            'assigned_to','expected_close_date','lost_reason','notes'
        ]);

        if (in_array($request->status, ['converted','lost'])) {
            $data['closed_date'] = now()->toDateString();
        }

        $lead->update($data);
        return back()->with('success', 'Lead updated.');
    }

    // ── Interactions ─────────────────────────────────────────────────────────

    public function interactions(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = CrmInteraction::where('tenant_id', $tenantId)
            ->with('createdBy', 'lead')
            ->latest('interacted_at');

        if ($request->filled('type'))     $query->where('interaction_type', $request->type);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('summary', 'like', "%{$s}%")
                  ->orWhere('subject', 'like', "%{$s}%");
            });
        }

        $interactions = $query->paginate(30)->withQueryString();
        return view('crm.interactions', compact('interactions'));
    }

    public function storeInteraction(Request $request)
    {
        $request->validate([
            'interaction_type' => 'required|in:call,meeting,email,whatsapp,visit,sms,note',
            'direction'        => 'required|in:inbound,outbound,internal',
            'summary'          => 'required|string|max:2000',
            'interacted_at'    => 'required|date',
            'subject_type'     => 'required|in:account,lead,customer',
            'subject_id'       => 'required|string|max:36',
            'lead_id'          => 'nullable|uuid',
            'account_id'       => 'nullable|string|max:36',
            'subject'          => 'nullable|string|max:200',
            'outcome'          => 'nullable|string|max:1000',
            'next_action'      => 'nullable|string|max:200',
            'next_action_date' => 'nullable|date',
            'duration_mins'    => 'nullable|integer|min:0',
        ]);

        CrmInteraction::create(array_merge(
            $request->only([
                'interaction_type','direction','summary','interacted_at',
                'subject_type','subject_id','lead_id','account_id',
                'subject','outcome','next_action','next_action_date','duration_mins'
            ]),
            ['tenant_id' => auth()->user()->tenant_id, 'created_by' => auth()->id()]
        ));

        return back()->with('success', 'Interaction logged.');
    }

    // ── Follow-ups ────────────────────────────────────────────────────────────

    public function storeFollowUp(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:200',
            'subject_type' => 'required|in:lead,account',
            'subject_id'   => 'required|string|max:36',
            'due_at'       => 'required|date',
            'assigned_to'  => 'nullable|exists:users,id',
            'notes'        => 'nullable|string|max:500',
        ]);

        CrmFollowUp::create(array_merge(
            $request->only(['title','subject_type','subject_id','due_at','assigned_to','notes']),
            [
                'tenant_id'   => auth()->user()->tenant_id,
                'created_by'  => auth()->id(),
                'assigned_to' => $request->assigned_to ?? auth()->id(),
            ]
        ));
        return back()->with('success', 'Follow-up scheduled.');
    }

    public function completeFollowUp(CrmFollowUp $followUp)
    {
        abort_unless($followUp->tenant_id === auth()->user()->tenant_id, 403);
        $followUp->update(['status' => 'completed']);
        return back()->with('success', 'Follow-up marked as done.');
    }

    // ── Pipeline Stage Management ─────────────────────────────────────────────

    public function stageSettings(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $stages   = CrmPipelineStage::where('tenant_id', $tenantId)->orderBy('position')->get();
        return view('crm.stages', compact('stages'));
    }

    public function storeStage(Request $request)
    {
        $request->validate(['name' => 'required|string|max:80', 'color' => 'nullable|string|max:7']);
        $tenantId = auth()->user()->tenant_id;
        $max      = CrmPipelineStage::where('tenant_id', $tenantId)->max('position') ?? 0;
        CrmPipelineStage::create([
            'tenant_id' => $tenantId,
            'name'      => $request->name,
            'color'     => $request->color ?? '#3b82f6',
            'position'  => $max + 1,
        ]);
        return back()->with('success', 'Stage added.');
    }

    public function destroyStage(CrmPipelineStage $stage)
    {
        abort_unless($stage->tenant_id === auth()->user()->tenant_id, 403);
        if ($stage->leads()->exists()) return back()->with('error', 'Cannot delete stage with leads.');
        $stage->delete();
        return back()->with('success', 'Stage removed.');
    }

    // ── Customer 360 ─────────────────────────────────────────────────────────

    public function customer360(Request $request, string $accountId)
    {
        $tenantId = auth()->user()->tenant_id;

        $account = Account::where('id', $accountId)->where('tenant_id', $tenantId)->firstOrFail();
        $account->load(['owner', 'branch']);

        $interactions = CrmInteraction::where('tenant_id', $tenantId)
            ->where('subject_type', 'account')
            ->where('subject_id', $accountId)
            ->with('createdBy')
            ->latest('interacted_at')
            ->take(20)
            ->get();

        $followUps = CrmFollowUp::where('tenant_id', $tenantId)
            ->where('subject_type', 'account')
            ->where('subject_id', $accountId)
            ->where('status', 'pending')
            ->orderBy('due_at')
            ->get();

        return view('crm.customer360', compact('account', 'interactions', 'followUps'));
    }
}
