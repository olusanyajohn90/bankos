<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-bankos-text dark:text-bankos-dark-text">Fee Configuration</h2>
                <p class="text-sm text-bankos-muted mt-0.5">Manage transaction fee rules for your institution</p>
            </div>
            <button
                @click="showAddModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90 transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Fee Rule
            </button>
        </div>
    </x-slot>

    <div
        x-data="{
            showAddModal: false,
            showEditModal: false,
            editRule: {},
            feeType: 'flat',
            editFeeType: 'flat',
            csrfToken: '{{ csrf_token() }}',

            openEdit(rule) {
                this.editRule = { ...rule };
                this.editFeeType = rule.fee_type;
                this.showEditModal = true;
            },

            async toggleActive(id, pill) {
                const res = await fetch(`/fee-rules/${id}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                if (data.success) {
                    pill.classList.toggle('bg-green-100', data.is_active);
                    pill.classList.toggle('text-green-700', data.is_active);
                    pill.classList.toggle('dark:bg-green-900/30', data.is_active);
                    pill.classList.toggle('dark:text-green-400', data.is_active);
                    pill.classList.toggle('bg-gray-100', !data.is_active);
                    pill.classList.toggle('text-gray-500', !data.is_active);
                    pill.classList.toggle('dark:bg-gray-700', !data.is_active);
                    pill.classList.toggle('dark:text-gray-400', !data.is_active);
                    pill.querySelector('span.pill-label').textContent = data.is_active ? 'Active' : 'Inactive';
                }
            }
        }"
        class="p-6 space-y-6"
    >

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            {{ session('error') }}
        </div>
        @endif

        @if($grouped->isEmpty())
        {{-- Empty state --}}
        <div class="flex flex-col items-center justify-center py-20 text-center bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border">
            <div class="w-14 h-14 rounded-full bg-bankos-light dark:bg-bankos-primary/10 flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-primary"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <p class="text-bankos-text dark:text-bankos-dark-text font-medium">No fee rules configured yet.</p>
            <p class="text-bankos-muted text-sm mt-1">Add your first rule to start charging transaction fees.</p>
            <button
                @click="showAddModal = true"
                class="mt-5 inline-flex items-center gap-2 px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90 transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Fee Rule
            </button>
        </div>
        @else

        @php
        $typeLabels = [
            'transfer'        => 'Transfer',
            'withdrawal'      => 'Withdrawal',
            'bill_payment'    => 'Bill Payment',
            'airtime'         => 'Airtime',
            'loan_repayment'  => 'Loan Repayment',
            'fee'             => 'Fee',
            'deposit'         => 'Deposit',
        ];
        @endphp

        @foreach($grouped as $transactionType => $rules)
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="px-5 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-primary"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">
                    {{ $typeLabels[$transactionType] ?? ucwords(str_replace('_', ' ', $transactionType)) }}
                </h3>
                <span class="ml-auto text-xs text-bankos-muted">{{ $rules->count() }} {{ Str::plural('rule', $rules->count()) }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-bankos-dark-bg text-left">
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Account Type</th>
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Fee Type</th>
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Amount</th>
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Min / Max Fee</th>
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Txn Range</th>
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Waivable</th>
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Active</th>
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @foreach($rules as $rule)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors">
                            <td class="px-4 py-3 font-medium text-bankos-text dark:text-bankos-dark-text">{{ $rule->name }}</td>
                            <td class="px-4 py-3 text-bankos-text-sec dark:text-bankos-dark-text-sec">
                                {{ $rule->account_type ? ucfirst($rule->account_type) : 'All Types' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $rule->fee_type === 'flat' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' }}">
                                    {{ ucfirst($rule->fee_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-bankos-text dark:text-bankos-dark-text font-mono">
                                @if($rule->fee_type === 'flat')
                                    &#x20A6;{{ number_format($rule->amount, 2) }}
                                @else
                                    {{ $rule->amount }}%
                                @endif
                            </td>
                            <td class="px-4 py-3 text-bankos-text-sec dark:text-bankos-dark-text-sec font-mono text-xs">
                                @if($rule->fee_type === 'percentage')
                                    {{ $rule->min_fee !== null ? '&#x20A6;'.number_format($rule->min_fee, 2) : '—' }}
                                    /
                                    {{ $rule->max_fee !== null ? '&#x20A6;'.number_format($rule->max_fee, 2) : '—' }}
                                @else
                                    <span class="text-bankos-muted">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-bankos-text-sec dark:text-bankos-dark-text-sec font-mono text-xs">
                                {{ $rule->min_transaction_amount !== null ? '&#x20A6;'.number_format($rule->min_transaction_amount, 2) : '0' }}
                                &ndash;
                                {{ $rule->max_transaction_amount !== null ? '&#x20A6;'.number_format($rule->max_transaction_amount, 2) : '&infin;' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($rule->waivable)
                                    <span class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        Yes
                                    </span>
                                @else
                                    <span class="text-xs text-bankos-muted">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    @click="toggleActive('{{ $rule->id }}', $el)"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium cursor-pointer transition-colors
                                        {{ $rule->is_active
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                            : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full {{ $rule->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                    <span class="pill-label">{{ $rule->is_active ? 'Active' : 'Inactive' }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    {{-- Edit --}}
                                    <button
                                        @click="openEdit({
                                            id: '{{ $rule->id }}',
                                            name: @js($rule->name),
                                            transaction_type: '{{ $rule->transaction_type }}',
                                            account_type: '{{ $rule->account_type ?? '' }}',
                                            fee_type: '{{ $rule->fee_type }}',
                                            amount: '{{ $rule->amount }}',
                                            min_fee: '{{ $rule->min_fee ?? '' }}',
                                            max_fee: '{{ $rule->max_fee ?? '' }}',
                                            min_transaction_amount: '{{ $rule->min_transaction_amount ?? '' }}',
                                            max_transaction_amount: '{{ $rule->max_transaction_amount ?? '' }}',
                                            waivable: {{ $rule->waivable ? 'true' : 'false' }},
                                        })"
                                        class="p-1.5 rounded-lg text-bankos-muted hover:text-bankos-primary hover:bg-bankos-light dark:hover:bg-bankos-primary/10 transition-colors"
                                        title="Edit"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>

                                    {{-- Delete --}}
                                    <form method="POST" action="{{ route('fee-rules.destroy', $rule->id) }}" onsubmit="return confirm('Delete this fee rule?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 rounded-lg text-bankos-muted hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                            title="Delete"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
        @endif

        {{-- ── ADD MODAL ──────────────────────────────────────────────────────────── --}}
        <div
            x-show="showAddModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            @keydown.escape.window="showAddModal = false"
        >
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showAddModal = false"></div>

            <div class="relative w-full max-w-2xl bg-white dark:bg-bankos-dark-surface rounded-2xl shadow-2xl overflow-y-auto max-h-[90vh]">
                <div class="flex items-center justify-between px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
                    <h3 class="text-base font-semibold text-bankos-text dark:text-bankos-dark-text">Add Fee Rule</h3>
                    <button @click="showAddModal = false" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('fee-rules.store') }}" class="p-6 space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Name --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-bankos-muted mb-1.5">Rule Name</label>
                            <input
                                type="text"
                                name="name"
                                required
                                maxlength="100"
                                placeholder="e.g. Transfer Fee"
                                class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                            >
                        </div>

                        {{-- Transaction Type --}}
                        <div>
                            <label class="block text-xs font-medium text-bankos-muted mb-1.5">Transaction Type</label>
                            <select
                                name="transaction_type"
                                required
                                class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                            >
                                <option value="transfer">Transfer</option>
                                <option value="withdrawal">Withdrawal</option>
                                <option value="bill_payment">Bill Payment</option>
                                <option value="airtime">Airtime</option>
                                <option value="loan_repayment">Loan Repayment</option>
                                <option value="fee">Fee</option>
                                <option value="deposit">Deposit</option>
                            </select>
                        </div>

                        {{-- Account Type --}}
                        <div>
                            <label class="block text-xs font-medium text-bankos-muted mb-1.5">Account Type</label>
                            <select
                                name="account_type"
                                class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                            >
                                <option value="">All Types</option>
                                <option value="savings">Savings</option>
                                <option value="current">Current</option>
                                <option value="domiciliary">Domiciliary</option>
                                <option value="kids">Kids</option>
                            </select>
                        </div>

                        {{-- Fee Type --}}
                        <div>
                            <label class="block text-xs font-medium text-bankos-muted mb-1.5">Fee Type</label>
                            <select
                                name="fee_type"
                                x-model="feeType"
                                class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                            >
                                <option value="flat">Flat</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>

                        {{-- Amount --}}
                        <div>
                            <label class="block text-xs font-medium text-bankos-muted mb-1.5">
                                <span x-text="feeType === 'flat' ? 'Amount (NGN)' : 'Percentage (%)'"></span>
                            </label>
                            <input
                                type="number"
                                name="amount"
                                required
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                            >
                        </div>

                        {{-- Min Fee / Max Fee (percentage only) --}}
                        <template x-if="feeType === 'percentage'">
                            <div class="sm:col-span-2 grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-bankos-muted mb-1.5">Min Fee (NGN) <span class="text-bankos-muted font-normal">optional</span></label>
                                    <input
                                        type="number"
                                        name="min_fee"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                                    >
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-bankos-muted mb-1.5">Max Fee (NGN) <span class="text-bankos-muted font-normal">optional</span></label>
                                    <input
                                        type="number"
                                        name="max_fee"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                                    >
                                </div>
                            </div>
                        </template>

                        {{-- Min Transaction Amount --}}
                        <div>
                            <label class="block text-xs font-medium text-bankos-muted mb-1.5">Min Transaction Amount <span class="text-bankos-muted font-normal">optional</span></label>
                            <input
                                type="number"
                                name="min_transaction_amount"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                            >
                        </div>

                        {{-- Max Transaction Amount --}}
                        <div>
                            <label class="block text-xs font-medium text-bankos-muted mb-1.5">Max Transaction Amount <span class="text-bankos-muted font-normal">optional, blank = unlimited</span></label>
                            <input
                                type="number"
                                name="max_transaction_amount"
                                min="0"
                                step="0.01"
                                placeholder="Unlimited"
                                class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                            >
                        </div>

                        {{-- Waivable --}}
                        <div class="sm:col-span-2 flex items-center gap-3">
                            <input
                                type="checkbox"
                                name="waivable"
                                id="add_waivable"
                                value="1"
                                checked
                                class="w-4 h-4 rounded border-bankos-border text-bankos-primary focus:ring-bankos-primary/30"
                            >
                            <label for="add_waivable" class="text-sm text-bankos-text dark:text-bankos-dark-text cursor-pointer">
                                Waivable — allow admin to waive this fee on individual transactions
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 text-sm font-medium text-bankos-text dark:text-bankos-dark-text bg-gray-100 dark:bg-bankos-dark-bg rounded-lg hover:bg-gray-200 dark:hover:bg-bankos-dark-border transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-bankos-primary rounded-lg hover:bg-bankos-primary/90 transition-colors">
                            Create Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── EDIT MODAL ──────────────────────────────────────────────────────────── --}}
        <div
            x-show="showEditModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            @keydown.escape.window="showEditModal = false"
        >
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showEditModal = false"></div>

            <div class="relative w-full max-w-2xl bg-white dark:bg-bankos-dark-surface rounded-2xl shadow-2xl overflow-y-auto max-h-[90vh]">
                <div class="flex items-center justify-between px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
                    <h3 class="text-base font-semibold text-bankos-text dark:text-bankos-dark-text">Edit Fee Rule</h3>
                    <button @click="showEditModal = false" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <template x-if="editRule.id">
                    <form :action="`/fee-rules/${editRule.id}`" method="POST" class="p-6 space-y-5">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Name --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-bankos-muted mb-1.5">Rule Name</label>
                                <input
                                    type="text"
                                    name="name"
                                    required
                                    maxlength="100"
                                    :value="editRule.name"
                                    class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                                >
                            </div>

                            {{-- Transaction Type --}}
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1.5">Transaction Type</label>
                                <select
                                    name="transaction_type"
                                    required
                                    :value="editRule.transaction_type"
                                    x-init="$nextTick(() => { $el.value = editRule.transaction_type })"
                                    class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                                >
                                    <option value="transfer">Transfer</option>
                                    <option value="withdrawal">Withdrawal</option>
                                    <option value="bill_payment">Bill Payment</option>
                                    <option value="airtime">Airtime</option>
                                    <option value="loan_repayment">Loan Repayment</option>
                                    <option value="fee">Fee</option>
                                    <option value="deposit">Deposit</option>
                                </select>
                            </div>

                            {{-- Account Type --}}
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1.5">Account Type</label>
                                <select
                                    name="account_type"
                                    x-init="$nextTick(() => { $el.value = editRule.account_type || '' })"
                                    class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                                >
                                    <option value="">All Types</option>
                                    <option value="savings">Savings</option>
                                    <option value="current">Current</option>
                                    <option value="domiciliary">Domiciliary</option>
                                    <option value="kids">Kids</option>
                                </select>
                            </div>

                            {{-- Fee Type --}}
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1.5">Fee Type</label>
                                <select
                                    name="fee_type"
                                    x-model="editFeeType"
                                    x-init="$nextTick(() => { editFeeType = editRule.fee_type; $el.value = editRule.fee_type })"
                                    class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                                >
                                    <option value="flat">Flat</option>
                                    <option value="percentage">Percentage</option>
                                </select>
                            </div>

                            {{-- Amount --}}
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1.5">
                                    <span x-text="editFeeType === 'flat' ? 'Amount (NGN)' : 'Percentage (%)'"></span>
                                </label>
                                <input
                                    type="number"
                                    name="amount"
                                    required
                                    min="0"
                                    step="0.01"
                                    :value="editRule.amount"
                                    class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                                >
                            </div>

                            {{-- Min Fee / Max Fee (percentage only) --}}
                            <template x-if="editFeeType === 'percentage'">
                                <div class="sm:col-span-2 grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-muted mb-1.5">Min Fee (NGN) <span class="font-normal">optional</span></label>
                                        <input
                                            type="number"
                                            name="min_fee"
                                            min="0"
                                            step="0.01"
                                            :value="editRule.min_fee"
                                            class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-muted mb-1.5">Max Fee (NGN) <span class="font-normal">optional</span></label>
                                        <input
                                            type="number"
                                            name="max_fee"
                                            min="0"
                                            step="0.01"
                                            :value="editRule.max_fee"
                                            class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                                        >
                                    </div>
                                </div>
                            </template>

                            {{-- Min Transaction Amount --}}
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1.5">Min Transaction Amount <span class="font-normal">optional</span></label>
                                <input
                                    type="number"
                                    name="min_transaction_amount"
                                    min="0"
                                    step="0.01"
                                    :value="editRule.min_transaction_amount"
                                    class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                                >
                            </div>

                            {{-- Max Transaction Amount --}}
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1.5">Max Transaction Amount <span class="font-normal">optional, blank = unlimited</span></label>
                                <input
                                    type="number"
                                    name="max_transaction_amount"
                                    min="0"
                                    step="0.01"
                                    :value="editRule.max_transaction_amount"
                                    placeholder="Unlimited"
                                    class="w-full px-3 py-2 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text text-sm focus:outline-none focus:ring-2 focus:ring-bankos-primary/30 focus:border-bankos-primary"
                                >
                            </div>

                            {{-- Waivable --}}
                            <div class="sm:col-span-2 flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    name="waivable"
                                    id="edit_waivable"
                                    value="1"
                                    :checked="editRule.waivable"
                                    x-init="$nextTick(() => { $el.checked = editRule.waivable })"
                                    class="w-4 h-4 rounded border-bankos-border text-bankos-primary focus:ring-bankos-primary/30"
                                >
                                <label for="edit_waivable" class="text-sm text-bankos-text dark:text-bankos-dark-text cursor-pointer">
                                    Waivable — allow admin to waive this fee on individual transactions
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2 text-sm font-medium text-bankos-text dark:text-bankos-dark-text bg-gray-100 dark:bg-bankos-dark-bg rounded-lg hover:bg-gray-200 dark:hover:bg-bankos-dark-border transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-bankos-primary rounded-lg hover:bg-bankos-primary/90 transition-colors">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

    </div>
</x-app-layout>
