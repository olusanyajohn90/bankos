<?php

namespace App\Http\Controllers;

use App\Models\TransferProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransferProviderController extends Controller
{
    // ── List ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $tenantId = Auth::user()->tenant_id;

        $providers = TransferProvider::where('tenant_id', $tenantId)
            ->orderByDesc('is_default')
            ->orderByDesc('priority')
            ->orderBy('name')
            ->get();

        return view('transfer-providers.index', compact('providers'));
    }

    // ── Create form ─────────────────────────────────────────────────────────

    public function create()
    {
        return view('transfer-providers.create');
    }

    // ── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'code'           => 'required|string|max:30|regex:/^[a-z0-9_-]+$/',
            'provider_class' => 'required|string|max:255',
            'config'         => 'nullable|json',
            'is_active'      => 'boolean',
            'is_default'     => 'boolean',
            'max_amount'     => 'nullable|numeric|min:0',
            'min_amount'     => 'nullable|numeric|min:0',
            'flat_fee'       => 'nullable|numeric|min:0',
            'percentage_fee' => 'nullable|numeric|min:0|max:1',
            'fee_cap'        => 'nullable|numeric|min:0',
            'priority'       => 'nullable|integer|min:0',
        ]);

        $tenantId = Auth::user()->tenant_id;

        $data['tenant_id']      = $tenantId;
        $data['config']         = $data['config'] ? json_decode($data['config'], true) : null;
        $data['is_active']      = $request->boolean('is_active', true);
        $data['is_default']     = $request->boolean('is_default', false);
        $data['min_amount']     = $data['min_amount'] ?? 0;
        $data['flat_fee']       = $data['flat_fee'] ?? 0;
        $data['percentage_fee'] = $data['percentage_fee'] ?? 0;
        $data['priority']       = $data['priority'] ?? 0;

        // If setting as default, unset other defaults for this tenant
        if ($data['is_default']) {
            TransferProvider::where('tenant_id', $tenantId)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        TransferProvider::create($data);

        return redirect()->route('transfer-providers.index')
            ->with('success', 'Transfer provider created successfully.');
    }

    // ── Edit form ────────────────────────────────────────────────────────────

    public function edit(TransferProvider $provider)
    {
        return view('transfer-providers.edit', compact('provider'));
    }

    // ── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, TransferProvider $provider)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'code'           => 'required|string|max:30|regex:/^[a-z0-9_-]+$/',
            'provider_class' => 'required|string|max:255',
            'config'         => 'nullable|json',
            'is_active'      => 'boolean',
            'is_default'     => 'boolean',
            'max_amount'     => 'nullable|numeric|min:0',
            'min_amount'     => 'nullable|numeric|min:0',
            'flat_fee'       => 'nullable|numeric|min:0',
            'percentage_fee' => 'nullable|numeric|min:0|max:1',
            'fee_cap'        => 'nullable|numeric|min:0',
            'priority'       => 'nullable|integer|min:0',
        ]);

        $tenantId = Auth::user()->tenant_id;

        $data['config']         = $data['config'] ? json_decode($data['config'], true) : null;
        $data['is_active']      = $request->boolean('is_active', true);
        $data['is_default']     = $request->boolean('is_default', false);
        $data['min_amount']     = $data['min_amount'] ?? 0;
        $data['flat_fee']       = $data['flat_fee'] ?? 0;
        $data['percentage_fee'] = $data['percentage_fee'] ?? 0;
        $data['priority']       = $data['priority'] ?? 0;

        // If setting as default, unset other defaults for this tenant
        if ($data['is_default']) {
            TransferProvider::where('tenant_id', $tenantId)
                ->where('is_default', true)
                ->where('id', '!=', $provider->id)
                ->update(['is_default' => false]);
        }

        $provider->update($data);

        return redirect()->route('transfer-providers.index')
            ->with('success', 'Transfer provider updated successfully.');
    }

    // ── Toggle Active ────────────────────────────────────────────────────────

    public function toggleActive(TransferProvider $provider)
    {
        $provider->update(['is_active' => !$provider->is_active]);

        $status = $provider->is_active ? 'activated' : 'deactivated';

        return redirect()->route('transfer-providers.index')
            ->with('success', "Provider \"{$provider->name}\" {$status}.");
    }

    // ── Set Default ──────────────────────────────────────────────────────────

    public function setDefault(TransferProvider $provider)
    {
        $tenantId = Auth::user()->tenant_id;

        // Unset all other defaults
        TransferProvider::where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        $provider->update([
            'is_default' => true,
            'is_active'  => true, // Ensure default is also active
        ]);

        return redirect()->route('transfer-providers.index')
            ->with('success', "\"{$provider->name}\" is now the default transfer provider.");
    }
}
