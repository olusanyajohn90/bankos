<?php

namespace App\Http\Controllers\Teller;

use App\Http\Controllers\Controller;
use App\Models\TellerSession;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TellerMonitoringController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $today    = now()->toDateString();

        // Today's sessions with teller + branch eager loaded
        $todaySessions = TellerSession::where('tenant_id', $tenantId)
            ->where('session_date', $today)
            ->with(['teller', 'branch'])
            ->orderBy('created_at')
            ->get();

        // Summary stats for today
        $stats = [
            'open_sessions'     => $todaySessions->where('status', 'open')->count(),
            'closed_sessions'   => $todaySessions->whereIn('status', ['balanced', 'unbalanced', 'closed'])->count(),
            'total_cash_in'     => $todaySessions->sum('cash_in'),
            'total_cash_out'    => $todaySessions->sum('cash_out'),
            'unbalanced_count'  => $todaySessions->where('status', 'unbalanced')->count(),
            'total_variance'    => $todaySessions->sum('variance'),
        ];

        // Transaction counts per teller today
        $tellerTxCounts = DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', $today)
            ->whereNotNull('performed_by')
            ->select('performed_by', DB::raw('count(*) as tx_count'), DB::raw('sum(amount) as tx_volume'))
            ->groupBy('performed_by')
            ->pluck(null, 'performed_by');

        // Last 7 days daily cash flow
        $dailyCashFlow = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $row  = DB::table('teller_sessions')
                ->where('tenant_id', $tenantId)
                ->where('session_date', $date)
                ->selectRaw('SUM(cash_in) as cash_in, SUM(cash_out) as cash_out')
                ->first();
            $dailyCashFlow[] = [
                'date'     => now()->subDays($i)->format('D d'),
                'cash_in'  => (float) ($row->cash_in ?? 0),
                'cash_out' => (float) ($row->cash_out ?? 0),
            ];
        }

        // Variance alerts: sessions that closed unbalanced in last 30 days
        $varianceAlerts = TellerSession::where('tenant_id', $tenantId)
            ->where('status', 'unbalanced')
            ->where('session_date', '>=', now()->subDays(30)->toDateString())
            ->with(['teller', 'branch'])
            ->orderByDesc('variance')
            ->limit(10)
            ->get();

        // Monthly volume trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $row = DB::table('teller_sessions')
                ->where('tenant_id', $tenantId)
                ->whereYear('session_date', $m->year)
                ->whereMonth('session_date', $m->month)
                ->selectRaw('SUM(cash_in) as cash_in, SUM(cash_out) as cash_out, COUNT(*) as sessions')
                ->first();
            $monthlyTrend[] = [
                'month'    => $m->format('M y'),
                'cash_in'  => (float) ($row->cash_in ?? 0),
                'cash_out' => (float) ($row->cash_out ?? 0),
                'sessions' => (int)   ($row->sessions ?? 0),
            ];
        }

        return view('teller.monitor', compact(
            'todaySessions', 'stats', 'tellerTxCounts',
            'dailyCashFlow', 'varianceAlerts', 'monthlyTrend'
        ));
    }
}
