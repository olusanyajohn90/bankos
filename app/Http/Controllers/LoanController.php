<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\Account;
use App\Models\LoanProduct;
use App\Models\Transaction;
use App\Services\NotificationService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoanController extends Controller
{
    /**
     * Display a listing of loans.
     */
    public function index(Request $request)
    {
        $query = Loan::with(['customer', 'loanProduct']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('loan_reference', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $loans = $query->latest()->paginate(20)->withQueryString();

        return view('loans.index', compact('loans'));
    }

    /**
     * Show the form for creating a new loan (Origination process).
     */
    public function create(Request $request)
    {
        if (!$request->has('customer_id')) {
            return redirect()->route('customers.index')->with('error', 'Select a customer first to initiate a loan application.');
        }

        $customer = Customer::with('accounts')->findOrFail($request->customer_id);
        
        // Ensure customer has completed KYC
        if ($customer->kyc_status !== 'approved') {
            return redirect()->route('customers.show', $customer)
                ->with('error', 'Cannot apply for loan. Customer KYC must be fully verified.');
        }

        if ($customer->accounts->isEmpty()) {
            return redirect()->route('customers.show', $customer)
                ->with('error', 'Customer needs an active deposit account to receive loan disbursement.');
        }

        $products = LoanProduct::all();

        return view('loans.create', compact('customer', 'products'));
    }

    /**
     * Store a newly created loan application.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'loan_product_id' => 'required|exists:loan_products,id',
            'disbursement_account_id' => 'required|exists:accounts,id',
            'principal_amount' => 'required|numeric|min:1',
            'duration' => 'required|integer|min:1',
            'purpose' => 'required|string|max:255',
        ]);

        $product = LoanProduct::findOrFail($validated['loan_product_id']);
        
        // Product Rule Validations
        if ($validated['principal_amount'] < $product->min_amount || $validated['principal_amount'] > $product->max_amount) {
            return back()->with('error', "Requested amount must be between {$product->min_amount} and {$product->max_amount}.")->withInput();
        }
        // Duration checks
        if ($validated['duration'] < $product->min_tenure || $validated['duration'] > $product->max_tenure) {
            return back()->with('error', "Loan duration must be between {$product->min_tenure} and {$product->max_tenure} {$product->duration_type}.")->withInput();
        }

        // Calculate simple interest for now
        $interestRate = $product->interest_rate / 100;
        $totalInterest = $validated['principal_amount'] * $interestRate; // Adjust for actual formula based on duration later
        
        $loanReference = 'LNF-' . strtoupper(Str::random(10));

        $loan = Loan::create([
            'customer_id' => $validated['customer_id'],
            'product_id' => $product->id,
            'account_id' => $validated['disbursement_account_id'],
            'loan_number' => $loanReference,
            'principal_amount' => $validated['principal_amount'],
            'interest_rate' => $product->interest_rate,
            'interest_method' => $product->interest_method ?? 'flat',
            'amortization' => $product->amortization ?? 'equal_installments',
            'tenure_days' => $validated['duration'],
            'repayment_frequency' => 'monthly',
            'outstanding_balance' => $validated['principal_amount'] + $totalInterest,
            'purpose' => $validated['purpose'],
            'status' => 'pending', // Default to pending status for workflow
        ]);

        // After loan is stored, evaluate credit policy
        $creditDecision = app(\App\Services\Credit\CreditPolicyService::class)->evaluate($loan);
        app(\App\Services\Credit\CreditPolicyService::class)->applyDecision($creditDecision);

        app(WorkflowService::class)->create('Loan Approval', $loan, [
            'amount'   => $validated['principal_amount'],
            'metadata' => ['purpose' => $validated['purpose']],
        ]);

        return redirect()->route('loans.show', $loan)
            ->with('success', "Loan application {$loanReference} submitted and is pending review.");
    }

    /**
     * Display the specified loan dashboard.
     */
    public function show(Loan $loan)
    {
        $loan->load(['customer', 'loanProduct']);
        return view('loans.show', compact('loan'));
    }

    /**
     * Approve the loan application.
     */
    public function approve(Loan $loan)
    {
        if (!auth()->user()->can('loans.approve_l1') && !auth()->user()->hasRole('tenant_admin')) {
            abort(403);
        }

        if ($loan->status !== 'pending') {
            return back()->with('error', 'Only pending loans can be approved.');
        }

        $loan->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Advance/resolve the workflow
        app(WorkflowService::class)->resolveForSubject($loan, 'approve');

        // Notify customer via configured templates (email/SMS/WhatsApp)
        $loan->load('customer', 'loanProduct');
        if ($loan->customer) {
            app(NotificationService::class)->send($loan->customer, 'loan_approved', [
                'customer_name' => $loan->customer->first_name . ' ' . $loan->customer->last_name,
                'amount'        => number_format($loan->principal_amount, 2),
                'loan_number'   => $loan->loan_number,
                'product_name'  => $loan->loanProduct?->name ?? 'N/A',
                'tenure'        => $loan->tenure_days . ' months',
            ]);
        }

        return back()->with('success', 'Loan has been approved.');
    }

    /**
     * Reject a pending loan application.
     */
    public function reject(Request $request, Loan $loan)
    {
        if (!auth()->user()->can('loans.approve_l1') && !auth()->user()->hasRole('tenant_admin')) {
            abort(403);
        }

        if ($loan->status !== 'pending') {
            return back()->with('error', 'Only pending loans can be rejected.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        $loan->update(['status' => 'rejected']);

        app(WorkflowService::class)->resolveForSubject($loan, 'reject', $validated['notes'] ?? null, auth()->user());

        $loan->load('customer', 'loanProduct');
        if ($loan->customer) {
            app(NotificationService::class)->send($loan->customer, 'loan_rejected', [
                'customer_name' => $loan->customer->first_name . ' ' . $loan->customer->last_name,
                'amount'        => number_format($loan->principal_amount, 2),
                'loan_number'   => $loan->loan_number,
                'reason'        => $validated['notes'] ?? 'Application did not meet credit requirements.',
            ]);
        }

        return back()->with('success', 'Loan application has been rejected.');
    }

    /**
     * Disburse the approved loan to the customer's account.
     */
    public function disburse(Loan $loan)
    {
        if (!auth()->user()->can('loans.disburse') && !auth()->user()->hasRole('tenant_admin')) {
            abort(403);
        }

        if ($loan->status !== 'approved') {
            return back()->with('error', 'Only approved loans can be disbursed.');
        }

        $account = Account::findOrFail($loan->disbursement_account_id);

        try {
            DB::beginTransaction();

            // Create Disbursal Transaction
            $reference = 'DSB-' . strtoupper(Str::random(10));
            Transaction::create([
                'account_id' => $account->id,
                'reference' => $reference,
                'type' => 'disbursement',
                'amount' => $loan->principal_amount,
                'currency' => $account->currency,
                'description' => "Loan Disbursement: {$loan->loan_reference}",
                'status' => 'success',
            ]);

            // Fund Account
            $account->increment('available_balance', $loan->principal_amount);
            $account->increment('ledger_balance', $loan->principal_amount);

            // Update Loan Status & Dates
            $loan->update([
                'status' => 'active',
                'disbursed_at' => now(),
                // Basic date calculation, assuming months for MVP simplicity
                'expected_maturity_date' => now()->addMonths($loan->duration), 
            ]);

            DB::commit();

            // Notify customer via configured templates (email/SMS/WhatsApp)
            $loan->load('customer', 'loanProduct', 'account');
            if ($loan->customer) {
                app(NotificationService::class)->send($loan->customer, 'loan_disbursed', [
                    'customer_name'       => $loan->customer->first_name . ' ' . $loan->customer->last_name,
                    'amount'              => number_format((float) $loan->principal_amount, 2),
                    'loan_account_number' => $loan->loan_number,
                    'account_number'      => $account->account_number,
                    'first_repayment_date'=> now()->addMonth()->format('d M, Y'),
                    'tenure'              => $loan->tenure_days . ' months',
                ]);
            }

            return back()->with('success', "Loan disbursed to {$account->account_number}. Account credited with {$account->currency} " . number_format($loan->principal_amount, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Disbursement failed: ' . $e->getMessage());
        }
    }

    /**
     * Process a manual loan repayment.
     */
    public function repay(Request $request, Loan $loan)
    {
        if (!auth()->user()->can('transactions.create') && !auth()->user()->hasRole('tenant_admin')) {
            abort(403);
        }

        if (!in_array($loan->status, ['active', 'overdue'])) {
            return back()->with('error', 'Repayments can only be posted against active or overdue loans.');
        }

        $validated = $request->validate([
            'amount'      => ['required', 'numeric', 'min:0.01', "max:{$loan->outstanding_balance}"],
            'description' => 'nullable|string|max:255',
        ]);

        $account = Account::findOrFail($loan->account_id);

        if ($account->available_balance < $validated['amount']) {
            return back()->with('error', 'Insufficient funds in the linked account to process this repayment.');
        }

        try {
            DB::beginTransaction();

            $reference = 'RPY-' . strtoupper(Str::random(10));

            // Create repayment transaction record
            Transaction::create([
                'account_id'  => $account->id,
                'reference'   => $reference,
                'type'        => 'repayment',
                'amount'      => $validated['amount'],
                'currency'    => $account->currency,
                'description' => $validated['description'] ?? "Loan Repayment: {$loan->loan_number}",
                'status'      => 'success',
            ]);

            // Debit the linked account
            $account->decrement('available_balance', $validated['amount']);
            $account->decrement('ledger_balance', $validated['amount']);

            // Reduce outstanding balance on the loan
            $newOutstanding = max(0, $loan->outstanding_balance - $validated['amount']);
            $loanUpdate = ['outstanding_balance' => $newOutstanding];

            // Automatically close the loan if fully settled
            if ($newOutstanding <= 0) {
                $loanUpdate['status'] = 'closed';
            }

            $loan->update($loanUpdate);

            DB::commit();

            // Notify customer
            $loan->load('customer');
            if ($loan->customer) {
                app(NotificationService::class)->send($loan->customer, 'repayment_received', [
                    'customer_name'      => $loan->customer->first_name . ' ' . $loan->customer->last_name,
                    'amount'             => number_format($validated['amount'], 2),
                    'date'               => now()->format('d M, Y'),
                    'loan_number'        => $loan->loan_number,
                    'outstanding_balance'=> number_format($newOutstanding, 2),
                ]);
            }

            $msg = '₦' . number_format($validated['amount'], 2) . ' repayment posted successfully.';
            if ($newOutstanding <= 0) {
                $msg .= ' 🎉 Loan is now fully repaid and closed!';
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Repayment failed: ' . $e->getMessage());
        }
    }
}
