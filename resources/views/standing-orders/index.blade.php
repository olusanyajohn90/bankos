<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    Standing Orders
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Automated recurring transfers for customers</p>
            </div>
            <a href="{{ route('standing-orders.create') }}"
               class="btn btn-primary flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create Standing Order
            </a>
        </div>
    </x-slot>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-200 text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-800 dark:text-red-200 text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="card p-0 overflow-hidden">

        {{-- Status Filter Tabs --}}
        <div class="border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20 px-4 py-3">
            <div class="flex items-center gap-1 flex-wrap">
                @php
                    $statusTabs = [
                        '' => ['label' => 'All', 'color' => ''],
                        'active' => ['label' => 'Active', 'color' => 'text-green-700'],
                        'paused' => ['label' => 'Paused', 'color' => 'text-amber-700'],
                        'completed' => ['label' => 'Completed', 'color' => 'text-blue-700'],
                        'failed' => ['label' => 'Failed', 'color' => 'text-red-700'],
                        'cancelled' => ['label' => 'Cancelled', 'color' => 'text-gray-500'],
                    ];
                @endphp
                @foreach($statusTabs as $value => $tab)
                    <a href="{{ route('standing-orders.index', $value ? ['status' => $value] : []) }}"
                       class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors
                           {{ request('status', '') === $value
                               ? 'bg-bankos-primary text-white'
                               : 'text-bankos-text-sec hover:bg-gray-100 dark:hover:bg-gray-700 ' . $tab['color'] }}">
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-5 py-4 font-semibold">Source Account</th>
                        <th class="px-5 py-4 font-semibold">Beneficiary</th>
                        <th class="px-5 py-4 font-semibold text-right">Amount</th>
                        <th class="px-5 py-4 font-semibold">Frequency</th>
                        <th class="px-5 py-4 font-semibold">Next Run</th>
                        <th class="px-5 py-4 font-semibold text-center">Runs</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <p class="font-bold font-mono text-bankos-primary text-xs">
                                {{ $order->sourceAccount?->account_number }}
                            </p>
                            @if($order->sourceAccount?->customer)
                                <p class="text-xs text-bankos-text dark:text-gray-300 mt-0.5">
                                    {{ $order->sourceAccount->customer->first_name }}
                                    {{ $order->sourceAccount->customer->last_name }}
                                </p>
                            @endif
                            <p class="text-[10px] text-bankos-muted mt-0.5 uppercase">
                                {{ $order->transfer_type === 'internal' ? 'Internal' : 'External' }}
                            </p>
                        </td>
                        <td class="px-5 py-4">
                            @if($order->transfer_type === 'internal' && $order->internalDestAccount)
                                <p class="font-mono text-xs text-bankos-text dark:text-gray-300">
                                    {{ $order->internalDestAccount->account_number }}
                                </p>
                                @if($order->internalDestAccount->customer)
                                    <p class="text-xs text-bankos-muted mt-0.5">
                                        {{ $order->internalDestAccount->customer->first_name }}
                                        {{ $order->internalDestAccount->customer->last_name }}
                                    </p>
                                @endif
                            @else
                                <p class="text-xs font-medium text-bankos-text dark:text-gray-300">
                                    {{ $order->beneficiary_name ?? '—' }}
                                </p>
                                @if($order->beneficiary_account_number)
                                    <p class="font-mono text-[10px] text-bankos-muted mt-0.5">
                                        {{ $order->beneficiary_account_number }}
                                        @if($order->beneficiary_bank_code)
                                            ({{ $order->beneficiary_bank_code }})
                                        @endif
                                    </p>
                                @endif
                            @endif
                            @if($order->narration)
                                <p class="text-[10px] text-bankos-muted italic mt-0.5 max-w-[180px] truncate">{{ $order->narration }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <p class="font-bold text-bankos-text dark:text-white">₦{{ number_format($order->amount, 2) }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-100 dark:border-blue-800">
                                {{ ucfirst($order->frequency) }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            @if($order->status === 'active')
                                @php $isOverdue = $order->next_run_date->isPast(); @endphp
                                <p class="text-xs font-semibold {{ $isOverdue ? 'text-red-600' : 'text-bankos-text dark:text-white' }}">
                                    {{ $order->next_run_date->format('d M Y') }}
                                </p>
                                @if($isOverdue)
                                    <p class="text-[10px] text-red-500 mt-0.5">Overdue</p>
                                @else
                                    <p class="text-[10px] text-bankos-muted mt-0.5">{{ $order->next_run_date->diffForHumans() }}</p>
                                @endif
                            @elseif($order->last_run_at)
                                <p class="text-xs text-bankos-muted">Last: {{ $order->last_run_at->format('d M Y') }}</p>
                            @else
                                <p class="text-xs text-bankos-muted">—</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <p class="font-semibold text-bankos-text dark:text-white">{{ $order->runs_completed }}</p>
                            @if($order->max_runs)
                                <p class="text-[10px] text-bankos-muted mt-0.5">of {{ $order->max_runs }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($order->status === 'active')
                                <span class="badge badge-active">Active</span>
                            @elseif($order->status === 'paused')
                                <span class="badge bg-amber-100 text-amber-800 border border-amber-200">Paused</span>
                            @elseif($order->status === 'completed')
                                <span class="badge bg-blue-100 text-blue-700 border border-blue-200">Completed</span>
                            @elseif($order->status === 'failed')
                                <div>
                                    <span class="badge badge-danger">Failed</span>
                                    @if($order->last_failure_reason)
                                        <p class="text-[10px] text-red-500 mt-0.5 max-w-[120px] truncate" title="{{ $order->last_failure_reason }}">
                                            {{ $order->last_failure_reason }}
                                        </p>
                                    @endif
                                </div>
                            @elseif($order->status === 'cancelled')
                                <span class="badge bg-gray-100 text-gray-500 border border-gray-200">Cancelled</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Pause / Resume --}}
                                @if(in_array($order->status, ['active', 'paused']))
                                <form action="{{ route('standing-orders.pause', $order) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="text-xs font-medium border border-bankos-border dark:border-bankos-dark-border px-2.5 py-1.5 rounded hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors
                                            {{ $order->status === 'paused' ? 'text-green-600 hover:border-green-300' : 'text-amber-600 hover:border-amber-300' }}">
                                        {{ $order->status === 'paused' ? 'Resume' : 'Pause' }}
                                    </button>
                                </form>
                                @endif

                                {{-- Cancel --}}
                                @if(!in_array($order->status, ['cancelled', 'completed']))
                                <form action="{{ route('standing-orders.destroy', $order) }}" method="POST"
                                      onsubmit="return confirm('Cancel this standing order? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs font-medium text-red-600 border border-bankos-border dark:border-bankos-dark-border px-2.5 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 hover:border-red-300 transition-colors">
                                        Cancel
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-bankos-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                                <p class="text-sm">No standing orders found.</p>
                                <a href="{{ route('standing-orders.create') }}" class="btn btn-primary text-sm mt-1">Create First Standing Order</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
