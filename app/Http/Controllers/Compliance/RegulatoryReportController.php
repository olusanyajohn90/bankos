<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;

class RegulatoryReportController extends Controller
{
    public function dashboard()
    {
        $tenantId     = auth()->user()->tenant_id;
        $currentMonth = now()->format('Y-m');
        [$year, $mon] = explode('-', $currentMonth);

        $totalDepositors = Account::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['closed'])
            ->distinct('customer_id')
            ->count('customer_id');

        $totalDeposits = Account::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['closed'])
            ->sum('ledger_balance');

        $largeTxns30d = Transaction::where('tenant_id', $tenantId)
            ->where('amount', '>=', 5_000_000)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $totalAccounts   = Account::where('tenant_id', $tenantId)->whereNotIn('status', ['closed'])->count();
        $dormantAccounts = Account::where('tenant_id', $tenantId)->where('status', 'dormant')->count();

        return view('compliance.dashboard', compact(
            'totalDepositors', 'totalDeposits', 'largeTxns30d',
            'totalAccounts', 'dormantAccounts', 'currentMonth'
        ));
    }

    public function ndicDepositors(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $data = Account::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['closed'])
            ->selectRaw('type, COUNT(DISTINCT customer_id) as depositor_count, SUM(ledger_balance) as total_balance')
            ->groupBy('type')
            ->orderBy('type')
            ->get();

        $totals = [
            'depositors' => $data->sum('depositor_count'),
            'balance'    => $data->sum('total_balance'),
        ];

        return view('compliance.ndic', compact('data', 'totals'));
    }

    public function ndicDownload(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $rows = Account::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['closed'])
            ->with('customer')
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Account Number', 'Customer Name', 'Account Type', 'Balance (NGN)', 'Status']);

            foreach ($rows as $account) {
                fputcsv($handle, [
                    $account->account_number,
                    $account->customer ? trim($account->customer->first_name . ' ' . $account->customer->last_name) : 'N/A',
                    strtoupper($account->type),
                    number_format($account->ledger_balance, 2, '.', ''),
                    $account->status,
                ]);
            }

            fclose($handle);
        }, 'ndic-depositors-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function nfiuCtr(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $dateFrom = $request->date_from ?? now()->subDays(30)->toDateString();
        $dateTo   = $request->date_to   ?? now()->toDateString();

        $transactions = Transaction::where('tenant_id', $tenantId)
            ->where('amount', '>=', 5_000_000)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->with('account.customer')
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('compliance.nfiu-ctr', compact('transactions', 'dateFrom', 'dateTo'));
    }

    public function nfiuCtrDownload(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $dateFrom = $request->date_from ?? now()->subDays(30)->toDateString();
        $dateTo   = $request->date_to   ?? now()->toDateString();

        $transactions = Transaction::where('tenant_id', $tenantId)
            ->where('amount', '>=', 5_000_000)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->with('account.customer')
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Reference', 'Account Number', 'Customer Name', 'BVN', 'Amount (NGN)', 'Type', 'Description']);

            foreach ($transactions as $txn) {
                fputcsv($handle, [
                    $txn->created_at->format('Y-m-d H:i:s'),
                    $txn->reference,
                    $txn->account->account_number ?? '',
                    $txn->account->customer
                        ? trim($txn->account->customer->first_name . ' ' . $txn->account->customer->last_name)
                        : '',
                    $txn->account->customer->bvn ?? '',
                    number_format($txn->amount, 2, '.', ''),
                    strtoupper($txn->type),
                    $txn->description ?? '',
                ]);
            }

            fclose($handle);
        }, 'nfiu-ctr-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
