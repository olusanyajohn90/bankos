<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\AiReviewService;
use App\Services\NotificationService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('kyc_status') && $request->kyc_status !== 'all') {
            $query->where('kyc_status', $request->kyc_status);
        }
        
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('customer_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:individual,corporate',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email,NULL,id,tenant_id,' . auth()->user()->tenant_id,
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'marital_status' => 'required|string|in:single,married,divorced,widowed',
            'occupation' => 'required|string|max:255',
            'bvn' => 'nullable|string|size:11',
            'nin' => 'nullable|string|size:11',
            'address_street' => 'required|string|max:255',
            'address_lga' => 'required|string|max:255',
            'address_state' => 'required|string|max:255',
        ]);

        // Generate a unique 10-digit customer number
        $customerNumber = 'CUS' . strtoupper(Str::random(7));

        $customer = Customer::create([
            'customer_number' => $customerNumber,
            'type' => $validated['type'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'marital_status' => $validated['marital_status'],
            'occupation' => $validated['occupation'],
            'bvn' => $validated['bvn'] ?? null,
            'nin' => $validated['nin'] ?? null,
            'address' => [
                'street' => $validated['address_street'],
                'lga' => $validated['address_lga'],
                'state' => $validated['address_state'],
            ],
            // New customers start as pending review for KYC
            'status' => 'pending', 
            'kyc_status' => 'manual_review',
        ]);

        app(WorkflowService::class)->create('KYC Review', $customer);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer created successfully and queued for KYC review.');
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        // View policy check or trait scoping ensures tenant boundary
        $customer->load(['accounts.savingsProduct', 'loans', 'kycDocuments']);
        
        $totalLedgerBal = $customer->accounts->sum('ledger_balance');
        $totalAvailableBal = $customer->accounts->sum('available_balance');
        
        $activeLoansBal = $customer->loans->whereIn('status', ['active', 'overdue'])->sum(function($loan) {
            return $loan->total_payable - $loan->amount_paid;
        });

        $recentTransactions = \App\Models\Transaction::whereIn('account_id', $customer->accounts->pluck('id'))
            ->with('account')
            ->latest()
            ->take(5)
            ->get();

        // Portal 360 data — query portal_* tables directly (shared DB).
        // These tables are created by bankos-portal; guard against them being absent.
        try {
            $challenges = \Illuminate\Support\Facades\Schema::hasTable('portal_savings_challenges')
                ? DB::table('portal_savings_challenges')->where('customer_id', $customer->id)->orderBy('created_at', 'desc')->get()
                : collect();
            $portal360 = [
                'challenges'         => $challenges,
                'challenges_count'   => $challenges->count(),
                'active_challenges'  => $challenges->where('status', 'active')->count(),
                'airtime'            => \Illuminate\Support\Facades\Schema::hasTable('portal_airtime_orders') ? DB::table('portal_airtime_orders')->where('customer_id', $customer->id)->latest('created_at')->take(5)->get() : collect(),
                'airtime_count'      => \Illuminate\Support\Facades\Schema::hasTable('portal_airtime_orders') ? DB::table('portal_airtime_orders')->where('customer_id', $customer->id)->count() : 0,
                'airtime_total'      => \Illuminate\Support\Facades\Schema::hasTable('portal_airtime_orders') ? DB::table('portal_airtime_orders')->where('customer_id', $customer->id)->sum('amount') : 0,
                'scheduled'          => \Illuminate\Support\Facades\Schema::hasTable('portal_scheduled_transfers') ? DB::table('portal_scheduled_transfers')->where('customer_id', $customer->id)->latest('scheduled_at')->take(5)->get() : collect(),
                'scheduled_count'    => \Illuminate\Support\Facades\Schema::hasTable('portal_scheduled_transfers') ? DB::table('portal_scheduled_transfers')->where('customer_id', $customer->id)->count() : 0,
                'scheduled_pending'  => \Illuminate\Support\Facades\Schema::hasTable('portal_scheduled_transfers') ? DB::table('portal_scheduled_transfers')->where('customer_id', $customer->id)->where('status', 'pending')->count() : 0,
                'disputes'           => \Illuminate\Support\Facades\Schema::hasTable('portal_disputes') ? DB::table('portal_disputes')->where('customer_id', $customer->id)->latest('created_at')->take(5)->get() : collect(),
                'open_disputes'      => \Illuminate\Support\Facades\Schema::hasTable('portal_disputes') ? DB::table('portal_disputes')->where('customer_id', $customer->id)->whereIn('status', ['open', 'investigating', 'escalated'])->count() : 0,
                'total_disputes'     => \Illuminate\Support\Facades\Schema::hasTable('portal_disputes') ? DB::table('portal_disputes')->where('customer_id', $customer->id)->count() : 0,
            ];
        } catch (\Exception $e) {
            $portal360 = [
                'challenges' => collect(), 'challenges_count' => 0, 'active_challenges' => 0,
                'airtime' => collect(), 'airtime_count' => 0, 'airtime_total' => 0,
                'scheduled' => collect(), 'scheduled_count' => 0, 'scheduled_pending' => 0,
                'disputes' => collect(), 'open_disputes' => 0, 'total_disputes' => 0,
            ];
        }

        // Proxy Actions data for Portal 360 workspace
        $accountIds = $customer->accounts->pluck('id');

        $proxyActions = DB::table('proxy_actions_log as pal')
            ->join('users as u', 'u.id', '=', 'pal.actor_id')
            ->where('pal.customer_id', $customer->id)
            ->where('pal.tenant_id', auth()->user()->tenant_id)
            ->select('pal.*', 'u.name as actor_name')
            ->orderByDesc('pal.created_at')
            ->take(10)
            ->get();

        $feeTransactions = \App\Models\Transaction::whereIn('account_id', $accountIds)
            ->where('type', 'fee')
            ->where('status', '!=', 'reversed')
            ->with('account')
            ->latest()
            ->take(20)
            ->get();

        // Feature flag data for the Portal Access tab
        // Feature list mirrors FeatureFlagController::$features
        $allFeatures = [
            'portal_savings_pockets'     => ['label' => 'Savings Pockets',        'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to create goal-based savings pockets'],
            'portal_investments'         => ['label' => 'Fixed Investments',      'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to create fixed-term investments'],
            'portal_loan_apply'          => ['label' => 'Online Loan Application','group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to apply for loans via the portal'],
            'portal_pay_requests'        => ['label' => 'Payment Requests',       'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to create shareable payment request links'],
            'portal_virtual_cards'       => ['label' => 'Virtual Cards',          'group' => 'Customer Portal', 'default' => true,  'desc' => 'Enable virtual debit card issuance to customers'],
            'portal_credit_score'        => ['label' => 'Credit Score',           'group' => 'Customer Portal', 'default' => true,  'desc' => 'Show customers their internal credit score'],
            'portal_fx_rates'            => ['label' => 'FX Rates',               'group' => 'Customer Portal', 'default' => true,  'desc' => 'Show live FX rates and currency converter'],
            'portal_referral'            => ['label' => 'Referral Programme',     'group' => 'Customer Portal', 'default' => true,  'desc' => 'Enable the referral programme with NGN 500 reward'],
            'portal_budget'              => ['label' => 'Budget Manager',         'group' => 'Customer Portal', 'default' => true,  'desc' => 'Monthly budget tracking with transaction categorisation'],
            'portal_disputes'            => ['label' => 'Dispute Management',     'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to raise transaction disputes online'],
            'portal_kyc_upgrade'         => ['label' => 'KYC Self-Upgrade',       'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to submit KYC upgrade documents online'],
            'portal_2fa'                 => ['label' => 'Two-Factor Auth (2FA)',  'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to enable 2FA on their portal login'],
            'portal_account_freeze'      => ['label' => 'Self-Service Freeze',    'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to freeze their own accounts temporarily'],
            'portal_bills'               => ['label' => 'Bill Payments',          'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to pay utility bills via the portal'],
            'portal_beneficiaries'       => ['label' => 'Saved Beneficiaries',    'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to save and manage transfer beneficiaries'],
            'portal_standing_orders'     => ['label' => 'Standing Orders',        'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to set up recurring automatic transfers'],
            'ussd_banking'               => ['label' => 'USSD Banking',           'group' => 'Channels',        'default' => true,  'desc' => 'Enable USSD banking channel'],
            'agent_banking'              => ['label' => 'Agent Banking',          'group' => 'Channels',        'default' => true,  'desc' => 'Enable agent banking network'],
            'nip_transfers'              => ['label' => 'NIP / Interbank',        'group' => 'Channels',        'default' => true,  'desc' => 'Enable NIP interbank transfer processing'],
            'loan_auto_disburse'         => ['label' => 'Auto-Disbursement',      'group' => 'Lending',         'default' => false, 'desc' => 'Automatically disburse loans upon approval without officer action'],
            'loan_restructure'           => ['label' => 'Loan Restructuring',     'group' => 'Lending',         'default' => true,  'desc' => 'Allow loan officers to restructure non-performing loans'],
            'loan_topup'                 => ['label' => 'Loan Top-Up',            'group' => 'Lending',         'default' => true,  'desc' => 'Allow qualifying borrowers to top up active loans'],
            'ecl_provisioning'           => ['label' => 'ECL Provisioning',       'group' => 'Lending',         'default' => true,  'desc' => 'Enable IFRS9 Expected Credit Loss provisioning engine'],
            'teller_module'              => ['label' => 'Teller Module',          'group' => 'Operations',      'default' => true,  'desc' => 'Enable teller cash management module'],
            'cheque_management'          => ['label' => 'Cheque Management',      'group' => 'Operations',      'default' => true,  'desc' => 'Enable cheque book request and clearing module'],
            'fixed_deposits'             => ['label' => 'Fixed Deposits',         'group' => 'Operations',      'default' => true,  'desc' => 'Enable fixed deposit product management'],
            'payroll_module'             => ['label' => 'Staff Payroll',          'group' => 'Operations',      'default' => true,  'desc' => 'Enable built-in staff payroll processing module'],
            'insurance_module'           => ['label' => 'Insurance',              'group' => 'Operations',      'default' => false, 'desc' => 'Enable insurance product upsell and management'],
        ];

        $tenantId = auth()->user()->tenant_id;

        $tenantFlags = DB::table('tenant_feature_flags')
            ->where('tenant_id', $tenantId)
            ->whereNull('customer_id')
            ->pluck('is_enabled', 'feature_key');

        $customerFlagRows = DB::table('tenant_feature_flags')
            ->where('tenant_id', $tenantId)
            ->where('customer_id', $customer->id)
            ->pluck('is_enabled', 'feature_key');

        $customerFeatures = collect($allFeatures)->map(function ($cfg, $key) use ($tenantFlags, $customerFlagRows) {
            $hasOverride      = $customerFlagRows->has($key);
            $tenantEnabled    = $tenantFlags->has($key) ? (bool) $tenantFlags[$key] : $cfg['default'];
            $effectiveEnabled = $hasOverride ? (bool) $customerFlagRows[$key] : $tenantEnabled;

            return array_merge($cfg, [
                'key'            => $key,
                'enabled'        => $effectiveEnabled,
                'tenant_enabled' => $tenantEnabled,
                'has_override'   => $hasOverride,
            ]);
        })->groupBy('group');

        return view('customers.show', compact(
            'customer', 'totalLedgerBal', 'totalAvailableBal', 'activeLoansBal',
            'recentTransactions', 'portal360', 'proxyActions', 'feeTransactions',
            'customerFeatures'
        ));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id . ',id,tenant_id,' . auth()->user()->tenant_id,
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer profile updated successfully.');
    }

    /**
     * Process KYC review action by a Compliance Officer.
     */
    public function reviewKyc(Request $request, Customer $customer)
    {
        // Spatie permission check - ensure user has compliance rights
        if (!auth()->user()->can('kyc.approve')) {
            abort(403, 'Unauthorized action. Only Compliance Officers can review KYC.');
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,approve_t1,approve_t2,approve_t3,reject',
            'notes'  => 'nullable|string|max:1000',
        ]);

        $action = $validated['action'];
        $notes  = $validated['notes'] ?? null;

        if ($action === 'reject') {
            $customer->update([
                'kyc_status' => 'rejected',
                'status'     => 'inactive',
            ]);
            $msg      = 'KYC Rejected. Customer remains inactive.';
            $wfStatus = 'rejected';
        } else {
            // approve / approve_t1 / approve_t2 / approve_t3
            $tier = match($action) {
                'approve_t1' => 1,
                'approve_t2' => 2,
                'approve_t3' => 3,
                default      => $customer->kyc_tier ?? 1,
            };
            $customer->update([
                'kyc_status' => 'approved',
                'kyc_tier'   => $tier,
                'status'     => 'active',
            ]);
            $msg      = "KYC Approved at Tier {$tier}. Customer is now active.";
            $wfStatus = 'approved';
        }

        // Advance/resolve workflow
        app(WorkflowService::class)->resolveForSubject(
            $customer,
            $wfStatus === 'approved' ? 'approve' : 'reject',
            $notes,
            auth()->user()
        );

        return back()->with('success', $msg);
    }

    /**
     * Upload a KYC document for the customer.
     */
    public function uploadDocument(Request $request, Customer $customer)
    {
        // Spatie permission check - ensure user can edit customers
        if (!auth()->user()->can('customers.edit')) {
            abort(403, 'Unauthorized action. You cannot upload documents.');
        }

        $validated = $request->validate([
            'document_type' => 'required|string|max:50',
            'document_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date|after:today',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        if ($request->hasFile('document_file')) {
            $path = $request->file('document_file')->store('kyc_documents', 'public');

            $customer->kycDocuments()->create([
                'tenant_id' => auth()->user()->tenant_id,
                'document_type' => $validated['document_type'],
                'document_number' => $validated['document_number'],
                'expiry_date' => $validated['expiry_date'],
                'file_path' => $path,
                'status' => 'pending',
            ]);

            return redirect()->route('customers.show', ['customer' => $customer, 'tab' => 'kyc'])
                ->with('success', 'Document uploaded successfully.');
        }

        return back()->withErrors(['document_file' => 'Failed to upload document.']);
    }

    /**
     * Activate portal access for a customer (sets a temporary password).
     */
    public function portalActivate(Request $request, Customer $customer)
    {
        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $tempPassword = strtoupper(Str::random(3)) . rand(100, 999) . strtolower(Str::random(3));

        $customer->update([
            'portal_password' => Hash::make($tempPassword),
            'portal_active'   => true,
        ]);

        return back()->with('success', "Portal access activated. Temporary password: {$tempPassword} — share this securely with the customer.");
    }

    /**
     * Deactivate portal access for a customer.
     */
    public function portalDeactivate(Customer $customer)
    {
        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $customer->update(['portal_active' => false]);

        return back()->with('success', 'Portal access has been deactivated for this customer.');
    }

    /**
     * Reset portal password and return a new temporary password.
     */
    public function portalResetPassword(Customer $customer)
    {
        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $tempPassword = strtoupper(Str::random(3)) . rand(100, 999) . strtolower(Str::random(3));

        $customer->update([
            'portal_password' => Hash::make($tempPassword),
        ]);

        return back()->with('success', "Password reset. New temporary password: {$tempPassword} — share this securely with the customer.");
    }

    /**
     * Generate an AI profile review via AiReviewService.
     */
    public function generateAiReview(Customer $customer, AiReviewService $aiReviewService)
    {
        if (!auth()->user()->can('customers.view')) {
            abort(403, 'Unauthorized action.');
        }

        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $review = $aiReviewService->generateReview($customer);

        return response()->json([
            'review' => \Illuminate\Support\Str::markdown($review)
        ]);
    }
}
