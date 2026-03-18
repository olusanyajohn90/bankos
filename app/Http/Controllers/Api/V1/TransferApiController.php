<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\TransactionResource;
use App\Models\Account;
use App\Models\BankList;
use App\Models\Customer;
use App\Models\NipOutwardTransfer;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TransferApiController extends BaseApiController
{
    private function resolveCustomer(Request $request): Customer
    {
        $user = $request->user();
        if ($user instanceof Customer) {
            return $user;
        }
        abort(401, 'Customer authentication required.');
    }

    /**
     * Intrabank transfer — between accounts within the same bank (same tenant).
     */
    public function intrabank(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from_account_id'    => 'required|uuid',
            'to_account_number'  => 'required|string',
            'amount'             => 'required|numeric|min:1',
            'description'        => 'sometimes|string|max:255',
            'pin'                => 'required|string|min:4',
        ]);

        $customer = $this->resolveCustomer($request);

        if (!Hash::check($data['pin'], $customer->portal_pin)) {
            return $this->error('Invalid PIN.', 403);
        }

        $fromAccount = Account::where('id', $data['from_account_id'])
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->firstOrFail();

        if ($fromAccount->status !== 'active') {
            return $this->error('Source account is not active.', 422);
        }

        if ((float) $fromAccount->available_balance < (float) $data['amount']) {
            return $this->error('Insufficient balance.', 422);
        }

        $toAccount = Account::where('account_number', $data['to_account_number'])
            ->where('tenant_id', $customer->tenant_id)
            ->first();

        if (!$toAccount) {
            return $this->error('Destination account not found.', 404);
        }

        if ($toAccount->status !== 'active') {
            return $this->error('Destination account is not active.', 422);
        }

        if ($fromAccount->id === $toAccount->id) {
            return $this->error('Cannot transfer to the same account.', 422);
        }

        $reference   = 'TRF-' . strtoupper(Str::random(12));
        $description = $data['description'] ?? 'Intrabank transfer';
        $amount      = (float) $data['amount'];

        DB::transaction(function () use ($fromAccount, $toAccount, $amount, $reference, $description, $customer) {
            // Debit source
            $fromAccount->decrement('available_balance', $amount);
            $fromAccount->decrement('ledger_balance', $amount);
            $fromAccount->refresh();

            Transaction::create([
                'tenant_id'   => $customer->tenant_id,
                'account_id'  => $fromAccount->id,
                'reference'   => $reference . '-DR',
                'type'        => 'debit',
                'amount'      => $amount,
                'currency'    => $fromAccount->currency ?? 'NGN',
                'description' => $description,
                'status'      => 'success',
            ]);

            // Credit destination
            $toAccount->increment('available_balance', $amount);
            $toAccount->increment('ledger_balance', $amount);
            $toAccount->refresh();

            Transaction::create([
                'tenant_id'   => $customer->tenant_id,
                'account_id'  => $toAccount->id,
                'reference'   => $reference . '-CR',
                'type'        => 'credit',
                'amount'      => $amount,
                'currency'    => $toAccount->currency ?? 'NGN',
                'description' => 'Transfer from ' . $fromAccount->account_number . ': ' . $description,
                'status'      => 'success',
            ]);
        });

        return $this->success([
            'reference'          => $reference,
            'amount'             => $amount,
            'from_account'       => $fromAccount->account_number,
            'to_account'         => $toAccount->account_number,
            'to_account_name'    => $toAccount->account_name,
            'description'        => $description,
            'status'             => 'success',
            'timestamp'          => now()->toIso8601String(),
        ], 'Transfer successful');
    }

    /**
     * Interbank NIP transfer.
     */
    public function interbank(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from_account_id'    => 'required|uuid',
            'to_account_number'  => 'required|string',
            'bank_code'          => 'required|string',
            'amount'             => 'required|numeric|min:1',
            'description'        => 'sometimes|string|max:255',
            'pin'                => 'required|string|min:4',
        ]);

        $customer = $this->resolveCustomer($request);

        if (!Hash::check($data['pin'], $customer->portal_pin)) {
            return $this->error('Invalid PIN.', 403);
        }

        $fromAccount = Account::where('id', $data['from_account_id'])
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->firstOrFail();

        if ($fromAccount->status !== 'active') {
            return $this->error('Source account is not active.', 422);
        }

        $amount = (float) $data['amount'];

        if ((float) $fromAccount->available_balance < $amount) {
            return $this->error('Insufficient balance.', 422);
        }

        $bank = BankList::where('cbn_code', $data['bank_code'])->where('is_active', true)->first();
        if (!$bank) {
            return $this->error('Invalid or unsupported bank code.', 422);
        }

        $reference   = 'NIP-' . strtoupper(Str::random(12));
        $description = $data['description'] ?? 'Interbank transfer';

        DB::transaction(function () use ($fromAccount, $amount, $reference, $description, $customer, $data, $bank) {
            $fromAccount->decrement('available_balance', $amount);
            $fromAccount->decrement('ledger_balance', $amount);
            $fromAccount->refresh();

            Transaction::create([
                'tenant_id'   => $customer->tenant_id,
                'account_id'  => $fromAccount->id,
                'reference'   => $reference,
                'type'        => 'debit',
                'amount'      => $amount,
                'currency'    => $fromAccount->currency ?? 'NGN',
                'description' => $description,
                'status'      => 'success',
            ]);

            NipOutwardTransfer::create([
                'tenant_id'                  => $customer->tenant_id,
                'initiated_by'               => null,
                'source_account_id'          => $fromAccount->id,
                'session_id'                 => $reference,
                'name_enquiry_ref'           => $data['to_account_number'],
                'sender_account_number'      => $fromAccount->account_number,
                'sender_account_name'        => $fromAccount->account_name,
                'sender_bank_code'           => config('banking.bank_code', '000000'),
                'beneficiary_account_number' => $data['to_account_number'],
                'beneficiary_account_name'   => $data['beneficiary_name'] ?? 'Unknown',
                'beneficiary_bank_code'      => $data['bank_code'],
                'beneficiary_bank_name'      => $bank->bank_name,
                'amount'                     => $amount,
                'narration'                  => $description,
                'status'                     => 'pending',
                'initiated_at'               => now(),
            ]);
        });

        return $this->success([
            'reference'      => $reference,
            'amount'         => $amount,
            'from_account'   => $fromAccount->account_number,
            'to_account'     => $data['to_account_number'],
            'bank'           => $bank->bank_name,
            'description'    => $description,
            'status'         => 'pending',
            'timestamp'      => now()->toIso8601String(),
        ], 'Interbank transfer initiated');
    }

    /**
     * Name enquiry — resolve account name before transfer.
     */
    public function nameEnquiry(Request $request): JsonResponse
    {
        $request->validate([
            'account_number' => 'required|string',
            'bank_code'      => 'sometimes|string',
        ]);

        $customer = $this->resolveCustomer($request);

        // Intrabank enquiry (no bank_code or own bank)
        if (!$request->filled('bank_code')) {
            $account = Account::where('account_number', $request->account_number)
                ->where('tenant_id', $customer->tenant_id)
                ->where('status', 'active')
                ->select(['id', 'account_number', 'account_name'])
                ->first();

            if (!$account) {
                return $this->error('Account not found.', 404);
            }

            return $this->success([
                'account_number' => $account->account_number,
                'account_name'   => $account->account_name,
                'bank_code'      => null,
                'bank_name'      => 'Own Bank',
            ], 'Name enquiry successful');
        }

        // Interbank — in production this calls the NIBSS Name Enquiry API.
        // For now we return a stub indicating the enquiry is ready for downstream processing.
        $bank = BankList::where('cbn_code', $request->bank_code)->where('is_active', true)->first();

        if (!$bank) {
            return $this->error('Invalid bank code.', 422);
        }

        return $this->success([
            'account_number' => $request->account_number,
            'account_name'   => null, // populated by NIBSS integration
            'bank_code'      => $bank->cbn_code,
            'bank_name'      => $bank->bank_name,
            'enquiry_status' => 'pending_nibss',
        ], 'Name enquiry initiated — NIBSS lookup pending');
    }

    /**
     * Paginated transfer history for the authenticated customer.
     */
    public function history(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => 'sometimes|uuid',
            'type'       => 'sometimes|in:debit,credit',
            'from'       => 'sometimes|date',
            'to'         => 'sometimes|date|after_or_equal:from',
            'per_page'   => 'sometimes|integer|min:5|max:100',
        ]);

        $customer = $this->resolveCustomer($request);

        // Collect all customer account IDs
        $accountIds = Account::where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->pluck('id');

        $query = Transaction::whereIn('account_id', $accountIds)
            ->where('tenant_id', $customer->tenant_id)
            ->latest();

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $perPage      = $request->integer('per_page', 20);
        $transactions = $query->paginate($perPage);
        $paginated    = $transactions->through(fn ($txn) => new TransactionResource($txn));

        return $this->paginated($paginated, 'Transfer history retrieved');
    }

    /**
     * Single transaction receipt by reference.
     */
    public function receipt(Request $request, string $reference): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        $accountIds = Account::where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->pluck('id');

        $transaction = Transaction::where('reference', $reference)
            ->whereIn('account_id', $accountIds)
            ->where('tenant_id', $customer->tenant_id)
            ->firstOrFail();

        return $this->success(new TransactionResource($transaction), 'Receipt retrieved');
    }
}
