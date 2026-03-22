@extends('layouts.app')

@section('title', 'Contribution History — ' . $customer->first_name . ' ' . $customer->last_name)

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('cooperative.contributions.index') }}" class="text-gray-400 hover:text-blue-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $customer->first_name }} {{ $customer->last_name }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Contribution history &middot; {{ $customer->customer_number ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="card p-5 inline-block">
        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Contributions Paid</p>
        <p class="text-2xl font-bold text-green-600 mt-2">&#8358;{{ number_format($totalPaid, 2) }}</p>
    </div>

    {{-- History Table --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Payment History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Schedule</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Period</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($contributions as $contribution)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-6 py-4 font-semibold text-sm text-gray-900 dark:text-white">{{ $contribution->schedule_name }}</td>
                        <td class="px-6 py-4 font-mono text-sm text-gray-600 dark:text-gray-300">{{ $contribution->period }}</td>
                        <td class="px-6 py-4 text-right font-mono text-sm font-semibold text-gray-900 dark:text-white">&#8358;{{ number_format($contribution->amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 capitalize">{{ $contribution->payment_method }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($contribution->status === 'paid')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Paid</span>
                            @elseif($contribution->status === 'waived')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Waived</span>
                            @elseif($contribution->status === 'refunded')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Refunded</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400">{{ ucfirst($contribution->status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 font-mono">{{ $contribution->reference ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ \Carbon\Carbon::parse($contribution->created_at)->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">No contributions recorded for this member.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($contributions->hasPages())
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/30">{{ $contributions->links() }}</div>
        @endif
    </div>
</div>
@endsection
