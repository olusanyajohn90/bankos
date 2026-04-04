<?php

namespace App\Http\Controllers;

use App\Models\RegulatoryReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegulatoryReportingController extends Controller
{
    public function dashboard()
    {
        try {
            $totalReports = RegulatoryReport::count();
            $pendingReports = RegulatoryReport::where('status', 'pending')->count();
            $draftReports = RegulatoryReport::where('status', 'draft')->count();
            $submittedReports = RegulatoryReport::where('status', 'submitted')->count();
            $acceptedReports = RegulatoryReport::where('status', 'accepted')->count();
            $rejectedReports = RegulatoryReport::where('status', 'rejected')->count();

            // Overdue reports
            $overdueReports = RegulatoryReport::whereIn('status', ['pending', 'draft'])
                ->where('due_date', '<', now())
                ->count();

            // Due this month
            $dueThisMonth = RegulatoryReport::whereIn('status', ['pending', 'draft'])
                ->whereYear('due_date', now()->year)
                ->whereMonth('due_date', now()->month)
                ->get();

            // By type
            $byType = RegulatoryReport::select('report_type', DB::raw('COUNT(*) as count'))
                ->groupBy('report_type')
                ->get();

            // By status
            $byStatus = RegulatoryReport::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get();

            // Calendar (next 90 days)
            $upcomingDeadlines = RegulatoryReport::whereIn('status', ['pending', 'draft'])
                ->where('due_date', '>=', now())
                ->where('due_date', '<=', now()->addDays(90))
                ->orderBy('due_date')
                ->get();

            // Submission trend
            $monthlySubmissions = RegulatoryReport::where('status', 'submitted')
                ->whereNotNull('submitted_date')
                ->where('submitted_date', '>=', now()->subMonths(6))
                ->select(
                    DB::raw("TO_CHAR(submitted_date, 'YYYY-MM') as month"),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy(DB::raw("TO_CHAR(submitted_date, 'YYYY-MM')"))
                ->orderBy('month')
                ->get();

        } catch (\Exception $e) {
            return view('regulatory.dashboard', [
                'error' => $e->getMessage(),
                'totalReports' => 0, 'pendingReports' => 0, 'draftReports' => 0,
                'submittedReports' => 0, 'acceptedReports' => 0, 'rejectedReports' => 0,
                'overdueReports' => 0, 'dueThisMonth' => collect(), 'byType' => collect(),
                'byStatus' => collect(), 'upcomingDeadlines' => collect(), 'monthlySubmissions' => collect(),
            ]);
        }

        return view('regulatory.dashboard', compact(
            'totalReports', 'pendingReports', 'draftReports', 'submittedReports',
            'acceptedReports', 'rejectedReports', 'overdueReports', 'dueThisMonth',
            'byType', 'byStatus', 'upcomingDeadlines', 'monthlySubmissions'
        ));
    }

    public function index(Request $request)
    {
        try {
            $query = RegulatoryReport::with('preparer')->latest();
            if ($request->filled('report_type')) $query->where('report_type', $request->report_type);
            if ($request->filled('status')) $query->where('status', $request->status);
            if ($request->filled('period')) $query->where('period', $request->period);
            $reports = $query->paginate(20)->withQueryString();
        } catch (\Exception $e) {
            $reports = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('regulatory.index', compact('reports'));
    }

    public function create()
    {
        return view('regulatory.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'report_type' => 'required|string|max:50',
            'report_name' => 'required|string|max:200',
            'period'      => 'required|string|max:20',
            'due_date'    => 'required|date',
            'notes'       => 'nullable|string',
        ]);

        try {
            RegulatoryReport::create([
                'report_type' => $request->report_type,
                'report_name' => $request->report_name,
                'period'      => $request->period,
                'due_date'    => $request->due_date,
                'notes'       => $request->notes,
                'prepared_by' => auth()->id(),
            ]);

            return redirect()->route('regulatory.index')->with('success', 'Report entry created.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $report = RegulatoryReport::with('preparer', 'approver')->findOrFail($id);
        } catch (\Exception $e) {
            return redirect()->route('regulatory.index')->with('error', 'Report not found.');
        }
        return view('regulatory.show', compact('report'));
    }

    public function submit($id)
    {
        try {
            $report = RegulatoryReport::findOrFail($id);
            $report->update([
                'status'         => 'submitted',
                'submitted_date' => now(),
                'approved_by'    => auth()->id(),
            ]);
            return back()->with('success', 'Report marked as submitted.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function generate($type)
    {
        try {
            $tenantId = auth()->user()->tenant_id;
            $reportData = [];

            switch ($type) {
                case 'cbn_returns':
                    $reportData = [
                        'total_deposits'     => DB::table('accounts')->where('tenant_id', $tenantId)->where('status', 'active')->sum('available_balance'),
                        'total_loans'        => DB::table('loans')->where('tenant_id', $tenantId)->whereIn('status', ['active', 'disbursed'])->sum('principal_amount'),
                        'total_assets'       => DB::table('gl_accounts')->where('tenant_id', $tenantId)->where('type', 'asset')->sum('balance'),
                        'total_liabilities'  => DB::table('gl_accounts')->where('tenant_id', $tenantId)->where('type', 'liability')->sum('balance'),
                        'total_customers'    => DB::table('customers')->where('tenant_id', $tenantId)->count(),
                        'generated_at'       => now()->toIso8601String(),
                    ];
                    break;

                case 'ndic_premium':
                    $reportData = [
                        'total_insured_deposits' => DB::table('accounts')->where('tenant_id', $tenantId)->where('status', 'active')->sum('available_balance'),
                        'deposit_count'          => DB::table('accounts')->where('tenant_id', $tenantId)->where('status', 'active')->count(),
                        'premium_rate'           => 0.0035,
                        'estimated_premium'      => DB::table('accounts')->where('tenant_id', $tenantId)->where('status', 'active')->sum('available_balance') * 0.0035,
                        'generated_at'           => now()->toIso8601String(),
                    ];
                    break;

                case 'nfiu_ctr':
                    $reportData = [
                        'cash_transactions_above_5m' => DB::table('transactions')
                            ->where('tenant_id', $tenantId)
                            ->where('type', 'cash')
                            ->where('amount', '>=', 5000000)
                            ->where('created_at', '>=', now()->subMonth())
                            ->count(),
                        'total_volume' => DB::table('transactions')
                            ->where('tenant_id', $tenantId)
                            ->where('type', 'cash')
                            ->where('amount', '>=', 5000000)
                            ->where('created_at', '>=', now()->subMonth())
                            ->sum('amount'),
                        'generated_at' => now()->toIso8601String(),
                    ];
                    break;

                case 'prudential_guidelines':
                    $totalLoans = DB::table('loans')->where('tenant_id', $tenantId)->whereIn('status', ['active', 'disbursed'])->sum('principal_amount');
                    $totalDeposits = DB::table('accounts')->where('tenant_id', $tenantId)->where('status', 'active')->sum('available_balance');
                    $reportData = [
                        'loan_to_deposit_ratio' => $totalDeposits > 0 ? round(($totalLoans / $totalDeposits) * 100, 2) : 0,
                        'total_loans'           => $totalLoans,
                        'total_deposits'        => $totalDeposits,
                        'capital_adequacy'      => 'Requires manual input',
                        'generated_at'          => now()->toIso8601String(),
                    ];
                    break;

                default:
                    return back()->with('error', 'Unknown report type.');
            }

            return response()->json([
                'report_type' => $type,
                'data'        => $reportData,
                'message'     => 'Report data generated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
