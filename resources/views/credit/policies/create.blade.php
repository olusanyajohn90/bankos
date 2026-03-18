@extends('layouts.app')

@section('title', 'New Credit Policy')

@section('content')
<div class="max-w-3xl space-y-6" x-data="{
    rules: [],
    addRule() {
        this.rules.push({
            rule_type: '',
            operator: 'gte',
            threshold_value: '',
            action_on_fail: 'refer',
            action_param: '',
            severity: 'hard',
        });
    },
    removeRule(index) {
        this.rules.splice(index, 1);
    }
}">

    {{-- Back + Title --}}
    <div>
        <a href="{{ route('credit.policies.index') }}"
           class="text-sm text-bankos-muted hover:text-bankos-primary flex items-center gap-1 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Policies
        </a>
        <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">New Credit Policy</h1>
        <p class="text-sm text-bankos-muted mt-0.5">Configure an automated credit scoring policy and attach decision rules.</p>
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

    <form action="{{ route('credit.policies.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Policy Details Card --}}
        <div class="card p-6 space-y-5">
            <h2 class="font-bold text-base text-bankos-text dark:text-bankos-dark-text border-b border-bankos-border dark:border-bankos-dark-border pb-3">
                Policy Details
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                {{-- Name --}}
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-bankos-text-sec mb-1.5">
                        Policy Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="form-input w-full" placeholder="e.g. Standard SME Loan Policy">
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-bankos-text-sec mb-1.5">
                        Description <span class="text-bankos-muted text-xs font-normal">(optional)</span>
                    </label>
                    <textarea id="description" name="description" rows="2"
                              class="form-input w-full" placeholder="Brief description of this policy's purpose...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Loan Product --}}
                <div>
                    <label for="loan_product_id" class="block text-sm font-medium text-bankos-text-sec mb-1.5">
                        Loan Product <span class="text-bankos-muted text-xs font-normal">(optional)</span>
                    </label>
                    <select id="loan_product_id" name="loan_product_id" class="form-input w-full">
                        <option value="">All Products</option>
                        @foreach($loanProducts as $product)
                            <option value="{{ $product->id }}" @selected(old('loan_product_id') == $product->id)>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-bankos-muted mt-1">Leave blank to apply to all loan products.</p>
                    @error('loan_product_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Auto-Approve --}}
                <div>
                    <label for="auto_approve_above" class="block text-sm font-medium text-bankos-text-sec mb-1.5">
                        Auto-Approve Score (&ge;) <span class="text-bankos-muted text-xs font-normal">(optional)</span>
                    </label>
                    <input type="number" id="auto_approve_above" name="auto_approve_above"
                           value="{{ old('auto_approve_above') }}" min="300" max="850"
                           class="form-input w-full" placeholder="e.g. 750">
                    <p class="text-xs text-bankos-muted mt-1">Score at or above triggers auto-approval (if no hard failures).</p>
                    @error('auto_approve_above')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Auto-Decline --}}
                <div>
                    <label for="auto_decline_below" class="block text-sm font-medium text-bankos-text-sec mb-1.5">
                        Auto-Decline Score (&lt;) <span class="text-bankos-muted text-xs font-normal">(optional)</span>
                    </label>
                    <input type="number" id="auto_decline_below" name="auto_decline_below"
                           value="{{ old('auto_decline_below') }}" min="300" max="850"
                           class="form-input w-full" placeholder="e.g. 500">
                    <p class="text-xs text-bankos-muted mt-1">Score below this threshold triggers automatic decline.</p>
                    @error('auto_decline_below')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Toggles Row --}}
                <div class="sm:col-span-2 flex flex-col sm:flex-row gap-6 pt-1">

                    {{-- Active Toggle --}}
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative" x-data="{ on: {{ old('is_active', true) ? 'true' : 'false' }} }">
                            <input type="hidden" name="is_active" :value="on ? '1' : '0'">
                            <button type="button" @click="on = !on"
                                    :class="on ? 'bg-bankos-primary' : 'bg-gray-300 dark:bg-gray-600'"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-bankos-primary focus:ring-offset-2">
                                <span :class="on ? 'translate-x-6' : 'translate-x-1'"
                                      class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-sm"></span>
                            </button>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-bankos-text-sec">Active</p>
                            <p class="text-xs text-bankos-muted">Enable this policy immediately on creation.</p>
                        </div>
                    </label>

                    {{-- Require Bureau Report --}}
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="require_bureau_report" id="require_bureau_report"
                               value="1" @checked(old('require_bureau_report', true))
                               class="h-4 w-4 rounded border-bankos-border text-bankos-primary focus:ring-bankos-primary">
                        <div>
                            <p class="text-sm font-medium text-bankos-text-sec">Require Bureau Report</p>
                            <p class="text-xs text-bankos-muted">Block evaluation if no credit bureau report is attached.</p>
                        </div>
                    </label>

                </div>
            </div>
        </div>

        {{-- Initial Rules Card --}}
        <div class="card p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-bankos-border dark:border-bankos-dark-border pb-3">
                <div>
                    <h2 class="font-bold text-base text-bankos-text dark:text-bankos-dark-text">Initial Rules</h2>
                    <p class="text-xs text-bankos-muted mt-0.5">Optionally pre-configure rules — you can also add them from the policy detail page.</p>
                </div>
                <button type="button" @click="addRule()" class="btn btn-secondary text-sm flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Rule
                </button>
            </div>

            <template x-if="rules.length === 0">
                <p class="text-sm text-bankos-muted italic py-2">No rules added yet. Rules can be configured after the policy is created.</p>
            </template>

            <div class="space-y-4">
                <template x-for="(rule, index) in rules" :key="index">
                    <div class="rounded-lg border border-bankos-border dark:border-bankos-dark-border p-4 space-y-4 bg-gray-50/50 dark:bg-bankos-dark-bg/20">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-bankos-text-sec" x-text="'Rule ' + (index + 1)"></p>
                            <button type="button" @click="removeRule(index)"
                                    class="text-red-400 hover:text-red-600 transition-colors p-1 rounded" title="Remove rule">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Rule Type</label>
                                <select :name="'rules[' + index + '][rule_type]'" x-model="rule.rule_type" class="form-input w-full text-sm">
                                    <option value="">Select type...</option>
                                    <option value="min_bureau_score">Min Bureau Score</option>
                                    <option value="max_dti_ratio">Max DTI Ratio</option>
                                    <option value="max_loan_to_income">Max Loan-to-Income</option>
                                    <option value="min_customer_age">Min Customer Age</option>
                                    <option value="max_active_loans">Max Active Loans</option>
                                    <option value="min_bvn_verified">BVN Verification Required</option>
                                    <option value="max_delinquency_count">Max Delinquency Count</option>
                                    <option value="max_outstanding_ratio">Max Outstanding Ratio</option>
                                    <option value="collateral_required">Collateral Required</option>
                                    <option value="min_kyc_tier">Min KYC Tier</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Operator</label>
                                <select :name="'rules[' + index + '][operator]'" x-model="rule.operator" class="form-input w-full text-sm">
                                    <option value="gte">&ge; (gte)</option>
                                    <option value="lte">&le; (lte)</option>
                                    <option value="eq">= (eq)</option>
                                    <option value="neq">&ne; (neq)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Threshold</label>
                                <input type="number" step="any" :name="'rules[' + index + '][threshold_value]'"
                                       x-model="rule.threshold_value" class="form-input w-full text-sm" placeholder="e.g. 600">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Action on Fail</label>
                                <select :name="'rules[' + index + '][action_on_fail]'" x-model="rule.action_on_fail" class="form-input w-full text-sm">
                                    <option value="decline">Decline</option>
                                    <option value="refer">Refer</option>
                                    <option value="flag">Flag</option>
                                    <option value="reduce_amount">Reduce Amount</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">
                                    Action Param <span class="text-bankos-muted font-normal">(optional)</span>
                                </label>
                                <input type="text" :name="'rules[' + index + '][action_param]'"
                                       x-model="rule.action_param" class="form-input w-full text-sm" placeholder="e.g. 0.8">
                                <p class="text-[10px] text-bankos-muted mt-0.5">For reduce_amount: reduction factor (0.8 = 80%)</p>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Severity</label>
                                <select :name="'rules[' + index + '][severity]'" x-model="rule.severity" class="form-input w-full text-sm">
                                    <option value="hard">Hard (blocking)</option>
                                    <option value="soft">Soft (advisory)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex justify-end gap-3 pb-2">
            <a href="{{ route('credit.policies.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Policy</button>
        </div>

    </form>
</div>
@endsection
