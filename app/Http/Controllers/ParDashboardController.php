<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParDashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Total active loan portfolio
        $totalPortfolio = Loan::where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'overdue'])
            ->sum('outstanding_balance');

        // PAR buckets: 1-30, 31-60, 61-90, 91-180, 180+ days overdue
        $buckets = [
            ['label' => 'Current',    'min' => null, 'max' => 0,   'color' => '#16a34a'],
            ['label' => 'PAR 1-30',   'min' => 1,    'max' => 30,  'color' => '#ca8a04'],
            ['label' => 'PAR 31-60',  'min' => 31,   'max' => 60,  'color' => '#ea580c'],
            ['label' => 'PAR 61-90',  'min' => 61,   'max' => 90,  'color' => '#dc2626'],
            ['label' => 'PAR 91-180', 'min' => 91,   'max' => 180, 'color' => '#9f1239'],
            ['label' => 'PAR 180+',   'min' => 181,  'max' => null,'color' => '#4c0519'],
        ];

        $parData = [];
        $now = now();

        foreach ($buckets as $bucket) {
            $query = Loan::where('tenant_id', $tenantId)
                ->whereIn('status', ['active', 'overdue']);

            if ($bucket['min'] === null) {
                // Current: not overdue or 0 days
                $query->where(function ($q) use ($now) {
                    $q->whereNull('next_due_date')
                      ->orWhere('next_due_date', '>=', $now->toDateString());
                });
            } elseif ($bucket['max'] === null) {
                $query->where('next_due_date', '<=', $now->copy()->subDays($bucket['min'])->toDateString());
            } else {
                $query->whereBetween('next_due_date', [
                    $now->copy()->subDays($bucket['max'])->toDateString(),
                    $now->copy()->subDays($bucket['min'])->toDateString(),
                ]);
            }

            $amount = $query->sum('outstanding_balance');
            $count  = $query->count();
            $pct    = $totalPortfolio > 0 ? ($amount / $totalPortfolio) * 100 : 0;

            $parData[] = array_merge($bucket, [
                'amount' => $amount,
                'count'  => $count,
                'pct'    => round($pct, 2),
            ]);
        }

        // Total PAR (any loan with overdue > 0 days)
        $totalParAmount = Loan::where('tenant_id', $tenantId)
            ->where('status', 'overdue')
            ->sum('outstanding_balance');
        $parRatio = $totalPortfolio > 0 ? round(($totalParAmount / $totalPortfolio) * 100, 2) : 0;

        // Top 10 largest overdue loans
        $topOverdue = Loan::with('customer')
            ->where('tenant_id', $tenantId)
            ->where('status', 'overdue')
            ->orderByDesc('outstanding_balance')
            ->limit(10)
            ->get();

        // Monthly trend: PAR amounts for last 6 months
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $overdue = DB::table('loans')
                ->where('tenant_id', $tenantId)
                ->where('status', 'overdue')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('outstanding_balance');
            $trend[] = ['month' => $month->format('M'), 'amount' => $overdue];
        }

        // Overdue by product
        $byProduct = DB::table('loans')
            ->join('loan_products', 'loans.product_id', '=', 'loan_products.id')
            ->where('loans.tenant_id', $tenantId)
            ->where('loans.status', 'overdue')
            ->selectRaw('loan_products.name, count(*) as cnt, sum(loans.outstanding_balance) as total')
            ->groupBy('loan_products.id', 'loan_products.name')
            ->get();

        return view('par-dashboard.index', compact(
            'totalPortfolio', 'totalParAmount', 'parRatio',
            'parData', 'topOverdue', 'trend', 'byProduct'
        ));
    }
}
