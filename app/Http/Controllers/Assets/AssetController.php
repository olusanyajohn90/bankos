<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\AssetMaintenanceLog;
use App\Models\ProcurementRequest;
use App\Models\StaffProfile;
use App\Services\ApprovalService;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function __construct(private ApprovalService $approvalService) {}

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = Asset::where('tenant_id', $tenantId)
            ->with(['category', 'branch', 'currentAssignment.staffProfile.user'])
            ->latest();

        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('branch_id'))   $query->where('branch_id', $request->branch_id);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('asset_tag', 'like', "%{$s}%")
                  ->orWhere('serial_number', 'like', "%{$s}%");
            });
        }

        $assets     = $query->paginate(25)->withQueryString();
        $categories = AssetCategory::where('tenant_id', $tenantId)->where('is_active', true)->get();
        $branches   = \App\Models\Branch::where('tenant_id', $tenantId)->get();

        // Stats
        $total      = Asset::where('tenant_id', $tenantId)->count();
        $available  = Asset::where('tenant_id', $tenantId)->where('status', 'available')->count();
        $assigned   = Asset::where('tenant_id', $tenantId)->where('status', 'assigned')->count();
        $maint      = Asset::where('tenant_id', $tenantId)->where('status', 'under_maintenance')->count();
        $totalValue = Asset::where('tenant_id', $tenantId)->sum('current_value');

        return view('assets.index', compact(
            'assets', 'categories', 'branches',
            'total', 'available', 'assigned', 'maint', 'totalValue'
        ));
    }

    // ── Show asset ────────────────────────────────────────────────────────────

    public function show(Asset $asset)
    {
        abort_unless($asset->tenant_id === auth()->user()->tenant_id, 403);
        $asset->load(['category', 'branch', 'currentAssignment.staffProfile.user', 'assignments.staffProfile.user', 'maintenanceLogs']);
        $staff = StaffProfile::where('tenant_id', $asset->tenant_id)->with('user')->where('status', 'active')->get();
        return view('assets.show', compact('asset', 'staff'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:200',
            'category_id'    => 'required|uuid|exists:asset_categories,id',
            'serial_number'  => 'nullable|string|max:100',
            'model'          => 'nullable|string|max:150',
            'manufacturer'   => 'nullable|string|max:100',
            'vendor'         => 'nullable|string|max:150',
            'purchase_date'  => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'warranty_expiry'=> 'nullable|date',
            'condition'      => 'nullable|in:new,good,fair,poor,beyond_repair',
            'branch_id'      => 'nullable|uuid',
            'location'       => 'nullable|string|max:200',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $count    = Asset::where('tenant_id', $tenantId)->count() + 1;
        $tag      = 'AST-' . now()->format('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

        $data = $request->only([
            'name','category_id','serial_number','model','manufacturer','vendor',
            'purchase_date','purchase_price','warranty_expiry','condition','branch_id','location','notes'
        ]);
        $data['tenant_id']      = $tenantId;
        $data['asset_tag']      = $tag;
        $data['current_value']  = $request->purchase_price;
        $data['status']         = 'available';
        $data['added_by']       = auth()->id();

        $asset = Asset::create($data);
        return back()->with('success', "Asset {$tag} registered.");
    }

    // ── Assign ────────────────────────────────────────────────────────────────

    public function assign(Request $request, Asset $asset)
    {
        abort_unless($asset->tenant_id === auth()->user()->tenant_id, 403);
        $request->validate([
            'staff_profile_id'          => 'required|uuid|exists:staff_profiles,id',
            'condition_at_assignment'    => 'required|in:new,good,fair,poor',
            'notes'                      => 'nullable|string|max:500',
        ]);

        if ($asset->status === 'assigned') {
            return back()->with('error', 'Asset is already assigned. Return it first.');
        }

        AssetAssignment::create([
            'tenant_id'               => $asset->tenant_id,
            'asset_id'                => $asset->id,
            'staff_profile_id'        => $request->staff_profile_id,
            'assigned_date'           => now()->toDateString(),
            'condition_at_assignment' => $request->condition_at_assignment,
            'notes'                   => $request->notes,
            'assigned_by'             => auth()->id(),
        ]);

        $asset->update(['status' => 'assigned']);
        return back()->with('success', 'Asset assigned successfully.');
    }

    // ── Return ────────────────────────────────────────────────────────────────

    public function returnAsset(Request $request, Asset $asset)
    {
        abort_unless($asset->tenant_id === auth()->user()->tenant_id, 403);
        $request->validate([
            'condition_at_return' => 'required|in:good,fair,poor,damaged',
            'notes'               => 'nullable|string|max:500',
        ]);

        $assignment = $asset->currentAssignment;
        if (!$assignment) return back()->with('error', 'No active assignment found.');

        $assignment->update([
            'returned_date'       => now()->toDateString(),
            'condition_at_return' => $request->condition_at_return,
            'notes'               => $request->notes,
            'received_by'         => auth()->id(),
        ]);

        $asset->update([
            'status'    => 'available',
            'condition' => $request->condition_at_return,
        ]);

        return back()->with('success', 'Asset returned successfully.');
    }

    // ── Maintenance ───────────────────────────────────────────────────────────

    public function logMaintenance(Request $request, Asset $asset)
    {
        abort_unless($asset->tenant_id === auth()->user()->tenant_id, 403);
        $request->validate([
            'maintenance_type' => 'required|in:routine,repair,upgrade,inspection,disposal_prep',
            'scheduled_date'   => 'required|date',
            'description'      => 'required|string|max:1000',
            'cost'             => 'nullable|numeric|min:0',
            'vendor'           => 'nullable|string|max:150',
        ]);

        AssetMaintenanceLog::create(array_merge(
            $request->only(['maintenance_type','scheduled_date','description','cost','vendor']),
            [
                'tenant_id' => $asset->tenant_id,
                'asset_id'  => $asset->id,
                'status'    => 'scheduled',
                'logged_by' => auth()->id(),
            ]
        ));

        $asset->update(['status' => 'under_maintenance']);
        return back()->with('success', 'Maintenance log created.');
    }

    public function completeMaintenance(Request $request, AssetMaintenanceLog $log)
    {
        abort_unless($log->tenant_id === auth()->user()->tenant_id, 403);
        $request->validate(['findings' => 'nullable|string|max:1000']);

        $log->update([
            'status'         => 'completed',
            'completed_date' => now()->toDateString(),
            'findings'       => $request->findings,
            'performed_by'   => auth()->id(),
        ]);
        $log->asset->update(['status' => 'available']);
        return back()->with('success', 'Maintenance completed.');
    }

    // ── Categories ────────────────────────────────────────────────────────────

    public function categories(Request $request)
    {
        $tenantId   = auth()->user()->tenant_id;
        $categories = AssetCategory::where('tenant_id', $tenantId)->withCount('assets')->get();
        return view('assets.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:100',
            'code'                => 'nullable|string|max:20',
            'depreciation_years'  => 'required|integer|min:0|max:50',
            'depreciation_method' => 'required|in:straight_line,reducing_balance,none',
        ]);
        AssetCategory::create(array_merge(
            $request->only(['name','code','description','depreciation_years','depreciation_method']),
            ['tenant_id' => auth()->user()->tenant_id, 'is_active' => true]
        ));
        return back()->with('success', 'Category created.');
    }
}
