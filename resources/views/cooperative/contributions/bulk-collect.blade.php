@extends('layouts.app')

@section('title', 'Bulk Contribution Collection')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('cooperative.contributions.index') }}" class="text-gray-400 hover:text-blue-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Bulk Contribution Collection</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Collect contributions from multiple members at once (e.g. during a meeting)</p>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('cooperative.contributions.bulk-collect.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Settings Row --}}
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Collection Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="contribution_schedule_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contribution Schedule <span class="text-red-500">*</span></label>
                    <select name="contribution_schedule_id" id="contribution_schedule_id" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select schedule...</option>
                        @foreach($schedules as $schedule)
                            <option value="{{ $schedule->id }}">
                                {{ $schedule->name }} (&#8358;{{ number_format($schedule->amount, 2) }})
                            </option>
                        @endforeach
                    </select>
                    @error('contribution_schedule_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="period" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Period <span class="text-red-500">*</span></label>
                    <input type="text" name="period" id="period" value="{{ old('period', now()->format('Y-m')) }}" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g. 2026-03">
                    @error('period')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method <span class="text-red-500">*</span></label>
                    <select name="payment_method" id="payment_method" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="cash">Cash</option>
                        <option value="transfer">Bank Transfer</option>
                        <option value="deduction">Account Deduction</option>
                    </select>
                    @error('payment_method')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Member Selection --}}
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Select Members</h3>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500 dark:text-gray-400" id="selectedCount">0 selected</span>
                    <button type="button" onclick="toggleAll()" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 font-medium">Select All</button>
                </div>
            </div>

            @error('members')<div class="px-6 py-2 bg-red-50 dark:bg-red-900/20"><p class="text-red-500 text-xs">{{ $message }}</p></div>@enderror

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">
                                <input type="checkbox" id="selectAllCheckbox" onclick="toggleAll()"
                                    class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Member</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Account No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Phone</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($customers as $customer)
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10">
                            <td class="px-6 py-3">
                                <input type="checkbox" name="members[]" value="{{ $customer->id }}" class="member-checkbox rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:ring-blue-500" onchange="updateCount()">
                            </td>
                            <td class="px-6 py-3">
                                <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $customer->first_name }} {{ $customer->last_name }}</p>
                            </td>
                            <td class="px-6 py-3 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $customer->customer_number ?? 'N/A' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $customer->phone ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('cooperative.contributions.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Record Bulk Collection</button>
        </div>
    </form>
</div>

<script>
    function toggleAll() {
        const checkboxes = document.querySelectorAll('.member-checkbox');
        const selectAll = document.getElementById('selectAllCheckbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateCount();
    }

    function updateCount() {
        const checked = document.querySelectorAll('.member-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = checked + ' selected';
    }
</script>
@endsection
