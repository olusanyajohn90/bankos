<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\AttendancePolicy;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\StaffProfile;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // ── Dashboard / Monthly View ──────────────────────────────────────────────

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $month    = $request->get('month', now()->format('Y-m'));
        $branchId = $request->get('branch_id');

        [$year, $mon] = explode('-', $month);
        $startDate = "{$year}-{$mon}-01";
        $endDate   = date('Y-m-t', strtotime($startDate));

        $staffQuery = StaffProfile::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with(['user', 'branch']);

        if ($branchId) $staffQuery->where('branch_id', $branchId);

        $staff    = $staffQuery->get();
        $profiles = $staff->pluck('id')->toArray();

        $records = AttendanceRecord::where('tenant_id', $tenantId)
            ->whereIn('staff_profile_id', $profiles)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('staff_profile_id');

        // Summary stats
        $summary = [];
        foreach ($staff as $profile) {
            $profileRecords = $records->get($profile->id, collect());
            $summary[$profile->id] = [
                'profile'     => $profile,
                'present'     => $profileRecords->whereIn('status', ['present','late'])->count(),
                'late'        => $profileRecords->where('status', 'late')->count(),
                'absent'      => $profileRecords->where('status', 'absent')->count(),
                'on_leave'    => $profileRecords->where('status', 'on_leave')->count(),
                'total_hours' => round($profileRecords->sum('hours_worked'), 1),
            ];
        }

        $policies = AttendancePolicy::where('tenant_id', $tenantId)->get();
        $branches = \App\Models\Branch::where('tenant_id', $tenantId)->get();

        return view('hr.attendance.index', compact(
            'summary', 'month', 'year', 'mon', 'startDate', 'endDate',
            'policies', 'branches', 'branchId'
        ));
    }

    // ── Staff Attendance Detail ───────────────────────────────────────────────

    public function staffDetail(Request $request, StaffProfile $staffProfile)
    {
        abort_unless($staffProfile->tenant_id === auth()->user()->tenant_id, 403);

        $month = $request->get('month', now()->format('Y-m'));
        [$year, $mon] = explode('-', $month);
        $startDate = "{$year}-{$mon}-01";
        $endDate   = date('Y-m-t', strtotime($startDate));

        $records = AttendanceRecord::where('staff_profile_id', $staffProfile->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        $staffProfile->load(['user', 'branch', 'orgDepartment']);

        return view('hr.attendance.staff-detail', compact('staffProfile', 'records', 'month', 'startDate', 'endDate'));
    }

    // ── Mark Attendance ───────────────────────────────────────────────────────

    public function markAttendance(Request $request)
    {
        $request->validate([
            'staff_profile_id' => 'required|uuid|exists:staff_profiles,id',
            'date'             => 'required|date',
            'clock_in'         => 'nullable|date_format:H:i',
            'clock_out'        => 'nullable|date_format:H:i',
            'status'           => 'required|in:present,absent,late,half_day,excused,on_leave,public_holiday',
            'notes'            => 'nullable|string|max:500',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $profile  = StaffProfile::where('id', $request->staff_profile_id)->where('tenant_id', $tenantId)->firstOrFail();

        $policy = AttendancePolicy::where('tenant_id', $tenantId)->where('is_default', true)->first();
        $minutesLate = 0;
        $hoursWorked = null;

        if ($request->clock_in && $policy) {
            $expectedIn = strtotime($request->date . ' ' . $policy->work_start_time);
            $actualIn   = strtotime($request->date . ' ' . $request->clock_in . ':00');
            $graceSecs  = $policy->grace_minutes * 60;
            if ($actualIn > $expectedIn + $graceSecs) {
                $minutesLate = (int) (($actualIn - $expectedIn) / 60);
            }
        }

        if ($request->clock_in && $request->clock_out) {
            $in  = strtotime($request->date . ' ' . $request->clock_in . ':00');
            $out = strtotime($request->date . ' ' . $request->clock_out . ':00');
            $hoursWorked = round(($out - $in) / 3600, 2);
        }

        AttendanceRecord::updateOrCreate(
            ['staff_profile_id' => $profile->id, 'date' => $request->date],
            [
                'tenant_id'            => $tenantId,
                'clock_in'             => $request->clock_in ? $request->clock_in . ':00' : null,
                'clock_out'            => $request->clock_out ? $request->clock_out . ':00' : null,
                'expected_in'          => $policy?->work_start_time,
                'expected_out'         => $policy?->work_end_time,
                'status'               => $request->status,
                'minutes_late'         => $minutesLate,
                'hours_worked'         => $hoursWorked,
                'is_manually_adjusted' => true,
                'notes'                => $request->notes,
                'marked_by'            => auth()->id(),
            ]
        );

        return back()->with('success', 'Attendance recorded.');
    }

    // ── Bulk Mark for a Day ───────────────────────────────────────────────────

    public function bulkMark(Request $request)
    {
        $request->validate([
            'date'     => 'required|date',
            'statuses' => 'required|array',
            'statuses.*' => 'in:present,absent,late,half_day,excused,on_leave',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $policy   = AttendancePolicy::where('tenant_id', $tenantId)->where('is_default', true)->first();

        $updated = 0;
        foreach ($request->statuses as $profileId => $status) {
            $profile = StaffProfile::where('id', $profileId)->where('tenant_id', $tenantId)->first();
            if (!$profile) continue;

            AttendanceRecord::updateOrCreate(
                ['staff_profile_id' => $profileId, 'date' => $request->date],
                [
                    'tenant_id'    => $tenantId,
                    'expected_in'  => $policy?->work_start_time,
                    'expected_out' => $policy?->work_end_time,
                    'status'       => $status,
                    'marked_by'    => auth()->id(),
                ]
            );
            $updated++;
        }

        return back()->with('success', "{$updated} attendance records saved.");
    }

    // ── Policy CRUD ───────────────────────────────────────────────────────────

    public function policies(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $policies = AttendancePolicy::where('tenant_id', $tenantId)->get();
        return view('hr.attendance.policies', compact('policies'));
    }

    public function storePolicy(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:100',
            'work_start_time' => 'required|date_format:H:i',
            'work_end_time'   => 'required|date_format:H:i',
            'grace_minutes'   => 'required|integer|min:0|max:60',
            'daily_work_hours'=> 'required|numeric|min:1|max:24',
        ]);

        $tenantId = auth()->user()->tenant_id;

        if ($request->boolean('is_default')) {
            AttendancePolicy::where('tenant_id', $tenantId)->update(['is_default' => false]);
        }

        AttendancePolicy::create(array_merge(
            $request->only(['name','work_start_time','work_end_time','grace_minutes','daily_work_hours','half_day_hours','allow_overtime']),
            [
                'tenant_id'    => $tenantId,
                'is_default'   => $request->boolean('is_default'),
                'working_days' => [1, 2, 3, 4, 5],
            ]
        ));

        return back()->with('success', 'Policy created.');
    }

    // ── Export monthly attendance ─────────────────────────────────────────────

    public function exportMonthly(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $month    = $request->get('month', now()->format('Y-m'));
        [$year, $mon] = explode('-', $month);
        $startDate = "{$year}-{$mon}-01";
        $endDate   = date('Y-m-t', strtotime($startDate));

        $records = AttendanceRecord::where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['staffProfile.user', 'staffProfile.branch'])
            ->orderBy('date')
            ->get();

        $filename = "attendance-{$month}.csv";
        $headers  = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];

        $callback = function () use ($records) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Staff Name', 'Staff Code', 'Branch', 'Date', 'Clock In', 'Clock Out', 'Status', 'Hours Worked', 'Minutes Late']);
            foreach ($records as $r) {
                fputcsv($out, [
                    $r->staffProfile?->user?->name,
                    $r->staffProfile?->staff_code,
                    $r->staffProfile?->branch?->name,
                    $r->date->format('d/m/Y'),
                    $r->clock_in ?? '—',
                    $r->clock_out ?? '—',
                    ucfirst(str_replace('_', ' ', $r->status)),
                    $r->hours_worked ?? 0,
                    $r->minutes_late,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
