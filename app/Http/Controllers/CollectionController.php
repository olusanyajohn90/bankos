<?php

namespace App\Http\Controllers;

use App\Models\CollectionLog;
use App\Models\Loan;
use App\Services\OverdueScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectionController extends Controller
{
    public function __construct(private OverdueScoreService $scoreService) {}

    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $overdueLoans = $this->scoreService->getOverdueLoansWithScores($tenantId);

        // Paginate the collection manually
        $page = $request->page ?? 1;
        $perPage = 20;
        $paged = $overdueLoans->forPage($page, $perPage);

        return view('collections.index', [
            'overdueLoans' => $paged,
            'total'        => $overdueLoans->count(),
            'page'         => $page,
            'perPage'      => $perPage,
        ]);
    }

    public function show(Loan $loan)
    {
        $loan->load('customer');
        $loan->overdue_score = $this->scoreService->score($loan);
        $loan->days_past_due = $this->scoreService->getDpd($loan);

        $logs = CollectionLog::where('loan_id', $loan->id)
            ->with('officer')
            ->latest('actioned_at')
            ->paginate(15);

        return view('collections.show', compact('loan', 'logs'));
    }

    public function logAction(Request $request, Loan $loan)
    {
        $data = $request->validate([
            'action'         => 'required|in:call,sms,visit,demand_letter,legal,write_off,restructure',
            'outcome'        => 'required|in:contacted,promised_to_pay,paid,unreachable,disputed,escalated',
            'promise_amount' => 'nullable|numeric|min:0',
            'promise_date'   => 'nullable|date',
            'notes'          => 'nullable|string',
        ]);

        CollectionLog::create(array_merge($data, [
            'tenant_id'    => Auth::user()->tenant_id,
            'loan_id'      => $loan->id,
            'customer_id'  => $loan->customer_id,
            'officer_id'   => Auth::id(),
            'days_past_due'=> $this->scoreService->getDpd($loan),
            'overdue_score'=> $this->scoreService->score($loan),
            'actioned_at'  => now(),
        ]));

        return back()->with('success', 'Collection action logged.');
    }
}
