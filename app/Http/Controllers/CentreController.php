<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\Branch;
use Illuminate\Http\Request;

class CentreController extends Controller
{
    public function index()
    {
        $centres = Centre::with(['branch', 'groups'])->orderBy('name')->paginate(20);
        return view('centres.index', compact('centres'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        return view('centres.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'code'             => 'nullable|string|max:50',
            'branch_id'        => 'nullable|exists:branches,id',
            'meeting_location' => 'nullable|string|max:255',
            'meeting_day'      => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'meeting_time'     => 'nullable|date_format:H:i',
            'status'           => 'required|in:active,inactive',
        ]);

        Centre::create($validated);

        return redirect()->route('centres.index')->with('success', 'Centre created successfully.');
    }

    public function show(Centre $centre)
    {
        $centre->load(['branch', 'groups.loanOfficer', 'groups.members']);
        return view('centres.show', compact('centre'));
    }

    public function edit(Centre $centre)
    {
        $branches = Branch::orderBy('name')->get();
        return view('centres.edit', compact('centre', 'branches'));
    }

    public function update(Request $request, Centre $centre)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'code'             => 'nullable|string|max:50',
            'branch_id'        => 'nullable|exists:branches,id',
            'meeting_location' => 'nullable|string|max:255',
            'meeting_day'      => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'meeting_time'     => 'nullable|date_format:H:i',
            'status'           => 'required|in:active,inactive',
        ]);

        $centre->update($validated);

        return redirect()->route('centres.index')->with('success', 'Centre updated successfully.');
    }

    public function destroy(Centre $centre)
    {
        if ($centre->groups()->exists()) {
            return back()->with('error', 'Cannot delete a centre that has groups assigned to it.');
        }

        $centre->delete();
        return redirect()->route('centres.index')->with('success', 'Centre deleted successfully.');
    }
}
