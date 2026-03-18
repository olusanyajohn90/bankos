<?php

namespace App\Http\Controllers;

use App\Models\LoanProduct;
use Illuminate\Http\Request;

class LoanProductController extends Controller
{
    /**
     * Display a listing of the loan products.
     */
    public function index()
    {
        $products = LoanProduct::latest()->paginate(15);
        return view('loan_products.index', compact('products'));
    }

    /**
     * Show the form for creating a new loan product.
     */
    public function create()
    {
        return view('loan_products.create');
    }

    /**
     * Store a newly created loan product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:loan_products,code,NULL,id,tenant_id,' . auth()->user()->tenant_id,
            'description' => 'nullable|string',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'interest_type' => 'required|in:flat,reducing_balance',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0|gte:min_amount',
            'min_tenure' => 'required|integer|min:1',
            'max_tenure' => 'required|integer|min:1|gte:min_tenure',
            'duration_type' => 'required|in:days,weeks,months,years',
            'requires_collateral' => 'boolean',
            'require_guarantor' => 'boolean',
            'max_dti_ratio' => 'required|numeric|min:0|max:100',
        ]);

        $product = LoanProduct::create($validated);

        return redirect()->route('loan-products.index')
            ->with('success', 'Loan product created successfully.');
    }

    /**
     * Show the form for editing the specified loan product.
     */
    public function edit(LoanProduct $loanProduct)
    {
        return view('loan_products.edit', compact('loanProduct'));
    }

    /**
     * Update the specified loan product in storage.
     */
    public function update(Request $request, LoanProduct $loanProduct)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:loan_products,code,' . $loanProduct->id . ',id,tenant_id,' . auth()->user()->tenant_id,
            'description' => 'nullable|string',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'interest_type' => 'required|in:flat,reducing_balance',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0|gte:min_amount',
            'min_tenure' => 'required|integer|min:1',
            'max_tenure' => 'required|integer|min:1|gte:min_tenure',
            'duration_type' => 'required|in:days,weeks,months,years',
            'requires_collateral' => 'boolean',
            'require_guarantor' => 'boolean',
            'max_dti_ratio' => 'required|numeric|min:0|max:100',
        ]);

        $loanProduct->update($validated);

        return redirect()->route('loan-products.index')
            ->with('success', 'Loan product updated successfully.');
    }
}
