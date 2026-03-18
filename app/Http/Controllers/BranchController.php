<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = Branch::with('manager')->paginate();
        return view('branches.index', compact('branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $managers = User::permission('branches.view')->get();
        $states = config('nigeria_states');
        return view('branches.create', compact('managers', 'states'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:branches,code',
            'branch_code' => 'nullable|string|max:20',
            'sort_code' => 'nullable|string|max:20',
            'routing_number' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'street' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'local_government' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,inactive',
        ]);

        Branch::create($validated);

        return redirect()->route('branches.index')->with('success', 'Branch created successfully.');
    }

    /**
     * HQ cross-branch analytics dashboard.
     */
    public function analytics(Request $request)
    {
        $period = $request->input('period', 'this_month');
        $stateFilter = $request->input('state', 'all');

        [$startDate, $endDate] = match($period) {
            'today'        => [now()->startOfDay(), now()->endOfDay()],
            'this_week'    => [now()->startOfWeek(), now()->endOfWeek()],
            'last_month'   => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'this_year'    => [now()->startOfYear(), now()->endOfYear()],
            'custom'       => [
                \Carbon\Carbon::parse($request->input('start_date', now()->startOfMonth())),
                \Carbon\Carbon::parse($request->input('end_date', now())),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $branchQuery = Branch::with('manager');
        if ($stateFilter !== 'all') {
            $branchQuery->where('state', $stateFilter);
        }
        $branches = $branchQuery->where('status', 'active')->get();

        // Build per-branch metrics
        $branchMetrics = $branches->map(function (Branch $b) use ($startDate, $endDate) {
            $accountIds = \App\Models\Account::where('branch_id', $b->id)->pluck('id');
            $loanQ      = \App\Models\Loan::where('branch_id', $b->id);

            $totalCustomers  = \App\Models\Customer::where('branch_id', $b->id)->count();
            $newCustomers    = \App\Models\Customer::where('branch_id', $b->id)->whereBetween('created_at', [$startDate, $endDate])->count();
            $activeLoans     = (clone $loanQ)->whereIn('status', ['active','overdue'])->count();
            $overdueLoans    = (clone $loanQ)->where('status', 'overdue')->count();
            $disbursed       = (clone $loanQ)->whereBetween('disbursed_at', [$startDate, $endDate])->sum('principal_amount');
            $outstanding     = (clone $loanQ)->whereIn('status', ['active','overdue'])->sum('outstanding_balance');
            $totalPrincipal  = (clone $loanQ)->whereIn('status', ['active','overdue'])->sum('principal_amount');
            $overdueBal      = (clone $loanQ)->where('status','overdue')->sum('outstanding_balance');
            $par             = $totalPrincipal > 0 ? round($overdueBal / $totalPrincipal * 100, 1) : 0;
            $deposits        = \App\Models\Account::where('branch_id', $b->id)->where('type','savings')->sum('available_balance');
            $collections     = \App\Models\Transaction::whereIn('account_id', $accountIds)
                ->where('type','repayment')->where('status','success')
                ->whereBetween('created_at', [$startDate, $endDate])->sum('amount');
            $staffCount      = \App\Models\User::where('branch_id', $b->id)->count();

            return [
                'branch'         => $b,
                'totalCustomers' => $totalCustomers,
                'newCustomers'   => $newCustomers,
                'activeLoans'    => $activeLoans,
                'overdueLoans'   => $overdueLoans,
                'disbursed'      => $disbursed,
                'outstanding'    => $outstanding,
                'par'            => $par,
                'deposits'       => $deposits,
                'collections'    => $collections,
                'staffCount'     => $staffCount,
            ];
        });

        // Network-wide totals
        $totals = [
            'customers'   => $branchMetrics->sum('totalCustomers'),
            'activeLoans' => $branchMetrics->sum('activeLoans'),
            'disbursed'   => $branchMetrics->sum('disbursed'),
            'outstanding' => $branchMetrics->sum('outstanding'),
            'deposits'    => $branchMetrics->sum('deposits'),
            'collections' => $branchMetrics->sum('collections'),
        ];

        // Network PAR
        $netOutstanding = \App\Models\Loan::whereIn('status', ['active','overdue'])->sum('outstanding_balance');
        $netOverdue     = \App\Models\Loan::where('status','overdue')->sum('outstanding_balance');
        $totals['par']  = $netOutstanding > 0 ? round($netOverdue / $netOutstanding * 100, 1) : 0;

        // Monthly trend (last 6 months) for chart — all branches combined
        $trendMonths = collect();
        for ($i = 5; $i >= 0; $i--) {
            $mStart = now()->subMonths($i)->startOfMonth();
            $mEnd   = now()->subMonths($i)->endOfMonth();
            $trendMonths->push([
                'label'       => $mStart->format('M Y'),
                'disbursed'   => \App\Models\Loan::whereBetween('disbursed_at', [$mStart, $mEnd])->sum('principal_amount'),
                'outstanding' => \App\Models\Loan::whereIn('status',['active','overdue'])->whereBetween('created_at', [$mStart, $mEnd])->sum('outstanding_balance'),
                'collections' => \App\Models\Transaction::where('type','repayment')->where('status','success')->whereBetween('created_at', [$mStart, $mEnd])->sum('amount'),
            ]);
        }

        // States available for filter
        $states = Branch::distinct()->pluck('state')->sort()->values();

        // Rankings
        $topByDisbursement = $branchMetrics->sortByDesc('disbursed')->take(5);
        $topByCustomers    = $branchMetrics->sortByDesc('totalCustomers')->take(5);
        $highestPAR        = $branchMetrics->sortByDesc('par')->take(5);

        return view('branches.analytics', compact(
            'branchMetrics', 'totals', 'trendMonths', 'period', 'stateFilter',
            'startDate', 'endDate', 'states', 'topByDisbursement', 'topByCustomers', 'highestPAR'
        ));
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch, Request $request)
    {
        $branch->load('manager');

        $period    = $request->input('period', 'this_month');
        $statusFilter = $request->input('status', 'all');

        [$startDate, $endDate] = match($period) {
            'today'        => [now()->startOfDay(), now()->endOfDay()],
            'this_week'    => [now()->startOfWeek(), now()->endOfWeek()],
            'last_month'   => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'this_year'    => [now()->startOfYear(), now()->endOfYear()],
            'custom'       => [
                \Carbon\Carbon::parse($request->input('start_date', now()->startOfMonth())),
                \Carbon\Carbon::parse($request->input('end_date', now())),
            ],
            default        => [now()->startOfMonth(), now()->endOfMonth()], // this_month
        };

        // Base loan query for this branch
        $loanQuery = \App\Models\Loan::where('branch_id', $branch->id);

        // KPI: customers
        $totalCustomers  = \App\Models\Customer::where('branch_id', $branch->id)->count();
        $newCustomers    = \App\Models\Customer::where('branch_id', $branch->id)
            ->whereBetween('created_at', [$startDate, $endDate])->count();

        // KPI: loans
        $activeLoans     = (clone $loanQuery)->whereIn('status', ['active','overdue'])->count();
        $overdueLoans    = (clone $loanQuery)->where('status', 'overdue')->count();
        $totalDisbursed  = (clone $loanQuery)->whereBetween('disbursed_at', [$startDate, $endDate])->sum('principal_amount');
        $totalOutstanding= (clone $loanQuery)->whereIn('status', ['active','overdue'])->sum('outstanding_balance');
        $totalPrincipal  = (clone $loanQuery)->whereIn('status', ['active','overdue'])->sum('principal_amount');
        $parRatio        = $totalPrincipal > 0
            ? (clone $loanQuery)->where('status','overdue')->sum('outstanding_balance') / $totalPrincipal * 100
            : 0;

        // KPI: deposits
        $totalDeposits   = \App\Models\Account::where('branch_id', $branch->id)
            ->where('type', 'savings')->sum('available_balance');

        // KPI: collections (repayment transactions in period)
        $accountIds = \App\Models\Account::where('branch_id', $branch->id)->pluck('id');
        $collections = \App\Models\Transaction::whereIn('account_id', $accountIds)
            ->where('type', 'repayment')->where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        // Staff
        $staff = \App\Models\User::where('branch_id', $branch->id)
            ->with('roles')->get();

        // Monthly trend: disbursements & collections (last 6 months)
        $trendMonths = collect();
        for ($i = 5; $i >= 0; $i--) {
            $mStart = now()->subMonths($i)->startOfMonth();
            $mEnd   = now()->subMonths($i)->endOfMonth();
            $trendMonths->push([
                'label'       => $mStart->format('M Y'),
                'disbursed'   => (clone $loanQuery)->whereBetween('disbursed_at', [$mStart, $mEnd])->sum('principal_amount'),
                'collections' => \App\Models\Transaction::whereIn('account_id', $accountIds)
                    ->where('type','repayment')->where('status','success')
                    ->whereBetween('created_at', [$mStart, $mEnd])->sum('amount'),
            ]);
        }

        // Loan status breakdown
        $loanByStatus = (clone $loanQuery)->selectRaw('status, count(*) as count, sum(outstanding_balance) as outstanding')
            ->groupBy('status')->get()->keyBy('status');

        // Top customers by outstanding
        $topBorrowers = \App\Models\Loan::where('branch_id', $branch->id)
            ->whereIn('status', ['active','overdue'])
            ->with('customer')
            ->orderByDesc('outstanding_balance')
            ->limit(10)->get();

        // Recent transactions
        $recentTxns = \App\Models\Transaction::whereIn('account_id', $accountIds)
            ->with('account.customer')
            ->orderByDesc('created_at')
            ->limit(10)->get();

        return view('branches.show', compact(
            'branch', 'period', 'statusFilter', 'startDate', 'endDate',
            'totalCustomers', 'newCustomers', 'activeLoans', 'overdueLoans',
            'totalDisbursed', 'totalOutstanding', 'parRatio', 'totalDeposits',
            'collections', 'staff', 'trendMonths', 'loanByStatus',
            'topBorrowers', 'recentTxns'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        $managers = User::permission('branches.view')->get();
        $states = config('nigeria_states');
        return view('branches.edit', compact('branch', 'managers', 'states'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:branches,code,' . $branch->id,
            'branch_code' => 'nullable|string|max:20',
            'sort_code' => 'nullable|string|max:20',
            'routing_number' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'street' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'local_government' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,inactive',
        ]);

        $branch->update($validated);

        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        if ($branch->users()->exists()) {
            return back()->with('error', 'Cannot delete branch because it has system users assigned to it.');
        }

        $branch->delete();
        
        return redirect()->route('branches.index')->with('success', 'Branch deleted successfully.');
    }
}
