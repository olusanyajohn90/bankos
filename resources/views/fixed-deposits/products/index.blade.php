<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div class="flex items-center gap-3">
                <a href="{{ route('fixed-deposits.index') }}"
                   class="text-bankos-muted hover:text-bankos-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                        FD Products
                    </h2>
                    <p class="text-sm text-bankos-text-sec mt-1">Configure fixed deposit product rates and rules</p>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-200 text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Products Table --}}
    <div class="card p-0 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20">
            <h3 class="font-semibold text-bankos-text dark:text-white">Existing Products</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-5 py-4 font-semibold">Name / Code</th>
                        <th class="px-5 py-4 font-semibold">Rate & Payment</th>
                        <th class="px-5 py-4 font-semibold">Tenure Range</th>
                        <th class="px-5 py-4 font-semibold">Min / Max Amount</th>
                        <th class="px-5 py-4 font-semibold">Rules</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                        x-data="{ editing: false }">
                        {{-- View row --}}
                        <td class="px-5 py-4" x-show="!editing">
                            <p class="font-bold text-bankos-primary">{{ $product->name }}</p>
                            @if($product->code)
                                <p class="text-xs text-bankos-muted font-mono mt-0.5">{{ $product->code }}</p>
                            @endif
                            @if($product->description)
                                <p class="text-xs text-bankos-muted mt-0.5 max-w-xs truncate">{{ $product->description }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4" x-show="!editing">
                            <p class="font-bold text-bankos-success">{{ number_format($product->interest_rate, 2) }}% p.a.</p>
                            <p class="text-xs text-bankos-muted mt-0.5">{{ ucwords(str_replace('_', ' ', $product->interest_payment)) }}</p>
                        </td>
                        <td class="px-5 py-4" x-show="!editing">
                            <p class="font-medium text-bankos-text dark:text-white">{{ number_format($product->min_tenure_days) }} – {{ number_format($product->max_tenure_days) }}</p>
                            <p class="text-xs text-bankos-muted mt-0.5">days</p>
                        </td>
                        <td class="px-5 py-4" x-show="!editing">
                            <p class="font-medium text-bankos-text dark:text-white">₦{{ number_format($product->min_amount, 0) }}</p>
                            @if($product->max_amount)
                                <p class="text-xs text-bankos-muted mt-0.5">max ₦{{ number_format($product->max_amount, 0) }}</p>
                            @else
                                <p class="text-xs text-bankos-muted mt-0.5">No max</p>
                            @endif
                        </td>
                        <td class="px-5 py-4" x-show="!editing">
                            <div class="space-y-1 text-xs">
                                <div class="flex items-center gap-1.5">
                                    @if($product->allow_early_liquidation)
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        <span class="text-green-700 dark:text-green-400">Early liquidation</span>
                                    @else
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                        <span class="text-bankos-muted">No early liquidation</span>
                                    @endif
                                </div>
                                @if($product->early_liquidation_penalty > 0)
                                <p class="text-amber-600">{{ number_format($product->early_liquidation_penalty, 1) }}% penalty</p>
                                @endif
                                <div class="flex items-center gap-1.5">
                                    @if($product->auto_rollover)
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        <span class="text-blue-600 dark:text-blue-400">Auto-rollover</span>
                                    @else
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                        <span class="text-bankos-muted">No rollover</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4" x-show="!editing">
                            @if($product->status === 'active')
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge bg-gray-100 text-gray-500 border border-gray-200">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right" x-show="!editing">
                            <button @click="editing = true"
                                    class="text-bankos-primary hover:text-blue-700 font-medium text-sm border border-bankos-border dark:border-bankos-dark-border px-3 py-1.5 rounded hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors">
                                Edit
                            </button>
                        </td>

                        {{-- Inline edit form (spans all cols) --}}
                        <td colspan="7" x-show="editing" class="px-5 py-5 bg-blue-50/50 dark:bg-blue-900/10">
                            <form action="{{ route('fd-products.update', $product) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-text-sec mb-1">Name *</label>
                                        <input type="text" name="name" value="{{ $product->name }}" class="form-input w-full text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-text-sec mb-1">Interest Rate % *</label>
                                        <input type="number" name="interest_rate" value="{{ $product->interest_rate }}" step="0.001" min="0" max="100" class="form-input w-full text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-text-sec mb-1">Interest Payment *</label>
                                        <select name="interest_payment" class="form-select w-full text-sm" required>
                                            <option value="on_maturity" {{ $product->interest_payment === 'on_maturity' ? 'selected' : '' }}>On Maturity</option>
                                            <option value="monthly" {{ $product->interest_payment === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="quarterly" {{ $product->interest_payment === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-text-sec mb-1">Min Tenure (days) *</label>
                                        <input type="number" name="min_tenure_days" value="{{ $product->min_tenure_days }}" min="1" class="form-input w-full text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-text-sec mb-1">Max Tenure (days) *</label>
                                        <input type="number" name="max_tenure_days" value="{{ $product->max_tenure_days }}" min="1" class="form-input w-full text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-text-sec mb-1">Min Amount (₦) *</label>
                                        <input type="number" name="min_amount" value="{{ $product->min_amount }}" step="0.01" min="0" class="form-input w-full text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-text-sec mb-1">Max Amount (₦)</label>
                                        <input type="number" name="max_amount" value="{{ $product->max_amount }}" step="0.01" min="0" class="form-input w-full text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-text-sec mb-1">Early Penalty % *</label>
                                        <input type="number" name="early_liquidation_penalty" value="{{ $product->early_liquidation_penalty }}" step="0.01" min="0" max="100" class="form-input w-full text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-text-sec mb-1">Status *</label>
                                        <select name="status" class="form-select w-full text-sm" required>
                                            <option value="active" {{ $product->status === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $product->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <button type="submit" class="btn btn-primary text-sm py-2">Save Changes</button>
                                    <button type="button" @click="editing = false" class="btn btn-secondary text-sm py-2">Cancel</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-bankos-muted text-sm">
                            No FD products configured yet. Create one below.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create New Product Form --}}
    <div class="card p-6" x-data="{ open: false }">
        <button @click="open = !open"
                class="flex items-center justify-between w-full text-left">
            <h3 class="font-semibold text-bankos-text dark:text-white flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-primary"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create New FD Product
            </h3>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 class="text-bankos-muted transition-transform"
                 :class="open ? 'rotate-180' : ''">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </button>

        <div x-show="open" x-transition class="mt-6">
            <form action="{{ route('fd-products.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-input w-full" placeholder="e.g. Standard Fixed Deposit" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Code</label>
                        <input type="text" name="code" value="{{ old('code') }}" class="form-input w-full" placeholder="e.g. SFD-001">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Interest Rate (% p.a.) <span class="text-red-500">*</span></label>
                        <input type="number" name="interest_rate" value="{{ old('interest_rate') }}" step="0.001" min="0" max="100" class="form-input w-full" placeholder="e.g. 12.5" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Interest Payment <span class="text-red-500">*</span></label>
                        <select name="interest_payment" class="form-select w-full" required>
                            <option value="on_maturity" {{ old('interest_payment') === 'on_maturity' ? 'selected' : '' }}>On Maturity</option>
                            <option value="monthly" {{ old('interest_payment') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ old('interest_payment') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Min Tenure (days) <span class="text-red-500">*</span></label>
                        <input type="number" name="min_tenure_days" value="{{ old('min_tenure_days', 30) }}" min="1" class="form-input w-full" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Max Tenure (days) <span class="text-red-500">*</span></label>
                        <input type="number" name="max_tenure_days" value="{{ old('max_tenure_days', 365) }}" min="1" class="form-input w-full" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Min Amount (₦) <span class="text-red-500">*</span></label>
                        <input type="number" name="min_amount" value="{{ old('min_amount', 0) }}" step="0.01" min="0" class="form-input w-full" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Max Amount (₦)</label>
                        <input type="number" name="max_amount" value="{{ old('max_amount') }}" step="0.01" min="0" class="form-input w-full" placeholder="Leave blank for unlimited">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Early Liquidation Penalty (%) <span class="text-red-500">*</span></label>
                        <input type="number" name="early_liquidation_penalty" value="{{ old('early_liquidation_penalty', 0) }}" step="0.01" min="0" max="100" class="form-input w-full" required>
                        <p class="text-xs text-bankos-muted mt-1">% of accrued interest forfeited</p>
                    </div>
                    <div class="lg:col-span-3">
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Description</label>
                        <textarea name="description" class="form-input w-full resize-none" rows="2" placeholder="Optional product description...">{{ old('description') }}</textarea>
                    </div>
                    <div class="lg:col-span-3 flex flex-wrap gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="allow_early_liquidation" value="0">
                            <input type="checkbox" name="allow_early_liquidation" value="1"
                                   {{ old('allow_early_liquidation', '1') ? 'checked' : '' }}
                                   class="rounded border-bankos-border text-bankos-primary focus:ring-bankos-primary">
                            <span class="text-sm font-medium text-bankos-text dark:text-white">Allow Early Liquidation</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="allow_top_up" value="0">
                            <input type="checkbox" name="allow_top_up" value="1"
                                   {{ old('allow_top_up') ? 'checked' : '' }}
                                   class="rounded border-bankos-border text-bankos-primary focus:ring-bankos-primary">
                            <span class="text-sm font-medium text-bankos-text dark:text-white">Allow Top-up</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="auto_rollover" value="0">
                            <input type="checkbox" name="auto_rollover" value="1"
                                   {{ old('auto_rollover') ? 'checked' : '' }}
                                   class="rounded border-bankos-border text-bankos-primary focus:ring-bankos-primary">
                            <span class="text-sm font-medium text-bankos-text dark:text-white">Auto Rollover by Default</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                    <button type="button" @click="open = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Create Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
