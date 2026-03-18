<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PortalDisputeAdminController extends Controller
{
    public function index(Request $request)
    {
        if (! $this->portalTableExists('portal_disputes')) {
            return view('portal-disputes.index', [
                'disputes'     => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'statusCounts' => collect(),
                'portalUnavailable' => true,
            ]);
        }

        $query = DB::table('portal_disputes')
            ->join('customers', 'portal_disputes.customer_id', '=', 'customers.id')
            ->where('customers.tenant_id', Auth::user()->tenant_id)
            ->select(
                'portal_disputes.*',
                'customers.first_name', 'customers.last_name',
                'customers.customer_number', 'customers.email'
            )
            ->orderByRaw("CASE portal_disputes.status WHEN 'open' THEN 1 WHEN 'investigating' THEN 2 WHEN 'escalated' THEN 3 WHEN 'resolved' THEN 4 WHEN 'rejected' THEN 5 ELSE 6 END")
            ->orderBy('portal_disputes.created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('portal_disputes.status', $request->status);
        }
        if ($request->filled('q')) {
            $q = '%'.$request->q.'%';
            $query->where(function($sub) use ($q) {
                $sub->where('portal_disputes.reference', 'like', $q)
                    ->orWhere('portal_disputes.description', 'like', $q)
                    ->orWhere('customers.first_name', 'like', $q)
                    ->orWhere('customers.last_name', 'like', $q);
            });
        }

        $disputes   = $query->paginate(20)->withQueryString();
        $statusCounts = DB::table('portal_disputes')
            ->join('customers', 'portal_disputes.customer_id', '=', 'customers.id')
            ->where('customers.tenant_id', Auth::user()->tenant_id)
            ->selectRaw('portal_disputes.status, COUNT(*) as cnt')
            ->groupBy('portal_disputes.status')
            ->pluck('cnt', 'status');

        return view('portal-disputes.index', compact('disputes', 'statusCounts'));
    }

    public function show($id)
    {
        $this->requirePortalTable('portal_disputes', 'Portal disputes');
        $dispute = DB::table('portal_disputes')
            ->join('customers', 'portal_disputes.customer_id', '=', 'customers.id')
            ->where('customers.tenant_id', Auth::user()->tenant_id)
            ->where('portal_disputes.id', $id)
            ->select(
                'portal_disputes.*',
                'customers.first_name', 'customers.last_name',
                'customers.customer_number', 'customers.email', 'customers.phone',
                'customers.id as cust_id'
            )
            ->first();
        abort_if(!$dispute, 404);

        $account = $dispute->account_id
            ? DB::table('accounts')->where('id', $dispute->account_id)->first()
            : null;

        return view('portal-disputes.show', compact('dispute', 'account'));
    }

    public function respond(Request $request, $id)
    {
        $this->requirePortalTable('portal_disputes', 'Portal disputes');
        $request->validate([
            'admin_response' => 'required|string|max:2000',
            'new_status'     => 'required|in:investigating,escalated,resolved,rejected',
        ]);

        $dispute = DB::table('portal_disputes')
            ->join('customers', 'portal_disputes.customer_id', '=', 'customers.id')
            ->where('customers.tenant_id', Auth::user()->tenant_id)
            ->where('portal_disputes.id', $id)
            ->select('portal_disputes.id')
            ->first();
        abort_if(!$dispute, 404);

        DB::table('portal_disputes')->where('id', $dispute->id)->update([
            'admin_response'      => $request->admin_response,
            'status'              => $request->new_status,
            'admin_responded_by'  => Auth::id(),
            'admin_responded_at'  => now(),
            'updated_at'          => now(),
        ]);

        return back()->with('success', 'Response sent. Status updated to '.ucfirst($request->new_status).'.');
    }
}
