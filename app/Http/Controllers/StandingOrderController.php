<?php

namespace App\Http\Controllers;

use App\Models\StandingOrder;
use App\Models\Account;
use App\Services\StandingOrder\StandingOrderService;
use Illuminate\Http\Request;

class StandingOrderController extends Controller
{
    public function __construct(protected StandingOrderService $service) {}

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $orders = StandingOrder::where('tenant_id', $tenantId)
            ->with(['sourceAccount.customer', 'internalDestAccount.customer'])
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('standing-orders.index', compact('orders'));
    }

    public function create()
    {
        $tenantId = auth()->user()->tenant_id;
        $accounts = Account::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with('customer')
            ->get();

        return view('standing-orders.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'source_account_id'          => 'required|uuid',
            'transfer_type'              => 'required|in:internal,external',
            'internal_dest_account_id'   => 'required_if:transfer_type,internal|nullable|uuid',
            'beneficiary_account_number' => 'required_if:transfer_type,external|nullable|string|max:20',
            'beneficiary_bank_code'      => 'required_if:transfer_type,external|nullable|string|max:10',
            'beneficiary_name'           => 'nullable|string|max:150',
            'amount'                     => 'required|numeric|min:1',
            'narration'                  => 'nullable|string|max:255',
            'frequency'                  => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'start_date'                 => 'required|date|after_or_equal:today',
            'end_date'                   => 'nullable|date|after:start_date',
            'max_runs'                   => 'nullable|integer|min:1',
        ]);

        $data['tenant_id']      = auth()->user()->tenant_id;
        $data['next_run_date']  = $data['start_date'];
        $data['created_by']     = auth()->id();

        StandingOrder::create($data);

        return redirect()->route('standing-orders.index')
            ->with('success', 'Standing order created successfully.');
    }

    public function destroy(StandingOrder $standingOrder)
    {
        $standingOrder->update(['status' => 'cancelled']);

        return back()->with('success', 'Standing order cancelled.');
    }

    public function pause(StandingOrder $standingOrder)
    {
        $newStatus = $standingOrder->status === 'paused' ? 'active' : 'paused';
        $standingOrder->update(['status' => $newStatus]);

        return back()->with('success', 'Standing order ' . ($newStatus === 'paused' ? 'paused' : 'resumed') . '.');
    }
}
