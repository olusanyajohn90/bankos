<?php

namespace App\Http\Controllers\Credit;

use App\Http\Controllers\Controller;
use App\Models\CreditDecision;
use App\Models\CreditPolicy;
use App\Models\CreditPolicyRule;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Services\Credit\CreditPolicyService;
use Illuminate\Http\Request;

class CreditPolicyController extends Controller
{
    /**
     * List all credit policies with rule counts.
     */
    public function index()
    {
        $policies = CreditPolicy::withCount('rules')
            ->with('loanProduct')
            ->latest()
            ->paginate(20);

        return view('credit.policies.index', compact('policies'));
    }

    /**
     * Show the create policy form.
     */
    public function create()
    {
        $loanProducts = LoanProduct::all();

        return view('credit.policies.create', compact('loanProducts'));
    }

    /**
     * Store a new policy with optional initial rules.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:150',
            'description'           => 'nullable|string',
            'loan_product_id'       => 'nullable|exists:loan_products,id',
            'is_active'             => 'boolean',
            'auto_approve_above'    => 'nullable|integer|min:300|max:850',
            'auto_decline_below'    => 'nullable|integer|min:300|max:850',
            'require_bureau_report' => 'boolean',
            'rules'                 => 'nullable|array',
            'rules.*.rule_type'     => 'required|string',
            'rules.*.operator'      => 'required|in:gte,lte,eq,neq',
            'rules.*.threshold_value' => 'required|numeric',
            'rules.*.action_on_fail'  => 'required|in:decline,refer,flag,reduce_amount',
            'rules.*.action_param'    => 'nullable|string|max:50',
            'rules.*.severity'        => 'required|in:hard,soft',
        ]);

        $policy = CreditPolicy::create([
            'name'                  => $validated['name'],
            'description'           => $validated['description'] ?? null,
            'loan_product_id'       => $validated['loan_product_id'] ?? null,
            'is_active'             => $request->boolean('is_active', true),
            'auto_approve_above'    => $validated['auto_approve_above'] ?? null,
            'auto_decline_below'    => $validated['auto_decline_below'] ?? null,
            'require_bureau_report' => $request->boolean('require_bureau_report', true),
        ]);

        foreach ($validated['rules'] ?? [] as $ruleData) {
            $policy->rules()->create([
                'rule_type'       => $ruleData['rule_type'],
                'operator'        => $ruleData['operator'],
                'threshold_value' => $ruleData['threshold_value'],
                'action_on_fail'  => $ruleData['action_on_fail'],
                'action_param'    => $ruleData['action_param'] ?? null,
                'severity'        => $ruleData['severity'],
                'is_active'       => true,
            ]);
        }

        return redirect()->route('credit.policies.show', $policy)
            ->with('success', "Credit policy \"{$policy->name}\" created successfully.");
    }

    /**
     * Show policy detail with its rules and recent decisions.
     */
    public function show(CreditPolicy $creditPolicy)
    {
        $creditPolicy->load(['rules', 'loanProduct']);

        $decisions = CreditDecision::with(['loan.customer'])
            ->where('policy_id', $creditPolicy->id)
            ->latest()
            ->paginate(15);

        $loanProducts = LoanProduct::all();

        return view('credit.policies.show', compact('creditPolicy', 'decisions', 'loanProducts'));
    }

    /**
     * Update policy fields (not rules — use storeRule / destroyRule).
     */
    public function update(Request $request, CreditPolicy $creditPolicy)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:150',
            'description'           => 'nullable|string',
            'loan_product_id'       => 'nullable|exists:loan_products,id',
            'is_active'             => 'boolean',
            'auto_approve_above'    => 'nullable|integer|min:300|max:850',
            'auto_decline_below'    => 'nullable|integer|min:300|max:850',
            'require_bureau_report' => 'boolean',
        ]);

        $creditPolicy->update([
            'name'                  => $validated['name'],
            'description'           => $validated['description'] ?? null,
            'loan_product_id'       => $validated['loan_product_id'] ?? null,
            'is_active'             => $request->boolean('is_active', true),
            'auto_approve_above'    => $validated['auto_approve_above'] ?? null,
            'auto_decline_below'    => $validated['auto_decline_below'] ?? null,
            'require_bureau_report' => $request->boolean('require_bureau_report', true),
        ]);

        return redirect()->route('credit.policies.show', $creditPolicy)
            ->with('success', 'Policy updated.');
    }

    /**
     * Add a rule to a policy.
     */
    public function storeRule(Request $request, CreditPolicy $creditPolicy)
    {
        $validated = $request->validate([
            'rule_type'       => 'required|in:min_bureau_score,max_dti_ratio,max_loan_to_income,min_customer_age,max_active_loans,min_bvn_verified,max_delinquency_count,max_outstanding_ratio,collateral_required,min_kyc_tier',
            'operator'        => 'required|in:gte,lte,eq,neq',
            'threshold_value' => 'required|numeric',
            'action_on_fail'  => 'required|in:decline,refer,flag,reduce_amount',
            'action_param'    => 'nullable|string|max:50',
            'severity'        => 'required|in:hard,soft',
        ]);

        $creditPolicy->rules()->create(array_merge($validated, ['is_active' => true]));

        return redirect()->route('credit.policies.show', $creditPolicy)
            ->with('success', 'Rule added to policy.');
    }

    /**
     * Delete a policy rule.
     */
    public function destroyRule(CreditPolicyRule $rule)
    {
        $policy = $rule->policy;
        $rule->delete();

        return redirect()->route('credit.policies.show', $policy)
            ->with('success', 'Rule removed.');
    }

    /**
     * Manually trigger policy evaluation for a loan. Returns JSON.
     */
    public function evaluate(Request $request, Loan $loan)
    {
        // Delete existing decision so we get a fresh evaluation
        CreditDecision::where('loan_id', $loan->id)->delete();

        $service  = app(CreditPolicyService::class);
        $decision = $service->evaluate($loan);
        $service->applyDecision($decision);

        return response()->json([
            'success'        => true,
            'recommendation' => $decision->recommendation,
            'final_score'    => $decision->final_score,
            'bureau_score'   => $decision->bureau_score,
            'internal_score' => $decision->internal_score,
            'auto_decided'   => $decision->auto_decided,
            'rules_passed'   => count($decision->rules_passed ?? []),
            'rules_failed'   => count($decision->rules_failed ?? []),
            'conditions'     => $decision->conditions ?? [],
            'notes'          => $decision->notes,
        ]);
    }
}
