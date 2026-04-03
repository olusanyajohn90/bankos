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

    // ── Payroll Dashboard ──────────────────────────────────────────────────
    public function payrollDashboard()
    {
        $tenantId = session('tenant_id');
        $month    = now()->month;
        $year     = now()->year;

        try {
            // Total payroll this month
            $latestRun = PayrollRun::where('tenant_id', $tenantId)
                ->whereMonth('pay_period_start', $month)
                ->whereYear('pay_period_start', $year)
                ->orderByDesc('created_at')
                ->first();

            $totalPayrollAmount = DB::table('payslips')
                ->where('tenant_id', $tenantId)
                ->when($latestRun, fn($q) => $q->where('payroll_run_id', $latestRun->id))
                ->sum('net_pay');

            $totalGross = DB::table('payslips')
                ->where('tenant_id', $tenantId)
                ->when($latestRun, fn($q) => $q->where('payroll_run_id', $latestRun->id))
                ->sum('gross_pay');

            $totalDeductions = DB::table('payslips')
                ->where('tenant_id', $tenantId)
                ->when($latestRun, fn($q) => $q->where('payroll_run_id', $latestRun->id))
                ->sum('total_deductions');

            $staffCount = StaffProfile::where('tenant_id', $tenantId)->where('status', 'active')->count();

            // Staff by pay grade
            $byPayGrade = StaffProfile::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->select('pay_grade', DB::raw('count(*) as total'))
                ->groupBy('pay_grade')
                ->pluck('total', 'pay_grade');

            // Deductions breakdown
            $payeTotal = DB::table('payslips')
                ->where('tenant_id', $tenantId)
                ->when($latestRun, fn($q) => $q->where('payroll_run_id', $latestRun->id))
                ->sum('paye');

            $pensionTotal = DB::table('payslips')
                ->where('tenant_id', $tenantId)
                ->when($latestRun, fn($q) => $q->where('payroll_run_id', $latestRun->id))
                ->sum('pension');

            $nhfTotal = DB::table('payslips')
                ->where('tenant_id', $tenantId)
                ->when($latestRun, fn($q) => $q->where('payroll_run_id', $latestRun->id))
                ->sum('nhf');

            // Payroll trend (last 6 months)
            $payrollTrend = DB::table('payroll_runs')
                ->where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->orderByDesc('pay_period_start')
                ->limit(6)
                ->get(['pay_period_start', 'total_gross', 'total_net', 'total_deductions', 'headcount']);

            // Top earners
            $topEarners = DB::table('payslips')
                ->where('payslips.tenant_id', $tenantId)
                ->when($latestRun, fn($q) => $q->where('payroll_run_id', $latestRun->id))
                ->join('staff_profiles', 'payslips.staff_profile_id', '=', 'staff_profiles.id')
                ->join('users', 'staff_profiles.user_id', '=', 'users.id')
                ->orderByDesc('payslips.gross_pay')
                ->limit(10)
                ->select('users.name', 'staff_profiles.department_id', 'payslips.gross_pay', 'payslips.net_pay')
                ->get();

            // Pending salary advances
            $pendingAdvances = SalaryAdvance::where('tenant_id', $tenantId)->where('status', 'pending')->count();

            $advancesAmount = SalaryAdvance::where('tenant_id', $tenantId)->where('status', 'pending')->sum('amount');

            // Payroll run status
            $recentRuns = PayrollRun::where('tenant_id', $tenantId)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

        } catch (\Exception $e) {
            $latestRun = null;
            $totalPayrollAmount = $totalGross = $totalDeductions = $staffCount = 0;
            $byPayGrade = collect();
            $payeTotal = $pensionTotal = $nhfTotal = 0;
            $payrollTrend = collect();
            $topEarners = collect();
            $pendingAdvances = $advancesAmount = 0;
            $recentRuns = collect();
        }

        return view('hr.payroll-dashboard', compact(
            'latestRun', 'totalPayrollAmount', 'totalGross', 'totalDeductions', 'staffCount',
            'byPayGrade', 'payeTotal', 'pensionTotal', 'nhfTotal',
            'payrollTrend', 'topEarners', 'pendingAdvances', 'advancesAmount', 'recentRuns'
        ));
    }

    // ── Leave Dashboard ────────────────────────────────────────────────────
    public function leaveDashboard()
    {
        $tenantId = session('tenant_id');
        $today    = now()->toDateString();
        $month    = now()->month;
        $year     = now()->year;

        try {
            // Leave balance overview by type
            $leaveTypes = DB::table('leave_types')
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->get();

            $leaveBalances = [];
            foreach ($leaveTypes as $type) {
                $totalAllowance = DB::table('leave_balances')
                    ->where('tenant_id', $tenantId)
                    ->where('leave_type_id', $type->id)
                    ->sum('total_days');
                $usedDays = DB::table('leave_balances')
                    ->where('tenant_id', $tenantId)
                    ->where('leave_type_id', $type->id)
                    ->sum('used_days');
                $leaveBalances[] = [
                    'type' => $type->name,
                    'total' => $totalAllowance,
                    'used' => $usedDays,
                    'pct' => $totalAllowance > 0 ? round(($usedDays / $totalAllowance) * 100, 1) : 0,
                ];
            }

            // Staff on leave today
            $staffOnLeave = LeaveRequest::where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->with('staffProfile.user', 'leaveType')
                ->get();

            // Pending leave requests
            $pendingRequests = LeaveRequest::where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->with('staffProfile.user', 'leaveType')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            $pendingCount = LeaveRequest::where('tenant_id', $tenantId)->where('status', 'pending')->count();

            // Leave trend by month (last 6 months)
            $leaveTrend = DB::table('leave_requests')
                ->where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->whereDate('created_at', '>=', now()->subMonths(6))
                ->select(DB::raw("TO_CHAR(start_date, 'YYYY-MM') as month"), DB::raw("count(*) as total"))
                ->groupBy(DB::raw("TO_CHAR(start_date, 'YYYY-MM')"))
                ->orderBy('month')
                ->get();

            // Leave by type (for pie chart)
            $leaveByType = DB::table('leave_requests')
                ->where('leave_requests.tenant_id', $tenantId)
                ->where('leave_requests.status', 'approved')
                ->whereYear('leave_requests.start_date', $year)
                ->join('leave_types', 'leave_requests.leave_type_id', '=', 'leave_types.id')
                ->select('leave_types.name', DB::raw("count(*) as total"))
                ->groupBy('leave_types.name')
                ->pluck('total', 'name');

            // Top leave takers
            $topLeaveTakers = DB::table('leave_requests')
                ->where('leave_requests.tenant_id', $tenantId)
                ->where('leave_requests.status', 'approved')
                ->whereYear('leave_requests.start_date', $year)
                ->join('staff_profiles', 'leave_requests.staff_profile_id', '=', 'staff_profiles.id')
                ->join('users', 'staff_profiles.user_id', '=', 'users.id')
                ->select('users.name', DB::raw("SUM(leave_requests.days_count) as total_days"))
                ->groupBy('users.name')
                ->orderByDesc('total_days')
                ->limit(10)
                ->get();

        } catch (\Exception $e) {
            $leaveBalances = [];
            $staffOnLeave = collect();
            $pendingRequests = collect();
            $pendingCount = 0;
            $leaveTrend = collect();
            $leaveByType = collect();
            $topLeaveTakers = collect();
        }

        return view('hr.leave-dashboard', compact(
            'leaveBalances', 'staffOnLeave', 'pendingRequests', 'pendingCount',
            'leaveTrend', 'leaveByType', 'topLeaveTakers'
        ));
    }

    // ── Staff Dashboard ────────────────────────────────────────────────────
    public function staffDashboard()
    {
        $tenantId = session('tenant_id');
        $month    = now()->month;
        $year     = now()->year;

        try {
            $totalEmployees = StaffProfile::where('tenant_id', $tenantId)->where('status', 'active')->count();

            // By department
            $byDepartment = DB::table('staff_profiles')
                ->where('staff_profiles.tenant_id', $tenantId)
                ->where('staff_profiles.status', 'active')
                ->leftJoin('departments', 'staff_profiles.department_id', '=', 'departments.id')
                ->select(DB::raw("COALESCE(departments.name, 'Unassigned') as dept"), DB::raw("count(*) as total"))
                ->groupBy(DB::raw("COALESCE(departments.name, 'Unassigned')"))
                ->orderByDesc('total')
                ->get();

            // By gender
            $byGender = StaffProfile::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->select('gender', DB::raw('count(*) as total'))
                ->groupBy('gender')
                ->pluck('total', 'gender');

            // By employment type
            $byEmploymentType = StaffProfile::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->select('employment_type', DB::raw('count(*) as total'))
                ->groupBy('employment_type')
                ->pluck('total', 'employment_type');

            // New hires this month & quarter
            $newHiresMonth = StaffProfile::where('tenant_id', $tenantId)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->count();

            $newHiresQuarter = StaffProfile::where('tenant_id', $tenantId)
                ->whereDate('created_at', '>=', now()->startOfQuarter())
                ->whereDate('created_at', '<=', now()->endOfQuarter())
                ->count();

            // Attrition rate (terminated this year / avg headcount)
            $terminatedThisYear = StaffProfile::where('tenant_id', $tenantId)
                ->where('status', 'terminated')
                ->whereYear('updated_at', $year)
                ->count();

            $attritionRate = $totalEmployees > 0
                ? round(($terminatedThisYear / $totalEmployees) * 100, 1)
                : 0;

            // Staff by branch
            $byBranch = DB::table('staff_profiles')
                ->where('staff_profiles.tenant_id', $tenantId)
                ->where('staff_profiles.status', 'active')
                ->leftJoin('branches', 'staff_profiles.branch_id', '=', 'branches.id')
                ->select(DB::raw("COALESCE(branches.name, 'Head Office') as branch"), DB::raw("count(*) as total"))
                ->groupBy(DB::raw("COALESCE(branches.name, 'Head Office')"))
                ->orderByDesc('total')
                ->get();

            // Tenure distribution
            $tenureDistribution = DB::table('staff_profiles')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->whereNotNull('hire_date')
                ->select(DB::raw("
                    CASE
                        WHEN hire_date >= NOW() - INTERVAL '1 year' THEN '< 1 Year'
                        WHEN hire_date >= NOW() - INTERVAL '3 years' THEN '1-3 Years'
                        WHEN hire_date >= NOW() - INTERVAL '5 years' THEN '3-5 Years'
                        ELSE '5+ Years'
                    END as bracket
                "), DB::raw("count(*) as total"))
                ->groupBy(DB::raw("
                    CASE
                        WHEN hire_date >= NOW() - INTERVAL '1 year' THEN '< 1 Year'
                        WHEN hire_date >= NOW() - INTERVAL '3 years' THEN '1-3 Years'
                        WHEN hire_date >= NOW() - INTERVAL '5 years' THEN '3-5 Years'
                        ELSE '5+ Years'
                    END
                "))
                ->pluck('total', 'bracket');

        } catch (\Exception $e) {
            $totalEmployees = 0;
            $byDepartment = collect();
            $byGender = collect();
            $byEmploymentType = collect();
            $newHiresMonth = $newHiresQuarter = 0;
            $attritionRate = 0;
            $terminatedThisYear = 0;
            $byBranch = collect();
            $tenureDistribution = collect();
        }

        return view('hr.staff-dashboard', compact(
            'totalEmployees', 'byDepartment', 'byGender', 'byEmploymentType',
            'newHiresMonth', 'newHiresQuarter', 'attritionRate', 'terminatedThisYear',
            'byBranch', 'tenureDistribution'
        ));
    }

    // ── Performance Dashboard ──────────────────────────────────────────────
    public function performanceDashboard()
    {
        $tenantId = session('tenant_id');

        try {
            // Current/latest review cycle
            $currentCycle = DB::table('review_cycles')
                ->where('tenant_id', $tenantId)
                ->orderByDesc('created_at')
                ->first();

            $cycleStatus = $currentCycle->status ?? 'none';

            // Reviews in current cycle
            $totalReviews = 0;
            $completedReviews = 0;
            $pendingReviews = 0;
            $avgRating = 0;
            $ratingsDistribution = collect();
            $topPerformers = collect();
            $performanceByDept = collect();

            if ($currentCycle) {
                $totalReviews = DB::table('performance_reviews')
                    ->where('tenant_id', $tenantId)
                    ->where('review_cycle_id', $currentCycle->id)
                    ->count();

                $completedReviews = DB::table('performance_reviews')
                    ->where('tenant_id', $tenantId)
                    ->where('review_cycle_id', $currentCycle->id)
                    ->where('status', 'completed')
                    ->count();

                $pendingReviews = DB::table('performance_reviews')
                    ->where('tenant_id', $tenantId)
                    ->where('review_cycle_id', $currentCycle->id)
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->count();

                $avgRating = DB::table('performance_reviews')
                    ->where('tenant_id', $tenantId)
                    ->where('review_cycle_id', $currentCycle->id)
                    ->where('status', 'completed')
                    ->avg('overall_rating');
                $avgRating = $avgRating ? round($avgRating, 1) : 0;

                // Ratings distribution (1-5)
                $ratingsDistribution = DB::table('performance_reviews')
                    ->where('tenant_id', $tenantId)
                    ->where('review_cycle_id', $currentCycle->id)
                    ->where('status', 'completed')
                    ->select(DB::raw("ROUND(overall_rating) as rating"), DB::raw("count(*) as total"))
                    ->groupBy(DB::raw("ROUND(overall_rating)"))
                    ->pluck('total', 'rating');

                // Top performers
                $topPerformers = DB::table('performance_reviews')
                    ->where('performance_reviews.tenant_id', $tenantId)
                    ->where('performance_reviews.review_cycle_id', $currentCycle->id)
                    ->where('performance_reviews.status', 'completed')
                    ->join('staff_profiles', 'performance_reviews.staff_profile_id', '=', 'staff_profiles.id')
                    ->join('users', 'staff_profiles.user_id', '=', 'users.id')
                    ->orderByDesc('performance_reviews.overall_rating')
                    ->limit(10)
                    ->select('users.name', 'performance_reviews.overall_rating')
                    ->get();

                // Performance by department
                $performanceByDept = DB::table('performance_reviews')
                    ->where('performance_reviews.tenant_id', $tenantId)
                    ->where('performance_reviews.review_cycle_id', $currentCycle->id)
                    ->where('performance_reviews.status', 'completed')
                    ->join('staff_profiles', 'performance_reviews.staff_profile_id', '=', 'staff_profiles.id')
                    ->leftJoin('departments', 'staff_profiles.department_id', '=', 'departments.id')
                    ->select(
                        DB::raw("COALESCE(departments.name, 'Unassigned') as dept"),
                        DB::raw("ROUND(AVG(performance_reviews.overall_rating)::numeric, 1) as avg_rating"),
                        DB::raw("count(*) as total")
                    )
                    ->groupBy(DB::raw("COALESCE(departments.name, 'Unassigned')"))
                    ->orderByDesc('avg_rating')
                    ->get();
            }

            // All review cycles
            $reviewCycles = DB::table('review_cycles')
                ->where('tenant_id', $tenantId)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

        } catch (\Exception $e) {
            $currentCycle = null;
            $cycleStatus = 'none';
            $totalReviews = $completedReviews = $pendingReviews = 0;
            $avgRating = 0;
            $ratingsDistribution = collect();
            $topPerformers = collect();
            $performanceByDept = collect();
            $reviewCycles = collect();
        }

        return view('hr.performance-dashboard', compact(
            'currentCycle', 'cycleStatus', 'totalReviews', 'completedReviews', 'pendingReviews',
            'avgRating', 'ratingsDistribution', 'topPerformers', 'performanceByDept', 'reviewCycles'
        ));
    }
}
