<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportTeam;
use App\Models\Support\SupportSlaPolicy;
use App\Models\Support\SupportCategory;
use App\Models\Support\SupportKbArticle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportTeamController extends Controller
{
    public function index()
    {
        $tenantId = session('tenant_id');
        $teams    = SupportTeam::where('tenant_id', $tenantId)
            ->withCount(['members','tickets','openTickets'])
            ->with('teamLead')->get();

        $users = \App\Models\User::where('tenant_id', $tenantId)->orderBy('name')->get();
        return view('support.teams.index', compact('teams', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:200',
            'division' => 'nullable|string|max:200',
        ]);

        SupportTeam::create([
            'id'           => Str::uuid(),
            'tenant_id'    => session('tenant_id'),
            'name'         => $request->name,
            'code'         => $request->code,
            'division'     => $request->division,
            'description'  => $request->description,
            'email'        => $request->email,
            'team_lead_id' => $request->team_lead_id,
            'is_active'    => true,
        ]);

        return back()->with('success', 'Team created.');
    }

    public function update(Request $request, SupportTeam $supportTeam)
    {
        $supportTeam->update($request->only('name','code','division','description','email','team_lead_id'));
        return back()->with('success', 'Team updated.');
    }

    public function addMember(Request $request, SupportTeam $supportTeam)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $supportTeam->members()->syncWithoutDetaching([
            $request->user_id => ['role' => $request->role ?? 'agent', 'is_active' => true]
        ]);
        return back()->with('success', 'Member added.');
    }

    public function removeMember(SupportTeam $supportTeam, User $user)
    {
        $supportTeam->members()->detach($user->id);
        return back()->with('success', 'Member removed.');
    }

    public function toggle(SupportTeam $supportTeam)
    {
        $supportTeam->update(['is_active' => ! $supportTeam->is_active]);
        return back()->with('success', 'Team ' . ($supportTeam->is_active ? 'activated' : 'deactivated') . '.');
    }

    // ── SLA Policies ────────────────────────────────────────────────────────

    public function slaIndex()
    {
        $tenantId = session('tenant_id');
        $policies = SupportSlaPolicy::where('tenant_id', $tenantId)->orderBy('priority')->get();
        return view('support.sla.index', compact('policies'));
    }

    public function slaStore(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:200',
            'priority'           => 'required|in:low,medium,high,critical',
            'response_minutes'   => 'required|integer|min:5',
            'resolution_minutes' => 'required|integer|min:15',
        ]);

        SupportSlaPolicy::create([
            'id'                  => Str::uuid(),
            'tenant_id'           => session('tenant_id'),
            'name'                => $request->name,
            'priority'            => $request->priority,
            'response_minutes'    => $request->response_minutes,
            'resolution_minutes'  => $request->resolution_minutes,
            'business_hours_only' => $request->boolean('business_hours_only', true),
            'is_default'          => $request->boolean('is_default'),
        ]);

        return back()->with('success', 'SLA policy created.');
    }

    public function slaUpdate(Request $request, SupportSlaPolicy $supportSlaPolicy)
    {
        $supportSlaPolicy->update($request->only('name','response_minutes','resolution_minutes','business_hours_only','is_default'));
        return back()->with('success', 'SLA updated.');
    }

    // ── Categories ───────────────────────────────────────────────────────────

    public function categoryToggle(SupportCategory $supportCategory)
    {
        $supportCategory->update(['is_active' => ! $supportCategory->is_active]);
        return back()->with('success', 'Category ' . ($supportCategory->is_active ? 'enabled' : 'disabled') . '.');
    }

    public function categoriesIndex()
    {
        $tenantId   = session('tenant_id');
        $categories = SupportCategory::where('tenant_id', $tenantId)
            ->withCount('tickets')->with('team')->get();
        $teams      = SupportTeam::where('tenant_id', $tenantId)->where('is_active', true)->get();
        return view('support.categories.index', compact('categories','teams'));
    }

    public function categoriesStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:200']);
        SupportCategory::create([
            'id'        => Str::uuid(),
            'tenant_id' => session('tenant_id'),
            'team_id'   => $request->team_id,
            'name'      => $request->name,
            'icon'      => $request->icon,
            'is_active' => true,
        ]);
        return back()->with('success', 'Category created.');
    }

    // ── Knowledge Base ───────────────────────────────────────────────────────

    public function kbIndex()
    {
        $tenantId = session('tenant_id');
        $articles = SupportKbArticle::where('tenant_id', $tenantId)
            ->with('createdBy')->orderByDesc('created_at')->paginate(20);
        return view('support.kb.index', compact('articles'));
    }

    public function kbStore(Request $request)
    {
        $request->validate(['title' => 'required|string|max:300', 'body' => 'required|string']);

        SupportKbArticle::create([
            'id'         => Str::uuid(),
            'tenant_id'  => session('tenant_id'),
            'created_by' => auth()->id(),
            'title'      => $request->title,
            'body'       => $request->body,
            'category'   => $request->category,
            'status'     => $request->boolean('publish') ? 'published' : 'draft',
        ]);

        return back()->with('success', 'Article saved.');
    }

    public function kbPublish(SupportKbArticle $supportKbArticle)
    {
        $supportKbArticle->update(['status' => 'published']);
        return back()->with('success', 'Article published.');
    }

    public function kbDestroy(SupportKbArticle $supportKbArticle)
    {
        $supportKbArticle->delete();
        return back()->with('success', 'Article deleted.');
    }
}
