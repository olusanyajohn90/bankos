@extends('layouts.app')

@section('title', 'Record Contribution')

@section('content')
<div class="space-y-6 max-w-2xl">
    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('cooperative.contributions.index') }}" class="text-gray-400 hover:text-blue-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Record Contribution</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Record a single member contribution payment</p>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('cooperative.contributions.collect.store') }}" method="POST" class="card p-6 space-y-6">
        @csrf

        <div>
            <label for="customer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Member <span class="text-red-500">*</span></label>
            <select name="customer_id" id="customer_id" required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select member...</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id') === $customer->id ? 'selected' : '' }}>
                        {{ $customer->first_name }} {{ $customer->last_name }} ({{ $customer->customer_number ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
            @error('customer_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="contribution_schedule_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contribution Schedule <span class="text-red-500">*</span></label>
            <select name="contribution_schedule_id" id="contribution_schedule_id" required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select schedule...</option>
                @foreach($schedules as $schedule)
                    <option value="{{ $schedule->id }}" data-amount="{{ $schedule->amount }}" {{ old('contribution_schedule_id') === $schedule->id ? 'selected' : '' }}>
                        {{ $schedule->name }} (&#8358;{{ number_format($schedule->amount, 2) }} &mdash; {{ ucfirst(str_replace('_', ' ', $schedule->frequency)) }})
                    </option>
                @endforeach
            </select>
            @error('contribution_schedule_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount (&#8358;) <span class="text-red-500">*</span></label>
                <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required step="0.01" min="0.01"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="0.00">
                @error('amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="period" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Period <span class="text-red-500">*</span></label>
                <input type="text" name="period" id="period" value="{{ old('period', now()->format('Y-m')) }}" required
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="e.g. 2026-03">
                @error('period')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method <span class="text-red-500">*</span></label>
                <select name="payment_method" id="payment_method" required
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="transfer" {{ old('payment_method') === 'transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="deduction" {{ old('payment_method') === 'deduction' ? 'selected' : '' }}>Account Deduction</option>
                </select>
                @error('payment_method')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="reference" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reference</label>
                <input type="text" name="reference" id="reference" value="{{ old('reference') }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Receipt or transfer reference">
                @error('reference')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
            <textarea name="notes" id="notes" rows="2"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Optional notes...">{{ old('notes') }}</textarea>
            @error('notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('cooperative.contributions.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Record Payment</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('contribution_schedule_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const amount = selected.getAttribute('data-amount');
        if (amount) {
            document.getElementById('amount').value = amount;
        }
    });
</script>
@endsection
