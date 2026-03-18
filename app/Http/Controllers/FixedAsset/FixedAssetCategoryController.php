<?php

namespace App\Http\Controllers\FixedAsset;

use App\Http\Controllers\Controller;
use App\Models\FixedAssetCategory;
use Illuminate\Http\Request;

class FixedAssetCategoryController extends Controller
{
    public function index()
    {
        $tenantId   = auth()->user()->tenant_id;
        $categories = FixedAssetCategory::where('tenant_id', $tenantId)
            ->withCount('assets')
            ->latest()
            ->get();

        return view('fixed-assets.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:100',
            'depreciation_method'  => 'required|in:straight_line,declining_balance',
            'useful_life_years'    => 'required|integer|min:1|max:100',
            'residual_rate'        => 'nullable|numeric|min:0|max:100',
            'gl_asset_code'        => 'nullable|string|max:20',
            'gl_depreciation_code' => 'nullable|string|max:20',
        ]);

        FixedAssetCategory::create([
            'tenant_id'            => auth()->user()->tenant_id,
            'name'                 => $data['name'],
            'depreciation_method'  => $data['depreciation_method'],
            'useful_life_years'    => $data['useful_life_years'],
            'residual_rate'        => $data['residual_rate'] ?? 0,
            'gl_asset_code'        => $data['gl_asset_code'] ?? null,
            'gl_depreciation_code' => $data['gl_depreciation_code'] ?? null,
        ]);

        return back()->with('success', 'Asset category created.');
    }

    public function update(Request $request, FixedAssetCategory $fixedAssetCategory)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:100',
            'depreciation_method'  => 'required|in:straight_line,declining_balance',
            'useful_life_years'    => 'required|integer|min:1|max:100',
            'residual_rate'        => 'nullable|numeric|min:0|max:100',
            'gl_asset_code'        => 'nullable|string|max:20',
            'gl_depreciation_code' => 'nullable|string|max:20',
        ]);

        $fixedAssetCategory->update($data);

        return back()->with('success', 'Category updated.');
    }
}
