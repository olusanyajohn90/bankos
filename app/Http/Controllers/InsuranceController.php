<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InsurancePolicy;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InsuranceController extends Controller
{
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
