<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Teller Monitoring</h2>
                <p class="text-sm text-bankos-text-sec mt-1">{{ now()->format('l, F j, Y') }} — Real-time teller operations overview</p>
            </div>
            <a href="{{ route('teller.index') }}" class="btn btn-secondary text-sm">My Workstation</a>
        </div>
    </x-slot>

    {{-- ─── KPI Stats ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Open Sessions</p>
            <p class="text-3xl font-black {{ $stats['open_sessions'] > 0 ? 'text-green-600' : 'text-bankos-text dark:text-bankos-dark-text' }}">{{ $stats['open_sessions'] }}</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Closed Today</p>
            <p class="text-3xl font-black text-bankos-text dark:text-bankos-dark-text">{{ $stats['closed_sessions'] }}</p>
        </div>
        <div class="card p-4 text-center col-span-2">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Total Cash In</p>
            <p class="text-2xl font-black text-green-600">₦{{ number_format($stats['total_cash_in'], 2) }}</p>
        </div>
        <div class="card p-4 text-center col-span-2">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Total Cash Out</p>
            <p class="text-2xl font-black text-red-600">₦{{ number_format($stats['total_cash_out'], 2) }}</p>
        </div>
    </div>

    @if($stats['unbalanced_count'] > 0)
    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-600 shrink-0"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <p class="text-sm font-semibold text-red-700 dark:text-red-400">
            {{ $stats['unbalanced_count'] }} session{{ $stats['unbalanced_count'] > 1 ? 's' : '' }} closed with variance today — Total variance: ₦{{ number_format($stats['total_variance'], 2) }}
        </p>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- ─── Today's Sessions Table ─────────────────────────────────────── --}}
        <div class="lg:col-span-2 card p-0 overflow-hidden">
            <div class="px-5 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex items-center justify-between">
                <h3 class="font-semibold text-sm text-bankos-text dark:text-bankos-dark-text">Today's Teller Sessions</h3>
                <span class="text-xs text-bankos-muted">{{ $todaySessions->count() }} total</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-muted">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Teller</th>
                            <th class="px-4 py-3 font-semibold">Branch</th>
                            <th class="px-4 py-3 font-semibold text-right">Cash In</th>
                            <th class="px-4 py-3 font-semibold text-right">Cash Out</th>
                            <th class="px-4 py-3 font-semibold text-right">Variance</th>
                            <th class="px-4 py-3 font-semibold text-center">Txns</th>
                            <th class="px-4 py-3 font-semibold text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($todaySessions as $session)
                        @php
                        $statusCls = match($session->status) {
                            'open'        => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                            'balanced'    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                            'unbalanced'  => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            default       => 'bg-gray-100 text-gray-500',
                        };
                        $txInfo = $tellerTxCounts->get($session->teller_id);
                        $variance = (float) $session->variance;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors {{ $session->status === 'unbalanced' ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-bankos-text dark:text-bankos-dark-text text-sm">{{ $session->teller?->name ?? '—' }}</p>
                                <p class="text-xs text-bankos-muted">{{ $session->created_at?->format('H:i') ?? '—' }} opened</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-bankos-text-sec">{{ $session->branch?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm text-green-600 font-medium">₦{{ number_format($session->cash_in, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-red-600 font-medium">₦{{ number_format($session->cash_out, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold {{ $variance < 0 ? 'text-red-600' : ($variance > 0 ? 'text-amber-600' : 'text-bankos-muted') }}">
                                {{ $session->status === 'open' ? '—' : (($variance >= 0 ? '+' : '') . '₦' . number_format($variance, 2)) }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-bankos-text-sec">
                                {{ $txInfo?->tx_count ?? 0 }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full capitalize {{ $statusCls }}">{{ $session->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-bankos-muted text-sm">No teller sessions today.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ─── 7-Day Cash Flow ─────────────────────────────────────────────── --}}
        <div class="card p-5">
            <h3 class="font-semibold text-sm text-bankos-text dark:text-bankos-dark-text mb-4">7-Day Cash Flow</h3>
            <div class="space-y-3">
                @foreach($dailyCashFlow as $day)
                @php
                $maxVal = max(collect($dailyCashFlow)->max('cash_in'), collect($dailyCashFlow)->max('cash_out'), 1);
                $inPct  = min(100, round(($day['cash_in'] / $maxVal) * 100));
                $outPct = min(100, round(($day['cash_out'] / $maxVal) * 100));
                @endphp
                <div>
                    <div class="flex justify-between text-xs text-bankos-muted mb-1">
                        <span class="font-medium">{{ $day['date'] }}</span>
                        <span class="text-green-600">+₦{{ number_format($day['cash_in']/1000, 0) }}k</span>
                    </div>
                    <div class="flex gap-1 h-3">
                        <div class="bg-green-400 dark:bg-green-600 rounded-sm transition-all" style="width: {{ $inPct }}%"></div>
                        <div class="bg-red-400 dark:bg-red-600 rounded-sm transition-all" style="width: {{ $outPct }}%"></div>
                    </div>
                </div>
                @endforeach
                <div class="flex gap-4 text-xs text-bankos-muted pt-2 border-t border-bankos-border dark:border-bankos-dark-border">
                    <span class="flex items-center gap-1"><span class="inline-block w-2 h-2 bg-green-400 rounded-sm"></span> Cash In</span>
                    <span class="flex items-center gap-1"><span class="inline-block w-2 h-2 bg-red-400 rounded-sm"></span> Cash Out</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ─── Variance Alerts ─────────────────────────────────────────────── --}}
        <div class="card p-0 overflow-hidden">
            <div class="px-5 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-500"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <h3 class="font-semibold text-sm text-bankos-text dark:text-bankos-dark-text">Variance Alerts (Last 30 Days)</h3>
            </div>
            @if($varianceAlerts->isEmpty())
            <p class="px-5 py-10 text-center text-bankos-muted text-sm">No variance incidents in the last 30 days.</p>
            @else
            <div class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                @foreach($varianceAlerts as $alert)
                @php $v = (float)$alert->variance; @endphp
                <div class="px-4 py-3 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">{{ $alert->teller?->name ?? '—' }}</p>
                        <p class="text-xs text-bankos-muted">{{ $alert->branch?->name ?? '—' }} · {{ \Carbon\Carbon::parse($alert->session_date)->format('d M Y') }}</p>
                    </div>
                    <span class="text-sm font-bold {{ $v < 0 ? 'text-red-600' : 'text-amber-600' }} shrink-0">
                        {{ ($v >= 0 ? '+' : '') }}₦{{ number_format($v, 2) }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ─── Monthly Volume Trend ────────────────────────────────────────── --}}
        <div class="card p-5">
            <h3 class="font-semibold text-sm text-bankos-text dark:text-bankos-dark-text mb-4">Monthly Volume (6 Months)</h3>
            @php $maxMonthly = max(collect($monthlyTrend)->max('cash_in'), collect($monthlyTrend)->max('cash_out'), 1); @endphp
            <div class="space-y-4">
                @foreach($monthlyTrend as $m)
                @php
                $inPct  = min(100, round(($m['cash_in'] / $maxMonthly) * 100));
                $outPct = min(100, round(($m['cash_out'] / $maxMonthly) * 100));
                @endphp
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-semibold text-bankos-text dark:text-bankos-dark-text">{{ $m['month'] }}</span>
                        <span class="text-bankos-muted">{{ $m['sessions'] }} sessions</span>
                    </div>
                    <div class="grid grid-cols-2 gap-1">
                        <div>
                            <div class="h-4 bg-gray-100 dark:bg-gray-700 rounded-sm overflow-hidden">
                                <div class="h-full bg-green-500 rounded-sm" style="width: {{ $inPct }}%"></div>
                            </div>
                            <p class="text-xs text-green-600 mt-0.5">₦{{ number_format($m['cash_in']/1000000, 1) }}M in</p>
                        </div>
                        <div>
                            <div class="h-4 bg-gray-100 dark:bg-gray-700 rounded-sm overflow-hidden">
                                <div class="h-full bg-red-500 rounded-sm" style="width: {{ $outPct }}%"></div>
                            </div>
                            <p class="text-xs text-red-600 mt-0.5">₦{{ number_format($m['cash_out']/1000000, 1) }}M out</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</x-app-layout>
