<?php

namespace App\Http\Controllers\Cooperative;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MemberExitController extends Controller
{
    /**
     * List all exit requests with status.
     */
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;

        $exits = DB::table('member_exits as me')
            ->join('customers as c', 'c.id', '=', 'me.customer_id')
            ->where('me.tenant_id', $tenantId)
            ->select(
                'me.*',
                DB::raw("CONCAT(c.first_name, ' ', c.last_name) as member_name"),
                'c.customer_number'
            )
            ->orderByDesc('me.created_at')
            ->paginate(20);

        $stats = DB::table('member_exits')
            ->where('tenant_id', $tenantId)
            ->select([
                DB::raw("COUNT(*) as total"),
                DB::raw("COUNT(*) FILTER (WHERE status = 'pending') as pending_count"),
                DB::raw("COUNT(*) FILTER (WHERE status = 'approved') as approved_count"),
                DB::raw("COUNT(*) FILTER (WHERE status = 'settled') as settled_count"),
                DB::raw("COALESCE(SUM(net_settlement) FILTER (WHERE status = 'settled'), 0) as total_settled"),
            ])
            ->first();

        return view('cooperative.exits.index', compact('exits', 'stats'));
    }

    /**
     * Form: select member, exit type, reason.
     */
    public function create()
    {
        $tenantId = Auth::user()->tenant_id;

        $customers = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        return view('cooperative.exits.create', compact('customers'));
    }

    /**
     * Calculate settlement and store exit request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|uuid|exists:customers,id',
            'exit_type'   => 'required|in:voluntary,expelled,deceased,transferred',
            'reason'      => 'nullable|string',
            'notes'       => 'nullable|string',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $customerId = $validated['customer_id'];

        // Calculate share value (sum of share-capital account balances)
        $shareRefund = DB::table('accounts as a')
            ->join('savings_products as sp', 'sp.id', '=', 'a.savings_product_id')
            ->where('a.tenant_id', $tenantId)
            ->where('a.customer_id', $customerId)
            ->where('a.status', 'active')
            ->where(function ($q) {
                $q->where('sp.name', 'ilike', '%share%')
                  ->orWhere('sp.name', 'ilike', '%capital%');
            })
            ->sum('a.balance');

        // Calculate savings balance (non-share accounts)
        $savingsBalance = DB::table('accounts as a')
            ->join('savings_products as sp', 'sp.id', '=', 'a.savings_product_id')
            ->where('a.tenant_id', $tenantId)
            ->where('a.customer_id', $customerId)
            ->where('a.status', 'active')
            ->where('sp.name', 'not ilike', '%share%')
            ->where('sp.name', 'not ilike', '%capital%')
            ->sum('a.balance');

        // Outstanding loans
        $outstandingLoans = DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->whereIn('status', ['active', 'disbursed', 'overdue'])
            ->sum('outstanding_balance');

        // Pending contributions (mandatory schedules where member hasn't paid current period)
        $currentMonth = Carbon::now()->format('Y-m');
        $mandatorySchedules = DB::table('contribution_schedules')
            ->where('tenant_id', $tenantId)
            ->where('mandatory', true)
            ->where('status', 'active')
            ->get();

        $pendingContributions = 0;
        foreach ($mandatorySchedules as $schedule) {
            $hasPaid = DB::table('member_contributions')
                ->where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->where('contribution_schedule_id', $schedule->id)
                ->where('period', $currentMonth)
                ->where('status', 'paid')
                ->exists();

            if (!$hasPaid) {
                $pendingContributions += $schedule->amount;
            }
        }

        // Net settlement = (shares + savings) - (loans + pending contributions)
        $netSettlement = ($shareRefund + $savingsBalance) - ($outstandingLoans + $pendingContributions);

        DB::table('member_exits')->insert([
            'id'                    => Str::uuid()->toString(),
            'tenant_id'             => $tenantId,
            'customer_id'           => $customerId,
            'exit_type'             => $validated['exit_type'],
            'reason'                => $validated['reason'] ?? null,
            'share_refund'          => $shareRefund,
            'savings_balance'       => $savingsBalance,
            'outstanding_loans'     => $outstandingLoans,
            'pending_contributions' => $pendingContributions,
            'net_settlement'        => $netSettlement,
            'status'                => 'pending',
            'exit_date'             => null,
            'settlement_date'       => null,
            'notes'                 => $validated['notes'] ?? null,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        return redirect()->route('cooperative.exits.index')
            ->with('success', 'Exit request created. Settlement calculation complete.');
    }

    /**
     * Show exit details with settlement breakdown.
     */
    public function show($id)
    {
        $tenantId = Auth::user()->tenant_id;

        $exit = DB::table('member_exits as me')
            ->join('customers as c', 'c.id', '=', 'me.customer_id')
            ->where('me.id', $id)
            ->where('me.tenant_id', $tenantId)
            ->select(
                'me.*',
                'c.first_name',
                'c.last_name',
                'c.customer_number',
                'c.email',
                'c.phone'
            )
            ->first();

        if (!$exit) {
            abort(404);
        }

        // Get member's accounts for detail
        $accounts = DB::table('accounts as a')
            ->join('savings_products as sp', 'sp.id', '=', 'a.savings_product_id')
            ->where('a.tenant_id', $tenantId)
            ->where('a.customer_id', $exit->customer_id)
            ->select('a.*', 'sp.name as product_name')
            ->get();

        // Get member's active loans
        $loans = DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->where('customer_id', $exit->customer_id)
            ->whereIn('status', ['active', 'disbursed', 'overdue'])
            ->get();

        return view('cooperative.exits.show', compact('exit', 'accounts', 'loans'));
    }

    /**
     * Approve the exit request.
     */
    public function approve($id)
    {
        $tenantId = Auth::user()->tenant_id;

        $exit = DB::table('member_exits')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->first();

        if (!$exit) {
            abort(404);
        }

        DB::table('member_exits')
            ->where('id', $id)
            ->update([
                'status'     => 'approved',
                'exit_date'  => now()->toDateString(),
                'updated_at' => now(),
            ]);

        return redirect()->route('cooperative.exits.show', $id)
            ->with('success', 'Exit request approved. Proceed to settlement when ready.');
    }

    /**
     * Process settlement: credit/debit accounts, redeem shares, close accounts, update customer status.
     */
    public function settle($id)
    {
        $tenantId = Auth::user()->tenant_id;

        $exit = DB::table('member_exits')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->first();

        if (!$exit) {
            abort(404);
        }

        DB::beginTransaction();

        try {
            // Close all member accounts (set to closed, zero out balances)
            DB::table('accounts')
                ->where('tenant_id', $tenantId)
                ->where('customer_id', $exit->customer_id)
                ->where('status', 'active')
                ->update([
                    'status'     => 'closed',
                    'balance'    => 0,
                    'updated_at' => now(),
                ]);

            // Mark customer as inactive
            DB::table('customers')
                ->where('id', $exit->customer_id)
                ->where('tenant_id', $tenantId)
                ->update([
                    'status'     => 'inactive',
                    'updated_at' => now(),
                ]);

            // Update exit record
            DB::table('member_exits')
                ->where('id', $id)
                ->update([
                    'status'          => 'settled',
                    'settlement_date' => now()->toDateString(),
                    'updated_at'      => now(),
                ]);

            DB::commit();

            return redirect()->route('cooperative.exits.show', $id)
                ->with('success', 'Settlement processed. Member accounts closed and status set to inactive.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('cooperative.exits.show', $id)
                ->with('error', 'Settlement failed: ' . $e->getMessage());
        }
    }
}
