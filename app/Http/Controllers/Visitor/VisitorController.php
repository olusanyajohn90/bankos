<?php

namespace App\Http\Controllers\Visitor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Visitor\Visitor;
use App\Models\Visitor\VisitorVisit;
use App\Models\Visitor\VisitorMeeting;
use App\Models\Visitor\VisitorMeetingRoom;
use App\Models\Visitor\VisitorMeetingAttendee;
use App\Models\Visitor\VisitorActivity;
use App\Models\Visitor\VisitorWatchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VisitorController extends Controller
{
    private function tid(): string
    {
        return session('tenant_id') ?? auth()->user()->tenant_id;
    }

    // ── Dashboard ────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $tenantId = $this->tid();

        $stats = [
            'checked_in_now'  => VisitorVisit::where('tenant_id', $tenantId)->where('status', 'checked_in')->count(),
            'today_total'     => VisitorVisit::where('tenant_id', $tenantId)->whereDate('checked_in_at', today())->count(),
            'this_month'      => VisitorVisit::where('tenant_id', $tenantId)->whereMonth('checked_in_at', now()->month)->count(),
            'blacklisted'     => Visitor::where('tenant_id', $tenantId)->where('is_blacklisted', true)->count(),
            'expected_today'  => VisitorVisit::where('tenant_id', $tenantId)->where('status', 'expected')->whereDate('expected_at', today())->count(),
            'meetings_today'  => VisitorMeeting::where('tenant_id', $tenantId)->whereDate('scheduled_at', today())->count(),
        ];

        $currentVisitors = VisitorVisit::where('tenant_id', $tenantId)
            ->where('status', 'checked_in')
            ->with(['visitor', 'host'])
            ->orderByDesc('checked_in_at')
            ->get();

        $expectedToday = VisitorVisit::where('tenant_id', $tenantId)
            ->where('status', 'expected')
            ->whereDate('expected_at', today())
            ->with(['visitor', 'host'])
            ->orderBy('expected_at')
            ->get();

        $meetingsToday = VisitorMeeting::where('tenant_id', $tenantId)
            ->whereDate('scheduled_at', today())
            ->with(['organiser', 'room', 'attendees'])
            ->orderBy('scheduled_at')
            ->get();

        $recentVisits = VisitorVisit::where('tenant_id', $tenantId)
            ->whereDate('checked_in_at', today())
            ->with(['visitor', 'host'])
            ->orderByDesc('checked_in_at')
            ->take(10)
            ->get();

        $users  = User::where('tenant_id', $tenantId)->orderBy('name')->get();
        $rooms  = VisitorMeetingRoom::where('tenant_id', $tenantId)->get();

        // ── Enhanced: Visitor Trend (last 30 days) ──
        try {
            $visitorTrend = VisitorVisit::where('tenant_id', $tenantId)
                ->whereDate('checked_in_at', '>=', now()->subDays(30))
                ->select(\Illuminate\Support\Facades\DB::raw("DATE(checked_in_at) as date"), \Illuminate\Support\Facades\DB::raw("count(*) as total"))
                ->groupBy(\Illuminate\Support\Facades\DB::raw("DATE(checked_in_at)"))
                ->orderBy('date')
                ->get();

            // ── Enhanced: Visit Purpose Breakdown ──
            $purposeBreakdown = VisitorVisit::where('tenant_id', $tenantId)
                ->whereNotNull('purpose')
                ->whereDate('checked_in_at', '>=', now()->subDays(30))
                ->select('purpose', \Illuminate\Support\Facades\DB::raw("count(*) as total"))
                ->groupBy('purpose')
                ->orderByDesc('total')
                ->limit(10)
                ->pluck('total', 'purpose');

            // ── Enhanced: Peak Visiting Hours ──
            $peakHours = VisitorVisit::where('tenant_id', $tenantId)
                ->whereNotNull('checked_in_at')
                ->whereDate('checked_in_at', '>=', now()->subDays(30))
                ->select(\Illuminate\Support\Facades\DB::raw("EXTRACT(HOUR FROM checked_in_at) as hour"), \Illuminate\Support\Facades\DB::raw("count(*) as total"))
                ->groupBy(\Illuminate\Support\Facades\DB::raw("EXTRACT(HOUR FROM checked_in_at)"))
                ->orderBy('hour')
                ->pluck('total', 'hour');

            // ── Enhanced: Repeat Visitors ──
            $repeatVisitors = Visitor::where('tenant_id', $tenantId)
                ->whereHas('visits', function ($q) {
                    $q->havingRaw('count(*) > 1');
                }, '>=', 2)
                ->count();

            // Simple fallback for repeat visitor count
            $repeatVisitors = \Illuminate\Support\Facades\DB::table('visitor_visits')
                ->where('tenant_id', $tenantId)
                ->select('visitor_id', \Illuminate\Support\Facades\DB::raw('count(*) as visit_count'))
                ->groupBy('visitor_id')
                ->havingRaw('count(*) > 1')
                ->get()
                ->count();

            // ── Enhanced: Watchlist Alerts ──
            $watchlistAlerts = VisitorWatchlist::where('tenant_id', $tenantId)
                ->where('status', 'blacklisted')
                ->count();

            $vipCount = VisitorWatchlist::where('tenant_id', $tenantId)
                ->where('status', 'vip')
                ->count();

        } catch (\Exception $e) {
            $visitorTrend = collect();
            $purposeBreakdown = collect();
            $peakHours = collect();
            $repeatVisitors = 0;
            $watchlistAlerts = 0;
            $vipCount = 0;
        }

        return view('visitor.dashboard', compact(
            'stats', 'currentVisitors', 'expectedToday', 'meetingsToday', 'recentVisits', 'users', 'rooms',
            'visitorTrend', 'purposeBreakdown', 'peakHours', 'repeatVisitors', 'watchlistAlerts', 'vipCount'
        ));
    }

    // ── Visitor Registry ─────────────────────────────────────────────────────

    public function visitors(Request $request)
    {
        $tenantId = $this->tid();
        $query = Visitor::where('tenant_id', $tenantId)->withCount('visits');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($s) => $s->where('full_name','like',"%$q%")->orWhere('phone','like',"%$q%")->orWhere('id_number','like',"%$q%")->orWhere('company','like',"%$q%"));
        }
        if ($request->filter === 'blacklisted') $query->where('is_blacklisted', true);
        if ($request->filter === 'vip') $query->whereHas('watchlist', fn($q) => $q->where('status','vip'));

        $visitors = $query->orderBy('full_name')->paginate(25)->withQueryString();

        return view('visitor.visitors', compact('visitors'));
    }

    public function visitorStore(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:200',
            'phone'     => 'nullable|string|max:20',
        ]);

        $visitor = Visitor::create([
            'id'        => Str::uuid(),
            'tenant_id' => $this->tid(),
            'full_name' => $request->full_name,
            'id_type'   => $request->id_type,
            'id_number' => $request->id_number,
            'phone'     => $request->phone,
            'email'     => $request->email,
            'company'   => $request->company,
            'notes'     => $request->notes,
        ]);

        if ($request->ajax()) {
            return response()->json(['id' => $visitor->id, 'name' => $visitor->full_name]);
        }

        return back()->with('success', 'Visitor registered.');
    }

    public function visitorBlacklist(Request $request, Visitor $visitor)
    {
        abort_unless($visitor->tenant_id === $this->tid(), 403);
        $request->validate(['reason' => 'required|string|max:500']);

        $visitor->update(['is_blacklisted' => true, 'blacklist_reason' => $request->reason]);

        VisitorWatchlist::create([
            'id'         => Str::uuid(),
            'tenant_id'  => $this->tid(),
            'visitor_id' => $visitor->id,
            'status'     => 'blacklisted',
            'reason'     => $request->reason,
            'added_by'   => auth()->id(),
        ]);

        return back()->with('success', 'Visitor blacklisted.');
    }

    // ── Visit Log ────────────────────────────────────────────────────────────

    public function visits(Request $request)
    {
        $tenantId = $this->tid();
        $query = VisitorVisit::where('tenant_id', $tenantId)
            ->with(['visitor', 'host', 'checkedInBy']);

        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('date_from')) $query->whereDate('checked_in_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('checked_in_at', '<=', $request->date_to);
        if ($request->filled('search')) {
            $q = $request->search;
            $query->whereHas('visitor', fn($s) => $s->where('full_name','like',"%$q%"));
        }

        $visits = $query->orderByDesc('checked_in_at')->paginate(25)->withQueryString();
        $users  = User::where('tenant_id', $tenantId)->orderBy('name')->get();

        return view('visitor.visits', compact('visits', 'users'));
    }

    // ── Check In ─────────────────────────────────────────────────────────────

    public function checkIn(Request $request)
    {
        $request->validate([
            'visitor_id'   => 'required|exists:visitors,id',
            'host_user_id' => 'required|exists:users,id',
            'purpose'      => 'required|string|max:200',
        ]);

        $tenantId = $this->tid();
        $visitor  = Visitor::findOrFail($request->visitor_id);

        if ($visitor->is_blacklisted) {
            return back()->with('error', "Visitor is BLACKLISTED. Entry denied. Reason: {$visitor->blacklist_reason}");
        }

        $visit = VisitorVisit::create([
            'id'             => Str::uuid(),
            'tenant_id'      => $tenantId,
            'visitor_id'     => $visitor->id,
            'host_user_id'   => $request->host_user_id,
            'purpose'        => $request->purpose,
            'badge_number'   => $request->badge_number,
            'vehicle_plate'  => $request->vehicle_plate,
            'items_brought'  => $request->items_brought,
            'status'         => 'checked_in',
            'checked_in_at'  => now(),
            'checked_in_by'  => auth()->id(),
            'notes'          => $request->notes,
        ]);

        return back()->with('success', "Visitor {$visitor->full_name} checked in. Badge: {$visit->badge_number}");
    }

    // ── Pre-register (Expected visitor) ──────────────────────────────────────

    public function preRegister(Request $request)
    {
        $request->validate([
            'visitor_id'   => 'required|exists:visitors,id',
            'host_user_id' => 'required|exists:users,id',
            'purpose'      => 'required|string|max:200',
            'expected_at'  => 'required|date',
        ]);

        VisitorVisit::create([
            'id'           => Str::uuid(),
            'tenant_id'    => $this->tid(),
            'visitor_id'   => $request->visitor_id,
            'host_user_id' => $request->host_user_id,
            'purpose'      => $request->purpose,
            'status'       => 'expected',
            'expected_at'  => $request->expected_at,
            'notes'        => $request->notes,
        ]);

        return back()->with('success', 'Visitor pre-registered successfully.');
    }

    // ── Check Out ────────────────────────────────────────────────────────────

    public function checkOut(VisitorVisit $visit)
    {
        abort_unless($visit->tenant_id === $this->tid(), 403);

        $visit->update([
            'status'         => 'checked_out',
            'checked_out_at' => now(),
            'checked_out_by' => auth()->id(),
        ]);

        return back()->with('success', "Visitor {$visit->visitor->full_name} checked out. Duration: {$visit->duration()}");
    }

    // ── Deny entry ───────────────────────────────────────────────────────────

    public function denyEntry(Request $request, VisitorVisit $visit)
    {
        abort_unless($visit->tenant_id === $this->tid(), 403);
        $request->validate(['reason' => 'required|string|max:500']);

        $visit->update([
            'status'        => 'denied',
            'denial_reason' => $request->reason,
        ]);

        return back()->with('success', 'Entry denied.');
    }

    // ── Visit Detail ─────────────────────────────────────────────────────────

    public function visitShow(VisitorVisit $visit)
    {
        abort_unless($visit->tenant_id === $this->tid(), 403);
        $visit->load(['visitor', 'host', 'checkedInBy', 'checkedOutBy', 'activities.loggedBy']);

        $users = User::where('tenant_id', $this->tid())->orderBy('name')->get();

        return view('visitor.visit-show', compact('visit', 'users'));
    }

    // ── Log activity on a visit ───────────────────────────────────────────────

    public function logActivity(Request $request, VisitorVisit $visit)
    {
        abort_unless($visit->tenant_id === $this->tid(), 403);
        $request->validate(['description' => 'required|string|max:1000', 'activity_type' => 'required|string']);

        VisitorActivity::create([
            'id'            => Str::uuid(),
            'visit_id'      => $visit->id,
            'logged_by'     => auth()->id(),
            'activity_type' => $request->activity_type,
            'description'   => $request->description,
            'area_accessed' => $request->area_accessed,
            'occurred_at'   => now(),
        ]);

        return back()->with('success', 'Activity logged.');
    }

    // ── Meetings ─────────────────────────────────────────────────────────────

    public function meetings(Request $request)
    {
        $tenantId = $this->tid();
        $query = VisitorMeeting::where('tenant_id', $tenantId)
            ->with(['organiser', 'room', 'attendees.visitor', 'attendees.user']);

        if ($request->filled('date')) $query->whereDate('scheduled_at', $request->date);
        if ($request->filled('status')) $query->where('status', $request->status);

        $meetings       = $query->orderBy('scheduled_at', 'desc')->paginate(20)->withQueryString();
        $rooms          = VisitorMeetingRoom::where('tenant_id', $tenantId)->get();
        $users          = User::where('tenant_id', $tenantId)->orderBy('name')->get();
        $recentVisitors = Visitor::where('tenant_id', $tenantId)->orderBy('full_name')->get();

        return view('visitor.meetings', compact('meetings', 'rooms', 'users', 'recentVisitors'));
    }

    public function meetingStore(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:200',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer|min:15',
        ]);

        $meeting = VisitorMeeting::create([
            'id'               => Str::uuid(),
            'tenant_id'        => $this->tid(),
            'organiser_id'     => auth()->id(),
            'room_id'          => $request->room_id,
            'title'            => $request->title,
            'agenda'           => $request->agenda,
            'status'           => 'scheduled',
            'scheduled_at'     => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes,
        ]);

        // Add attendees
        if ($request->filled('visitor_ids')) {
            foreach (explode(',', $request->visitor_ids) as $vid) {
                if (trim($vid)) {
                    VisitorMeetingAttendee::create(['meeting_id' => $meeting->id, 'visitor_id' => trim($vid), 'type' => 'visitor', 'attendance_status' => 'invited']);
                }
            }
        }
        if ($request->filled('staff_ids')) {
            foreach ($request->staff_ids as $uid) {
                VisitorMeetingAttendee::create(['meeting_id' => $meeting->id, 'user_id' => $uid, 'type' => 'staff', 'attendance_status' => 'invited']);
            }
        }

        return back()->with('success', 'Meeting scheduled.');
    }

    public function meetingUpdateStatus(Request $request, VisitorMeeting $meeting)
    {
        abort_unless($meeting->tenant_id === $this->tid(), 403);
        $request->validate(['status' => 'required|in:in_progress,completed,cancelled']);

        $updates = ['status' => $request->status];
        if ($request->status === 'in_progress') $updates['started_at'] = now();
        if ($request->status === 'completed') {
            $updates['ended_at'] = now();
            if ($request->filled('minutes')) $updates['minutes'] = $request->minutes;
        }

        $meeting->update($updates);
        return back()->with('success', 'Meeting updated.');
    }

    // ── Meeting Rooms ────────────────────────────────────────────────────────

    public function rooms(Request $request)
    {
        $tenantId = $this->tid();
        $rooms    = VisitorMeetingRoom::where('tenant_id', $tenantId)->withCount('meetings')->get();
        return view('visitor.rooms', compact('rooms'));
    }

    public function roomStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:200']);
        VisitorMeetingRoom::create([
            'id'          => Str::uuid(),
            'tenant_id'   => $this->tid(),
            'name'        => $request->name,
            'location'    => $request->location,
            'capacity'    => $request->capacity ?? 2,
            'is_available' => true,
        ]);
        return back()->with('success', 'Room created.');
    }

    public function roomToggle(VisitorMeetingRoom $room)
    {
        abort_unless($room->tenant_id === $this->tid(), 403);
        $room->update(['is_available' => ! $room->is_available]);
        return back()->with('success', 'Room ' . ($room->is_available ? 'available' : 'unavailable') . '.');
    }

    // ── Watchlist ────────────────────────────────────────────────────────────

    public function watchlist(Request $request)
    {
        $tenantId  = $this->tid();
        $watchlist = VisitorWatchlist::where('tenant_id', $tenantId)
            ->with(['visitor', 'addedBy'])
            ->orderByDesc('created_at')
            ->paginate(25);
        $visitors  = Visitor::where('tenant_id', $tenantId)->orderBy('full_name')->get();

        return view('visitor.watchlist', compact('watchlist', 'visitors'));
    }

    public function watchlistStore(Request $request)
    {
        $request->validate([
            'visitor_id' => 'required|exists:visitors,id',
            'status'     => 'required|in:blacklisted,vip,pre_approved',
            'reason'     => 'required|string|max:500',
        ]);

        $tenantId = $this->tid();

        VisitorWatchlist::create([
            'id'         => Str::uuid(),
            'tenant_id'  => $tenantId,
            'visitor_id' => $request->visitor_id,
            'status'     => $request->status,
            'reason'     => $request->reason,
            'added_by'   => auth()->id(),
            'expires_at' => $request->expires_at,
        ]);

        if ($request->status === 'blacklisted') {
            Visitor::where('id', $request->visitor_id)->update([
                'is_blacklisted'   => true,
                'blacklist_reason' => $request->reason,
            ]);
        }

        return back()->with('success', 'Added to watchlist.');
    }
}
