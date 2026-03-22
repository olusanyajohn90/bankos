@extends('layouts.app')

@section('title', 'Member Contributions')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Member Contributions</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage dues, levies, and regular member contributions</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('cooperative.contributions.collect') }}" class="btn btn-secondary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                Collect
            </a>
            <a href="{{ route('cooperative.contributions.bulk-collect') }}" class="btn btn-secondary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                Bulk Collect
            </a>
            <a href="{{ route('cooperative.contributions.schedules.create') }}" class="btn btn-primary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Schedule
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Collected This Month</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">&#8358;{{ number_format($totalCollectedThisMonth, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">Period: {{ $currentMonth }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Members Paid</p>
            <p class="text-2xl font-bold text-green-600 mt-2">{{ $paidMembers }}</p>
            <p class="text-xs text-gray-400 mt-1">of {{ $totalMembers }} active members</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Compliance Rate</p>
            <p class="text-2xl font-bold {{ $complianceRate >= 80 ? 'text-green-600' : ($complianceRate >= 50 ? 'text-amber-600' : 'text-red-600') }} mt-2">{{ $complianceRate }}%</p>
            <p class="text-xs text-gray-400 mt-1">This month</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active Schedules</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $scheduleStats->where('status', 'active')->count() }}</p>
            <p class="text-xs text-gray-400 mt-1">
                <a href="{{ route('cooperative.contributions.report') }}" class="text-blue-600 hover:underline">View Report</a>
            </p>
        </div>
    </div>

    {{-- Contribution Schedules Table --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Contribution Schedules</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Frequency</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Collected (Month)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Members Paid</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($scheduleStats as $schedule)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $schedule->name }}</p>
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-sm font-semibold text-gray-900 dark:text-white">&#8358;{{ number_format($schedule->amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600 dark:text-gray-300 capitalize">{{ str_replace('_', ' ', $schedule->frequency) }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($schedule->mandatory)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Mandatory</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Voluntary</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-sm text-gray-900 dark:text-white">&#8358;{{ number_format($schedule->total_collected, 2) }}</td>
                        <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">{{ $schedule->members_paid }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($schedule->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('cooperative.contributions.schedules.show', $schedule->id) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 font-medium text-sm">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                            <p class="font-medium">No contribution schedules yet.</p>
                            <a href="{{ route('cooperative.contributions.schedules.create') }}" class="btn btn-primary mt-3 inline-block">Create First Schedule</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
