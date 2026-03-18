<?php

namespace App\Http\Controllers\FixedDeposit;

use App\Http\Controllers\Controller;
use App\Models\FixedDeposit;
use App\Models\FixedDepositProduct;
use App\Models\Customer;
use App\Models\Account;
use App\Services\FixedDeposit\FixedDepositService;
use Illuminate\Http\Request;

class FixedDepositController extends Controller
{
    public function __construct(protected FixedDepositService $service) {}

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = FixedDeposit::where('tenant_id', $tenantId)
            ->with(['customer', 'product', 'sourceAccount'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(fn ($q) => $q
                ->where('fd_number', 'like', '%' . $request->search . '%')
                ->orWhereHas('customer', fn ($cq) => $cq
                    ->where('first_name', 'like', '%' . $request->search . '%')
                    ->orWhere('last_name', 'like', '%' . $request->search . '%')
                )
            );
        }

        $fds = $query->paginate(20)->withQueryString();

        $statusCounts = FixedDeposit::where('tenant_id', $tenantId)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('fixed-deposits.index', compact('fds', 'statusCounts'));
    }

    public function create()
    {
        $tenantId = auth()->user()->tenant_id;
        $products  = FixedDepositProduct::where('tenant_id', $tenantId)->active()->get();
        $customers = Customer::where('tenant_id', $tenantId)->where('status', 'active')->orderBy('first_name')->get();

        return view('fixed-deposits.create', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'        => 'required|uuid',
            'customer_id'       => 'required|uuid',
            'source_account_id' => 'required|uuid',
            'principal_amount'  => 'required|numeric|min:1',
            'tenure_days'       => 'required|integer|min:1',
            'interest_rate'     => 'required|numeric|min:0',
            'start_date'        => 'nullable|date',
            'auto_rollover'     => 'boolean',
        ]);

        $fd = $this->service->create($data, auth()->user()->tenant_id);

        return redirect()->route('fixed-deposits.show', $fd)
            ->with('success', 'Fixed deposit created successfully.');
    }

    public function show(FixedDeposit $fixedDeposit)
    {
        $fixedDeposit->load(['customer', 'product', 'sourceAccount', 'branch', 'createdBy']);

        return view('fixed-deposits.show', compact('fixedDeposit'));
    }

    public function liquidate(Request $request, FixedDeposit $fixedDeposit)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        if (!in_array($fixedDeposit->status, ['active', 'matured'])) {
            return back()->with('error', 'This fixed deposit cannot be liquidated.');
        }

        $this->service->liquidate($fixedDeposit, $request->reason ?? '');

        return redirect()->route('fixed-deposits.show', $fixedDeposit)
            ->with('success', 'Fixed deposit liquidated successfully.');
    }
}
