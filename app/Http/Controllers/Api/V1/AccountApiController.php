<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\AccountResource;
use App\Http\Resources\TransactionResource;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountApiController extends BaseApiController
{
    /**
     * Resolve the authenticated customer or abort 401.
     */
    private function resolveCustomer(Request $request): Customer
    {
        $user = $request->user();

        if ($user instanceof Customer) {
            return $user;
        }

        abort(401, 'Customer authentication required.');
    }

    /**
     * List all accounts for the authenticated customer (tenant-scoped).
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        $accounts = Account::where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->whereNotIn('status', ['closed'])
            ->get();

        return $this->success(AccountResource::collection($accounts), 'Accounts retrieved');
    }

    /**
     * Single account detail.
     */
    public function show(Request $request, string $accountId): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        $account = Account::where('id', $accountId)
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->firstOrFail();

        return $this->success(new AccountResource($account), 'Account retrieved');
    }

    /**
     * Account statement with date range and pagination.
     */
    public function statement(Request $request, string $accountId): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        $account = Account::where('id', $accountId)
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->firstOrFail();

        $request->validate([
            'from'     => 'sometimes|date',
            'to'       => 'sometimes|date|after_or_equal:from',
            'per_page' => 'sometimes|integer|min:5|max:100',
        ]);

        $query = Transaction::where('account_id', $account->id)
            ->where('tenant_id', $customer->tenant_id)
            ->latest();

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $perPage      = $request->integer('per_page', 20);
        $transactions = $query->paginate($perPage);

        $paginated = $transactions->through(fn ($txn) => new TransactionResource($txn));

        return $this->paginated($paginated, 'Statement retrieved');
    }

    /**
     * Quick balance check — lightweight endpoint.
     */
    public function balance(Request $request, string $accountId): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        $account = Account::where('id', $accountId)
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->select(['id', 'account_number', 'currency', 'available_balance', 'ledger_balance', 'status'])
            ->firstOrFail();

        return $this->success([
            'account_number'    => $account->account_number,
            'currency'          => $account->currency ?? 'NGN',
            'available_balance' => (float) $account->available_balance,
            'ledger_balance'    => (float) $account->ledger_balance,
            'status'            => $account->status,
        ], 'Balance retrieved');
    }

    /**
     * Freeze account (requires PIN verification).
     */
    public function freeze(Request $request, string $accountId): JsonResponse
    {
        $request->validate(['pin' => 'required|string|min:4']);

        $customer = $this->resolveCustomer($request);

        if (!Hash::check($request->pin, $customer->portal_pin)) {
            return $this->error('Invalid PIN.', 403);
        }

        $account = Account::where('id', $accountId)
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->firstOrFail();

        if ($account->status === 'frozen') {
            return $this->error('Account is already frozen.', 422);
        }

        $account->update(['status' => 'frozen']);

        return $this->success(new AccountResource($account->fresh()), 'Account frozen successfully');
    }

    /**
     * Unfreeze account (requires PIN verification).
     */
    public function unfreeze(Request $request, string $accountId): JsonResponse
    {
        $request->validate(['pin' => 'required|string|min:4']);

        $customer = $this->resolveCustomer($request);

        if (!Hash::check($request->pin, $customer->portal_pin)) {
            return $this->error('Invalid PIN.', 403);
        }

        $account = Account::where('id', $accountId)
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->firstOrFail();

        if ($account->status !== 'frozen') {
            return $this->error('Account is not currently frozen.', 422);
        }

        $account->update(['status' => 'active']);

        return $this->success(new AccountResource($account->fresh()), 'Account unfrozen successfully');
    }
}
