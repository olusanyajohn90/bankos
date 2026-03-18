<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ChequeBook;
use App\Models\ChequeTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChequeController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $books = ChequeBook::where('tenant_id', $tenantId)
            ->with('account.customer')
            ->latest()
            ->paginate(20);

        $pendingCheques = ChequeTransaction::where('tenant_id', $tenantId)
            ->whereIn('status', ['presented', 'clearing'])
            ->with('account.customer', 'chequeBook')
            ->latest()
            ->get();

        return view('cheques.index', compact('books', 'pendingCheques'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'account_id'   => 'required|uuid',
            'series_start' => 'required|string|max:20',
            'series_end'   => 'required|string|max:20',
            'leaves'       => 'required|integer|min:1|max:200',
            'issued_date'  => 'required|date',
        ]);

        ChequeBook::create([
            'tenant_id'    => auth()->user()->tenant_id,
            'account_id'   => $data['account_id'],
            'series_start' => $data['series_start'],
            'series_end'   => $data['series_end'],
            'leaves'       => $data['leaves'],
            'issued_date'  => $data['issued_date'],
            'issued_by'    => auth()->id(),
        ]);

        return back()->with('success', 'Cheque book issued successfully.');
    }

    public function cancel(ChequeBook $chequeBook)
    {
        $chequeBook->update(['status' => 'cancelled']);
        return back()->with('success', 'Cheque book cancelled.');
    }

    public function storeCheque(Request $request)
    {
        $data = $request->validate([
            'account_id'      => 'required|uuid',
            'cheque_book_id'  => 'nullable|uuid',
            'cheque_number'   => 'required|string|max:20',
            'amount'          => 'required|numeric|min:0.01',
            'payee_name'      => 'nullable|string|max:255',
            'cheque_date'     => 'required|date',
        ]);

        ChequeTransaction::create([
            'tenant_id'      => auth()->user()->tenant_id,
            'account_id'     => $data['account_id'],
            'cheque_book_id' => $data['cheque_book_id'] ?? null,
            'cheque_number'  => $data['cheque_number'],
            'amount'         => $data['amount'],
            'payee_name'     => $data['payee_name'] ?? null,
            'cheque_date'    => $data['cheque_date'],
            'status'         => 'issued',
        ]);

        // Increment leaves used on the cheque book if linked
        if (!empty($data['cheque_book_id'])) {
            ChequeBook::where('id', $data['cheque_book_id'])->increment('leaves_used');
        }

        return back()->with('success', 'Cheque recorded.');
    }

    public function process(Request $request, ChequeTransaction $cheque)
    {
        $data = $request->validate([
            'action'        => 'required|in:present,clear,return,stop',
            'return_reason' => 'required_if:action,return|nullable|string|max:255',
        ]);

        $statusMap = [
            'present' => 'presented',
            'clear'   => 'cleared',
            'return'  => 'returned',
            'stop'    => 'stopped',
        ];

        DB::transaction(function () use ($cheque, $data, $statusMap) {
            $newStatus = $statusMap[$data['action']];

            $cheque->update([
                'status'         => $newStatus,
                'presented_date' => in_array($data['action'], ['present', 'clear']) ? now()->toDateString() : $cheque->presented_date,
                'return_reason'  => $data['return_reason'] ?? null,
                'processed_by'   => auth()->id(),
            ]);

            // Debit account on clearing
            if ($newStatus === 'cleared') {
                $account = Account::findOrFail($cheque->account_id);
                if ($account->available_balance < $cheque->amount) {
                    abort(422, 'Insufficient funds to clear cheque.');
                }
                $account->decrement('available_balance', $cheque->amount);
                $account->decrement('ledger_balance', $cheque->amount);
            }
        });

        return back()->with('success', 'Cheque status updated to ' . $statusMap[$data['action']] . '.');
    }
}
