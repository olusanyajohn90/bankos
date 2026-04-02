<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Agent;
use App\Models\CalendarEvent;
use App\Models\ChatMessage;
use App\Models\Customer;
use App\Models\FixedAsset;
use App\Models\InsurancePolicy;
use App\Models\Loan;
use App\Models\MarketingCampaign;
use App\Models\PmProject;
use App\Models\PmTask;
use App\Models\Support\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        $today    = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $lastMonthStart = $today->copy()->subMonth()->startOfMonth();
        $lastMonthEnd   = $today->copy()->subMonth()->endOfMonth();

        // --- Row 1: Primary KPI Cards ---

        // Total Customers + growth %
        $totalCustomers  = Customer::where('tenant_id', $tenantId)->count();
        $customersLastMonth = Customer::where('tenant_id', $tenantId)
            ->where('created_at', '<', $monthStart)->count();
        $customersThisMonth = Customer::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $monthStart)->count();
        $customerGrowth = $customersLastMonth > 0
            ? round(($customersThisMonth / $customersLastMonth) * 100, 1) : 0;

        // Total Deposits + trend
        $totalDeposits   = Account::where('tenant_id', $tenantId)
                                  ->whereIn('type', ['savings', 'current', 'fixed'])
                                  ->sum('available_balance');
        $depositsLastMonth = Transaction::where('tenant_id', $tenantId)
            ->where('type', 'deposit')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');
        $depositsThisMonth = Transaction::where('tenant_id', $tenantId)
            ->where('type', 'deposit')
            ->where('created_at', '>=', $monthStart)
            ->sum('amount');
        $depositTrend = $depositsLastMonth > 0
            ? round((($depositsThisMonth - $depositsLastMonth) / $depositsLastMonth) * 100, 1) : 0;

        // Loan Portfolio + PAR %
        $loanPortfolio   = Loan::where('tenant_id', $tenantId)
                               ->whereIn('status', ['active', 'overdue', 'defaulted'])
                               ->sum('outstanding_balance');
        $parBalance = Loan::where('tenant_id', $tenantId)
                          ->whereIn('status', ['overdue', 'defaulted'])
                          ->sum('outstanding_balance');
        $par30 = $loanPortfolio > 0 ? round(($parBalance / $loanPortfolio) * 100, 1) : 0;

        // Monthly Revenue (fees + interest from transactions)
        $monthlyRevenue = Transaction::where('tenant_id', $tenantId)
            ->whereIn('type', ['fee', 'interest_charge', 'penalty'])
            ->where('created_at', '>=', $monthStart)
            ->sum('amount');
        $lastMonthRevenue = Transaction::where('tenant_id', $tenantId)
            ->whereIn('type', ['fee', 'interest_charge', 'penalty'])
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');
        $revenueTrend = $lastMonthRevenue > 0
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : 0;

        // Active Accounts
        $totalAccounts   = Account::where('tenant_id', $tenantId)
                                  ->where('status', 'active')
                                  ->count();

        // Pending Actions
        $pendingKyc      = Customer::where('tenant_id', $tenantId)
                                   ->where('kyc_status', 'manual_review')
                                   ->count();
        $pendingLoans    = Loan::where('tenant_id', $tenantId)
                               ->where('status', 'pending')
                               ->count();
        $pendingDisputes = DB::table('portal_disputes')
            ->where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->count();
        $pendingActions = $pendingKyc + $pendingLoans + $pendingDisputes;

        // --- Row 2: Charts ---

        // Deposit vs Loan Trend (last 6 months)
        $depositLoanTrend = $this->getDepositLoanTrend($tenantId);

        // Transaction Volume by Type (pie chart)
        $txnByType = $this->getTransactionsByType($tenantId, $monthStart, $today);

        // --- Row 3: Activity Feed + Quick Stats ---

        // Recent Transactions (last 10)
        $recentTransactions = Transaction::where('tenant_id', $tenantId)
            ->with('account.customer')
            ->latest()
            ->limit(10)
            ->get();

        // Quick stats
        $todayTxnCount  = Transaction::where('tenant_id', $tenantId)
                                     ->whereDate('created_at', $today)
                                     ->count();
        $todayTxnVolume = Transaction::where('tenant_id', $tenantId)
                                     ->whereDate('created_at', $today)
                                     ->sum('amount');
        $newCustomersMonth = $customersThisMonth;
        $loansDisbursedCount = Loan::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $monthStart)
            ->whereIn('status', ['active', 'overdue', 'defaulted', 'closed'])
            ->count();
        $loansDisbursedAmount = Loan::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $monthStart)
            ->whereIn('status', ['active', 'overdue', 'defaulted', 'closed'])
            ->sum('principal_amount');
        $overdueLoansCount = Loan::where('tenant_id', $tenantId)
            ->where('status', 'overdue')
            ->count();
        $overdueLoansAmount = Loan::where('tenant_id', $tenantId)
            ->where('status', 'overdue')
            ->sum('outstanding_balance');

        // --- Row 4: Module Summaries ---
        $modules = $this->getModuleSummaries($tenantId);

        return view('dashboard', compact(
            'totalCustomers', 'customerGrowth', 'totalDeposits', 'depositTrend',
            'loanPortfolio', 'par30', 'monthlyRevenue', 'revenueTrend',
            'totalAccounts', 'pendingActions', 'pendingKyc', 'pendingLoans', 'pendingDisputes',
            'depositLoanTrend', 'txnByType',
            'recentTransactions',
            'todayTxnCount', 'todayTxnVolume', 'newCustomersMonth',
            'loansDisbursedCount', 'loansDisbursedAmount',
            'overdueLoansCount', 'overdueLoansAmount',
            'modules'
        ));
    }

    private function getDepositLoanTrend(string $tenantId): array
    {
        $labels   = [];
        $deposits = [];
        $loans    = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::today()->subMonths($i);
            $labels[] = $month->format('M Y');
            $start = $month->copy()->startOfMonth();
            $end   = $month->copy()->endOfMonth();

            $deposits[] = (float) Transaction::where('tenant_id', $tenantId)
                ->where('type', 'deposit')
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount') / 1_000_000;

            $loans[] = (float) Loan::where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$start, $end])
                ->sum('principal_amount') / 1_000_000;
        }

        return compact('labels', 'deposits', 'loans');
    }

    private function getTransactionsByType(string $tenantId, Carbon $start, Carbon $end): array
    {
        $rows = Transaction::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$start, $end])
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('type')->map(fn($t) => ucfirst(str_replace('_', ' ', $t)))->toArray(),
            'data'   => $rows->pluck('count')->toArray(),
        ];
    }

    private function getModuleSummaries(string $tenantId): array
    {
        $modules = [];

        // Projects
        try {
            $activeProjects = PmProject::where('tenant_id', $tenantId)->where('status', 'active')->count();
            $pendingTasks   = PmTask::whereHas('project', fn($q) => $q->where('tenant_id', $tenantId))
                ->whereNull('completed_at')->count();
            $modules['projects'] = ['active' => $activeProjects, 'pending_tasks' => $pendingTasks];
        } catch (\Exception $e) {
            $modules['projects'] = ['active' => 0, 'pending_tasks' => 0];
        }

        // Marketing
        try {
            $campaignCount = MarketingCampaign::where('tenant_id', $tenantId)->count();
            $deliveryRate  = MarketingCampaign::where('tenant_id', $tenantId)
                ->where('status', 'sent')
                ->avg('delivery_rate') ?? 0;
            $modules['marketing'] = ['campaigns' => $campaignCount, 'delivery_rate' => round($deliveryRate, 1)];
        } catch (\Exception $e) {
            $modules['marketing'] = ['campaigns' => 0, 'delivery_rate' => 0];
        }

        // Chat
        try {
            $unreadMessages = ChatMessage::whereHas('conversation', fn($q) => $q->where('tenant_id', $tenantId))
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->count();
            $modules['chat'] = ['unread' => $unreadMessages];
        } catch (\Exception $e) {
            $modules['chat'] = ['unread' => 0];
        }

        // Calendar
        try {
            $upcomingEvents = CalendarEvent::whereHas('calendar', fn($q) => $q->where('tenant_id', $tenantId))
                ->where('start_time', '>=', Carbon::now())
                ->where('start_time', '<=', Carbon::now()->addDays(7))
                ->count();
            $modules['calendar'] = ['upcoming' => $upcomingEvents];
        } catch (\Exception $e) {
            $modules['calendar'] = ['upcoming' => 0];
        }

        // Insurance
        try {
            $activePolicies = InsurancePolicy::where('tenant_id', $tenantId)->where('status', 'active')->count();
            $totalCoverage  = InsurancePolicy::where('tenant_id', $tenantId)->where('status', 'active')->sum('sum_assured');
            $modules['insurance'] = ['active' => $activePolicies, 'coverage' => $totalCoverage];
        } catch (\Exception $e) {
            $modules['insurance'] = ['active' => 0, 'coverage' => 0];
        }

        // Support
        try {
            $openTickets = SupportTicket::where('tenant_id', $tenantId)
                ->whereIn('status', ['open', 'in_progress'])
                ->count();
            $modules['support'] = ['open_tickets' => $openTickets];
        } catch (\Exception $e) {
            $modules['support'] = ['open_tickets' => 0];
        }

        // HR
        try {
            $employees = User::where('tenant_id', $tenantId)->where('status', 'active')->count();
            $onLeave   = DB::table('leave_requests')
                ->where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', Carbon::today())
                ->whereDate('end_date', '>=', Carbon::today())
                ->count();
            $modules['hr'] = ['employees' => $employees, 'on_leave' => $onLeave];
        } catch (\Exception $e) {
            $modules['hr'] = ['employees' => 0, 'on_leave' => 0];
        }

        // Cortex AI
        try {
            $cortexAnalyses = DB::table('cortex_usage')
                ->where('tenant_id', $tenantId)
                ->count();
            $cortexAlerts = DB::table('cortex_usage')
                ->where('tenant_id', $tenantId)
                ->where('success', false)
                ->count();
            $modules['cortex'] = ['analyses' => $cortexAnalyses, 'alerts' => $cortexAlerts];
        } catch (\Exception $e) {
            $modules['cortex'] = ['analyses' => 0, 'alerts' => 0];
        }

        return $modules;
    }
}
