<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Issue Trade Finance Instrument</h2>
                <p class="text-sm text-bankos-text-sec mt-1">LC, guarantee, bill for collection, invoice discounting</p>
            </div>
            <a href="{{ route('trade-finance.index') }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('trade-finance.store') }}" class="card p-6 space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Instrument Type</label>
                    <select name="type" class="input w-full" required>
                        <option value="letter_of_credit">Letter of Credit</option>
                        <option value="bank_guarantee">Bank Guarantee</option>
                        <option value="bill_for_collection">Bill for Collection</option>
                        <option value="invoice_discounting">Invoice Discounting</option>
                    </select>
                </div>
                <div>
                    <label class="label">Customer</label>
                    <select name="customer_id" class="input w-full" required>
                        <option value="">Select customer</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->business_name ?? ($c->first_name . ' ' . $c->last_name) }}</option>
                        @endforeach
                    </select>
                    @error('customer_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Beneficiary Name</label>
                    <input type="text" name="beneficiary_name" value="{{ old('beneficiary_name') }}" class="input w-full" required>
                </div>
                <div>
                    <label class="label">Beneficiary Bank</label>
                    <input type="text" name="beneficiary_bank" value="{{ old('beneficiary_bank') }}" class="input w-full">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="label">Amount</label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="input w-full" required>
                </div>
                <div>
                    <label class="label">Currency</label>
                    <select name="currency" class="input w-full">
                        <option value="NGN">NGN</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="GBP">GBP</option>
                    </select>
                </div>
                <div>
                    <label class="label">Commission Rate (%)</label>
                    <input type="number" step="0.01" name="commission_rate" value="{{ old('commission_rate', '1.00') }}" class="input w-full">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Issue Date</label>
                    <input type="date" name="issue_date" value="{{ old('issue_date', date('Y-m-d')) }}" class="input w-full" required>
                </div>
                <div>
                    <label class="label">Expiry Date</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="input w-full" required>
                </div>
            </div>
            <div>
                <label class="label">Purpose</label>
                <textarea name="purpose" class="input w-full" rows="2">{{ old('purpose') }}</textarea>
            </div>
            <div>
                <label class="label">Terms &amp; Conditions</label>
                <textarea name="terms" class="input w-full" rows="3">{{ old('terms') }}</textarea>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('trade-finance.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Instrument</button>
            </div>
        </form>
    </div>
</x-app-layout>
