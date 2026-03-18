<?php

namespace App\Http\Controllers\Teller;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\TellerSession;
use App\Models\Transaction;
use App\Models\VaultEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TellerController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $today    = now()->toDateString();

        $mySession = TellerSession::where('tenant_id', $tenantId)
            ->where('teller_id', auth()->id())
            ->where('session_date', $today)
            ->first();

        $branchSessions = TellerSession::where('tenant_id', $tenantId)
            ->where('session_date', $today)
            ->with('teller', 'branch')
            ->get();

        return view('teller.index', compact('mySession', 'branchSessions', 'today'));
    }

    public function openSession(Request $request)
    {
        $data = $request->validate([
            'branch_id'    => 'required|uuid',
            'opening_cash' => 'required|numeric|min:0',
        ]);

        $existing = TellerSession::where('teller_id', auth()->id())
            ->where('session_date', now()->toDateString())
            ->first();

        if ($existing) {
            return back()->with('error', 'Session already open for today.');
        }

        TellerSession::create([
            'tenant_id'    => auth()->user()->tenant_id,
            'branch_id'    => $data['branch_id'],
            'teller_id'    => auth()->id(),
            'session_date' => now()->toDateString(),
            'opening_cash' => $data['opening_cash'],
            'status'       => 'open',
        ]);

        return back()->with('success', 'Teller session opened.');
    }

    public function closeSession(Request $request, TellerSession $session)
    {
        $data = $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes'        => 'nullable|string',
        ]);

        $expected = (float) $session->opening_cash + (float) $session->cash_in - (float) $session->cash_out;
        $variance = (float) $data['closing_cash'] - $expected;

        $session->update([
            'closing_cash'     => $data['closing_cash'],
            'expected_closing' => $expected,
            'variance'         => $variance,
            'status'           => abs($variance) < 1 ? 'balanced' : 'unbalanced',
            'notes'            => $data['notes'],
            'supervised_by'    => auth()->id(),
        ]);

        return back()->with('success', 'Session closed. Variance: ₦' . number_format($variance, 2));
    }

    public function cashDeposit(Request $request)
    {
        $data = $request->validate([
            'account_id' => 'required|uuid',
            'amount'     => 'required|numeric|min:1',
            'narration'  => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data) {
            $account = Account::findOrFail($data['account_id']);
            $account->increment('available_balance', $data['amount']);
            $account->increment('ledger_balance', $data['amount']);

            Transaction::create([
                'tenant_id'    => auth()->user()->tenant_id,
                'account_id'   => $account->id,
                'reference'    => 'CDR-' . strtoupper(substr(uniqid(), -8)),
                'type'         => 'credit',
                'amount'       => $data['amount'],
                'currency'     => 'NGN',
                'description'  => $data['narration'] ?? 'Cash deposit',
                'status'       => 'completed',
                'performed_by' => auth()->id(),
            ]);

            $session = TellerSession::where('teller_id', auth()->id())
                ->where('session_date', now()->toDateString())
                ->where('status', 'open')
                ->first();
            $session?->increment('cash_in', $data['amount']);
        });

        return back()->with('success', 'Cash deposit posted.');
    }

    public function cashWithdrawal(Request $request)
    {
        $data = $request->validate([
            'account_id' => 'required|uuid',
            'amount'     => 'required|numeric|min:1',
            'narration'  => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data) {
            $account = Account::findOrFail($data['account_id']);

            if ($account->pnd_active ?? false) {
                abort(422, 'Account has Post-No-Debit restriction.');
            }
            if ($account->available_balance < $data['amount']) {
                abort(422, 'Insufficient available balance.');
            }

            $account->decrement('available_balance', $data['amount']);
            $account->decrement('ledger_balance', $data['amount']);

            Transaction::create([
                'tenant_id'    => auth()->user()->tenant_id,
                'account_id'   => $account->id,
                'reference'    => 'CWD-' . strtoupper(substr(uniqid(), -8)),
                'type'         => 'debit',
                'amount'       => $data['amount'],
                'currency'     => 'NGN',
                'description'  => $data['narration'] ?? 'Cash withdrawal',
                'status'       => 'completed',
                'performed_by' => auth()->id(),
            ]);

            $session = TellerSession::where('teller_id', auth()->id())
                ->where('session_date', now()->toDateString())
                ->where('status', 'open')
                ->first();
            $session?->increment('cash_out', $data['amount']);
        });

        return back()->with('success', 'Cash withdrawal processed.');
    }

    public function lookupAccount(Request $request)
    {
        $request->validate(['account_number' => 'required|string']);
        $account = Account::where('tenant_id', auth()->user()->tenant_id)
            ->where('account_number', $request->account_number)
            ->with('customer')
            ->first();

        if (!$account) {
            return response()->json(['error' => 'Account not found.'], 404);
        }

        return response()->json([
            'id'             => $account->id,
            'account_number' => $account->account_number,
            'account_name'   => $account->account_name,
            'customer_name'  => $account->customer?->full_name,
            'balance'        => number_format((float) $account->available_balance, 2),
            'status'         => $account->status,
            'pnd_active'     => (bool) $account->pnd_active,
        ]);
    }
}
