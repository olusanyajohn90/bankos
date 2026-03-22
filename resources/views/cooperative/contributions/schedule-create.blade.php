@extends('layouts.app')

@section('title', 'Create Contribution Schedule')

@section('content')
<div class="space-y-6 max-w-2xl">
    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('cooperative.contributions.index') }}" class="text-gray-400 hover:text-blue-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Contribution Schedule</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Define a new recurring contribution for members</p>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('cooperative.contributions.schedules.store') }}" method="POST" class="card p-6 space-y-6">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Schedule Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="e.g. Monthly Dues, Building Levy, Welfare Fund">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
            <textarea name="description" id="description" rows="3"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Brief description of this contribution...">{{ old('description') }}</textarea>
            @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
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
                <label for="frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Frequency <span class="text-red-500">*</span></label>
                <select name="frequency" id="frequency" required
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select frequency...</option>
                    <option value="weekly" {{ old('frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ old('frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="quarterly" {{ old('frequency') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                    <option value="annual" {{ old('frequency') === 'annual' ? 'selected' : '' }}>Annual</option>
                    <option value="one_time" {{ old('frequency') === 'one_time' ? 'selected' : '' }}>One Time</option>
                </select>
                @error('frequency')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <input type="checkbox" name="mandatory" id="mandatory" value="1" {{ old('mandatory', true) ? 'checked' : '' }}
                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:ring-blue-500">
            <label for="mandatory" class="text-sm font-medium text-gray-700 dark:text-gray-300">Mandatory for all members</label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('cooperative.contributions.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Schedule</button>
        </div>
    </form>
</div>
@endsection
