<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\StaffProfile;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffProfileController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $profiles = StaffProfile::where('tenant_id', $tenantId)
            ->with(['user', 'branch', 'team', 'manager'])
            ->when($request->search, fn($q) =>
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$request->search}%"))
            )
            ->when($request->department, fn($q) => $q->where('department', $request->department))
            ->paginate(25)
            ->withQueryString();

        $branches = Branch::where('tenant_id', $tenantId)->get();
        $teams    = Team::where('tenant_id', $tenantId)->get();
        $managers = User::where('tenant_id', $tenantId)->get();

        return view('kpi.setup.staff', compact('profiles', 'branches', 'teams', 'managers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'         => 'required|exists:users,id|unique:staff_profiles,user_id',
            'branch_id'       => 'nullable|exists:branches,id',
            'manager_id'      => 'nullable|exists:users,id',
            'team_id'         => 'nullable|exists:teams,id',
            'department'      => 'nullable|string|max:50',
            'job_title'       => 'nullable|string|max:100',
            'staff_code'      => 'nullable|string|max:30',
            'joined_date'     => 'nullable|date',
            'employment_type' => 'required|in:full_time,contract,intern',
        ]);

        StaffProfile::create([
            ...$data,
            'tenant_id'     => auth()->user()->tenant_id,
            'referral_code' => strtoupper(Str::random(8)),
        ]);

        return back()->with('success', 'Staff profile created.');
    }

    public function update(Request $request, StaffProfile $staffProfile)
    {
        $data = $request->validate([
            'branch_id'       => 'nullable|exists:branches,id',
            'manager_id'      => 'nullable|exists:users,id',
            'team_id'         => 'nullable|exists:teams,id',
            'department'      => 'nullable|string|max:50',
            'job_title'       => 'nullable|string|max:100',
            'staff_code'      => 'nullable|string|max:30',
            'joined_date'     => 'nullable|date',
            'employment_type' => 'required|in:full_time,contract,intern',
            'status'          => 'required|in:active,inactive,suspended',
        ]);

        $staffProfile->update($data);
        return back()->with('success', 'Staff profile updated.');
    }

    public function regenerateCode(StaffProfile $staffProfile)
    {
        $staffProfile->update(['referral_code' => strtoupper(Str::random(8))]);
        return back()->with('success', 'Referral code regenerated: ' . $staffProfile->referral_code);
    }
}
