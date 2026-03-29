<?php

namespace App\Http\Controllers\FixedAsset;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\FixedAsset;
use App\Models\FixedAssetCategory;
use App\Models\FixedAssetRevaluation;
use App\Services\FixedAsset\FixedAssetService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FixedAssetController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $assets = FixedAsset::where('tenant_id', $tenantId)
            ->with('category', 'branch')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->search, fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('name', 'like', '%' . $request->search . '%')
                   ->orWhere('asset_tag', 'like', '%' . $request->search . '%');
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = FixedAssetCategory::where('tenant_id', $tenantId)->get();

        $summary = [
            'total_cost'  => FixedAsset::where('tenant_id', $tenantId)->active()->sum('purchase_cost'),
            'total_nbv'   => FixedAsset::where('tenant_id', $tenantId)->active()->sum('current_book_value'),
            'total_depr'  => FixedAsset::where('tenant_id', $tenantId)->active()->sum('accumulated_depreciation'),
            'active_count' => FixedAsset::where('tenant_id', $tenantId)->active()->count(),
        ];

        return view('fixed-assets.index', compact('assets', 'categories', 'summary'));
    }

    public function create()
    {
        $tenantId   = auth()->user()->tenant_id;
        $categories = FixedAssetCategory::where('tenant_id', $tenantId)->get();
        $branches   = Branch::where('tenant_id', $tenantId)->get();

        return view('fixed-assets.create', compact('categories', 'branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'         => 'required|uuid',
            'asset_tag'           => 'nullable|string|max:50',
            'name'                => 'required|string|max:150',
            'description'         => 'nullable|string',
            'purchase_date'       => 'required|date',
            'purchase_cost'       => 'required|numeric|min:0.01',
            'residual_value'      => 'nullable|numeric|min:0',
            'useful_life_years'   => 'required|integer|min:1|max:100',
            'depreciation_method' => 'required|in:straight_line,declining_balance',
            'branch_id'           => 'nullable|uuid',
        ]);

        $cost     = (float) $data['purchase_cost'];
        $residual = (float) ($data['residual_value'] ?? 0);

        FixedAsset::create([
            'tenant_id'                => auth()->user()->tenant_id,
            'category_id'              => $data['category_id'],
            'asset_tag'                => $data['asset_tag'] ?? null,
            'name'                     => $data['name'],
            'description'              => $data['description'] ?? null,
            'purchase_date'            => $data['purchase_date'],
            'purchase_cost'            => $cost,
            'current_book_value'       => $cost,
            'accumulated_depreciation' => 0,
            'depreciation_method'      => $data['depreciation_method'],
            'useful_life_years'        => $data['useful_life_years'],
            'residual_value'           => $residual,
            'branch_id'                => $data['branch_id'] ?? null,
            'purchased_by'             => auth()->id(),
        ]);

        return redirect()->route('fixed-assets.index')->with('success', 'Fixed asset registered successfully.');
    }

    public function show(FixedAsset $fixedAsset)
    {
        $fixedAsset->load('category', 'branch', 'purchasedBy');
        $schedule = $fixedAsset->depreciation_schedule;

        $revaluations = FixedAssetRevaluation::where('fixed_asset_id', $fixedAsset->id)
            ->with('revaluedBy')
            ->orderByDesc('revalued_at')
            ->get();

        return view('fixed-assets.show', compact('fixedAsset', 'schedule', 'revaluations'));
    }

    public function update(Request $request, FixedAsset $fixedAsset)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'asset_tag'   => 'nullable|string|max:50',
            'branch_id'   => 'nullable|uuid',
        ]);

        $fixedAsset->update($data);

        return back()->with('success', 'Asset updated successfully.');
    }

    public function dispose(Request $request, FixedAsset $fixedAsset, FixedAssetService $service)
    {
        $data = $request->validate([
            'disposal_value' => 'required|numeric|min:0',
            'disposal_notes' => 'nullable|string',
            'disposed_at'    => 'required|date',
        ]);

        $service->dispose(
            $fixedAsset,
            (float) $data['disposal_value'],
            $data['disposal_notes'] ?? '',
            Carbon::parse($data['disposed_at'])
        );

        return back()->with('success', 'Asset disposed successfully.');
    }

    public function revalue(Request $request, FixedAsset $fixedAsset, FixedAssetService $service)
    {
        $data = $request->validate([
            'new_book_value' => 'required|numeric|min:0',
            'reason'         => 'nullable|string|max:500',
        ]);

        $service->revalue(
            $fixedAsset,
            (float) $data['new_book_value'],
            $data['reason'] ?? null,
            auth()->id()
        );

        return back()->with('success', 'Asset revalued successfully.');
    }
}
