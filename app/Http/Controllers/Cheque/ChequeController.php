<?php

namespace App\Http\Controllers\Cheque;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ChequeBook;
use App\Models\ChequeTransaction;
use Illuminate\Http\Request;

class ChequeController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $books = ChequeBook::where('tenant_id', $tenantId)
            ->with('account.customer')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('cheques.index', compact('books'));
    }

    public function create()
    {
        $tenantId = auth()->user()->tenant_id;
        $accounts = Account::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with('customer')
            ->get();

        return view('cheques.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'account_id'   => 'required|uuid',
            'series_start' => 'required|string|max:20',
            'series_end'   => 'required|string|max:20',
            'issued_date'  => 'required|date',
        ]);

        // Auto-calculate leaves from numeric range if both are numeric
        $leaves = 25;
        if (is_numeric($data['series_start']) && is_numeric($data['series_end'])) {
            $leaves = (int) $data['series_end'] - (int) $data['series_start'] + 1;
        }

        ChequeBook::create([
            'tenant_id'    => auth()->user()->tenant_id,
            'account_id'   => $data['account_id'],
            'series_start' => $data['series_start'],
            'series_end'   => $data['series_end'],
            'leaves'       => max(1, $leaves),
            'issued_date'  => $data['issued_date'],
            'issued_by'    => auth()->id(),
        ]);

        return redirect()->route('cheques.index')->with('success', 'Cheque book issued successfully.');
    }

    public function show(ChequeBook $chequeBook)
    {
        $chequeBook->load('account.customer', 'issuedBy', 'cheques');

        return view('cheques.show', compact('chequeBook'));
    }

    public function issueLeaf(Request $request, ChequeBook $chequeBook)
    {
        $data = $request->validate([
            'cheque_number'    => 'required|string|max:20',
            'payee_name'       => 'nullable|string|max:150',
            'amount'           => 'nullable|numeric|min:0.01',
            'issue_date'       => 'nullable|date',
            'drawer_reference' => 'nullable|string|max:100',
            'notes'            => 'nullable|string',
        ]);

        // Validate cheque number is within series range (if numeric)
        if (is_numeric($data['cheque_number'])
            && is_numeric($chequeBook->series_start)
            && is_numeric($chequeBook->series_end)
        ) {
            $num = (int) $data['cheque_number'];
            if ($num < (int) $chequeBook->series_start || $num > (int) $chequeBook->series_end) {
                return back()->withErrors(['cheque_number' => 'Cheque number is outside the book series range.']);
            }
        }

        ChequeTransaction::create([
            'tenant_id'        => auth()->user()->tenant_id,
            'cheque_book_id'   => $chequeBook->id,
            'account_id'       => $chequeBook->account_id,
            'cheque_number'    => $data['cheque_number'],
            'payee_name'       => $data['payee_name'] ?? null,
            'amount'           => $data['amount'] ?? null,
            'issue_date'       => $data['issue_date'] ?? now()->toDateString(),
            'status'           => 'issued',
            'drawer_reference' => $data['drawer_reference'] ?? null,
            'notes'            => $data['notes'] ?? null,
        ]);

        $chequeBook->increment('leaves_used');

        // Mark as exhausted if all leaves used
        if ($chequeBook->leaves_used >= $chequeBook->leaves) {
            $chequeBook->update(['status' => 'exhausted']);
        }

        return back()->with('success', 'Cheque leaf issued.');
    }

    public function updateLeaf(Request $request, ChequeBook $chequeBook)
    {
        $data = $request->validate([
            'cheque_transaction_id' => 'required|uuid',
            'status'                => 'required|in:presented,cleared,bounced,cancelled',
            'presented_date'        => 'nullable|date',
            'cleared_date'          => 'nullable|date',
            'bounced_date'          => 'nullable|date',
            'bank_reference'        => 'nullable|string|max:100',
            'notes'                 => 'nullable|string',
        ]);

        $transaction = ChequeTransaction::where('id', $data['cheque_transaction_id'])
            ->where('cheque_book_id', $chequeBook->id)
            ->firstOrFail();

        $transaction->update([
            'status'         => $data['status'],
            'presented_date' => $data['presented_date'] ?? $transaction->presented_date,
            'cleared_date'   => $data['status'] === 'cleared' ? ($data['cleared_date'] ?? now()->toDateString()) : $transaction->cleared_date,
            'bounced_date'   => $data['status'] === 'bounced' ? ($data['bounced_date'] ?? now()->toDateString()) : $transaction->bounced_date,
            'bank_reference' => $data['bank_reference'] ?? $transaction->bank_reference,
            'notes'          => $data['notes'] ?? $transaction->notes,
        ]);

        return back()->with('success', 'Cheque status updated to ' . ucfirst($data['status']) . '.');
    }
}
