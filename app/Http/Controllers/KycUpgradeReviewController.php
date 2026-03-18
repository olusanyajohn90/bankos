<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KycUpgradeReviewController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $this->portalTableExists('kyc_upgrade_requests')) {
            $empty = collect(); $stats = ['pending'=>0,'approved_month'=>0,'rejected_month'=>0,'total_requests'=>0];
            $tierDist = collect(); $customers = collect();
            return view('kyc-review.index', compact('stats', 'tierDist', 'customers'))
                ->with('requests', new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20))
                ->with('portalUnavailable', true);
        }

        $query = DB::table('kyc_upgrade_requests')
            ->join('customers', 'kyc_upgrade_requests.customer_id', '=', 'customers.id')
            ->where('kyc_upgrade_requests.tenant_id', $tenantId)
            ->select(
                'kyc_upgrade_requests.*',
                DB::raw("customers.first_name || ' ' || customers.last_name as customer_name"),
                'customers.phone as customer_phone',
                'customers.kyc_tier as current_db_tier'
            )
            ->orderByDesc('kyc_upgrade_requests.created_at');

        if ($request->filled('status')) {
            $query->where('kyc_upgrade_requests.status', $request->status);
        }

        $requests = $query->paginate(20)->withQueryString();

        // Analytics stats
        $stats = [
            'pending'          => DB::table('kyc_upgrade_requests')->where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            'approved_month'   => DB::table('kyc_upgrade_requests')->where('tenant_id', $tenantId)->where('status', 'approved')->whereMonth('reviewed_at', now()->month)->whereYear('reviewed_at', now()->year)->count(),
            'rejected_month'   => DB::table('kyc_upgrade_requests')->where('tenant_id', $tenantId)->where('status', 'rejected')->whereMonth('reviewed_at', now()->month)->whereYear('reviewed_at', now()->year)->count(),
            'total_requests'   => DB::table('kyc_upgrade_requests')->where('tenant_id', $tenantId)->count(),
        ];

        // Tier distribution of all customers
        $tierDist = DB::table('customers')->where('tenant_id', $tenantId)
            ->where('status', '!=', 'inactive')
            ->select('kyc_tier', DB::raw('count(*) as cnt'))
            ->groupBy('kyc_tier')
            ->pluck('cnt', 'kyc_tier');

        // Customers list for manual adjust dropdown
        $customers = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->where('status', '!=', 'inactive')
            ->select('id', DB::raw("first_name || ' ' || last_name as name"), 'kyc_tier', 'customer_number')
            ->orderBy('first_name')
            ->get();

        return view('kyc-review.index', compact('requests', 'stats', 'tierDist', 'customers'));
    }

    public function show($id)
    {
        $this->requirePortalTable('kyc_upgrade_requests', 'KYC upgrade review');
        $req = DB::table('kyc_upgrade_requests')
            ->join('customers', 'kyc_upgrade_requests.customer_id', '=', 'customers.id')
            ->select('kyc_upgrade_requests.*',
                DB::raw("customers.first_name || ' ' || customers.last_name as customer_name"),
                'customers.phone as customer_phone',
                'customers.email as customer_email',
                'customers.kyc_tier as current_db_tier',
                'customers.id as customer_uuid')
            ->where('kyc_upgrade_requests.id', $id)
            ->first();
        abort_if(!$req, 404);

        return view('kyc-review.show', compact('req'));
    }

    public function approve(Request $request, $id)
    {
        $this->requirePortalTable('kyc_upgrade_requests', 'KYC upgrade review');
        $request->validate(['reviewer_notes' => 'nullable|string|max:1000']);

        $req = DB::table('kyc_upgrade_requests')->where('id', $id)->first();
        abort_if(!$req, 404);

        DB::transaction(function () use ($request, $id, $req) {
            DB::table('kyc_upgrade_requests')->where('id', $id)->update([
                'status'         => 'approved',
                'reviewer_notes' => $request->reviewer_notes,
                'reviewed_by'    => auth()->id(),
                'reviewed_at'    => now(),
                'updated_at'     => now(),
            ]);

            // Upgrade the customer's KYC tier
            Customer::where('id', $req->customer_id)
                ->update(['kyc_tier' => $req->target_tier, 'updated_at' => now()]);
        });

        return redirect()->route('kyc-review.index')
            ->with('success', "Customer KYC upgraded to Tier {$req->target_tier}.");
    }

    public function reject(Request $request, $id)
    {
        $this->requirePortalTable('kyc_upgrade_requests', 'KYC upgrade review');
        $request->validate(['reviewer_notes' => 'required|string|max:1000']);

        DB::table('kyc_upgrade_requests')->where('id', $id)->update([
            'status'         => 'rejected',
            'reviewer_notes' => $request->reviewer_notes,
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
            'updated_at'     => now(),
        ]);

        return redirect()->route('kyc-review.index')
            ->with('success', 'KYC upgrade request rejected.');
    }

    public function manualAdjust(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'new_tier'    => 'required|in:level_1,level_2,level_3',
            'reason'      => 'required|string|max:1000',
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        $tenantId = auth()->user()->tenant_id;

        abort_if($customer->tenant_id !== $tenantId, 403);

        $oldTier = $customer->kyc_tier;
        $newTier = $request->new_tier;

        DB::transaction(function () use ($customer, $newTier, $oldTier, $request) {
            $customer->update(['kyc_tier' => $newTier, 'kyc_status' => 'approved']);

            // Log the manual adjustment as a synthetic upgrade request
            DB::table('kyc_upgrade_requests')->insert([
                'id'             => \Illuminate\Support\Str::uuid()->toString(),
                'customer_id'    => $customer->id,
                'tenant_id'      => $customer->tenant_id,
                'current_tier'   => $oldTier,
                'target_tier'    => $newTier,
                'status'         => 'approved',
                'reviewer_notes' => '[Manual Admin Adjustment] ' . $request->reason,
                'reviewed_by'    => auth()->id(),
                'reviewed_at'    => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        });

        $label = ['level_1' => 'Tier 1', 'level_2' => 'Tier 2', 'level_3' => 'Tier 3'][$newTier] ?? $newTier;

        return redirect()->route('kyc-review.index')
            ->with('success', "{$customer->first_name} {$customer->last_name}'s KYC tier manually adjusted to {$label}.");
    }
}
