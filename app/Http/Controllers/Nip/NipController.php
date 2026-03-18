<?php

namespace App\Http\Controllers\Nip;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\BankList;
use App\Models\NipOutwardTransfer;
use App\Services\Nip\NipService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NipController extends Controller
{
    public function __construct(private NipService $nipService)
    {
    }

    // ── List ────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $transfers = NipOutwardTransfer::with('sourceAccount')
            ->where('tenant_id', $tenantId)
            ->when($request->status,   fn ($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,   fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('nip.index', compact('transfers'));
    }

    // ── Create form ─────────────────────────────────────────────────────────────

    public function create()
    {
        $tenantId = auth()->user()->tenant_id;

        $sourceAccounts = Account::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('pnd_active', false)
            ->orderBy('account_number')
            ->get(['id', 'account_number', 'account_name', 'available_balance']);

        $banks = BankList::active()->orderBy('bank_name')->get(['id', 'cbn_code', 'bank_name']);

        return view('nip.create', compact('sourceAccounts', 'banks'));
    }

    // ── Name Enquiry (JSON) ─────────────────────────────────────────────────────

    public function nameEnquiry(Request $request)
    {
        $request->validate([
            'account_number' => ['required', 'string', 'min:10', 'max:20'],
            'bank_code'      => ['required', 'string', 'max:10'],
        ]);

        $tenant = auth()->user()->tenant;
        $result = $this->nipService->nameEnquiry(
            $request->account_number,
            $request->bank_code,
            $tenant
        );

        return response()->json($result);
    }

    // ── Store ────────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_account_id'          => ['required', 'uuid', 'exists:accounts,id'],
            'beneficiary_account_number' => ['required', 'string', 'min:10', 'max:20'],
            'beneficiary_bank_code'      => ['required', 'string', 'max:10', 'exists:bank_list,cbn_code'],
            'beneficiary_account_name'   => ['required', 'string', 'max:150'],
            'amount'                     => ['required', 'numeric', 'min:1'],
            'narration'                  => ['nullable', 'string', 'max:255'],
        ]);

        $user   = auth()->user();
        $tenant = $user->tenant;

        // Ensure source account belongs to this tenant
        $sourceAccount = Account::where('id', $validated['source_account_id'])
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $bank = BankList::findByCode($validated['beneficiary_bank_code']);

        $sessionId = $this->nipService->generateSessionId($tenant);

        $transfer = NipOutwardTransfer::create([
            'tenant_id'                  => $tenant->id,
            'initiated_by'               => $user->id,
            'source_account_id'          => $sourceAccount->id,
            'session_id'                 => $sessionId,
            'sender_account_number'      => $sourceAccount->account_number,
            'sender_account_name'        => $sourceAccount->account_name,
            'sender_bank_code'           => $tenant->nibss_institution_code ?? '000',
            'beneficiary_account_number' => $validated['beneficiary_account_number'],
            'beneficiary_account_name'   => $validated['beneficiary_account_name'],
            'beneficiary_bank_code'      => $validated['beneficiary_bank_code'],
            'beneficiary_bank_name'      => $bank?->bank_name,
            'amount'                     => $validated['amount'],
            'narration'                  => $validated['narration'] ?? null,
            'status'                     => 'pending',
        ]);

        $result = $this->nipService->initiateTransfer($transfer, $tenant);

        if ($result['success']) {
            return redirect()->route('nip.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('nip.index')
            ->with('error', $result['message']);
    }

    // ── Show ─────────────────────────────────────────────────────────────────────

    public function show(NipOutwardTransfer $transfer)
    {
        $transfer->load('sourceAccount.customer', 'initiatedBy');
        return view('nip.show', compact('transfer'));
    }

    // ── NIBSS Callback (public — no auth middleware) ──────────────────────────────

    public function callback(Request $request)
    {
        $this->nipService->handleCallback($request->all());
        return response()->json(['status' => 'received'], 200);
    }
}
