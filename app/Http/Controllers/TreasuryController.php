<?php

namespace App\Http\Controllers;

use App\Models\TreasuryPlacement;
use App\Models\TreasuryFxDeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TreasuryController extends Controller
{
    public function dashboard()
    {
        $tenantId = auth()->user()->tenant_id;

        try {
            $totalPlacements = TreasuryPlacement::count();
            $activePlacements = TreasuryPlacement::where('status', 'active')->count();
            $totalPlacementValue = TreasuryPlacement::where('status', 'active')->sum('principal');
            $totalAccruedInterest = TreasuryPlacement::where('status', 'active')->sum('accrued_interest');
            $totalExpectedInterest = TreasuryPlacement::where('status', 'active')->sum('expected_interest');
            $avgInterestRate = TreasuryPlacement::where('status', 'active')->avg('interest_rate') ?? 0;

            // FX metrics
            $totalFxDeals = TreasuryFxDeal::count();
            $pendingFxDeals = TreasuryFxDeal::where('status', 'pending')->count();
            $totalFxVolume = TreasuryFxDeal::where('status', 'settled')->sum('counter_amount');
            $todayFxVolume = TreasuryFxDeal::where('status', 'settled')
                ->whereDate('settlement_date', today())->sum('counter_amount');

            // Maturity profile (next 30 days)
            $maturityProfile = TreasuryPlacement::where('status', 'active')
                ->where('maturity_date', '<=', now()->addDays(30))
                ->where('maturity_date', '>=', now())
                ->select(
                    DB::raw("TO_CHAR(maturity_date, 'YYYY-MM-DD') as date"),
                    DB::raw('SUM(principal) as total')
                )
                ->groupBy(DB::raw("TO_CHAR(maturity_date, 'YYYY-MM-DD')"))
                ->orderBy('date')
                ->get();

            // Placements by type
            $placementsByType = TreasuryPlacement::where('status', 'active')
                ->select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(principal) as total'))
                ->groupBy('type')
                ->get();

            // FX deals by currency pair
            $fxByCurrency = TreasuryFxDeal::select('currency_pair', DB::raw('COUNT(*) as count'), DB::raw('SUM(counter_amount) as volume'))
                ->groupBy('currency_pair')
                ->orderByDesc('volume')
                ->limit(10)
                ->get();

            // FX deals by deal type
            $fxByType = TreasuryFxDeal::select('deal_type', DB::raw('COUNT(*) as count'))
                ->groupBy('deal_type')
                ->get();

            // Maturing soon (next 7 days)
            $maturingSoon = TreasuryPlacement::where('status', 'active')
                ->where('maturity_date', '<=', now()->addDays(7))
                ->where('maturity_date', '>=', now())
                ->count();

        } catch (\Exception $e) {
            return view('treasury.dashboard', [
                'error' => 'Unable to load treasury data: ' . $e->getMessage(),
                'totalPlacements' => 0, 'activePlacements' => 0, 'totalPlacementValue' => 0,
                'totalAccruedInterest' => 0, 'totalExpectedInterest' => 0, 'avgInterestRate' => 0,
                'totalFxDeals' => 0, 'pendingFxDeals' => 0, 'totalFxVolume' => 0, 'todayFxVolume' => 0,
                'maturityProfile' => collect(), 'placementsByType' => collect(),
                'fxByCurrency' => collect(), 'fxByType' => collect(), 'maturingSoon' => 0,
            ]);
        }

        return view('treasury.dashboard', compact(
            'totalPlacements', 'activePlacements', 'totalPlacementValue',
            'totalAccruedInterest', 'totalExpectedInterest', 'avgInterestRate',
            'totalFxDeals', 'pendingFxDeals', 'totalFxVolume', 'todayFxVolume',
            'maturityProfile', 'placementsByType', 'fxByCurrency', 'fxByType', 'maturingSoon'
        ));
    }

    public function placements(Request $request)
    {
        try {
            $query = TreasuryPlacement::with('creator')->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            if ($request->filled('from')) {
                $query->whereDate('start_date', '>=', $request->from);
            }
            if ($request->filled('to')) {
                $query->whereDate('maturity_date', '<=', $request->to);
            }

            $placements = $query->paginate(20)->withQueryString();
        } catch (\Exception $e) {
            $placements = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('treasury.placements.index', compact('placements'));
    }

    public function createPlacement()
    {
        return view('treasury.placements.create');
    }

    public function storePlacement(Request $request)
    {
        $request->validate([
            'type'          => 'required|in:placement,borrowing',
            'counterparty'  => 'required|string|max:200',
            'principal'     => 'required|numeric|min:0.01',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'start_date'    => 'required|date',
            'maturity_date' => 'required|date|after:start_date',
            'notes'         => 'nullable|string',
        ]);

        try {
            $start = \Carbon\Carbon::parse($request->start_date);
            $maturity = \Carbon\Carbon::parse($request->maturity_date);
            $tenorDays = $start->diffInDays($maturity);
            $expectedInterest = ($request->principal * ($request->interest_rate / 100) * $tenorDays) / 365;

            TreasuryPlacement::create([
                'reference'         => 'TRP-' . strtoupper(Str::random(8)),
                'type'              => $request->type,
                'counterparty'      => $request->counterparty,
                'principal'         => $request->principal,
                'interest_rate'     => $request->interest_rate,
                'start_date'        => $request->start_date,
                'maturity_date'     => $request->maturity_date,
                'tenor_days'        => $tenorDays,
                'expected_interest' => $expectedInterest,
                'notes'             => $request->notes,
                'created_by'        => auth()->id(),
            ]);

            return redirect()->route('treasury.placements')->with('success', 'Placement created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create placement: ' . $e->getMessage());
        }
    }

    public function showPlacement($id)
    {
        try {
            $placement = TreasuryPlacement::with('creator')->findOrFail($id);
        } catch (\Exception $e) {
            return redirect()->route('treasury.placements')->with('error', 'Placement not found.');
        }

        return view('treasury.placements.show', compact('placement'));
    }

    public function fxDeals(Request $request)
    {
        try {
            $query = TreasuryFxDeal::with('dealer')->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('deal_type')) {
                $query->where('deal_type', $request->deal_type);
            }
            if ($request->filled('direction')) {
                $query->where('direction', $request->direction);
            }

            $deals = $query->paginate(20)->withQueryString();
        } catch (\Exception $e) {
            $deals = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('treasury.fx.index', compact('deals'));
    }

    public function createFxDeal()
    {
        return view('treasury.fx.create');
    }

    public function storeFxDeal(Request $request)
    {
        $request->validate([
            'deal_type'       => 'required|in:spot,forward,swap',
            'direction'       => 'required|in:buy,sell',
            'currency_pair'   => 'required|string|max:10',
            'amount'          => 'required|numeric|min:0.01',
            'rate'            => 'required|numeric|min:0.000001',
            'trade_date'      => 'required|date',
            'settlement_date' => 'required|date|after_or_equal:trade_date',
            'counterparty'    => 'nullable|string|max:200',
        ]);

        try {
            $counterAmount = $request->amount * $request->rate;

            TreasuryFxDeal::create([
                'reference'       => 'FX-' . strtoupper(Str::random(8)),
                'deal_type'       => $request->deal_type,
                'direction'       => $request->direction,
                'currency_pair'   => strtoupper($request->currency_pair),
                'amount'          => $request->amount,
                'rate'            => $request->rate,
                'counter_amount'  => $counterAmount,
                'trade_date'      => $request->trade_date,
                'settlement_date' => $request->settlement_date,
                'counterparty'    => $request->counterparty,
                'dealer_id'       => auth()->id(),
            ]);

            return redirect()->route('treasury.fx-deals')->with('success', 'FX deal booked successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to book FX deal: ' . $e->getMessage());
        }
    }

    public function showFxDeal($id)
    {
        try {
            $deal = TreasuryFxDeal::with('dealer')->findOrFail($id);
        } catch (\Exception $e) {
            return redirect()->route('treasury.fx-deals')->with('error', 'FX deal not found.');
        }

        return view('treasury.fx.show', compact('deal'));
    }
}
