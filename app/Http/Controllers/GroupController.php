<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Centre;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::with(['centre', 'branch', 'loanOfficer', 'members'])
            ->orderBy('name')
            ->paginate(20);
        return view('groups.index', compact('groups'));
    }

    public function create()
    {
        $centres = Centre::where('status', 'active')->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $loanOfficers = User::role(['loan_officer', 'tenant_admin'])->orderBy('name')->get();
        return view('groups.create', compact('centres', 'branches', 'loanOfficers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'code'                => 'nullable|string|max:50',
            'centre_id'           => 'nullable|exists:centres,id',
            'branch_id'           => 'nullable|exists:branches,id',
            'loan_officer_id'     => 'nullable|exists:users,id',
            'solidarity_guarantee'=> 'boolean',
            'status'              => 'required|in:active,inactive,dissolved',
            'notes'               => 'nullable|string',
        ]);

        $validated['solidarity_guarantee'] = $request->boolean('solidarity_guarantee');

        Group::create($validated);

        return redirect()->route('groups.index')->with('success', 'Group created successfully.');
    }

    public function show(Group $group)
    {
        $group->load([
            'centre', 'branch', 'loanOfficer',
            'members.customer',
            'meetings' => fn($q) => $q->orderByDesc('meeting_date')->limit(5),
            'loans' => fn($q) => $q->with('customer')->orderByDesc('created_at')->limit(10),
        ]);

        // Available customers to add (not already in this group)
        $existingIds = $group->members->pluck('customer_id')->toArray();
        $availableCustomers = Customer::whereNotIn('id', $existingIds)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        return view('groups.show', compact('group', 'availableCustomers'));
    }

    public function edit(Group $group)
    {
        $centres = Centre::where('status', 'active')->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $loanOfficers = User::role(['loan_officer', 'tenant_admin'])->orderBy('name')->get();
        return view('groups.edit', compact('group', 'centres', 'branches', 'loanOfficers'));
    }

    public function update(Request $request, Group $group)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'code'                => 'nullable|string|max:50',
            'centre_id'           => 'nullable|exists:centres,id',
            'branch_id'           => 'nullable|exists:branches,id',
            'loan_officer_id'     => 'nullable|exists:users,id',
            'solidarity_guarantee'=> 'boolean',
            'status'              => 'required|in:active,inactive,dissolved',
            'notes'               => 'nullable|string',
        ]);

        $validated['solidarity_guarantee'] = $request->boolean('solidarity_guarantee');

        $group->update($validated);

        return redirect()->route('groups.show', $group)->with('success', 'Group updated successfully.');
    }

    public function destroy(Group $group)
    {
        if ($group->loans()->whereIn('status', ['active', 'overdue'])->exists()) {
            return back()->with('error', 'Cannot delete a group with active or overdue loans.');
        }

        $group->delete();
        return redirect()->route('groups.index')->with('success', 'Group deleted successfully.');
    }

    // --- Member Management ---

    public function addMember(Request $request, Group $group)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'role'        => 'required|in:member,leader,treasurer',
            'joined_at'   => 'nullable|date',
        ]);

        // Prevent duplicates
        if ($group->members()->where('customer_id', $validated['customer_id'])->exists()) {
            return back()->with('error', 'This customer is already a member of the group.');
        }

        GroupMember::create([
            'tenant_id'   => auth()->user()->tenant_id,
            'group_id'    => $group->id,
            'customer_id' => $validated['customer_id'],
            'role'        => $validated['role'],
            'joined_at'   => $validated['joined_at'] ?? now(),
            'status'      => 'active',
        ]);

        return back()->with('success', 'Member added to group.');
    }

    public function removeMember(Group $group, GroupMember $member)
    {
        $member->update(['status' => 'exited']);
        return back()->with('success', 'Member marked as exited.');
    }
}
