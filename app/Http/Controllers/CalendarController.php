<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\CalendarEvent;
use App\Models\CalendarEventAttendee;
use App\Models\CalendarEventReminder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CalendarController extends Controller
{
    // Main page
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $calendars = Calendar::where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->where('owner_id', auth()->id())
                  ->orWhere('type', '!=', 'personal');
            })->get();

        $tenantUsers = User::where('tenant_id', $tenantId)
            ->where('id', '!=', auth()->id())
            ->select('id', 'name')->orderBy('name')->get();

        return view('calendar.index', compact('calendars', 'tenantUsers'));
    }

    // JSON: Get events for date range
    public function events(Request $request)
    {
        try {
        $tenantId = auth()->user()->tenant_id;
        $start = $request->input('start');
        $end = $request->input('end');

        $query = CalendarEvent::where('tenant_id', $tenantId)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, $end])
                  ->orWhereBetween('end_at', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_at', '<=', $start)
                          ->where('end_at', '>=', $end);
                  });
            })
            ->with(['attendees.user', 'createdBy', 'calendar'])
            ->get();

        // Also pull in chat tasks with due_date in range
        $tasks = \App\Models\ChatTask::where('tenant_id', $tenantId)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start, $end])
            ->with('assignedTo')
            ->get();

        // Pull in leave requests (if table exists)
        $leaves = collect();
        if (Schema::hasTable('leave_requests')) {
            $leaves = DB::table('leave_requests')
                ->join('users', 'leave_requests.user_id', '=', 'users.id')
                ->where('users.tenant_id', $tenantId)
                ->where('leave_requests.status', 'approved')
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_date', [$start, $end])
                      ->orWhereBetween('end_date', [$start, $end]);
                })
                ->select('leave_requests.*', 'users.name as user_name')
                ->get();
        }

        // Pull in public holidays
        $holidays = collect();
        if (Schema::hasTable('public_holidays')) {
            $holidays = DB::table('public_holidays')
                ->whereBetween('date', [$start, $end])
                ->get();
        }

        // Pull in loan maturities
        $loanMaturities = collect();
        $loans = \App\Models\Loan::where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'overdue'])
            ->whereNotNull('disbursed_at')
            ->with('customer')
            ->get();
        foreach ($loans as $loan) {
            $maturityDate = \Carbon\Carbon::parse($loan->disbursed_at)->addDays($loan->tenure_days);
            if ($maturityDate->between($start, $end)) {
                $loanMaturities->push($loan);
            }
        }

        // Format all events into FullCalendar format
        $events = collect();

        // Calendar events
        foreach ($query as $evt) {
            $events->push([
                'id' => $evt->id,
                'title' => $evt->title,
                'start' => $evt->start_at->toISOString(),
                'end' => $evt->end_at?->toISOString(),
                'allDay' => (bool) $evt->all_day,
                'color' => $evt->color ?? $evt->calendar?->color ?? '#3B82F6',
                'extendedProps' => [
                    'type' => $evt->type,
                    'source' => $evt->source,
                    'description' => $evt->description,
                    'location' => $evt->location,
                    'status' => $evt->status,
                    'created_by' => $evt->createdBy?->name,
                    'attendees' => $evt->attendees->map(fn ($a) => [
                        'user_id' => $a->user_id,
                        'name' => $a->user?->name,
                        'status' => $a->status,
                    ])->all(),
                    'calendar_name' => $evt->calendar?->name,
                    'is_recurring' => $evt->is_recurring,
                    'recurrence_rule' => $evt->recurrence_rule,
                    'event_id' => $evt->id,
                ],
            ]);
        }

        // Chat tasks
        foreach ($tasks as $task) {
            $events->push([
                'id' => 'task-' . $task->id,
                'title' => "\u{2705} " . $task->title,
                'start' => \Carbon\Carbon::parse($task->due_date)->toDateString(),
                'allDay' => true,
                'color' => $task->status === 'completed' ? '#10B981' : '#F59E0B',
                'extendedProps' => [
                    'type' => 'task',
                    'source' => 'chat_task',
                    'source_id' => $task->id,
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'assigned_to' => $task->assignedTo?->name,
                ],
            ]);
        }

        // Leaves
        foreach ($leaves as $leave) {
            $events->push([
                'id' => 'leave-' . $leave->id,
                'title' => "\u{1F3D6} " . ($leave->user_name ?? 'Staff') . ' - Leave',
                'start' => $leave->start_date,
                'end' => \Carbon\Carbon::parse($leave->end_date)->addDay()->format('Y-m-d'),
                'allDay' => true,
                'color' => '#10B981',
                'extendedProps' => ['type' => 'leave', 'source' => 'leave_request'],
            ]);
        }

        // Holidays
        foreach ($holidays as $hol) {
            $events->push([
                'id' => 'holiday-' . $hol->id,
                'title' => "\u{1F389} " . $hol->name,
                'start' => $hol->date,
                'allDay' => true,
                'color' => '#EF4444',
                'extendedProps' => ['type' => 'holiday', 'source' => 'holiday'],
            ]);
        }

        // Loan maturities
        foreach ($loanMaturities as $loan) {
            $matDate = \Carbon\Carbon::parse($loan->disbursed_at)->addDays($loan->tenure_days);
            $events->push([
                'id' => 'loan-' . $loan->id,
                'title' => "\u{1F4B0} Loan Maturity: " . ($loan->customer?->first_name ?? '') . ' ' . ($loan->customer?->last_name ?? '') . ' (' . "\u{20A6}" . number_format($loan->outstanding_balance, 0) . ')',
                'start' => $matDate->toISOString(),
                'allDay' => true,
                'color' => '#8B5CF6',
                'extendedProps' => ['type' => 'loan_maturity', 'source' => 'loan', 'source_id' => $loan->id],
            ]);
        }

        return response()->json($events->values());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Create event
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'all_day' => 'boolean',
            'type' => 'nullable|string|max:30',
            'description' => 'nullable|string|max:2000',
            'location' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'calendar_id' => 'nullable|uuid',
            'is_recurring' => 'boolean',
            'recurrence_rule' => 'nullable|string|max:255',
            'recurrence_end' => 'nullable|date',
            'attendee_ids' => 'nullable|array',
            'attendee_ids.*' => 'exists:users,id',
            'reminder_minutes' => 'nullable|array',
            'reminder_minutes.*' => 'integer|min:0',
        ]);

        $event = CalendarEvent::create([
            'tenant_id' => auth()->user()->tenant_id,
            'calendar_id' => $request->calendar_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->input('type', 'meeting'),
            'source' => 'manual',
            'color' => $request->color,
            'all_day' => $request->boolean('all_day'),
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'location' => $request->location,
            'is_recurring' => $request->boolean('is_recurring'),
            'recurrence_rule' => $request->recurrence_rule,
            'recurrence_end' => $request->recurrence_end,
            'created_by' => auth()->id(),
        ]);

        // Add attendees
        if ($request->attendee_ids) {
            foreach ($request->attendee_ids as $uid) {
                CalendarEventAttendee::create([
                    'event_id' => $event->id,
                    'user_id' => $uid,
                    'status' => 'pending',
                ]);
            }
        }

        // Add reminders
        if ($request->reminder_minutes) {
            foreach ($request->reminder_minutes as $mins) {
                CalendarEventReminder::create([
                    'event_id' => $event->id,
                    'minutes_before' => $mins,
                ]);
            }
        }

        return response()->json(['success' => true, 'event' => $event]);
    }

    // Update event
    public function update(Request $request, CalendarEvent $event)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'start_at' => 'sometimes|date',
            'end_at' => 'nullable|date',
            'all_day' => 'boolean',
            'description' => 'nullable|string|max:2000',
            'location' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'status' => 'nullable|string|in:confirmed,tentative,cancelled',
        ]);

        $event->update($request->only([
            'title', 'start_at', 'end_at', 'all_day', 'description',
            'location', 'color', 'status',
        ]));

        return response()->json(['success' => true]);
    }

    // Delete event
    public function destroy(CalendarEvent $event)
    {
        if ($event->source !== 'manual') {
            return response()->json(['error' => 'Cannot delete auto-generated events.'], 422);
        }
        $event->delete();
        return response()->json(['success' => true]);
    }

    // Respond to invitation
    public function respond(Request $request, CalendarEvent $event)
    {
        $request->validate(['status' => 'required|in:accepted,declined,tentative']);

        CalendarEventAttendee::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->update([
                'status' => $request->status,
                'responded_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    // List calendars
    public function calendars()
    {
        $tenantId = auth()->user()->tenant_id;
        $calendars = Calendar::where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->where('owner_id', auth()->id())
                  ->orWhere('type', '!=', 'personal');
            })->get();

        return response()->json(['calendars' => $calendars]);
    }

    // Create calendar
    public function createCalendar(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
            'type' => 'nullable|in:personal,shared',
        ]);

        $cal = Calendar::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $request->name,
            'color' => $request->input('color', '#3B82F6'),
            'type' => $request->input('type', 'personal'),
            'owner_id' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'calendar' => $cal]);
    }
}
