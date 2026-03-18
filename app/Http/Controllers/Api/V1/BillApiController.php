<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BillApiController extends BaseApiController
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
     * List bill categories.
     */
    public function categories(Request $request): JsonResponse
    {
        // Static list of common Nigerian bill categories.
        // In production this would be fetched from a bills aggregator (e.g. Interswitch, Paystack).
        $categories = [
            ['code' => 'electricity', 'name' => 'Electricity',        'icon' => 'bolt'],
            ['code' => 'cable_tv',    'name' => 'Cable TV / DSTV',    'icon' => 'tv'],
            ['code' => 'airtime',     'name' => 'Airtime / Top-Up',   'icon' => 'phone'],
            ['code' => 'internet',    'name' => 'Internet / Data',     'icon' => 'wifi'],
            ['code' => 'water',       'name' => 'Water Bills',         'icon' => 'droplet'],
            ['code' => 'education',   'name' => 'School Fees',         'icon' => 'book'],
            ['code' => 'insurance',   'name' => 'Insurance Premium',   'icon' => 'shield'],
            ['code' => 'tax',         'name' => 'Tax / Government',    'icon' => 'building'],
        ];

        return $this->success($categories, 'Bill categories retrieved');
    }

    /**
     * Pay a bill.
     */
    public function pay(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category'     => 'required|string|max:50',
            'biller_code'  => 'required|string|max:100',
            'customer_ref' => 'required|string|max:255',
            'amount'       => 'required|numeric|min:1',
            'account_id'   => 'required|uuid',
            'pin'          => 'required|string|min:4',
        ]);

        $customer = $this->resolveCustomer($request);

        if (!Hash::check($data['pin'], $customer->portal_pin)) {
            return $this->error('Invalid PIN.', 403);
        }

        $account = Account::where('id', $data['account_id'])
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->where('status', 'active')
            ->firstOrFail();

        $amount = (float) $data['amount'];

        if ((float) $account->available_balance < $amount) {
            return $this->error('Insufficient balance.', 422);
        }

        $reference = 'BIL-' . strtoupper(Str::random(12));

        DB::transaction(function () use ($account, $amount, $reference, $customer, $data) {
            $account->decrement('available_balance', $amount);
            $account->decrement('ledger_balance', $amount);
            $account->refresh();

            Transaction::create([
                'tenant_id'   => $customer->tenant_id,
                'account_id'  => $account->id,
                'reference'   => $reference,
                'type'        => 'debit',
                'amount'      => $amount,
                'currency'    => $account->currency ?? 'NGN',
                'description' => 'Bill payment — ' . strtoupper($data['category']) . ' / ' . $data['biller_code'],
                'status'      => 'success',
            ]);
        });

        return $this->success([
            'reference'    => $reference,
            'category'     => $data['category'],
            'biller_code'  => $data['biller_code'],
            'customer_ref' => $data['customer_ref'],
            'amount'       => $amount,
            'status'       => 'success',
            'timestamp'    => now()->toIso8601String(),
        ], 'Bill payment successful');
    }
}
