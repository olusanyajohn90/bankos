<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
            {{ __('Open New Account') }}
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto card p-8">
        
        <div class="flex items-center gap-4 mb-8 pb-6 border-b border-bankos-border dark:border-bankos-dark-border">
            <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 text-bankos-primary flex items-center justify-center font-bold text-lg ring-2 ring-white">
                {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name, 0, 1) }}
            </div>
            <div>
                <p class="text-sm text-bankos-text-sec">Opening account for</p>
                <h3 class="font-bold text-lg leading-tight">{{ $customer->first_name }} {{ $customer->last_name }}</h3>
                <p class="text-xs text-bankos-muted mt-0.5 font-mono">{{ $customer->customer_number }}</p>
            </div>
        </div>

        <form action="{{ route('accounts.store') }}" method="POST">
            @csrf
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">

            <div class="space-y-6 mb-8">
                
                <div>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Deposit Product <span class="text-red-500">*</span></label>
                    <select name="savings_product_id" class="form-select w-full" required>
                        <option value="">Select a product...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }} ({{ $product->code }}) - {{ number_format($product->interest_rate, 2) }}% p.a.
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Account Name Alias</label>
                    <input type="text" name="account_name" value="{{ old('account_name', $customer->first_name . ' ' . $customer->last_name) }}" class="form-input w-full" placeholder="Defaults to customer name">
                    <span class="text-xs text-bankos-muted mt-1 block">Leave as is unless specific naming is required (e.g. "Joint Account").</span>
                </div>

            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-bankos-border dark:border-bankos-dark-border">
                <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Generate Account Number</button>
            </div>
        </form>
    </div>
</x-app-layout>
