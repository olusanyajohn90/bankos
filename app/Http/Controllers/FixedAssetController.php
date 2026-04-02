<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\FixedAsset;
use App\Models\FixedAssetCategory;
use App\Services\FixedAssetService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FixedAssetController extends Controller
{
    public function dashboard()
    {
        $tenantId = auth()->user()->tenant_id;

        $totalAssets       = FixedAsset::where('tenant_id', $tenantId)->count();
        $activeAssets      = FixedAsset::where('tenant_id', $tenantId)->active()->count();
        $totalCost         = FixedAsset::where('tenant_id', $tenantId)->active()->sum('purchase_cost');
        $totalBookValue    = FixedAsset::where('tenant_id', $tenantId)->active()->sum('current_book_value');
        $totalDepreciation = FixedAsset::where('tenant_id', $tenantId)->active()->sum('accumulated_depreciation');

        // Fully depreciated
        $fullyDepreciated = FixedAsset::where('tenant_id', $tenantId)
            ->active()
            ->whereColumn('current_book_value', '<=', 'residual_value')
            ->count();

        // Monthly depreciation charge (sum of all active assets)
        $monthlyDeprCharge = FixedAsset::where('tenant_id', $tenantId)->active()->get()
            ->sum(fn($a) => $a->monthly_depreciation);

        // Assets by category (bar chart)
        $byCategory = FixedAsset::where('fixed_assets.tenant_id', $tenantId)
            ->where('fixed_assets.status', 'active')
            ->join('fixed_asset_categories', 'fixed_assets.category_id', '=', 'fixed_asset_categories.id')
            ->select('fixed_asset_categories.name', DB::raw('COUNT(*) as count'), DB::raw('SUM(fixed_assets.current_book_value) as total_nbv'))
            ->groupBy('fixed_asset_categories.name')
            ->orderByDesc('total_nbv')
            ->get();

        // Recently revalued (if revalue tracking exists)
        $recentlyRevalued = FixedAsset::where('tenant_id', $tenantId)
            ->active()
            ->whereNotNull('last_depreciation_date')
            ->orderByDesc('last_depreciation_date')
            ->limit(5)
            ->count();

        // Disposed assets
        $disposedCount = FixedAsset::where('tenant_id', $tenantId)->where('status', 'disposed')->count();

        return view('fixed-assets.dashboard', compact(
            'totalAssets', 'activeAssets', 'totalCost', 'totalBookValue', 'totalDepreciation',
            'fullyDepreciated', 'monthlyDeprCharge', 'byCategory', 'recentlyRevalued', 'disposedCount'
        ));
    }

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        /** @var LengthAwarePaginator $assets */
        $assets = FixedAsset::where('tenant_id', $tenantId)
            ->with('category', 'branch')
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('search'), fn ($q, $s) => $q->where(function ($q2) use ($s) {
                $q2->where('name', 'like', '%' . $s . '%')
                   ->orWhere('asset_tag', 'like', '%' . $s . '%');
            }))
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        $categories = FixedAssetCategory::where('tenant_id', $tenantId)->get();

        $summary = [
            'total_cost'   => FixedAsset::where('tenant_id', $tenantId)->active()->sum('purchase_cost'),
            'total_nbv'    => FixedAsset::where('tenant_id', $tenantId)->active()->sum('current_book_value'),
            'total_depr'   => FixedAsset::where('tenant_id', $tenantId)->active()->sum('accumulated_depreciation'),
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
            'name'                => 'required|string|max:255',
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
            'residual_value'           => $residual,
            'useful_life_years'        => $data['useful_life_years'],
            'depreciation_method'      => $data['depreciation_method'],
            'accumulated_depreciation' => 0,
            'current_book_value'       => $cost,
            'branch_id'                => $data['branch_id'] ?? null,
            'purchased_by'             => auth()->id(),
        ]);

        return redirect()->route('fixed-assets.index')->with('success', 'Fixed asset registered.');
    }

    public function show(FixedAsset $fixedAsset)
    {
        $fixedAsset->load('category', 'branch', 'purchasedBy');
        $schedule = $fixedAsset->depreciation_schedule;

        return view('fixed-assets.show', compact('fixedAsset', 'schedule'));
    }

    public function dispose(Request $request, FixedAsset $fixedAsset, FixedAssetService $service)
    {
        $data = $request->validate([
            'disposed_at'    => 'required|date',
            'disposal_value' => 'nullable|numeric|min:0',
            'disposal_notes' => 'nullable|string|max:500',
        ]);

        $service->dispose(
            $fixedAsset,
            $data['disposed_at'],
            (float) ($data['disposal_value'] ?? 0)
        );

        return back()->with('success', 'Asset disposed successfully.');
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:100',
            'useful_life_years'   => 'required|integer|min:1|max:100',
            'depreciation_method' => 'required|in:straight_line,declining_balance',
            'residual_rate'       => 'nullable|numeric|min:0|max:100',
        ]);

        FixedAssetCategory::create([
            'tenant_id'           => auth()->user()->tenant_id,
            'name'                => $data['name'],
            'useful_life_years'   => $data['useful_life_years'],
            'depreciation_method' => $data['depreciation_method'],
            'residual_rate'       => $data['residual_rate'] ?? 0,
        ]);

        return back()->with('success', 'Asset category created.');
    }
}
