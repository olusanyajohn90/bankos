@extends('layouts.app')

@section('title', 'Member Exits')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Member Exits & Withdrawals</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage member exit requests, settlements, and account closures</p>
        </div>
        <a href="{{ route('cooperative.exits.create') }}" class="btn btn-primary flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            New Exit Request
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="card p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Requests</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats->total ?? 0 }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending</p>
            <p class="text-2xl font-bold text-amber-600 mt-2">{{ $stats->pending_count ?? 0 }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Approved</p>
            <p class="text-2xl font-bold text-blue-600 mt-2">{{ $stats->approved_count ?? 0 }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Settled</p>
            <p class="text-2xl font-bold text-green-600 mt-2">{{ $stats->settled_count ?? 0 }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Settled</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">&#8358;{{ number_format($stats->total_settled ?? 0, 2) }}</p>
        </div>
    </div>

    {{-- Exit Requests Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Exit Type</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Net Settlement</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Exit Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($exits as $exit)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $exit->member_name }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $exit->customer_number ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($exit->exit_type === 'voluntary')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Voluntary</span>
                            @elseif($exit->exit_type === 'expelled')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Expelled</span>
                            @elseif($exit->exit_type === 'deceased')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400">Deceased</span>
                            @elseif($exit->exit_type === 'transferred')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">Transferred</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-sm font-semibold {{ $exit->net_settlement >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            &#8358;{{ number_format($exit->net_settlement, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($exit->status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Pending</span>
                            @elseif($exit->status === 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Approved</span>
                            @elseif($exit->status === 'settled')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Settled</span>
                            @elseif($exit->status === 'rejected')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Rejected</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $exit->exit_date ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ \Carbon\Carbon::parse($exit->created_at)->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('cooperative.exits.show', $exit->id) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 font-medium text-sm">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                            <p class="font-medium">No exit requests yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($exits->hasPages())
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/30">{{ $exits->links() }}</div>
        @endif
    </div>
</div>
@endsection
