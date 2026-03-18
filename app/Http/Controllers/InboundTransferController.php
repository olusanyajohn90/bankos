<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\InboundTransfer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InboundTransferController extends Controller
{
    /**
     * Read-only list of all inbound transfers.
     */
    public function index(Request $request)
    {
        $transfers = InboundTransfer::with('account')
            ->when($request->status,  fn($q) => $q->where('status', $request->status))
            ->when($request->channel, fn($q) => $q->where('channel', $request->channel))
            ->orderByDesc('received_at')
            ->paginate(25);

        return view('inbound_transfers.index', compact('transfers'));
    }

    public function show(InboundTransfer $inboundTransfer)
    {
        $inboundTransfer->load('account.customer');
        return view('inbound_transfers.show', compact('inboundTransfer'));
    }

    /**
     * Manually post a pending inbound transfer to the matched account.
     */
    public function post(InboundTransfer $inboundTransfer)
    {
        if ($inboundTransfer->status !== 'pending') {
            return back()->with('error', 'Only pending transfers can be posted.');
        }

        // Resolve destination account
        $account = Account::where('tenant_id', auth()->user()->tenant_id)
            ->where('account_number', $inboundTransfer->destination_account)
            ->where('status', 'active')
            ->first();

        if (!$account) {
            $inboundTransfer->update(['status' => 'failed']);
            return back()->with('error', 'Destination account not found or inactive.');
        }

        $tx = Transaction::create([
            'tenant_id'   => auth()->user()->tenant_id,
            'account_id'  => $account->id,
            'type'        => 'deposit',
            'amount'      => $inboundTransfer->amount,
            'currency'    => $inboundTransfer->currency,
            'description' => $inboundTransfer->narration ?? 'Inbound transfer from ' . ($inboundTransfer->sender_name ?? 'Unknown'),
            'status'      => 'success',
            'reference'   => 'INBD-' . strtoupper(Str::random(8)),
        ]);

        // Credit the account balance
        $account->increment('available_balance', $inboundTransfer->amount);
        $account->increment('ledger_balance',    $inboundTransfer->amount);

        $inboundTransfer->update([
            'status'         => 'posted',
            'account_id'     => $account->id,
            'transaction_id' => $tx->id,
            'posted_at'      => now(),
        ]);

        return back()->with('success', 'Transfer posted to account ' . $account->account_number . '.');
    }

    /**
     * Webhook receiver — called by NIBSS / payment switch.
     * No auth middleware — secured by signature verification (stub).
     */
    public function webhook(Request $request, string $tenantSlug)
    {
        // TODO: verify webhook signature from NIBSS/switch before processing
        $payload = $request->all();

        // Resolve tenant by short_name slug
        $tenant = \App\Models\Tenant::where('short_name', $tenantSlug)->where('status', 'active')->first();
        if (!$tenant) {
            return response()->json(['error' => 'Unknown tenant'], 404);
        }

        $sessionId = $payload['sessionId'] ?? $payload['session_id'] ?? Str::uuid();

        // Idempotency — skip if session already received
        if (InboundTransfer::where('session_id', $sessionId)->exists()) {
            return response()->json(['status' => 'duplicate']);
        }

        InboundTransfer::create([
            'tenant_id'           => $tenant->id,
            'session_id'          => $sessionId,
            'sender_name'         => $payload['senderName']    ?? $payload['sender_name']    ?? null,
            'sender_account'      => $payload['senderAccount'] ?? $payload['sender_account'] ?? null,
            'sender_bank'         => $payload['senderBank']    ?? $payload['sender_bank']    ?? null,
            'destination_account' => $payload['destinationAccount'] ?? $payload['account_number'] ?? '',
            'amount'              => $payload['amount'] ?? 0,
            'currency'            => $payload['currency'] ?? 'NGN',
            'channel'             => strtolower($payload['channel'] ?? 'nibss'),
            'narration'           => $payload['narration'] ?? $payload['payment_reference'] ?? null,
            'source'              => $payload['source'] ?? 'NIBSS',
            'posting_type'        => 'auto',
            'status'              => 'pending',
            'raw_payload'         => $payload,
            'received_at'         => now(),
        ]);

        return response()->json(['status' => 'received']);
    }
}
