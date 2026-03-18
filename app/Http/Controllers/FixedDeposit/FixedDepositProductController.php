<?php

namespace App\Http\Controllers\FixedDeposit;

use App\Http\Controllers\Controller;
use App\Models\FixedDepositProduct;
use Illuminate\Http\Request;

class FixedDepositProductController extends Controller
{
    public function index()
    {
        $products = FixedDepositProduct::where('tenant_id', auth()->user()->tenant_id)->latest()->get();

        return view('fixed-deposits.products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                      => 'required|string|max:150',
            'code'                      => 'nullable|string|max:30',
            'description'               => 'nullable|string',
            'interest_rate'             => 'required|numeric|min:0|max:100',
            'interest_payment'          => 'required|in:on_maturity,monthly,quarterly',
            'min_tenure_days'           => 'required|integer|min:1',
            'max_tenure_days'           => 'required|integer|min:1',
            'min_amount'                => 'required|numeric|min:0',
            'max_amount'                => 'nullable|numeric|min:0',
            'early_liquidation_penalty' => 'required|numeric|min:0|max:100',
            'allow_top_up'              => 'boolean',
            'allow_early_liquidation'   => 'boolean',
            'auto_rollover'             => 'boolean',
        ]);

        $data['tenant_id'] = auth()->user()->tenant_id;
        FixedDepositProduct::create($data);

        return back()->with('success', 'FD product created.');
    }

    public function update(Request $request, FixedDepositProduct $fixedDepositProduct)
    {
        $data = $request->validate([
            'name'                      => 'required|string|max:150',
            'interest_rate'             => 'required|numeric|min:0|max:100',
            'interest_payment'          => 'required|in:on_maturity,monthly,quarterly',
            'min_tenure_days'           => 'required|integer|min:1',
            'max_tenure_days'           => 'required|integer|min:1',
            'min_amount'                => 'required|numeric|min:0',
            'max_amount'                => 'nullable|numeric|min:0',
            'early_liquidation_penalty' => 'required|numeric|min:0|max:100',
            'status'                    => 'required|in:active,inactive',
        ]);

        $fixedDepositProduct->update($data);

        return back()->with('success', 'FD product updated.');
    }
}
