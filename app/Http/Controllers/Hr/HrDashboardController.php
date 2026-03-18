<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\LeaveRequest;
use App\Models\AttendanceRecord;
use App\Models\ExpenseClaim;
use App\Models\SalaryAdvance;
use App\Models\PayrollRun;
use App\Models\ApprovalRequest;
use App\Models\PublicHoliday;
use App\Models\Announcement;
use Illuminate\Support\Facades\DB;

class HrDashboardController extends Controller
{
    public function index()
    {
        $tenantId = session('tenant_id');
        $today    = now()->toDateString();
        $month    = now()->month;
        $year     = now()->year;

        // ── Headcount ──────────────────────────────────────────────────────
        $totalStaff    = StaffProfile::where('tenant_id', $tenantId)->where('status', 'active')->count();
        $newThisMonth  = StaffProfile::where('tenant_id', $tenantId)
            ->whereMonth('created_at', $month)->whereYear('created_at', $year)->count();

        $byEmploymentType = StaffProfile::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->select('employment_type', DB::raw('count(*) as total'))
            ->groupBy('employment_type')
            ->pluck('total', 'employment_type');

        // ── Attendance today ────────────────────────────────────────────────
        $presentToday = AttendanceRecord::where('tenant_id', $tenantId)
            ->where('date', $today)->where('status', 'present')->count();
        $lateToday    = AttendanceRecord::where('tenant_id', $tenantId)
            ->where('date', $today)->where('status', 'late')->count();
        $absentToday  = AttendanceRecord::where('tenant_id', $tenantId)
            ->where('date', $today)->where('status', 'absent')->count();
        $onLeaveToday = AttendanceRecord::where('tenant_id', $tenantId)
            ->where('date', $today)->where('status', 'on_leave')->count();

        // ── Leave ───────────────────────────────────────────────────────────
        $pendingLeave   = LeaveRequest::where('tenant_id', $tenantId)->where('status', 'pending')->count();
        $onLeaveNow     = LeaveRequest::where('tenant_id', $tenantId)->where('status', 'approved')
            ->where('start_date', '<=', $today)->where('end_date', '>=', $today)->count();
        $leaveThisMonth = LeaveRequest::where('tenant_id', $tenantId)
            ->whereMonth('created_at', $month)->whereYear('created_at', $year)->count();

        // ── Payroll ─────────────────────────────────────────────────────────
        $latestPayroll = PayrollRun::where('tenant_id', $tenantId)
            ->orderByDesc('created_at')->first();

        // ── Expense Claims ──────────────────────────────────────────────────
        $pendingExpenses = ExpenseClaim::where('tenant_id', $tenantId)->where('status', 'submitted')->count();
        $expenseAmount   = ExpenseClaim::where('tenant_id', $tenantId)
            ->whereIn('status', ['submitted','approved'])->sum('amount');

        // ── Salary Advances ─────────────────────────────────────────────────
        $pendingAdvances = SalaryAdvance::where('tenant_id', $tenantId)->where('status', 'pending')->count();

        // ── Approval inbox ──────────────────────────────────────────────────
        $myPendingApprovals = ApprovalRequest::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->whereHas('steps', fn($q) => $q->whereColumn('step_number', 'approval_requests.current_step')->where('assigned_to', auth()->id()))
            ->count();

        // ── Upcoming holidays ───────────────────────────────────────────────
        $upcomingHolidays = PublicHoliday::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('date', '>=', $today)
            ->where('date', '<=', now()->addDays(30)->toDateString())
            ->orderBy('date')
            ->limit(5)
            ->get();

        // ── Recent announcements ────────────────────────────────────────────
        $recentAnnouncements = Announcement::where('tenant_id', $tenantId)
            ->published()->orderByDesc('is_pinned')->orderByDesc('created_at')
            ->limit(3)->get();

        // ── Recent leave requests ───────────────────────────────────────────
        $recentLeave = LeaveRequest::where('tenant_id', $tenantId)
            ->with('staffProfile.user', 'leaveType')
            ->whereIn('status', ['pending','approved'])
            ->orderByDesc('created_at')->limit(8)->get();

        return view('hr.dashboard', compact(
            'totalStaff','newThisMonth','byEmploymentType',
            'presentToday','lateToday','absentToday','onLeaveToday',
            'pendingLeave','onLeaveNow','leaveThisMonth',
            'latestPayroll',
            'pendingExpenses','expenseAmount',
            'pendingAdvances',
            'myPendingApprovals',
            'upcomingHolidays','recentAnnouncements','recentLeave'
        ));
    }
}
