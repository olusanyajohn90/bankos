<?php

namespace App\Http\Controllers;

use App\Models\FeeRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeeRuleController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;

        $rules = FeeRule::where('tenant_id', $tenantId)
            ->orderBy('transaction_type')
            ->orderBy('name')
            ->get();

        $grouped = $rules->groupBy('transaction_type');

        return view('fee-rules.index', compact('grouped'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                    => 'required|string|max:100',
            'transaction_type'        => 'required|string|max:50',
            'account_type'            => 'nullable|string|max:30',
            'fee_type'                => 'required|in:flat,percentage',
            'amount'                  => 'required|numeric|min:0',
            'min_fee'                 => 'nullable|numeric|min:0',
            'max_fee'                 => 'nullable|numeric|min:0',
            'min_transaction_amount'  => 'nullable|numeric|min:0',
            'max_transaction_amount'  => 'nullable|numeric|min:0',
            'waivable'                => 'boolean',
        ]);

        $data['tenant_id'] = Auth::user()->tenant_id;
        $data['is_active']  = true;
        $data['account_type'] = ($data['account_type'] ?? '') === '' ? null : $data['account_type'];
        $data['waivable'] = $request->boolean('waivable', true);

        FeeRule::create($data);

        return redirect()->route('fee-rules.index')->with('success', 'Fee rule created successfully.');
    }

    public function update(Request $request, $id)
    {
        $tenantId = Auth::user()->tenant_id;

        $rule = FeeRule::where('tenant_id', $tenantId)->findOrFail($id);

        $data = $request->validate([
            'name'                    => 'required|string|max:100',
            'transaction_type'        => 'required|string|max:50',
            'account_type'            => 'nullable|string|max:30',
            'fee_type'                => 'required|in:flat,percentage',
            'amount'                  => 'required|numeric|min:0',
            'min_fee'                 => 'nullable|numeric|min:0',
            'max_fee'                 => 'nullable|numeric|min:0',
            'min_transaction_amount'  => 'nullable|numeric|min:0',
            'max_transaction_amount'  => 'nullable|numeric|min:0',
            'waivable'                => 'boolean',
        ]);

        $data['account_type'] = ($data['account_type'] ?? '') === '' ? null : $data['account_type'];
        $data['waivable'] = $request->boolean('waivable', true);

        $rule->update($data);

        return redirect()->route('fee-rules.index')->with('success', 'Fee rule updated successfully.');
    }

    public function destroy($id)
    {
        $tenantId = Auth::user()->tenant_id;

        $rule = FeeRule::where('tenant_id', $tenantId)->findOrFail($id);
        $rule->delete();

        return redirect()->route('fee-rules.index')->with('success', 'Fee rule deleted.');
    }

    public function toggle($id)
    {
        $tenantId = Auth::user()->tenant_id;

        $rule = FeeRule::where('tenant_id', $tenantId)->findOrFail($id);
        $rule->update(['is_active' => ! $rule->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $rule->is_active,
        ]);
    }
}
