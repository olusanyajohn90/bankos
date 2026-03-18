<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeatureFlagController extends Controller
{
    // All configurable features with default state and description
    private static array $features = [
        // Portal features
        'portal_savings_pockets'     => ['label' => 'Savings Pockets',       'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to create goal-based savings pockets'],
        'portal_investments'         => ['label' => 'Fixed Investments',     'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to create fixed-term investments'],
        'portal_loan_apply'          => ['label' => 'Online Loan Application','group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to apply for loans via the portal'],
        'portal_pay_requests'        => ['label' => 'Payment Requests',      'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to create shareable payment request links'],
        'portal_virtual_cards'       => ['label' => 'Virtual Cards',         'group' => 'Customer Portal', 'default' => true,  'desc' => 'Enable virtual debit card issuance to customers'],
        'portal_credit_score'        => ['label' => 'Credit Score',          'group' => 'Customer Portal', 'default' => true,  'desc' => 'Show customers their internal credit score'],
        'portal_fx_rates'            => ['label' => 'FX Rates',              'group' => 'Customer Portal', 'default' => true,  'desc' => 'Show live FX rates and currency converter'],
        'portal_referral'            => ['label' => 'Referral Programme',    'group' => 'Customer Portal', 'default' => true,  'desc' => 'Enable the referral programme with NGN 500 reward'],
        'portal_budget'              => ['label' => 'Budget Manager',        'group' => 'Customer Portal', 'default' => true,  'desc' => 'Monthly budget tracking with transaction categorisation'],
        'portal_disputes'            => ['label' => 'Dispute Management',    'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to raise transaction disputes online'],
        'portal_kyc_upgrade'         => ['label' => 'KYC Self-Upgrade',      'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to submit KYC upgrade documents online'],
        'portal_2fa'                 => ['label' => 'Two-Factor Auth (2FA)', 'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to enable 2FA on their portal login'],
        'portal_account_freeze'      => ['label' => 'Self-Service Freeze',   'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to freeze their own accounts temporarily'],
        'portal_bills'               => ['label' => 'Bill Payments',         'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to pay utility bills via the portal'],
        'portal_beneficiaries'       => ['label' => 'Saved Beneficiaries',   'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to save and manage transfer beneficiaries'],
        'portal_standing_orders'     => ['label' => 'Standing Orders',       'group' => 'Customer Portal', 'default' => true,  'desc' => 'Allow customers to set up recurring automatic transfers'],
        // Channels
        'ussd_banking'               => ['label' => 'USSD Banking',          'group' => 'Channels',        'default' => true,  'desc' => 'Enable USSD banking channel'],
        'agent_banking'              => ['label' => 'Agent Banking',         'group' => 'Channels',        'default' => true,  'desc' => 'Enable agent banking network'],
        'nip_transfers'              => ['label' => 'NIP / Interbank',       'group' => 'Channels',        'default' => true,  'desc' => 'Enable NIP interbank transfer processing'],
        // Lending
        'loan_auto_disburse'         => ['label' => 'Auto-Disbursement',     'group' => 'Lending',         'default' => false, 'desc' => 'Automatically disburse loans upon approval without officer action'],
        'loan_restructure'           => ['label' => 'Loan Restructuring',    'group' => 'Lending',         'default' => true,  'desc' => 'Allow loan officers to restructure non-performing loans'],
        'loan_topup'                 => ['label' => 'Loan Top-Up',           'group' => 'Lending',         'default' => true,  'desc' => 'Allow qualifying borrowers to top up active loans'],
        'ecl_provisioning'           => ['label' => 'ECL Provisioning',      'group' => 'Lending',         'default' => true,  'desc' => 'Enable IFRS9 Expected Credit Loss provisioning engine'],
        // Operations
        'teller_module'              => ['label' => 'Teller Module',         'group' => 'Operations',      'default' => true,  'desc' => 'Enable teller cash management module'],
        'cheque_management'          => ['label' => 'Cheque Management',     'group' => 'Operations',      'default' => true,  'desc' => 'Enable cheque book request and clearing module'],
        'fixed_deposits'             => ['label' => 'Fixed Deposits',        'group' => 'Operations',      'default' => true,  'desc' => 'Enable fixed deposit product management'],
        'payroll_module'             => ['label' => 'Staff Payroll',         'group' => 'Operations',      'default' => true,  'desc' => 'Enable built-in staff payroll processing module'],
        'insurance_module'           => ['label' => 'Insurance',             'group' => 'Operations',      'default' => false, 'desc' => 'Enable insurance product upsell and management'],
    ];

    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        // Load saved flags from DB
        $saved = DB::table('tenant_feature_flags')
            ->where('tenant_id', $tenantId)
            ->pluck('is_enabled', 'feature_key');

        $features = collect(self::$features)->map(function ($cfg, $key) use ($saved) {
            $cfg['key']     = $key;
            $cfg['enabled'] = $saved->has($key) ? (bool)$saved[$key] : $cfg['default'];
            return $cfg;
        })->groupBy('group');

        return view('feature-flags.index', compact('features'));
    }

    public function toggle(string $key)
    {
        $tenantId = auth()->user()->tenant_id;

        if (!array_key_exists($key, self::$features)) {
            return response()->json(['error' => 'Unknown feature'], 404);
        }

        $current = DB::table('tenant_feature_flags')
            ->where('tenant_id', $tenantId)
            ->where('feature_key', $key)
            ->value('is_enabled');

        $default = self::$features[$key]['default'];
        $newState = $current === null ? !$default : !$current;

        DB::table('tenant_feature_flags')->updateOrInsert(
            ['tenant_id' => $tenantId, 'feature_key' => $key],
            ['is_enabled' => $newState ? 1 : 0, 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['enabled' => $newState]);
    }

    public function update(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $enabled  = $request->input('flags', []);

        DB::transaction(function () use ($tenantId, $enabled) {
            foreach (self::$features as $key => $cfg) {
                DB::table('tenant_feature_flags')->updateOrInsert(
                    ['tenant_id' => $tenantId, 'feature_key' => $key],
                    ['is_enabled' => in_array($key, $enabled) ? 1 : 0, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        });

        return back()->with('success', 'Feature flags updated successfully.');
    }

    /**
     * Return JSON of feature flag states for a specific customer.
     * Tenant-level flags (customer_id IS NULL) form the base; customer-specific
     * overrides (customer_id = $customer->id) take precedence.
     */
    public function customerFlags(Customer $customer)
    {
        $tenantId   = auth()->user()->tenant_id;
        $customerId = $customer->id;

        // Tenant-wide flags (customer_id IS NULL)
        $tenantFlags = DB::table('tenant_feature_flags')
            ->where('tenant_id', $tenantId)
            ->whereNull('customer_id')
            ->pluck('is_enabled', 'feature_key');

        // Customer-specific overrides
        $customerFlags = DB::table('tenant_feature_flags')
            ->where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->pluck('is_enabled', 'feature_key');

        $result = [];
        foreach (self::$features as $key => $cfg) {
            if ($customerFlags->has($key)) {
                $result[$key] = [
                    'enabled' => (bool) $customerFlags[$key],
                    'source'  => 'customer',
                    'label'   => $cfg['label'],
                    'group'   => $cfg['group'],
                    'desc'    => $cfg['desc'],
                    'default' => $cfg['default'],
                ];
            } else {
                $result[$key] = [
                    'enabled' => $tenantFlags->has($key) ? (bool) $tenantFlags[$key] : $cfg['default'],
                    'source'  => 'tenant',
                    'label'   => $cfg['label'],
                    'group'   => $cfg['group'],
                    'desc'    => $cfg['desc'],
                    'default' => $cfg['default'],
                ];
            }
        }

        return response()->json($result);
    }

    /**
     * Upsert customer-specific feature flag overrides.
     *
     * POST body:
     *   flags[]        – array of feature_key strings that should be ENABLED
     *   reset_all      – if truthy, delete all customer overrides and return
     */
    public function customerFlagsUpdate(Request $request, Customer $customer)
    {
        $tenantId   = auth()->user()->tenant_id;
        $customerId = $customer->id;

        if ($request->boolean('reset_all')) {
            DB::table('tenant_feature_flags')
                ->where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->delete();

            return back()->with('success', 'Customer feature overrides have been reset to tenant defaults.');
        }

        $enabled = $request->input('flags', []);

        DB::transaction(function () use ($tenantId, $customerId, $enabled) {
            foreach (self::$features as $key => $cfg) {
                DB::table('tenant_feature_flags')->updateOrInsert(
                    [
                        'tenant_id'   => $tenantId,
                        'customer_id' => $customerId,
                        'feature_key' => $key,
                    ],
                    [
                        'is_enabled' => in_array($key, $enabled) ? 1 : 0,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        });

        return back()->with('success', 'Customer feature overrides saved successfully.');
    }
}
