<?php
namespace App\Http\Controllers;
use App\Models\{OverdraftFacility, Account};
use App\Services\OverdraftService;
use Illuminate\Http\Request;

class OverdraftController extends Controller {
    public function __construct(protected OverdraftService $service) {}

    public function index() {
        $facilities = OverdraftFacility::where('tenant_id', auth()->user()->tenant_id)
            ->with(['account.customer'])->latest()->paginate(20);
        return view('overdrafts.index', compact('facilities'));
    }

    public function store(Request $request) {
        $data = $request->validate([
            'account_id'    => 'required|uuid',
            'limit_amount'  => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'approved_date' => 'required|date',
            'expiry_date'   => 'nullable|date|after:approved_date',
            'notes'         => 'nullable|string|max:500',
        ]);
        $this->service->create($data, auth()->user()->tenant_id);
        return back()->with('success','Overdraft facility created.');
    }

    public function update(Request $request, OverdraftFacility $overdraft) {
        $data = $request->validate([
            'limit_amount'  => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'expiry_date'   => 'nullable|date',
            'status'        => 'required|in:active,suspended,closed',
        ]);
        $overdraft->update($data);
        return back()->with('success','Overdraft facility updated.');
    }
}
