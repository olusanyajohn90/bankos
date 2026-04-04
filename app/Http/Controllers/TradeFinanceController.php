<?php

namespace App\Http\Controllers;

use App\Models\TradeFinanceInstrument;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TradeFinanceController extends Controller
{
    public function dashboard()
    {
        try {
            $totalInstruments = TradeFinanceInstrument::count();
            $activeInstruments = TradeFinanceInstrument::whereIn('status', ['issued', 'amended'])->count();
            $totalExposure = TradeFinanceInstrument::whereIn('status', ['issued', 'amended'])->sum('amount');
            $totalCommissions = TradeFinanceInstrument::sum('commission_amount');

            $byType = TradeFinanceInstrument::select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                ->groupBy('type')
                ->get();

            $byStatus = TradeFinanceInstrument::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get();

            // Expiring in 30 days
            $expiringSoon = TradeFinanceInstrument::whereIn('status', ['issued', 'amended'])
                ->where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>=', now())
                ->count();

            // Monthly issuance trend (last 6 months)
            $monthlyTrend = TradeFinanceInstrument::select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(amount) as total')
                )
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM')"))
                ->orderBy('month')
                ->get();

            $draftCount = TradeFinanceInstrument::where('status', 'draft')->count();
            $avgInstrumentValue = TradeFinanceInstrument::avg('amount') ?? 0;
            $topBeneficiaries = TradeFinanceInstrument::select('beneficiary_name', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                ->groupBy('beneficiary_name')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

        } catch (\Exception $e) {
            return view('trade-finance.dashboard', [
                'error' => $e->getMessage(),
                'totalInstruments' => 0, 'activeInstruments' => 0, 'totalExposure' => 0,
                'totalCommissions' => 0, 'byType' => collect(), 'byStatus' => collect(),
                'expiringSoon' => 0, 'monthlyTrend' => collect(), 'draftCount' => 0,
                'avgInstrumentValue' => 0, 'topBeneficiaries' => collect(),
            ]);
        }

        return view('trade-finance.dashboard', compact(
            'totalInstruments', 'activeInstruments', 'totalExposure', 'totalCommissions',
            'byType', 'byStatus', 'expiringSoon', 'monthlyTrend', 'draftCount',
            'avgInstrumentValue', 'topBeneficiaries'
        ));
    }

    public function index(Request $request)
    {
        try {
            $query = TradeFinanceInstrument::with('customer', 'creator')->latest();

            if ($request->filled('type')) $query->where('type', $request->type);
            if ($request->filled('status')) $query->where('status', $request->status);
            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('reference', 'ilike', "%{$request->search}%")
                      ->orWhere('beneficiary_name', 'ilike', "%{$request->search}%");
                });
            }

            $instruments = $query->paginate(20)->withQueryString();
        } catch (\Exception $e) {
            $instruments = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('trade-finance.index', compact('instruments'));
    }

    public function create()
    {
        try {
            $customers = Customer::select('id', 'first_name', 'last_name', 'business_name')->limit(500)->get();
        } catch (\Exception $e) {
            $customers = collect();
        }
        return view('trade-finance.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'type'             => 'required|in:letter_of_credit,bank_guarantee,bill_for_collection,invoice_discounting',
            'beneficiary_name' => 'required|string|max:200',
            'beneficiary_bank' => 'nullable|string|max:200',
            'amount'           => 'required|numeric|min:0.01',
            'currency'         => 'required|string|max:3',
            'issue_date'       => 'required|date',
            'expiry_date'      => 'required|date|after:issue_date',
            'purpose'          => 'nullable|string',
            'terms'            => 'nullable|string',
            'commission_rate'  => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $commissionAmount = ($request->amount * ($request->commission_rate ?? 0)) / 100;

            TradeFinanceInstrument::create([
                'reference'         => 'TF-' . strtoupper(Str::random(8)),
                'customer_id'       => $request->customer_id,
                'type'              => $request->type,
                'beneficiary_name'  => $request->beneficiary_name,
                'beneficiary_bank'  => $request->beneficiary_bank,
                'amount'            => $request->amount,
                'currency'          => $request->currency,
                'issue_date'        => $request->issue_date,
                'expiry_date'       => $request->expiry_date,
                'purpose'           => $request->purpose,
                'terms'             => $request->terms,
                'commission_rate'   => $request->commission_rate ?? 0,
                'commission_amount' => $commissionAmount,
                'created_by'        => auth()->id(),
            ]);

            return redirect()->route('trade-finance.index')->with('success', 'Trade finance instrument created.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $instrument = TradeFinanceInstrument::with('customer', 'creator')->findOrFail($id);
        } catch (\Exception $e) {
            return redirect()->route('trade-finance.index')->with('error', 'Instrument not found.');
        }
        return view('trade-finance.show', compact('instrument'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,issued,amended,utilized,expired,cancelled',
        ]);

        try {
            $instrument = TradeFinanceInstrument::findOrFail($id);
            $instrument->update(['status' => $request->status]);
            return back()->with('success', 'Status updated to ' . $request->status);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }
}
