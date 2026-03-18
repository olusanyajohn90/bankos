<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionMonitorController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // KPI tiles
        $today = now()->toDateString();
        $todayVolume = Transaction::where('tenant_id', $tenantId)
            ->whereDate('created_at', $today)->sum('amount');
        $todayCount = Transaction::where('tenant_id', $tenantId)
            ->whereDate('created_at', $today)->count();
        $pendingCount = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'pending')->count();

        // High-value transactions today (> NGN 1M)
        $highValue = Transaction::where('tenant_id', $tenantId)
            ->whereDate('created_at', $today)
            ->where('amount', '>=', 1_000_000)
            ->count();

        // Hourly volume chart (last 24 hours)
        $hourlyData = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subHours(24))
            ->selectRaw('EXTRACT(HOUR FROM created_at) as hr, SUM(amount) as vol, COUNT(*) as cnt')
            ->groupByRaw('EXTRACT(HOUR FROM created_at)')
            ->orderBy('hr')
            ->get()
            ->keyBy('hr');

        $hourly = [];
        for ($h = 0; $h < 24; $h++) {
            $hourly[] = [
                'hour' => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00',
                'vol'  => $hourlyData->get($h)?->vol ?? 0,
                'cnt'  => $hourlyData->get($h)?->cnt ?? 0,
            ];
        }

        // Live feed: latest 50 transactions
        $query = Transaction::with(['account.customer'])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }
        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', $s)
                  ->orWhere('description', 'like', $s);
            });
        }

        $transactions = $query->paginate(50)->withQueryString();

        // Type breakdown
        $typeBreakdown = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', $today)
            ->selectRaw('type, COUNT(*) as cnt, SUM(amount) as vol')
            ->groupBy('type')
            ->get();

        return view('transaction-monitor.index', compact(
            'todayVolume', 'todayCount', 'pendingCount', 'highValue',
            'hourly', 'transactions', 'typeBreakdown'
        ));
    }

    public function reverse(Request $request, Transaction $transaction)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        if ($transaction->status !== 'completed') {
            return back()->with('error', 'Only completed transactions can be reversed.');
        }

        DB::transaction(function () use ($transaction, $request) {
            // Reverse account balances
            $account = $transaction->account;
            if ($account) {
                if ($transaction->type === 'credit') {
                    $account->decrement('balance', $transaction->amount);
                } else {
                    $account->increment('balance', $transaction->amount);
                }
            }

            $transaction->update([
                'status'     => 'reversed',
                'narration'  => ($transaction->narration ?? '') . ' [REVERSED: ' . $request->reason . ']',
                'updated_at' => now(),
            ]);

            // Create offsetting transaction
            Transaction::create([
                'id'          => (string) \Illuminate\Support\Str::uuid(),
                'tenant_id'   => $transaction->tenant_id,
                'account_id'  => $transaction->account_id,
                'type'        => $transaction->type === 'credit' ? 'debit' : 'credit',
                'amount'      => $transaction->amount,
                'balance_after'=> $account?->available_balance ?? 0,
                'reference'   => 'REV-' . $transaction->reference,
                'description' => 'Reversal: ' . $request->reason,
                'status'      => 'completed',
                'channel'     => 'admin',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        });

        return back()->with('success', 'Transaction reversed successfully.');
    }
}
