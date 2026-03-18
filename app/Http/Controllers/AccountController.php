<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\SavingsProduct;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of all accounts.
     */
    public function index(Request $request)
    {
        $query = Account::with(['customer', 'savingsProduct']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('account_number', 'like', "%{$search}%")
                  ->orWhere('account_name', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('customer_number', 'like', "%{$search}%");
                  });
        }

        $accounts = $query->latest()->paginate(20)->withQueryString();

        return view('accounts.index', compact('accounts'));
    }

    /**
     * Show the form for opening an account for a specific customer.
     */
    public function create(Request $request)
    {
        if (!$request->has('customer_id')) {
            return redirect()->route('customers.index')->with('error', 'Select a customer first to open an account.');
        }

        $customer = Customer::findOrFail($request->customer_id);
        
        // Ensure customer has completed KYC
        if ($customer->kyc_status !== 'approved') {
            return redirect()->route('customers.show', $customer)
                ->with('error', 'Cannot open an account. Customer KYC must be verified first.');
        }

        $products = SavingsProduct::all();

        return view('accounts.create', compact('customer', 'products'));
    }

    /**
     * Store a newly created account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'savings_product_id' => 'required|exists:savings_products,id',
            'account_name' => 'nullable|string|max:255',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $product = SavingsProduct::findOrFail($validated['savings_product_id']);

        // Generate NUBAN-like 10 digit account number starting with a product-specific prefix
        $isCurrent = str_contains(strtolower($product->name), 'current') || str_contains(strtolower($product->code), 'CUR');
        $accountType = $isCurrent ? 'current' : 'savings';
        $prefix = $isCurrent ? '20' : '10';
        $accountNumber = $prefix . mt_rand(10000000, 99999999);

        // Fallback name if none provided
        $accountName = $validated['account_name'] ?? ($customer->first_name . ' ' . $customer->last_name);

        $account = Account::create([
            'customer_id' => $customer->id,
            'savings_product_id' => $product->id,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'type' => $accountType,
            'currency' => $product->currency,
            'ledger_balance' => 0,
            'available_balance' => 0,
            'status' => 'active',
        ]);

        // Notify customer
        app(NotificationService::class)->send($customer, 'account_opened', [
            'customer_name' => $customer->first_name . ' ' . $customer->last_name,
            'account_number'=> $accountNumber,
            'account_name'  => $accountName,
            'product_name'  => $product->name,
            'currency'      => $product->currency ?? 'NGN',
        ]);

        return redirect()->route('accounts.show', $account)
            ->with('success', "Account {$accountNumber} created successfully.");
    }

    /**
     * Display the specified account and recent transactions.
     */
    public function show(Account $account)
    {
        $account->load(['customer', 'savingsProduct', 'transactions' => function($q) {
            $q->latest()->take(50);
        }]);
        
        return view('accounts.show', compact('account'));
    }

    /**
     * Update account status (freeze, activate).
     */
    public function updateStatus(Request $request, Account $account)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,dormant,frozen,closed',
        ]);

        if ($validated['status'] === 'closed' && $account->ledger_balance > 0) {
            return back()->with('error', 'Cannot close an account with a positive balance.');
        }

        $account->update(['status' => $validated['status']]);

        return back()->with('success', 'Account status updated.');
    }

    public function close(Request $request, Account $account)
    {
        $request->validate(['closure_reason' => 'required|string|max:500']);
        $service = app(\App\Services\AccountLifecycleService::class);
        try {
            $service->close($account, $request->closure_reason, auth()->id());
            return back()->with('success', 'Account closed successfully.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reactivate(Account $account)
    {
        $service = app(\App\Services\AccountLifecycleService::class);
        $service->reactivate($account);
        return back()->with('success', 'Account reactivated successfully.');
    }
}
