<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function index(Group $group)
    {
        $meetings = $group->meetings()
            ->withCount(['attendances', 'attendances as present_count' => fn($q) => $q->where('present', true)])
            ->orderByDesc('meeting_date')
            ->paginate(20);

        return view('meetings.index', compact('group', 'meetings'));
    }

    public function create(Group $group)
    {
        $loanOfficers = User::role(['loan_officer', 'tenant_admin'])->orderBy('name')->get();
        return view('meetings.create', compact('group', 'loanOfficers'));
    }

    public function store(Request $request, Group $group)
    {
        $validated = $request->validate([
            'meeting_date' => 'required|date',
            'meeting_time' => 'nullable|date_format:H:i',
            'location'     => 'nullable|string|max:255',
            'conducted_by' => 'nullable|exists:users,id',
            'notes'        => 'nullable|string',
            'status'       => 'required|in:scheduled,completed,cancelled',
        ]);

        $meeting = $group->meetings()->create([
            'tenant_id' => auth()->user()->tenant_id,
            ...$validated,
        ]);

        // Pre-populate attendance register with active group members
        $group->activeMembers()->with('customer')->get()->each(function ($member) use ($meeting) {
            MeetingAttendance::create([
                'tenant_id'   => auth()->user()->tenant_id,
                'meeting_id'  => $meeting->id,
                'customer_id' => $member->customer_id,
                'present'     => false,
                'amount_paid' => 0,
            ]);
        });

        return redirect()->route('groups.meetings.show', [$group, $meeting])
            ->with('success', 'Meeting created. Record attendance below.');
    }

    public function show(Group $group, Meeting $meeting)
    {
        $meeting->load(['group', 'conductedBy', 'attendances.customer']);
        return view('meetings.show', compact('group', 'meeting'));
    }

    public function updateAttendance(Request $request, Group $group, Meeting $meeting)
    {
        $validated = $request->validate([
            'attendance'                => 'required|array',
            'attendance.*.customer_id'  => 'required|exists:customers,id',
            'attendance.*.present'      => 'boolean',
            'attendance.*.amount_paid'  => 'nullable|numeric|min:0',
            'attendance.*.notes'        => 'nullable|string|max:255',
        ]);

        $totalCollected = 0;

        foreach ($validated['attendance'] as $record) {
            $present     = isset($record['present']) && $record['present'];
            $amountPaid  = (float) ($record['amount_paid'] ?? 0);
            $totalCollected += $amountPaid;

            MeetingAttendance::updateOrCreate(
                [
                    'meeting_id'  => $meeting->id,
                    'customer_id' => $record['customer_id'],
                ],
                [
                    'tenant_id'   => auth()->user()->tenant_id,
                    'present'     => $present,
                    'amount_paid' => $amountPaid,
                    'notes'       => $record['notes'] ?? null,
                ]
            );
        }

        $meeting->update([
            'total_collected' => $totalCollected,
            'status'          => 'completed',
        ]);

        return back()->with('success', 'Attendance and collections saved.');
    }
}
