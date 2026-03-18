<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
            {{ __('Create Deposit Product') }}
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto card p-8">
        <form action="{{ route('savings-products.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input w-full" placeholder="e.g. Standard Savings Account" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Product Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" class="form-input w-full" placeholder="e.g. SAV-STD" required>
                    <span class="text-xs text-bankos-muted mt-1 block">Must be unique</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Product Type <span class="text-red-500">*</span></label>
                    <select name="product_type" class="form-select w-full" required>
                        <option value="savings">Savings Account</option>
                        <option value="current">Current / Checking Account</option>
                        <option value="fixed">Fixed Deposit</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Interest Rate (% p.a.) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="interest_rate" value="{{ old('interest_rate', '0.00') }}" class="form-input w-full md:w-1/2" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Minimum Opening Balance <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bankos-muted">₦</div>
                        <input type="number" step="0.01" name="min_opening" value="{{ old('min_opening', '0.00') }}" class="form-input pl-8 w-full" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Minimum Operating Balance <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bankos-muted">₦</div>
                        <input type="number" step="0.01" name="min_balance" value="{{ old('min_balance', '0.00') }}" class="form-input pl-8 w-full" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Monthly Maintenance Fee <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bankos-muted">₦</div>
                        <input type="number" step="0.01" name="monthly_fee" value="{{ old('monthly_fee', '0.00') }}" class="form-input pl-8 w-full" required>
                    </div>
                </div>

            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-bankos-border dark:border-bankos-dark-border">
                <a href="{{ route('savings-products.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</x-app-layout>
