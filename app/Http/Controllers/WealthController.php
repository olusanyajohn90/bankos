<?php

namespace App\Http\Controllers;

use App\Models\InvestmentPortfolio;
use App\Models\InvestmentHolding;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WealthController extends Controller
{
    public function dashboard()
    {
        try {
            $totalPortfolios = InvestmentPortfolio::count();
            $activePortfolios = InvestmentPortfolio::where('status', 'active')->count();
            $totalAum = InvestmentPortfolio::where('status', 'active')->sum('total_value');
            $totalCost = InvestmentPortfolio::where('status', 'active')->sum('total_cost');
            $totalPnl = InvestmentPortfolio::where('status', 'active')->sum('unrealized_pnl');
            $avgPortfolioValue = $activePortfolios > 0 ? $totalAum / $activePortfolios : 0;

            // Asset allocation
            $assetAllocation = InvestmentHolding::where('status', 'active')
                ->select('asset_type', DB::raw('SUM(market_value) as total_value'), DB::raw('COUNT(*) as count'))
                ->groupBy('asset_type')
                ->orderByDesc('total_value')
                ->get();

            // Risk profile distribution
            $byRiskProfile = InvestmentPortfolio::where('status', 'active')
                ->select('risk_profile', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_value) as total'))
                ->groupBy('risk_profile')
                ->get();

            // Top performers (portfolios with highest unrealized P&L)
            $topPerformers = InvestmentPortfolio::where('status', 'active')
                ->with('customer')
                ->orderByDesc('unrealized_pnl')
                ->limit(5)
                ->get();

            // Holdings count
            $totalHoldings = InvestmentHolding::where('status', 'active')->count();
            $maturingHoldings = InvestmentHolding::where('status', 'active')
                ->whereNotNull('maturity_date')
                ->where('maturity_date', '<=', now()->addDays(30))
                ->where('maturity_date', '>=', now())
                ->count();

            // Monthly AUM trend (last 6 months approximation)
            $monthlyTrend = InvestmentPortfolio::where('status', 'active')
                ->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                    DB::raw('COUNT(*) as portfolios'),
                    DB::raw('SUM(total_value) as aum')
                )
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM')"))
                ->orderBy('month')
                ->get();

        } catch (\Exception $e) {
            return view('wealth.dashboard', [
                'error' => $e->getMessage(),
                'totalPortfolios' => 0, 'activePortfolios' => 0, 'totalAum' => 0,
                'totalCost' => 0, 'totalPnl' => 0, 'avgPortfolioValue' => 0,
                'assetAllocation' => collect(), 'byRiskProfile' => collect(),
                'topPerformers' => collect(), 'totalHoldings' => 0,
                'maturingHoldings' => 0, 'monthlyTrend' => collect(),
            ]);
        }

        return view('wealth.dashboard', compact(
            'totalPortfolios', 'activePortfolios', 'totalAum', 'totalCost', 'totalPnl',
            'avgPortfolioValue', 'assetAllocation', 'byRiskProfile', 'topPerformers',
            'totalHoldings', 'maturingHoldings', 'monthlyTrend'
        ));
    }

    public function portfolios(Request $request)
    {
        try {
            $query = InvestmentPortfolio::with('customer', 'advisor')->latest();

            if ($request->filled('status')) $query->where('status', $request->status);
            if ($request->filled('risk_profile')) $query->where('risk_profile', $request->risk_profile);
            if ($request->filled('search')) {
                $query->where('portfolio_name', 'ilike', "%{$request->search}%");
            }

            $portfolios = $query->paginate(20)->withQueryString();
        } catch (\Exception $e) {
            $portfolios = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('wealth.portfolios.index', compact('portfolios'));
    }

    public function createPortfolio()
    {
        try {
            $customers = Customer::select('id', 'first_name', 'last_name', 'business_name')->limit(500)->get();
        } catch (\Exception $e) {
            $customers = collect();
        }
        return view('wealth.portfolios.create', compact('customers'));
    }

    public function storePortfolio(Request $request)
    {
        $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'portfolio_name' => 'required|string|max:150',
            'risk_profile'   => 'required|in:conservative,moderate,aggressive',
        ]);

        try {
            InvestmentPortfolio::create([
                'customer_id'    => $request->customer_id,
                'portfolio_name' => $request->portfolio_name,
                'risk_profile'   => $request->risk_profile,
                'advisor_id'     => auth()->id(),
            ]);

            return redirect()->route('wealth.portfolios')->with('success', 'Portfolio created.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function showPortfolio($id)
    {
        try {
            $portfolio = InvestmentPortfolio::with('customer', 'advisor', 'holdings')->findOrFail($id);
            $holdingsByType = $portfolio->holdings->where('status', 'active')->groupBy('asset_type');
        } catch (\Exception $e) {
            return redirect()->route('wealth.portfolios')->with('error', 'Portfolio not found.');
        }

        return view('wealth.portfolios.show', compact('portfolio', 'holdingsByType'));
    }

    public function addHolding($portfolioId)
    {
        try {
            $portfolio = InvestmentPortfolio::findOrFail($portfolioId);
        } catch (\Exception $e) {
            return redirect()->route('wealth.portfolios')->with('error', 'Portfolio not found.');
        }
        return view('wealth.portfolios.add-holding', compact('portfolio'));
    }

    public function storeHolding(Request $request, $portfolioId)
    {
        $request->validate([
            'asset_type'    => 'required|in:treasury_bill,bond,mutual_fund,equity,money_market,fixed_deposit',
            'asset_name'    => 'required|string|max:200',
            'asset_code'    => 'nullable|string|max:50',
            'quantity'      => 'required|numeric|min:0.0001',
            'cost_price'    => 'required|numeric|min:0.0001',
            'current_price' => 'required|numeric|min:0.0001',
            'purchase_date' => 'required|date',
            'maturity_date' => 'nullable|date|after:purchase_date',
            'yield_rate'    => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $portfolio = InvestmentPortfolio::findOrFail($portfolioId);
            $marketValue = $request->quantity * $request->current_price;

            InvestmentHolding::create([
                'portfolio_id'  => $portfolio->id,
                'asset_type'    => $request->asset_type,
                'asset_name'    => $request->asset_name,
                'asset_code'    => $request->asset_code,
                'quantity'      => $request->quantity,
                'cost_price'    => $request->cost_price,
                'current_price' => $request->current_price,
                'market_value'  => $marketValue,
                'purchase_date' => $request->purchase_date,
                'maturity_date' => $request->maturity_date,
                'yield_rate'    => $request->yield_rate,
            ]);

            // Update portfolio totals
            $totalValue = $portfolio->holdings()->where('status', 'active')->sum('market_value') + $marketValue;
            $totalCost = $portfolio->holdings()->where('status', 'active')->sum(DB::raw('quantity * cost_price'))
                         + ($request->quantity * $request->cost_price);
            $portfolio->update([
                'total_value'    => $totalValue,
                'total_cost'     => $totalCost,
                'unrealized_pnl' => $totalValue - $totalCost,
            ]);

            return redirect()->route('wealth.portfolios.show', $portfolio->id)->with('success', 'Holding added.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function performance($id)
    {
        try {
            $portfolio = InvestmentPortfolio::with('holdings', 'customer')->findOrFail($id);
            $holdings = $portfolio->holdings->where('status', 'active');

            $totalCost = $holdings->sum(fn($h) => $h->quantity * $h->cost_price);
            $totalMarketValue = $holdings->sum('market_value');
            $totalPnl = $totalMarketValue - $totalCost;
            $returnPct = $totalCost > 0 ? ($totalPnl / $totalCost) * 100 : 0;
            $avgYield = $holdings->avg('yield_rate') ?? 0;

        } catch (\Exception $e) {
            return redirect()->route('wealth.portfolios')->with('error', 'Portfolio not found.');
        }

        return view('wealth.portfolios.performance', compact(
            'portfolio', 'holdings', 'totalCost', 'totalMarketValue', 'totalPnl', 'returnPct', 'avgYield'
        ));
    }
}
