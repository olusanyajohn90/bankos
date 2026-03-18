<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\ProcurementRequest;
use App\Services\ApprovalService;
use Illuminate\Http\Request;

class ProcurementController extends Controller
{
    public function __construct(private ApprovalService $approvalService) {}

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $user     = auth()->user();

        $query = ProcurementRequest::where('tenant_id', $tenantId)
            ->with(['category', 'requestedBy', 'approvalRequest'])
            ->latest();

        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('urgency'))  $query->where('urgency', $request->urgency);
        if (!$user->hasAnyRole(['super_admin','tenant_admin','finance_officer'])) {
            $query->where('requested_by', $user->id);
        }

        $requests   = $query->paginate(25)->withQueryString();
        $categories = AssetCategory::where('tenant_id', $tenantId)->where('is_active', true)->get();

        $pending   = ProcurementRequest::where('tenant_id', $tenantId)->where('status', 'pending')->count();
        $approved  = ProcurementRequest::where('tenant_id', $tenantId)->where('status', 'approved')->count();
        $totalSpend = ProcurementRequest::where('tenant_id', $tenantId)->where('status', 'received')->sum('total_amount');

        return view('assets.procurement', compact('requests', 'categories', 'pending', 'approved', 'totalSpend'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_name'          => 'required|string|max:200',
            'justification'      => 'required|string|max:2000',
            'quantity'           => 'required|integer|min:1',
            'unit_price'         => 'nullable|numeric|min:0',
            'vendor_name'        => 'nullable|string|max:150',
            'urgency'            => 'required|in:normal,urgent,critical',
            'required_by_date'   => 'nullable|date',
            'category_id'        => 'nullable|uuid',
            'notes'              => 'nullable|string|max:1000',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $qty      = $request->quantity;
        $unit     = $request->unit_price;
        $total    = $unit ? $unit * $qty : null;

        $procurement = ProcurementRequest::create(array_merge(
            $request->only(['item_name','justification','quantity','unit_price','vendor_name','urgency','required_by_date','category_id','notes']),
            [
                'tenant_id'    => $tenantId,
                'total_amount' => $total,
                'status'       => 'draft',
                'requested_by' => auth()->id(),
            ]
        ));

        return back()->with('success', "Procurement request #{$procurement->id} created as draft.");
    }

    public function submit(Request $request, ProcurementRequest $procurement)
    {
        abort_unless($procurement->tenant_id === auth()->user()->tenant_id, 403);
        abort_unless($procurement->status === 'draft', 403);

        $summary = "Asset Procurement: {$procurement->item_name} x{$procurement->quantity}";
        if ($procurement->total_amount) $summary .= " — ₦" . number_format($procurement->total_amount);

        $approvalReq = $this->approvalService->initiate(
            tenantId: $procurement->tenant_id,
            actionType: 'asset_procurement',
            initiatedBy: auth()->user(),
            summary: $summary,
            amount: $procurement->total_amount,
            metadata: [
                'item'     => $procurement->item_name,
                'vendor'   => $procurement->vendor_name,
                'quantity' => $procurement->quantity,
                'urgency'  => $procurement->urgency,
            ],
            reference: 'PROC-' . now()->format('Y') . '-' . str_pad(ProcurementRequest::where('tenant_id', $procurement->tenant_id)->count(), 5, '0', STR_PAD_LEFT)
        );

        $procurement->update([
            'status'              => 'pending',
            'approval_request_id' => $approvalReq?->id,
        ]);

        return back()->with('success', 'Procurement request submitted for approval.');
    }

    public function markReceived(Request $request, ProcurementRequest $procurement)
    {
        abort_unless($procurement->tenant_id === auth()->user()->tenant_id, 403);
        $request->validate(['notes' => 'nullable|string|max:500']);

        $procurement->update(['status' => 'received', 'notes' => $request->notes]);

        // Prompt to register as asset
        return back()->with('success', 'Marked as received. Don\'t forget to register this as an asset.');
    }
}
