<?php

namespace App\Http\Controllers;

use App\Models\SavingsProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SavingsProductController extends Controller
{
    /**
     * Display a listing of the savings products.
     */
    public function index()
    {
        $products = SavingsProduct::latest()->paginate(15);
        return view('savings_products.index', compact('products'));
    }

    /**
     * Show the form for creating a new savings product.
     */
    public function create()
    {
        return view('savings_products.create');
    }

    /**
     * Store a newly created savings product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:savings_products,code,NULL,id,tenant_id,' . auth()->user()->tenant_id,
            'description' => 'nullable|string',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'product_type' => 'required|in:current,savings,fixed',
            'min_opening' => 'required|numeric|min:0',
            'min_balance' => 'required|numeric|min:0',
            'monthly_fee' => 'required|numeric|min:0',
        ]);

        $product = SavingsProduct::create($validated);

        return redirect()->route('savings-products.index')
            ->with('success', 'Savings product created successfully.');
    }

    /**
     * Show the form for editing the specified savings product.
     */
    public function edit(SavingsProduct $savingsProduct)
    {
        return view('savings_products.edit', compact('savingsProduct'));
    }

    /**
     * Update the specified savings product in storage.
     */
    public function update(Request $request, SavingsProduct $savingsProduct)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'min_opening' => 'required|numeric|min:0',
            'min_balance' => 'required|numeric|min:0',
            'monthly_fee' => 'required|numeric|min:0',
        ]);

        $savingsProduct->update($validated);

        return redirect()->route('savings-products.index')
            ->with('success', 'Savings product updated successfully.');
    }
}
