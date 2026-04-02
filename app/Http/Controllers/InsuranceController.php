<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InsurancePolicy;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InsuranceController extends Controller
{
    public function dashboard()
    {
        $tenantId = auth()->user()->tenant_id;

        $totalPolicies  = InsurancePolicy::where('tenant_id', $tenantId)->count();
        $activePolicies = InsurancePolicy::where('tenant_id', $tenantId)->where('status', 'active')->count();
        $totalPremiums  = InsurancePolicy::where('tenant_id', $tenantId)->where('status', 'active')->sum('premium');
        $totalCoverage  = InsurancePolicy::where('tenant_id', $tenantId)->where('status', 'active')->sum('sum_assured');

        // Policies by type (pie)
        $byType = InsurancePolicy::where('tenant_id', $tenantId)
            ->select('product', DB::raw('COUNT(*) as count'))
            ->groupBy('product')
            ->pluck('count', 'product')
            ->toArray();

        // Policies by provider (bar)
        $byProvider = InsurancePolicy::where('tenant_id', $tenantId)
            ->select('provider', DB::raw('COUNT(*) as count'))
            ->groupBy('provider')
            ->orderByDesc('count')
            ->limit(8)
            ->pluck('count', 'provider')
            ->toArray();

        // Expiring soon (next 30 days)
        $expiringSoon = InsurancePolicy::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereBetween('end_date', [Carbon::today(), Carbon::today()->addDays(30)])
            ->count();

        // Claims count
        $claimsCount = InsurancePolicy::where('tenant_id', $tenantId)
            ->where('status', 'claimed')
            ->count();

        $lapsedCount = InsurancePolicy::where('tenant_id', $tenantId)
            ->where('status', 'lapsed')
            ->count();

        return view('insurance.dashboard', compact(
            'totalPolicies', 'activePolicies', 'totalPremiums', 'totalCoverage',
            'byType', 'byProvider', 'expiringSoon', 'claimsCount', 'lapsedCount'
        ));
    }

    public function index(Request $request)
    {
        $policies = InsurancePolicy::with(['customer', 'loan'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->product, fn($q) => $q->where('product', $request->product))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('insurance.index', compact('policies'));
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('first_name')->get();
        $loans     = Loan::where('status', 'active')->with('customer')->get();
        $preCustomer = $request->customer_id ? Customer::find($request->customer_id) : null;
        $preLoan     = $request->loan_id ? Loan::find($request->loan_id) : null;

        return view('insurance.create', compact('customers', 'loans', 'preCustomer', 'preLoan'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'       => 'required|exists:customers,id',
            'loan_id'           => 'nullable|exists:loans,id',
            'provider'          => 'required|string|max:100',
            'product'           => 'required|in:credit_life,health,asset',
            'sum_assured'       => 'required|numeric|min:0',
            'premium'           => 'required|numeric|min:0',
            'premium_frequency' => 'required|in:monthly,quarterly,annual,single',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after:start_date',
            'notes'             => 'nullable|string',
        ]);

        $data['policy_number'] = 'POL-' . strtoupper(Str::random(8));

        InsurancePolicy::create($data);

        return redirect()->route('insurance.index')->with('success', 'Insurance policy created.');
    }

    public function show(InsurancePolicy $insurance)
    {
        $insurance->load(['customer', 'loan']);
        $insurancePolicy = $insurance;
        return view('insurance.show', compact('insurancePolicy'));
    }

    public function update(Request $request, InsurancePolicy $insurancePolicy)
    {
        $data = $request->validate([
            'status' => 'required|in:active,lapsed,claimed,cancelled',
            'notes'  => 'nullable|string',
        ]);

        $insurancePolicy->update($data);

        return back()->with('success', 'Policy updated.');
    }
}
