<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\StaffProfile;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        $teams = Team::where('tenant_id', $tenantId)
            ->with(['branch', 'teamLead', 'members.user'])
            ->paginate(20);

        $branches = Branch::where('tenant_id', $tenantId)->get();
        $users    = User::where('tenant_id', $tenantId)->get();

        return view('kpi.setup.teams', compact('teams', 'branches', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'department'   => 'required|string|max:50',
            'branch_id'    => 'nullable|exists:branches,id',
            'team_lead_id' => 'nullable|exists:users,id',
            'description'  => 'nullable|string|max:500',
        ]);

        Team::create([...$data, 'tenant_id' => auth()->user()->tenant_id]);
        return back()->with('success', 'Team created.');
    }

    public function update(Request $request, Team $team)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'department'   => 'required|string|max:50',
            'branch_id'    => 'nullable|exists:branches,id',
            'team_lead_id' => 'nullable|exists:users,id',
            'description'  => 'nullable|string|max:500',
            'status'       => 'required|in:active,inactive',
        ]);

        $team->update($data);
        return back()->with('success', 'Team updated.');
    }

    public function destroy(Team $team)
    {
        $team->delete();
        return back()->with('success', 'Team deleted.');
    }

    public function addMember(Request $request, Team $team)
    {
        $data = $request->validate(['user_id' => 'required|exists:users,id']);

        StaffProfile::where('user_id', $data['user_id'])
            ->where('tenant_id', $team->tenant_id)
            ->update(['team_id' => $team->id]);

        return back()->with('success', 'Member added to team.');
    }

    public function removeMember(Team $team, User $user)
    {
        StaffProfile::where('user_id', $user->id)
            ->where('team_id', $team->id)
            ->update(['team_id' => null]);

        return back()->with('success', 'Member removed from team.');
    }
}
