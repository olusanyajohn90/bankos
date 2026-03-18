<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">CBN Single Obligor Limit (SOL)</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Customers whose total exposure exceeds {{ number_format($limitPct * 100, 0) }}% of shareholders' equity — as of {{ now()->format('d M Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="flex justify-end mb-6 print:hidden">
        <button class="btn btn-secondary text-sm flex items-center gap-2" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print
        </button>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Capital Base</p>
            <p class="text-2xl font-extrabold text-bankos-text mt-1">₦{{ number_format($capitalBase, 0) }}</p>
            <p class="text-xs text-bankos-muted mt-1">Shareholders' equity</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">SOL Threshold</p>
            <p class="text-2xl font-extrabold text-bankos-primary mt-1">₦{{ number_format($limit, 0) }}</p>
            <p class="text-xs text-bankos-muted mt-1">{{ number_format($limitPct * 100, 0) }}% of capital</p>
        </div>
        <div class="card p-5 {{ $breaches->count() > 0 ? 'border-red-300 dark:border-red-800' : '' }}">
            <p class="text-xs {{ $breaches->count() > 0 ? 'text-red-600' : 'text-bankos-text-sec' }} uppercase tracking-wider font-semibold">SOL Breaches</p>
            <p class="text-2xl font-extrabold {{ $breaches->count() > 0 ? 'text-red-600' : 'text-emerald-600' }} mt-1">{{ $breaches->count() }}</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $breaches->count() > 0 ? 'Require immediate action' : 'None — compliant' }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Borrowers Tracked</p>
            <p class="text-2xl font-extrabold text-bankos-text mt-1">{{ number_format($exposureByCustomer->count()) }}</p>
        </div>
    </div>

    {{-- Breaches --}}
    @if($breaches->isNotEmpty())
    <div class="card p-0 overflow-hidden shadow-md mb-8 border-red-300 dark:border-red-800">
        <div class="px-6 py-4 border-b border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20">
            <h3 class="text-sm font-semibold text-red-700 dark:text-red-400">SOL Breaches — Immediate Action Required</h3>
            <p class="text-xs text-red-600 dark:text-red-300 mt-0.5">The following customers exceed the ₦{{ number_format($limit, 0) }} single obligor limit</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-red-50/50 dark:bg-red-900/10 border-b border-red-200 dark:border-red-800 text-xs uppercase text-red-700 dark:text-red-400">
                        <th class="px-6 py-3 font-semibold">Customer</th>
                        <th class="px-6 py-3 font-semibold text-right">Active Loans</th>
                        <th class="px-6 py-3 font-semibold text-right">Total Exposure</th>
                        <th class="px-6 py-3 font-semibold text-right">Excess</th>
                        <th class="px-6 py-3 font-semibold text-right">% of Capital</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-red-100 dark:divide-red-900/30">
                    @foreach($breaches as $row)
                    <tr class="bg-red-50/30 dark:bg-red-900/5">
                        <td class="px-6 py-3">
                            <p class="font-semibold text-red-800 dark:text-red-300">{{ $row['customer']?->full_name ?? '—' }}</p>
                            <p class="text-xs text-red-600 dark:text-red-400">{{ $row['customer']?->customer_number ?? '—' }}</p>
                        </td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-text-sec">{{ $row['loan_count'] }}</td>
                        <td class="px-6 py-3 text-right font-mono font-bold text-red-700">₦{{ number_format($row['total_exposure'], 2) }}</td>
                        <td class="px-6 py-3 text-right font-mono text-red-600">₦{{ number_format($row['total_exposure'] - $limit, 2) }}</td>
                        <td class="px-6 py-3 text-right font-mono font-bold text-red-700">{{ number_format($row['total_exposure'] / $capitalBase * 100, 1) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="card p-8 text-center text-emerald-600 mb-8 border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/10">
        <svg class="mx-auto w-10 h-10 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="font-semibold">No SOL breaches detected — portfolio is compliant.</p>
    </div>
    @endif

    {{-- Full exposure list --}}
    <div class="card p-0 overflow-hidden shadow-md">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text">Full Exposure by Customer (Top 50)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b border-bankos-border text-xs uppercase text-bankos-text-sec">
                        <th class="px-6 py-3 font-semibold">Customer</th>
                        <th class="px-6 py-3 font-semibold text-right">Loans</th>
                        <th class="px-6 py-3 font-semibold text-right">Total Exposure</th>
                        <th class="px-6 py-3 font-semibold text-right">% of Capital</th>
                        <th class="px-6 py-3 font-semibold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($exposureByCustomer->take(50) as $row)
                    @php $pct = $row['total_exposure'] / $capitalBase * 100; @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 {{ $row['total_exposure'] > $limit ? 'bg-red-50/30 dark:bg-red-900/5' : '' }}">
                        <td class="px-6 py-3">
                            <p class="font-medium text-bankos-text">{{ $row['customer']?->full_name ?? '—' }}</p>
                            <p class="text-xs text-bankos-muted font-mono">{{ $row['customer']?->customer_number ?? '—' }}</p>
                        </td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-text-sec">{{ $row['loan_count'] }}</td>
                        <td class="px-6 py-3 text-right font-mono font-semibold {{ $row['total_exposure'] > $limit ? 'text-red-600' : 'text-bankos-primary' }}">₦{{ number_format($row['total_exposure'], 2) }}</td>
                        <td class="px-6 py-3 text-right font-mono {{ $pct > $limitPct * 100 ? 'text-red-600 font-bold' : 'text-bankos-text-sec' }}">{{ number_format($pct, 1) }}%</td>
                        <td class="px-6 py-3 text-center">
                            @if($row['total_exposure'] > $limit)
                                <span class="text-xs text-red-700 bg-red-100 px-2 py-0.5 rounded-full font-semibold">SOL Breach</span>
                            @else
                                <span class="text-xs text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">Compliant</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
