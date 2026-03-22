@extends('layouts.app')

@section('title', 'New Exit Request')

@section('content')
<div class="space-y-6 max-w-2xl">
    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('cooperative.exits.index') }}" class="text-gray-400 hover:text-blue-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">New Exit Request</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Initiate a member exit/withdrawal. Settlement will be calculated automatically.</p>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('cooperative.exits.store') }}" method="POST" class="card p-6 space-y-6">
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
            <label for="exit_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Exit Type <span class="text-red-500">*</span></label>
            <select name="exit_type" id="exit_type" required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select exit type...</option>
                <option value="voluntary" {{ old('exit_type') === 'voluntary' ? 'selected' : '' }}>Voluntary Withdrawal</option>
                <option value="expelled" {{ old('exit_type') === 'expelled' ? 'selected' : '' }}>Expelled</option>
                <option value="deceased" {{ old('exit_type') === 'deceased' ? 'selected' : '' }}>Deceased</option>
                <option value="transferred" {{ old('exit_type') === 'transferred' ? 'selected' : '' }}>Transferred</option>
            </select>
            @error('exit_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
            <textarea name="reason" id="reason" rows="3"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Reason for exit...">{{ old('reason') }}</textarea>
            @error('reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Additional Notes</label>
            <textarea name="notes" id="notes" rows="2"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Any additional notes...">{{ old('notes') }}</textarea>
            @error('notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600 mt-0.5 flex-shrink-0"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <div>
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Settlement Calculation</p>
                    <p class="text-sm text-amber-700 dark:text-amber-400 mt-1">Upon submission, the system will automatically calculate the net settlement by summing share capital and savings, then deducting outstanding loans and pending contributions.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('cooperative.exits.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Submit Exit Request</button>
        </div>
    </form>
</div>
@endsection
