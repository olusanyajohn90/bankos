@extends('layouts.app')

@section('title', $creditPolicy->name)

@section('content')
<div class="space-y-6" x-data="{ editMode: false, showAddRule: false }">

    {{-- Back --}}
    <div>
        <a href="{{ route('credit.policies.index') }}"
           class="text-sm text-bankos-muted hover:text-bankos-primary flex items-center gap-1 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Policies
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 px-4 py-3 flex items-start gap-3 text-sm text-green-800 dark:text-green-300">
            <svg class="h-5 w-5 flex-shrink-0 text-green-500 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 px-4 py-3 flex items-start gap-3 text-sm text-red-800 dark:text-red-300">
            <svg class="h-5 w-5 flex-shrink-0 text-red-500 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Page Title Row --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ $creditPolicy->name }}</h1>
            @if($creditPolicy->is_active)
                <span class="badge badge-success">Active</span>
            @else
                <span class="badge badge-danger">Inactive</span>
            @endif
        </div>
        <button @click="editMode = !editMode"
                :class="editMode ? 'btn btn-secondary ring-2 ring-bankos-primary/30' : 'btn btn-secondary'"
                class="flex items-center gap-1.5 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            <span x-text="editMode ? 'Cancel Edit' : 'Edit Policy'"></span>
        </button>
    </div>

    {{-- Stats Strip --}}
    <div class="card p-5">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 divide-x divide-bankos-border dark:divide-bankos-dark-border">
            <div class="text-center px-4">
                <p class="text-2xl font-bold text-bankos-primary">{{ $creditPolicy->rules->count() }}</p>
                <p class="text-xs text-bankos-muted mt-0.5">Total Rules</p>
            </div>
            <div class="text-center px-4">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $creditPolicy->auto_approve_above ?? '—' }}</p>
                <p class="text-xs text-bankos-muted mt-0.5">Auto-Approve &ge;</p>
            </div>
            <div class="text-center px-4">
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $creditPolicy->auto_decline_below ?? '—' }}</p>
                <p class="text-xs text-bankos-muted mt-0.5">Auto-Decline &lt;</p>
            </div>
            <div class="text-center px-4">
                <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ $decisions->total() }}</p>
                <p class="text-xs text-bankos-muted mt-0.5">Decisions Made</p>
            </div>
        </div>
    </div>

    {{-- Two-Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

        {{-- LEFT COLUMN — Policy Details + Inline Edit --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Policy Details Card --}}
            <div class="card p-6">
                <h2 class="font-bold text-base text-bankos-text dark:text-bankos-dark-text border-b border-bankos-border dark:border-bankos-dark-border pb-3 mb-4">
                    Policy Details
                </h2>

                <dl class="space-y-3 text-sm">
                    @if($creditPolicy->description)
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-bankos-muted font-semibold mb-0.5">Description</dt>
                        <dd class="text-bankos-text dark:text-bankos-dark-text">{{ $creditPolicy->description }}</dd>
                    </div>
                    @endif

                    <div>
                        <dt class="text-xs uppercase tracking-wider text-bankos-muted font-semibold mb-0.5">Loan Product</dt>
                        <dd>
                            @if($creditPolicy->loanProduct)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-700">
                                    {{ $creditPolicy->loanProduct->name }}
                                </span>
                            @else
                                <span class="text-bankos-muted italic">All Products</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs uppercase tracking-wider text-bankos-muted font-semibold mb-0.5">Status</dt>
                        <dd>
                            @if($creditPolicy->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </dd>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-bankos-muted font-semibold mb-0.5">Auto-Approve &ge;</dt>
                            <dd class="font-bold text-green-600 dark:text-green-400">
                                {{ $creditPolicy->auto_approve_above ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-bankos-muted font-semibold mb-0.5">Auto-Decline &lt;</dt>
                            <dd class="font-bold text-red-600 dark:text-red-400">
                                {{ $creditPolicy->auto_decline_below ?? '—' }}
                            </dd>
                        </div>
                    </div>

                    <div class="pt-1">
                        <dt class="text-xs uppercase tracking-wider text-bankos-muted font-semibold mb-0.5">Bureau Report</dt>
                        <dd>
                            @if($creditPolicy->require_bureau_report)
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 dark:text-green-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    Required
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-bankos-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    Not Required
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div class="pt-1 text-xs text-bankos-muted border-t border-bankos-border dark:border-bankos-dark-border mt-2">
                        Created {{ $creditPolicy->created_at->format('d M Y') }}
                        &middot; Updated {{ $creditPolicy->updated_at->diffForHumans() }}
                    </div>
                </dl>
            </div>

            {{-- Inline Edit Form --}}
            <div x-show="editMode" x-transition class="card p-6">
                <h2 class="font-bold text-base text-bankos-text dark:text-bankos-dark-text border-b border-bankos-border dark:border-bankos-dark-border pb-3 mb-4">
                    Edit Policy
                </h2>

                <form action="{{ route('credit.policies.update', $creditPolicy) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="edit_name" class="block text-xs font-medium text-bankos-text-sec mb-1">
                            Policy Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="edit_name" name="name" value="{{ old('name', $creditPolicy->name) }}"
                               required class="form-input w-full">
                    </div>

                    <div>
                        <label for="edit_description" class="block text-xs font-medium text-bankos-text-sec mb-1">Description</label>
                        <textarea id="edit_description" name="description" rows="2"
                                  class="form-input w-full">{{ old('description', $creditPolicy->description) }}</textarea>
                    </div>

                    <div>
                        <label for="edit_loan_product_id" class="block text-xs font-medium text-bankos-text-sec mb-1">Product Scope</label>
                        <select id="edit_loan_product_id" name="loan_product_id" class="form-input w-full">
                            <option value="">All Loan Products</option>
                            @foreach($loanProducts as $product)
                                <option value="{{ $product->id }}"
                                    @selected(old('loan_product_id', $creditPolicy->loan_product_id) == $product->id)>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_auto_approve_above" class="block text-xs font-medium text-bankos-text-sec mb-1">
                                Auto-Approve &ge;
                            </label>
                            <input type="number" id="edit_auto_approve_above" name="auto_approve_above"
                                   value="{{ old('auto_approve_above', $creditPolicy->auto_approve_above) }}"
                                   min="300" max="850" class="form-input w-full" placeholder="e.g. 750">
                        </div>
                        <div>
                            <label for="edit_auto_decline_below" class="block text-xs font-medium text-bankos-text-sec mb-1">
                                Auto-Decline &lt;
                            </label>
                            <input type="number" id="edit_auto_decline_below" name="auto_decline_below"
                                   value="{{ old('auto_decline_below', $creditPolicy->auto_decline_below) }}"
                                   min="300" max="850" class="form-input w-full" placeholder="e.g. 500">
                        </div>
                    </div>

                    <div class="space-y-3 pt-1">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                                   @checked(old('is_active', $creditPolicy->is_active))
                                   class="h-4 w-4 rounded border-bankos-border text-bankos-primary focus:ring-bankos-primary">
                            <span class="text-sm text-bankos-text-sec">Policy is Active</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="require_bureau_report" id="edit_require_bureau"
                                   value="1" @checked(old('require_bureau_report', $creditPolicy->require_bureau_report))
                                   class="h-4 w-4 rounded border-bankos-border text-bankos-primary focus:ring-bankos-primary">
                            <span class="text-sm text-bankos-text-sec">Require Bureau Report</span>
                        </label>
                    </div>

                    <div class="flex gap-2 justify-end pt-2">
                        <button type="button" @click="editMode = false" class="btn btn-secondary text-sm">Cancel</button>
                        <button type="submit" class="btn btn-primary text-sm">Save Changes</button>
                    </div>
                </form>
            </div>

        </div>{{-- /LEFT COLUMN --}}

        {{-- RIGHT COLUMN — Rules + Add Rule + Recent Decisions --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- Rules Card --}}
            <div class="card p-0 overflow-hidden">
                <div class="px-5 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex items-center justify-between bg-gray-50/50 dark:bg-bankos-dark-bg/20">
                    <div>
                        <h2 class="font-bold text-base text-bankos-text dark:text-bankos-dark-text">Policy Rules</h2>
                        <p class="text-xs text-bankos-muted mt-0.5">Rules are evaluated sequentially for each loan application.</p>
                    </div>
                    <button @click="showAddRule = !showAddRule"
                            class="btn btn-secondary text-sm flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Rule
                    </button>
                </div>

                {{-- Add Rule Form --}}
                <div x-show="showAddRule" x-transition
                     class="px-5 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-blue-50/40 dark:bg-blue-900/10">
                    <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-3">New Rule</p>
                    <form action="{{ route('credit.policies.rules.store', $creditPolicy) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">

                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Rule Type</label>
                                <select name="rule_type" class="form-input w-full text-sm" required>
                                    <option value="">Select type...</option>
                                    <option value="min_bureau_score">Min Bureau Score</option>
                                    <option value="max_dti_ratio">Max DTI Ratio</option>
                                    <option value="max_loan_to_income">Max Loan-to-Income</option>
                                    <option value="min_customer_age">Min Customer Age</option>
                                    <option value="max_active_loans">Max Active Loans</option>
                                    <option value="min_bvn_verified">BVN Verified</option>
                                    <option value="max_delinquency_count">Max Delinquency Count</option>
                                    <option value="max_outstanding_ratio">Max Outstanding Ratio</option>
                                    <option value="collateral_required">Collateral Required</option>
                                    <option value="min_kyc_tier">Min KYC Tier</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Operator</label>
                                <select name="operator" class="form-input w-full text-sm" required>
                                    <option value="gte">&ge; gte</option>
                                    <option value="lte">&le; lte</option>
                                    <option value="eq">= eq</option>
                                    <option value="neq">&ne; neq</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Threshold</label>
                                <input type="number" step="any" name="threshold_value"
                                       class="form-input w-full text-sm" required placeholder="e.g. 600">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Action on Fail</label>
                                <select name="action_on_fail" class="form-input w-full text-sm" required>
                                    <option value="decline">Decline</option>
                                    <option value="refer">Refer</option>
                                    <option value="flag">Flag</option>
                                    <option value="reduce_amount">Reduce Amount</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Severity</label>
                                <select name="severity" class="form-input w-full text-sm" required>
                                    <option value="hard">Hard (blocking)</option>
                                    <option value="soft">Soft (advisory)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">
                                    Action Param <span class="text-bankos-muted font-normal">(optional)</span>
                                </label>
                                <input type="text" name="action_param"
                                       class="form-input w-full text-sm" placeholder="e.g. 0.8">
                            </div>

                        </div>
                        <div class="flex justify-end mt-3">
                            <button type="button" @click="showAddRule = false" class="btn btn-secondary text-sm mr-2">Cancel</button>
                            <button type="submit" class="btn btn-primary text-sm">Add Rule</button>
                        </div>
                    </form>
                </div>

                {{-- Rules List --}}
                @php
                    $ruleLabels = [
                        'min_bureau_score'       => 'Min Bureau Score',
                        'max_dti_ratio'          => 'Max DTI Ratio',
                        'max_loan_to_income'     => 'Max Loan-to-Income',
                        'min_customer_age'       => 'Min Customer Age',
                        'max_active_loans'       => 'Max Active Loans',
                        'min_bvn_verified'       => 'BVN Verified',
                        'max_delinquency_count'  => 'Max Delinquency Count',
                        'max_outstanding_ratio'  => 'Max Outstanding Ratio',
                        'collateral_required'    => 'Collateral Required',
                        'min_kyc_tier'           => 'Min KYC Tier',
                    ];
                    $operatorSymbols = [
                        'gte' => '≥',
                        'lte' => '≤',
                        'eq'  => '=',
                        'neq' => '≠',
                    ];
                @endphp

                @if($creditPolicy->rules->isEmpty())
                    <div class="p-10 text-center text-bankos-muted text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto opacity-30 mb-3"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                        <p>No rules configured yet. Use the Add Rule button above.</p>
                    </div>
                @else
                <div class="overflow-x-auto">
                    <table class="table-auto w-full text-left text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-bankos-dark-bg/40 text-xs uppercase tracking-wider text-bankos-text-sec border-b border-bankos-border dark:border-bankos-dark-border">
                                <th class="px-5 py-3 font-semibold">Rule</th>
                                <th class="px-5 py-3 font-semibold text-center">Threshold</th>
                                <th class="px-5 py-3 font-semibold text-center">Severity</th>
                                <th class="px-5 py-3 font-semibold text-center">Action on Fail</th>
                                <th class="px-5 py-3 font-semibold text-center">Status</th>
                                <th class="px-5 py-3 font-semibold text-right">Remove</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                            @foreach($creditPolicy->rules as $rule)
                            @php
                                $actionBadge = match($rule->action_on_fail) {
                                    'decline'       => 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-700',
                                    'refer'         => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700',
                                    'flag'          => 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-700',
                                    'reduce_amount' => 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-700',
                                    default         => 'bg-gray-100 text-gray-600 border-gray-200',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-bankos-text dark:text-bankos-dark-text">
                                        {{ $ruleLabels[$rule->rule_type] ?? $rule->rule_type }}
                                    </p>
                                    @if($rule->action_param)
                                        <p class="text-xs text-bankos-muted mt-0.5">param: {{ $rule->action_param }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="font-mono font-semibold text-bankos-text dark:text-bankos-dark-text">
                                        {{ $operatorSymbols[$rule->operator] ?? $rule->operator }}
                                        {{ number_format($rule->threshold_value, 2) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if($rule->severity === 'hard')
                                        <span class="badge badge-danger text-xs">Hard</span>
                                    @else
                                        <span class="badge badge-pending text-xs">Soft</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold border {{ $actionBadge }}">
                                        {{ ucfirst(str_replace('_', ' ', $rule->action_on_fail)) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if($rule->is_active)
                                        <span class="badge badge-success text-xs">On</span>
                                    @else
                                        <span class="badge badge-danger text-xs">Off</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <form action="{{ route('credit.policies.rules.destroy', $rule) }}" method="POST"
                                          onsubmit="return confirm('Remove this rule from the policy?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-400 hover:text-red-600 transition-colors p-1 rounded"
                                                title="Delete rule">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>{{-- /Rules Card --}}

            {{-- Recent Decisions Card --}}
            <div class="card p-0 overflow-hidden">
                <div class="px-5 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20">
                    <h2 class="font-bold text-base text-bankos-text dark:text-bankos-dark-text">Recent Decisions</h2>
                    <p class="text-xs text-bankos-muted mt-0.5">Credit decisions evaluated against this policy.</p>
                </div>

                @if($decisions->isEmpty())
                    <div class="p-10 text-center text-bankos-muted text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto opacity-30 mb-3"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        <p>No decisions made with this policy yet.</p>
                    </div>
                @else
                <div class="overflow-x-auto">
                    <table class="table-auto w-full text-left text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-bankos-dark-bg/40 text-xs uppercase tracking-wider text-bankos-text-sec border-b border-bankos-border dark:border-bankos-dark-border">
                                <th class="px-5 py-3 font-semibold">Loan</th>
                                <th class="px-5 py-3 font-semibold">Customer</th>
                                <th class="px-5 py-3 font-semibold text-center">Score</th>
                                <th class="px-5 py-3 font-semibold text-center">Recommendation</th>
                                <th class="px-5 py-3 font-semibold text-center">Auto</th>
                                <th class="px-5 py-3 font-semibold text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                            @foreach($decisions as $decision)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-5 py-3">
                                    @if($decision->loan)
                                        <a href="{{ route('loans.show', $decision->loan) }}"
                                           class="font-mono text-xs font-semibold text-bankos-primary hover:underline">
                                            {{ $decision->loan->loan_number ?? $decision->loan->loan_reference ?? '#' . $decision->loan->id }}
                                        </a>
                                    @else
                                        <span class="text-bankos-muted">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-bankos-text dark:text-bankos-dark-text">
                                    {{ $decision->loan?->customer?->full_name ?? '—' }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="font-bold text-bankos-text dark:text-bankos-dark-text">
                                        {{ $decision->final_score ?? '—' }}
                                    </span>
                                    @if($decision->bureau_score || $decision->internal_score)
                                        <p class="text-[10px] text-bankos-muted mt-0.5">
                                            B:{{ $decision->bureau_score ?? '—' }}
                                            &nbsp;I:{{ $decision->internal_score ?? '—' }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if($decision->recommendation === 'approve')
                                        <span class="badge badge-success text-xs">Approve</span>
                                    @elseif($decision->recommendation === 'decline')
                                        <span class="badge badge-danger text-xs">Decline</span>
                                    @elseif($decision->recommendation === 'refer')
                                        <span class="badge badge-pending text-xs">Refer</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold border bg-gray-100 text-gray-600 border-gray-200">
                                            {{ ucfirst($decision->recommendation) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if($decision->auto_decided)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-bankos-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                            Auto
                                        </span>
                                    @else
                                        <span class="text-xs text-bankos-muted">Manual</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right text-xs text-bankos-muted whitespace-nowrap">
                                    {{ $decision->created_at->format('d M Y') }}
                                    <span class="block text-[10px]">{{ $decision->created_at->format('H:i') }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($decisions->hasPages())
                <div class="px-5 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
                    {{ $decisions->links() }}
                </div>
                @endif
                @endif
            </div>{{-- /Recent Decisions --}}

        </div>{{-- /RIGHT COLUMN --}}

    </div>{{-- /Two-Column Layout --}}

</div>
@endsection
