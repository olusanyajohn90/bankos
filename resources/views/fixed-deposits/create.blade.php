<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('fixed-deposits.index') }}"
               class="text-bankos-muted hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    New Fixed Deposit
                </h2>
                <p class="text-sm text-bankos-text-sec mt-0.5">Book a term deposit for a customer</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto"
         x-data="{
            selectedProduct: null,
            selectedCustomerId: '',
            products: @js($products->map(fn($p) => [
                'id'            => $p->id,
                'name'          => $p->name,
                'interest_rate' => (float) $p->interest_rate,
                'min_tenure'    => $p->min_tenure_days,
                'max_tenure'    => $p->max_tenure_days,
                'min_amount'    => (float) $p->min_amount,
                'max_amount'    => $p->max_amount ? (float) $p->max_amount : null,
                'allow_early_liquidation' => $p->allow_early_liquidation,
                'auto_rollover' => $p->auto_rollover,
                'penalty'       => (float) $p->early_liquidation_penalty,
            ])),
            customerAccounts: [],
            principal: '',
            tenureDays: '',
            interestRate: '',
            autoRollover: false,
            get expectedInterest() {
                const p = parseFloat(this.principal) || 0;
                const r = parseFloat(this.interestRate) || 0;
                const t = parseInt(this.tenureDays) || 0;
                return p * (r / 100 / 365) * t;
            },
            get maturityAmount() {
                return (parseFloat(this.principal) || 0) + this.expectedInterest;
            },
            selectProduct(id) {
                this.selectedProduct = this.products.find(p => p.id === id) || null;
                if (this.selectedProduct) {
                    this.interestRate = this.selectedProduct.interest_rate;
                    if (!this.tenureDays) this.tenureDays = this.selectedProduct.min_tenure;
                    this.autoRollover = this.selectedProduct.auto_rollover;
                }
            },
            loadAccounts(customerId) {
                this.selectedCustomerId = customerId;
                this.customerAccounts = [];
                if (!customerId) return;
                fetch(`/api/customers/${customerId}/accounts?tenant=1`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => { this.customerAccounts = data.accounts || []; })
                .catch(() => { this.customerAccounts = []; });
            }
         }">

        <form action="{{ route('fixed-deposits.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Validation errors --}}
            @if($errors->any())
                <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-sm font-semibold text-red-700 dark:text-red-300 mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside space-y-1 text-sm text-red-600 dark:text-red-400">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Step 1: Customer & Product --}}
            <div class="card p-6">
                <h3 class="font-semibold text-bankos-text dark:text-white mb-5 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-bankos-primary text-white flex items-center justify-center text-xs font-bold">1</span>
                    Customer & Product
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Customer --}}
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                            Customer <span class="text-red-500">*</span>
                        </label>
                        <select name="customer_id"
                                class="form-select w-full"
                                required
                                @change="loadAccounts($event.target.value)">
                            <option value="">Select customer...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}"
                                        {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->first_name }} {{ $customer->last_name }}
                                    ({{ $customer->customer_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- FD Product --}}
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                            FD Product <span class="text-red-500">*</span>
                        </label>
                        <select name="product_id"
                                class="form-select w-full"
                                required
                                @change="selectProduct($event.target.value)">
                            <option value="">Select product...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                        {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} — {{ number_format($product->interest_rate, 2) }}% p.a.
                                </option>
                            @endforeach
                        </select>
                        <template x-if="selectedProduct">
                            <p class="text-xs text-bankos-muted mt-1">
                                Tenure: <span class="font-semibold" x-text="selectedProduct.min_tenure + '–' + selectedProduct.max_tenure + ' days'"></span>
                                &nbsp;·&nbsp;
                                Min: ₦<span x-text="selectedProduct.min_amount.toLocaleString()"></span>
                                <template x-if="selectedProduct.max_amount">
                                    &nbsp;·&nbsp;Max: ₦<span x-text="selectedProduct.max_amount.toLocaleString()"></span>
                                </template>
                            </p>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Step 2: Source Account --}}
            <div class="card p-6">
                <h3 class="font-semibold text-bankos-text dark:text-white mb-5 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-bankos-primary text-white flex items-center justify-center text-xs font-bold">2</span>
                    Source Account
                </h3>

                <div x-show="selectedCustomerId && customerAccounts.length > 0">
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                        Debit Account <span class="text-red-500">*</span>
                    </label>
                    <select name="source_account_id" class="form-select w-full" required>
                        <option value="">Select account to debit...</option>
                        <template x-for="acct in customerAccounts" :key="acct.id">
                            <option :value="acct.id"
                                    x-text="acct.account_number + ' — ₦' + parseFloat(acct.available_balance).toLocaleString('en-NG', {minimumFractionDigits:2})">
                            </option>
                        </template>
                    </select>
                    <p class="text-xs text-bankos-muted mt-1">The principal amount will be debited from this account.</p>
                </div>

                <div x-show="selectedCustomerId && customerAccounts.length === 0" class="text-sm text-amber-600 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 rounded-lg p-3">
                    No active accounts found for this customer.
                </div>

                <div x-show="!selectedCustomerId" class="text-sm text-bankos-muted">
                    Select a customer first to see their accounts.
                </div>

                {{-- Fallback manual input if JS disabled --}}
                <noscript>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Source Account ID</label>
                        <input type="text" name="source_account_id" value="{{ old('source_account_id') }}"
                               class="form-input w-full" placeholder="Account UUID">
                    </div>
                </noscript>
            </div>

            {{-- Step 3: FD Terms --}}
            <div class="card p-6">
                <h3 class="font-semibold text-bankos-text dark:text-white mb-5 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-bankos-primary text-white flex items-center justify-center text-xs font-bold">3</span>
                    Deposit Terms
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- Principal --}}
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                            Principal Amount (₦) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="principal_amount"
                               x-model="principal"
                               value="{{ old('principal_amount') }}"
                               step="0.01" min="1"
                               class="form-input w-full"
                               placeholder="e.g. 500000"
                               required>
                        <template x-if="selectedProduct && selectedProduct.min_amount > 0">
                            <p class="text-xs text-bankos-muted mt-1">
                                Min: ₦<span x-text="selectedProduct.min_amount.toLocaleString()"></span>
                            </p>
                        </template>
                    </div>

                    {{-- Tenure --}}
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                            Tenure (days) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="tenure_days"
                               x-model="tenureDays"
                               value="{{ old('tenure_days') }}"
                               min="1"
                               :min="selectedProduct ? selectedProduct.min_tenure : 1"
                               :max="selectedProduct ? selectedProduct.max_tenure : null"
                               class="form-input w-full"
                               placeholder="e.g. 180"
                               required>
                        <template x-if="selectedProduct">
                            <p class="text-xs text-bankos-muted mt-1">
                                Range: <span x-text="selectedProduct.min_tenure + '–' + selectedProduct.max_tenure + ' days'"></span>
                            </p>
                        </template>
                    </div>

                    {{-- Interest Rate --}}
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                            Interest Rate (% p.a.) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="interest_rate"
                               x-model="interestRate"
                               value="{{ old('interest_rate') }}"
                               step="0.001" min="0"
                               class="form-input w-full"
                               placeholder="e.g. 12.5"
                               required>
                    </div>

                    {{-- Start Date --}}
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                            Start Date
                        </label>
                        <input type="date" name="start_date"
                               value="{{ old('start_date', now()->format('Y-m-d')) }}"
                               class="form-input w-full">
                        <p class="text-xs text-bankos-muted mt-1">Defaults to today if left blank.</p>
                    </div>

                    {{-- Auto Rollover --}}
                    <div class="flex items-start gap-3 pt-7">
                        <input type="hidden" name="auto_rollover" value="0">
                        <input type="checkbox" name="auto_rollover" id="auto_rollover"
                               value="1"
                               x-model="autoRollover"
                               {{ old('auto_rollover') ? 'checked' : '' }}
                               class="mt-0.5 rounded border-bankos-border text-bankos-primary focus:ring-bankos-primary">
                        <div>
                            <label for="auto_rollover" class="text-sm font-medium text-bankos-text dark:text-white cursor-pointer">
                                Auto Rollover
                            </label>
                            <p class="text-xs text-bankos-muted mt-0.5">Automatically renew on maturity at same terms</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Interest Preview --}}
            <div class="card p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 border-blue-200 dark:border-blue-800"
                 x-show="principal > 0 && tenureDays > 0 && interestRate > 0">
                <h3 class="font-semibold text-bankos-text dark:text-white mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-primary"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Interest Preview
                </h3>
                <div class="grid grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs text-bankos-muted uppercase tracking-wider">Principal</p>
                        <p class="text-xl font-bold text-bankos-text dark:text-white mt-1">
                            ₦<span x-text="parseFloat(principal || 0).toLocaleString('en-NG', {minimumFractionDigits:2})"></span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-bankos-muted uppercase tracking-wider">Expected Interest</p>
                        <p class="text-xl font-bold text-bankos-success mt-1">
                            + ₦<span x-text="expectedInterest.toLocaleString('en-NG', {minimumFractionDigits:2})"></span>
                        </p>
                        <p class="text-xs text-bankos-muted mt-0.5">
                            <span x-text="interestRate"></span>% p.a. × <span x-text="tenureDays"></span> days
                        </p>
                    </div>
                    <div class="border-l border-blue-200 dark:border-blue-800 pl-6">
                        <p class="text-xs text-bankos-muted uppercase tracking-wider">Maturity Amount</p>
                        <p class="text-2xl font-bold text-bankos-primary mt-1">
                            ₦<span x-text="maturityAmount.toLocaleString('en-NG', {minimumFractionDigits:2})"></span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-4">
                <a href="{{ route('fixed-deposits.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Book Fixed Deposit
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // Inline account loader — fetches customer accounts via the existing accounts API
        document.addEventListener('DOMContentLoaded', function () {
            // Listen for the Alpine custom event or use direct DOM approach
            // The Alpine x-data handles this via loadAccounts()
            // This script provides a fallback for the fetch API call pattern

            // Override the Alpine loadAccounts to use the correct bankOS endpoint
            window.loadCustomerAccounts = async function (customerId) {
                if (!customerId) return [];
                try {
                    const response = await fetch(`{{ url('/api/customers') }}/${customerId}/accounts`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    if (!response.ok) return [];
                    const data = await response.json();
                    return data.accounts || [];
                } catch (e) {
                    return [];
                }
            };
        });
    </script>
    @endpush
</x-app-layout>
