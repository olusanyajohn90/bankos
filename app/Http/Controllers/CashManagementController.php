<?php

namespace App\Http\Controllers;

use App\Models\CashPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashManagementController extends Controller
{
    public function dashboard()
    {
        try {
            $today = CashPosition::whereDate('position_date', today())->first();
            $yesterday = CashPosition::whereDate('position_date', today()->subDay())->first();

            $totalPositions = CashPosition::count();
            $latestBalance = $today->closing_balance ?? ($yesterday->closing_balance ?? 0);
            $latestVaultCash = $today->vault_cash ?? ($yesterday->vault_cash ?? 0);
            $latestNostro = $today->nostro_balance ?? ($yesterday->nostro_balance ?? 0);
            $todayInflows = $today->total_inflows ?? 0;
            $todayOutflows = $today->total_outflows ?? 0;
            $netPosition = $todayInflows - $todayOutflows;

            // 30-day trend
            $dailyTrend = CashPosition::where('position_date', '>=', now()->subDays(30))
                ->select(
                    DB::raw("TO_CHAR(position_date, 'YYYY-MM-DD') as date"),
                    'closing_balance', 'total_inflows', 'total_outflows'
                )
                ->orderBy('position_date')
                ->get();

            // Inflows vs Outflows last 7 days
            $weeklyFlow = CashPosition::where('position_date', '>=', now()->subDays(7))
                ->select(
                    DB::raw("TO_CHAR(position_date, 'Dy') as day"),
                    'total_inflows', 'total_outflows'
                )
                ->orderBy('position_date')
                ->get();

            // Average daily closing balance this month
            $avgMonthlyBalance = CashPosition::whereYear('position_date', now()->year)
                ->whereMonth('position_date', now()->month)
                ->avg('closing_balance') ?? 0;

            // Highest / lowest closing balance this month
            $maxBalance = CashPosition::whereYear('position_date', now()->year)
                ->whereMonth('position_date', now()->month)
                ->max('closing_balance') ?? 0;
            $minBalance = CashPosition::whereYear('position_date', now()->year)
                ->whereMonth('position_date', now()->month)
                ->min('closing_balance') ?? 0;

        } catch (\Exception $e) {
            return view('cash-management.dashboard', [
                'error' => $e->getMessage(),
                'today' => null, 'totalPositions' => 0, 'latestBalance' => 0,
                'latestVaultCash' => 0, 'latestNostro' => 0, 'todayInflows' => 0,
                'todayOutflows' => 0, 'netPosition' => 0, 'dailyTrend' => collect(),
                'weeklyFlow' => collect(), 'avgMonthlyBalance' => 0,
                'maxBalance' => 0, 'minBalance' => 0,
            ]);
        }

        return view('cash-management.dashboard', compact(
            'today', 'totalPositions', 'latestBalance', 'latestVaultCash', 'latestNostro',
            'todayInflows', 'todayOutflows', 'netPosition', 'dailyTrend', 'weeklyFlow',
            'avgMonthlyBalance', 'maxBalance', 'minBalance'
        ));
    }

    public function positions(Request $request)
    {
        try {
            $query = CashPosition::with('preparer')->orderByDesc('position_date');

            if ($request->filled('from')) $query->whereDate('position_date', '>=', $request->from);
            if ($request->filled('to')) $query->whereDate('position_date', '<=', $request->to);

            $positions = $query->paginate(20)->withQueryString();
        } catch (\Exception $e) {
            $positions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('cash-management.positions', compact('positions'));
    }

    public function createPosition()
    {
        return view('cash-management.create');
    }

    public function storePosition(Request $request)
    {
        $request->validate([
            'position_date'   => 'required|date',
            'opening_balance' => 'required|numeric',
            'total_inflows'   => 'required|numeric|min:0',
            'total_outflows'  => 'required|numeric|min:0',
            'vault_cash'      => 'nullable|numeric|min:0',
            'nostro_balance'  => 'nullable|numeric|min:0',
        ]);

        try {
            $closingBalance = $request->opening_balance + $request->total_inflows - $request->total_outflows;

            CashPosition::create([
                'position_date'   => $request->position_date,
                'opening_balance' => $request->opening_balance,
                'total_inflows'   => $request->total_inflows,
                'total_outflows'  => $request->total_outflows,
                'closing_balance' => $closingBalance,
                'vault_cash'      => $request->vault_cash ?? 0,
                'nostro_balance'  => $request->nostro_balance ?? 0,
                'prepared_by'     => auth()->id(),
            ]);

            return redirect()->route('cash-management.positions')->with('success', 'Cash position recorded.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function forecast()
    {
        try {
            // Simple 7-day forecast based on 30-day historical average
            $avgInflows = CashPosition::where('position_date', '>=', now()->subDays(30))
                ->avg('total_inflows') ?? 0;
            $avgOutflows = CashPosition::where('position_date', '>=', now()->subDays(30))
                ->avg('total_outflows') ?? 0;
            $lastPosition = CashPosition::orderByDesc('position_date')->first();
            $startingBalance = $lastPosition->closing_balance ?? 0;

            $forecast = [];
            $balance = $startingBalance;
            for ($i = 1; $i <= 7; $i++) {
                $balance = $balance + $avgInflows - $avgOutflows;
                $forecast[] = [
                    'date'     => now()->addDays($i)->format('Y-m-d'),
                    'day'      => now()->addDays($i)->format('D'),
                    'inflows'  => round($avgInflows, 2),
                    'outflows' => round($avgOutflows, 2),
                    'balance'  => round($balance, 2),
                ];
            }
        } catch (\Exception $e) {
            $forecast = [];
            $avgInflows = 0;
            $avgOutflows = 0;
            $startingBalance = 0;
        }

        return view('cash-management.forecast', compact('forecast', 'avgInflows', 'avgOutflows', 'startingBalance'));
    }
}
