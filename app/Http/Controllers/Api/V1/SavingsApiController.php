<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\Customer;
use App\Models\SavingsProduct;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SavingsApiController extends BaseApiController
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
     * List savings pockets (accounts with type=savings or goal_savings).
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        $pockets = Account::where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->whereIn('type', ['savings', 'goal_savings', 'target_savings'])
            ->whereNotIn('status', ['closed'])
            ->with('savingsProduct')
            ->get();

        return $this->success(AccountResource::collection($pockets), 'Savings pockets retrieved');
    }

    /**
     * Single savings pocket detail.
     */
    public function show(Request $request, string $pocketId): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        $pocket = Account::where('id', $pocketId)
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->whereIn('type', ['savings', 'goal_savings', 'target_savings'])
            ->with('savingsProduct')
            ->firstOrFail();

        return $this->success([
            'pocket'  => new AccountResource($pocket),
            'product' => $pocket->savingsProduct ? [
                'name'          => $pocket->savingsProduct->name,
                'interest_rate' => $pocket->savingsProduct->interest_rate,
                'product_type'  => $pocket->savingsProduct->product_type,
                'goal_target'   => $pocket->savingsProduct->goal_target,
                'maturity_date' => $pocket->savingsProduct->maturity_date?->toDateString(),
            ] : null,
        ], 'Pocket detail retrieved');
    }

    /**
     * Create a new savings pocket.
     */
    public function create(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'target_amount' => 'sometimes|numeric|min:0',
            'target_date'   => 'sometimes|date|after:today',
            'frequency'     => 'sometimes|in:daily,weekly,monthly',
            'product_id'    => 'sometimes|uuid',
        ]);

        $customer = $this->resolveCustomer($request);

        // Resolve savings product (default to first active savings product for tenant)
        $product = null;
        if (!empty($data['product_id'])) {
            $product = SavingsProduct::where('id', $data['product_id'])
                ->where('tenant_id', $customer->tenant_id)
                ->where('status', 'active')
                ->first();
        }

        if (!$product) {
            $product = SavingsProduct::where('tenant_id', $customer->tenant_id)
                ->where('status', 'active')
                ->whereIn('product_type', ['savings', 'goal_savings'])
                ->first();
        }

        if (!$product) {
            return $this->error('No active savings product available.', 422);
        }

        $accountNumber = '4' . str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT);

        $pocket = Account::create([
            'tenant_id'          => $customer->tenant_id,
            'customer_id'        => $customer->id,
            'account_number'     => $accountNumber,
            'account_name'       => $data['name'] . ' - ' . $customer->first_name,
            'type'               => 'goal_savings',
            'currency'           => 'NGN',
            'available_balance'  => 0,
            'ledger_balance'     => 0,
            'savings_product_id' => $product->id,
            'status'             => 'active',
        ]);

        return $this->success(new AccountResource($pocket), 'Savings pocket created', 201);
    }

    /**
     * Deposit to a savings pocket.
     */
    public function deposit(Request $request, string $pocketId): JsonResponse
    {
        $data = $request->validate([
            'account_id' => 'required|uuid',
            'amount'     => 'required|numeric|min:1',
            'pin'        => 'required|string|min:4',
        ]);

        $customer = $this->resolveCustomer($request);

        if (!Hash::check($data['pin'], $customer->portal_pin)) {
            return $this->error('Invalid PIN.', 403);
        }

        $pocket = Account::where('id', $pocketId)
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->whereIn('type', ['savings', 'goal_savings', 'target_savings'])
            ->where('status', 'active')
            ->firstOrFail();

        $sourceAccount = Account::where('id', $data['account_id'])
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->firstOrFail();

        $amount = (float) $data['amount'];

        if ((float) $sourceAccount->available_balance < $amount) {
            return $this->error('Insufficient balance in source account.', 422);
        }

        $reference = 'SAV-' . strtoupper(Str::random(10));

        DB::transaction(function () use ($sourceAccount, $pocket, $amount, $reference, $customer) {
            $sourceAccount->decrement('available_balance', $amount);
            $sourceAccount->decrement('ledger_balance', $amount);

            Transaction::create([
                'tenant_id'   => $customer->tenant_id,
                'account_id'  => $sourceAccount->id,
                'reference'   => $reference . '-DR',
                'type'        => 'debit',
                'amount'      => $amount,
                'currency'    => $sourceAccount->currency ?? 'NGN',
                'description' => 'Savings deposit to pocket',
                'status'      => 'success',
            ]);

            $pocket->increment('available_balance', $amount);
            $pocket->increment('ledger_balance', $amount);

            Transaction::create([
                'tenant_id'   => $customer->tenant_id,
                'account_id'  => $pocket->id,
                'reference'   => $reference . '-CR',
                'type'        => 'credit',
                'amount'      => $amount,
                'currency'    => $pocket->currency ?? 'NGN',
                'description' => 'Savings deposit',
                'status'      => 'success',
            ]);
        });

        return $this->success([
            'reference'      => $reference,
            'amount'         => $amount,
            'pocket_balance' => (float) $pocket->fresh()->available_balance,
        ], 'Deposit successful');
    }

    /**
     * Withdraw from a savings pocket.
     */
    public function withdraw(Request $request, string $pocketId): JsonResponse
    {
        $data = $request->validate([
            'account_id' => 'required|uuid',
            'amount'     => 'required|numeric|min:1',
            'pin'        => 'required|string|min:4',
        ]);

        $customer = $this->resolveCustomer($request);

        if (!Hash::check($data['pin'], $customer->portal_pin)) {
            return $this->error('Invalid PIN.', 403);
        }

        $pocket = Account::where('id', $pocketId)
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->whereIn('type', ['savings', 'goal_savings', 'target_savings'])
            ->where('status', 'active')
            ->firstOrFail();

        $destAccount = Account::where('id', $data['account_id'])
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->firstOrFail();

        $amount = (float) $data['amount'];

        if ((float) $pocket->available_balance < $amount) {
            return $this->error('Insufficient balance in savings pocket.', 422);
        }

        $reference = 'SWD-' . strtoupper(Str::random(10));

        DB::transaction(function () use ($pocket, $destAccount, $amount, $reference, $customer) {
            $pocket->decrement('available_balance', $amount);
            $pocket->decrement('ledger_balance', $amount);

            Transaction::create([
                'tenant_id'   => $customer->tenant_id,
                'account_id'  => $pocket->id,
                'reference'   => $reference . '-DR',
                'type'        => 'debit',
                'amount'      => $amount,
                'currency'    => $pocket->currency ?? 'NGN',
                'description' => 'Savings withdrawal',
                'status'      => 'success',
            ]);

            $destAccount->increment('available_balance', $amount);
            $destAccount->increment('ledger_balance', $amount);

            Transaction::create([
                'tenant_id'   => $customer->tenant_id,
                'account_id'  => $destAccount->id,
                'reference'   => $reference . '-CR',
                'type'        => 'credit',
                'amount'      => $amount,
                'currency'    => $destAccount->currency ?? 'NGN',
                'description' => 'Savings pocket withdrawal',
                'status'      => 'success',
            ]);
        });

        return $this->success([
            'reference'      => $reference,
            'amount'         => $amount,
            'pocket_balance' => (float) $pocket->fresh()->available_balance,
        ], 'Withdrawal successful');
    }
}
