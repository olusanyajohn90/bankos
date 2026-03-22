@extends('layouts.app')

@section('title', 'Contribution Compliance Report')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('cooperative.contributions.index') }}" class="text-gray-400 hover:text-blue-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Contribution Compliance Report</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">View who has paid and who hasn't for any schedule and period</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('cooperative.contributions.report') }}" class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            <div>
                <label for="schedule_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contribution Schedule</label>
                <select name="schedule_id" id="schedule_id"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select schedule...</option>
                    @foreach($schedules as $schedule)
                        <option value="{{ $schedule->id }}" {{ $scheduleId === $schedule->id ? 'selected' : '' }}>
                            {{ $schedule->name }} (&#8358;{{ number_format($schedule->amount, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="period" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Period</label>
                <input type="text" name="period" id="period" value="{{ $period }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="e.g. 2026-03">
            </div>

            <div>
                <button type="submit" class="btn btn-primary w-full">Generate Report</button>
            </div>
        </div>
    </form>

    {{-- Report Results --}}
    @if($scheduleId && $selectedSchedule)
        @php
            $paidMembers = collect($report)->where('paid', true);
            $unpaidMembers = collect($report)->where('paid', false);
            $totalCollected = $paidMembers->sum('amount');
            $rate = count($report) > 0 ? round(($paidMembers->count() / count($report)) * 100, 1) : 0;
        @endphp

        {{-- Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card p-5">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Schedule</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white mt-2">{{ $selectedSchedule->name }}</p>
                <p class="text-xs text-gray-400 mt-1">Period: {{ $period }}</p>
            </div>
            <div class="card p-5">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Paid</p>
                <p class="text-2xl font-bold text-green-600 mt-2">{{ $paidMembers->count() }}</p>
                <p class="text-xs text-gray-400 mt-1">of {{ count($report) }} members</p>
            </div>
            <div class="card p-5">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unpaid</p>
                <p class="text-2xl font-bold text-red-600 mt-2">{{ $unpaidMembers->count() }}</p>
                <p class="text-xs text-gray-400 mt-1">defaulters</p>
            </div>
            <div class="card p-5">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Compliance</p>
                <p class="text-2xl font-bold {{ $rate >= 80 ? 'text-green-600' : ($rate >= 50 ? 'text-amber-600' : 'text-red-600') }} mt-2">{{ $rate }}%</p>
                <p class="text-xs text-gray-400 mt-1">&#8358;{{ number_format($totalCollected, 2) }} collected</p>
            </div>
        </div>

        {{-- Detailed Table --}}
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Member Compliance &mdash; {{ $selectedSchedule->name }} ({{ $period }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Member</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Account No.</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($report as $index => $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 {{ !$row->paid ? 'bg-red-50/30 dark:bg-red-900/5' : '' }}">
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-3">
                                <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $row->customer->first_name }} {{ $row->customer->last_name }}</p>
                            </td>
                            <td class="px-6 py-3 font-mono text-xs text-gray-600 dark:text-gray-300">{{ $row->customer->customer_number ?? 'N/A' }}</td>
                            <td class="px-6 py-3 text-center">
                                @if($row->status === 'paid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Paid</span>
                                @elseif($row->status === 'waived')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Waived</span>
                                @elseif($row->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Pending</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Unpaid</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right font-mono text-sm">
                                @if($row->amount > 0)
                                    &#8358;{{ number_format($row->amount, 2) }}
                                @else
                                    <span class="text-gray-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('cooperative.contributions.member-history', $row->customer->id) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm font-medium">History</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($scheduleId && !$selectedSchedule)
        <div class="card p-12 text-center">
            <p class="text-gray-500 dark:text-gray-400">Schedule not found.</p>
        </div>
    @else
        <div class="card p-12 text-center">
            <p class="text-gray-500 dark:text-gray-400">Select a contribution schedule and period above to generate the compliance report.</p>
        </div>
    @endif
</div>
@endsection
