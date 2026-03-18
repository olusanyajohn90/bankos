<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    Fixed Deposits
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Manage term deposits, accrued interest, and maturities</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('fd-products.index') }}"
                   class="btn btn-secondary flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Products
                </a>
                <a href="{{ route('fixed-deposits.create') }}"
                   class="btn btn-primary flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New Fixed Deposit
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Flash Messages --}}
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

        {{-- Status Tab Bar --}}
        <div class="border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 p-4">
                {{-- Status Tabs --}}
                <div class="flex items-center gap-1 flex-wrap">
                    @php
                        $tabs = [
                            '' => 'All',
                            'active' => 'Active',
                            'matured' => 'Matured',
                            'liquidated' => 'Liquidated',
                            'rolled_over' => 'Rolled Over',
                        ];
                        $total = $statusCounts->sum();
                    @endphp
                    @foreach($tabs as $value => $label)
                        <a href="{{ route('fixed-deposits.index', array_merge(request()->except('page'), ['status' => $value ?: null])) }}"
                           class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors
                               {{ request('status', '') === $value
                                   ? 'bg-bankos-primary text-white'
                                   : 'text-bankos-text-sec hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            {{ $label }}
                            <span class="ml-1 text-xs {{ request('status', '') === $value ? 'text-blue-100' : 'text-bankos-muted' }}">
                                {{ $value ? ($statusCounts[$value] ?? 0) : $total }}
                            </span>
                        </a>
                    @endforeach
                </div>

                {{-- Search --}}
                <form action="{{ route('fixed-deposits.index') }}" method="GET" class="flex items-center gap-2">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bankos-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-input pl-9 pr-4 py-2 text-sm w-56"
                               placeholder="FD number or customer...">
                    </div>
                    <button type="submit" class="btn btn-secondary text-sm py-2">Search</button>
                    @if(request('search'))
                        <a href="{{ route('fixed-deposits.index', request()->except('search', 'page')) }}"
                           class="text-sm text-bankos-primary hover:underline">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-5 py-4 font-semibold">FD Number</th>
                        <th class="px-5 py-4 font-semibold">Customer</th>
                        <th class="px-5 py-4 font-semibold">Product</th>
                        <th class="px-5 py-4 font-semibold text-right">Principal</th>
                        <th class="px-5 py-4 font-semibold text-right">Rate / Tenure</th>
                        <th class="px-5 py-4 font-semibold">Start — Maturity</th>
                        <th class="px-5 py-4 font-semibold text-right">Accrued Interest</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($fds as $fd)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <p class="font-bold text-bankos-primary font-mono">{{ $fd->fd_number }}</p>
                            @if($fd->auto_rollover)
                                <span class="inline-flex items-center gap-1 text-[10px] text-blue-600 dark:text-blue-400 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                                    Auto-rollover
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-bankos-text dark:text-white">
                                {{ $fd->customer?->first_name }} {{ $fd->customer?->last_name }}
                            </p>
                            <p class="text-[10px] text-bankos-muted mt-0.5 font-mono">{{ $fd->customer?->customer_number }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-bankos-text dark:text-gray-300">{{ $fd->product?->name ?? '—' }}</p>
                            <p class="text-[10px] text-bankos-muted mt-0.5 uppercase">{{ $fd->product?->interest_payment }}</p>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <p class="font-bold text-bankos-text dark:text-gray-200">₦{{ number_format($fd->principal_amount, 2) }}</p>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <p class="font-semibold text-bankos-success">{{ number_format($fd->interest_rate, 2) }}% p.a.</p>
                            <p class="text-xs text-bankos-muted mt-0.5">{{ number_format($fd->tenure_days) }} days</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-xs text-bankos-text-sec">{{ $fd->start_date->format('d M Y') }}</p>
                            <p class="text-xs font-medium text-bankos-text dark:text-white mt-0.5">
                                {{ $fd->maturity_date->format('d M Y') }}
                            </p>
                            @if($fd->status === 'active')
                                @if($fd->days_remaining <= 7)
                                    <p class="text-[10px] text-red-600 font-semibold mt-0.5">{{ $fd->days_remaining }}d left</p>
                                @elseif($fd->days_remaining <= 30)
                                    <p class="text-[10px] text-amber-600 font-semibold mt-0.5">{{ $fd->days_remaining }}d left</p>
                                @endif
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <p class="font-semibold text-bankos-success">₦{{ number_format($fd->accrued_interest, 2) }}</p>
                            <p class="text-[10px] text-bankos-muted mt-0.5">of ₦{{ number_format($fd->expected_interest, 2) }}</p>
                        </td>
                        <td class="px-5 py-4">
                            @if($fd->status === 'active')
                                <span class="badge badge-active">Active</span>
                            @elseif($fd->status === 'matured')
                                <span class="badge bg-amber-100 text-amber-800 border border-amber-200">Matured</span>
                            @elseif($fd->status === 'liquidated')
                                <span class="badge bg-gray-100 text-gray-600 border border-gray-200">Liquidated</span>
                            @elseif($fd->status === 'rolled_over')
                                <span class="badge bg-blue-100 text-blue-700 border border-blue-200">Rolled Over</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('fixed-deposits.show', $fd) }}"
                               class="text-bankos-primary hover:text-blue-700 font-medium text-sm border border-bankos-border dark:border-bankos-dark-border px-3 py-1.5 rounded hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-bankos-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                <p class="text-sm">No fixed deposits found.</p>
                                <a href="{{ route('fixed-deposits.create') }}" class="btn btn-primary text-sm mt-1">Create First FD</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($fds->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $fds->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
