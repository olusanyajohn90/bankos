<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentVisit;
use App\Models\Branch;
use App\Models\Customer;
use App\Services\AgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentController extends Controller
{
    public function __construct(private AgentService $agentService) {}

    public function index(Request $request)
    {
        $agents = Agent::with('branch')
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            }))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('agents.index', compact('agents'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('agents.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'required|string|max:100',
            'phone'               => 'required|string|unique:agents,phone',
            'email'               => 'nullable|email',
            'bvn'                 => 'nullable|string|size:11',
            'nin'                 => 'nullable|string|size:11',
            'address'             => 'nullable|string',
            'branch_id'           => 'nullable|exists:branches,id',
            'daily_cash_in_limit' => 'nullable|numeric|min:0',
            'daily_cash_out_limit'=> 'nullable|numeric|min:0',
            'daily_transfer_limit'=> 'nullable|numeric|min:0',
            'commission_rate'     => 'nullable|numeric|min:0|max:1',
            'home_latitude'       => 'nullable|numeric',
            'home_longitude'      => 'nullable|numeric',
        ]);

        Agent::create($data);

        return redirect()->route('agents.index')->with('success', 'Agent created successfully.');
    }

    public function show(Agent $agent)
    {
        $agent->load('branch');
        $floatTransactions = $agent->floatTransactions()->latest()->paginate(10, ['*'], 'float_page');
        $visits = $agent->visits()->with('customer')->latest('visited_at')->paginate(10, ['*'], 'visit_page');

        return view('agents.show', compact('agent', 'floatTransactions', 'visits'));
    }

    public function edit(Agent $agent)
    {
        $branches = Branch::all();
        return view('agents.edit', compact('agent', 'branches'));
    }

    public function update(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'required|string|max:100',
            'phone'               => 'required|string|unique:agents,phone,' . $agent->id,
            'email'               => 'nullable|email',
            'bvn'                 => 'nullable|string|size:11',
            'nin'                 => 'nullable|string|size:11',
            'address'             => 'nullable|string',
            'branch_id'           => 'nullable|exists:branches,id',
            'status'              => 'required|in:active,suspended,inactive',
            'daily_cash_in_limit' => 'nullable|numeric|min:0',
            'daily_cash_out_limit'=> 'nullable|numeric|min:0',
            'daily_transfer_limit'=> 'nullable|numeric|min:0',
            'commission_rate'     => 'nullable|numeric|min:0|max:1',
            'home_latitude'       => 'nullable|numeric',
            'home_longitude'      => 'nullable|numeric',
        ]);

        $agent->update($data);

        return redirect()->route('agents.show', $agent)->with('success', 'Agent updated.');
    }

    public function fundFloat(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'amount'   => 'required|numeric|min:100',
            'narration'=> 'nullable|string|max:255',
        ]);

        $this->agentService->fundFloat($agent, $data['amount'], $data['narration'] ?? 'Float top-up');

        return back()->with('success', 'Float funded: ₦' . number_format($data['amount'], 2));
    }

    public function logVisit(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'customer_id'    => 'nullable|exists:customers,id',
            'latitude'       => 'required|numeric',
            'longitude'      => 'required|numeric',
            'address_resolved'=> 'nullable|string|max:255',
            'purpose'        => 'required|in:collection,account_opening,kyc,other',
            'notes'          => 'nullable|string',
            'amount_collected'=> 'nullable|numeric|min:0',
        ]);

        AgentVisit::create(array_merge($data, [
            'tenant_id'  => Auth::user()->tenant_id,
            'agent_id'   => $agent->id,
            'visited_at' => now(),
        ]));

        return back()->with('success', 'Visit logged.');
    }
}
