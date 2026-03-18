<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\LoanResource;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoanApiController extends BaseApiController
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
     * List customer's loans.
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        $request->validate([
            'status'   => 'sometimes|string',
            'per_page' => 'sometimes|integer|min:5|max:100',
        ]);

        $query = Loan::where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->with('product')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->integer('per_page', 15);
        $loans   = $query->paginate($perPage);
        $paginated = $loans->through(fn ($loan) => new LoanResource($loan));

        return $this->paginated($paginated, 'Loans retrieved');
    }

    /**
     * Single loan detail with repayment schedule.
     */
    public function show(Request $request, string $loanId): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        $loan = Loan::where('id', $loanId)
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->with('product')
            ->firstOrFail();

        return $this->success([
            'loan'               => new LoanResource($loan),
            'repayment_schedule' => $loan->amortization_schedule,
        ], 'Loan detail retrieved');
    }

    /**
     * Submit a loan application.
     */
    public function apply(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|uuid',
            'amount'     => 'required|numeric|min:1000',
            'tenure'     => 'required|integer|min:1',
            'purpose'    => 'required|string|max:500',
        ]);

        $customer = $this->resolveCustomer($request);

        $product = LoanProduct::where('id', $data['product_id'])
            ->where('tenant_id', $customer->tenant_id)
            ->where('status', 'active')
            ->first();

        if (!$product) {
            return $this->error('Loan product not found or inactive.', 404);
        }

        if ($data['amount'] < $product->min_amount || $data['amount'] > $product->max_amount) {
            return $this->error(
                "Amount must be between {$product->min_amount} and {$product->max_amount}.",
                422
            );
        }

        if ($data['tenure'] < $product->min_tenure || $data['tenure'] > $product->max_tenure) {
            return $this->error(
                "Tenure must be between {$product->min_tenure} and {$product->max_tenure} months.",
                422
            );
        }

        $loanNumber = 'LN-' . strtoupper(Str::random(10));

        $loan = Loan::create([
            'tenant_id'          => $customer->tenant_id,
            'customer_id'        => $customer->id,
            'product_id'         => $product->id,
            'loan_number'        => $loanNumber,
            'principal_amount'   => $data['amount'],
            'outstanding_balance'=> $data['amount'],
            'interest_rate'      => $product->interest_rate,
            'interest_method'    => $product->interest_method,
            'amortization'       => $product->amortization,
            'tenure_days'        => $data['tenure'],
            'purpose'            => $data['purpose'],
            'source_channel'     => 'mobile_app',
            'status'             => 'pending',
        ]);

        return $this->success(new LoanResource($loan->load('product')), 'Loan application submitted', 201);
    }

    /**
     * Make a loan repayment.
     */
    public function repay(Request $request, string $loanId): JsonResponse
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

        $loan = Loan::where('id', $loanId)
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->whereIn('status', ['active', 'overdue'])
            ->firstOrFail();

        $account = Account::where('id', $data['account_id'])
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->firstOrFail();

        $amount = (float) $data['amount'];

        if ((float) $account->available_balance < $amount) {
            return $this->error('Insufficient account balance.', 422);
        }

        // Cap repayment at outstanding balance
        $repayAmount = min($amount, (float) $loan->outstanding_balance);

        DB::transaction(function () use ($account, $loan, $repayAmount, $customer) {
            $account->decrement('available_balance', $repayAmount);
            $account->decrement('ledger_balance', $repayAmount);
            $account->refresh();

            $reference = 'RPY-' . strtoupper(Str::random(12));

            Transaction::create([
                'tenant_id'   => $customer->tenant_id,
                'account_id'  => $account->id,
                'reference'   => $reference,
                'type'        => 'debit',
                'amount'      => $repayAmount,
                'currency'    => $account->currency ?? 'NGN',
                'description' => 'Loan repayment — ' . $loan->loan_number,
                'status'      => 'success',
            ]);

            $newOutstanding = max(0, (float) $loan->outstanding_balance - $repayAmount);
            $loan->outstanding_balance = $newOutstanding;

            if ($newOutstanding <= 0) {
                $loan->status = 'closed';
            }

            $loan->save();
        });

        $loan->refresh();

        return $this->success([
            'loan_number'         => $loan->loan_number,
            'amount_paid'         => $repayAmount,
            'outstanding_balance' => (float) $loan->outstanding_balance,
            'loan_status'         => $loan->status,
        ], 'Repayment successful');
    }

    /**
     * Loan calculator — compute monthly payment, total interest, total repayment.
     */
    public function calculator(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|uuid',
            'amount'     => 'required|numeric|min:1000',
            'tenure'     => 'required|integer|min:1',
        ]);

        // Resolve tenant_id from authenticated user (customer or staff)
        $user     = $request->user();
        $tenantId = $user->tenant_id;

        $product = LoanProduct::where('id', $data['product_id'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if (!$product) {
            return $this->error('Loan product not found.', 404);
        }

        $principal    = (float) $data['amount'];
        $tenureMonths = (int) $data['tenure'];
        $annualRate   = (float) $product->interest_rate;

        // Flat-rate calculation (standard for most Nigerian MFBs)
        $totalInterest  = round($principal * ($annualRate / 100 / 12) * $tenureMonths, 2);
        $totalRepayment = round($principal + $totalInterest, 2);
        $monthlyPayment = round($totalRepayment / $tenureMonths, 2);

        return $this->success([
            'product_name'    => $product->name,
            'principal'       => $principal,
            'tenure_months'   => $tenureMonths,
            'interest_rate'   => $annualRate,
            'monthly_payment' => $monthlyPayment,
            'total_interest'  => $totalInterest,
            'total_repayment' => $totalRepayment,
        ], 'Calculation complete');
    }
}
