<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Transactions Dashboard</h1>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mt-1">Real-time transaction monitoring, volume analytics and trend analysis</p>
            </div>
        </div>
    </x-slot>

    {{-- ── Filters ────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('transactions.dashboard') }}" class="card p-4 flex flex-wrap items-end gap-4 mb-6">
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">Start Date</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="input input-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">End Date</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="input input-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">Type</label>
            <select name="type" class="input input-sm">
                <option value="">All Types</option>
                @foreach(['deposit','withdrawal','transfer','repayment','disbursement','fee','interest','reversal'] as $t)
                    <option value="{{ $t }}" {{ $filterType == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">Status</label>
            <select name="status" class="input input-sm">
                <option value="">All</option>
                @foreach(['success','pending','failed','reversed'] as $s)
                    <option value="{{ $s }}" {{ $filterStatus == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('transactions.dashboard') }}" class="btn btn-secondary btn-sm">Reset</a>
    </form>

    {{-- ── Row 1: Volume KPIs (Count) ────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Transactions Today</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($txnCountToday) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Volume: ₦{{ number_format($volumeToday, 0) }}</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">This Week</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($txnCountWeek) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Volume: ₦{{ number_format($volumeWeek, 0) }}</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">This Month</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($txnCountMonth) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Volume: ₦{{ number_format($volumeMonth, 0) }}</p>
        </div>
    </div>

    {{-- ── Row 2: Secondary KPIs ─────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Avg Transaction Size</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($avgTxnSize, 0) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">This month</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Success Rate</p>
                    <p class="text-2xl font-extrabold text-green-600 mt-1">{{ $successRate }}%</p>
                </div>
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Failed Rate</p>
                    <p class="text-2xl font-extrabold {{ $failedRate > 5 ? 'text-red-600' : 'text-bankos-text dark:text-bankos-dark-text' }} mt-1">{{ $failedRate }}%</p>
                </div>
                <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Pending</p>
                    <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($pendingTxns) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ number_format($reversedTxns) }} reversed this month</p>
        </div>
    </div>

    {{-- ── Charts Row 1 ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Transactions by Type</h3>
            <canvas id="txnTypeChart" height="280"></canvas>
        </div>

        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Transaction by Status</h3>
            <canvas id="txnStatusChart" height="280"></canvas>
        </div>
    </div>

    {{-- ── Charts Row 2 ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Transaction Trend (Last 30 Days)</h3>
            <canvas id="txnTrendChart" height="280"></canvas>
        </div>

        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Hourly Distribution (Today)</h3>
            <canvas id="hourlyChart" height="280"></canvas>
        </div>
    </div>

    {{-- ── Volume by Type Bar ────────────────────────────────────── --}}
    <div class="card p-5 mb-6">
        <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Volume by Transaction Type (This Month)</h3>
        <canvas id="volumeTypeChart" height="250"></canvas>
    </div>

    {{-- ── Top 10 Transactions Today ─────────────────────────────── --}}
    <div class="card p-5 mb-6">
        <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Top 10 Largest Transactions Today</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-bankos-border dark:border-bankos-dark-border">
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">#</th>
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">Reference</th>
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">Customer</th>
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">Account</th>
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">Type</th>
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">Status</th>
                        <th class="text-right py-3 px-4 font-semibold text-bankos-text-sec">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topTxnsToday as $i => $t)
                    @php
                        $statusBadge = match($t->status) {
                            'success' => 'bg-green-100 text-green-700',
                            'pending' => 'bg-amber-100 text-amber-700',
                            'failed' => 'bg-red-100 text-red-700',
                            'reversed' => 'bg-gray-100 text-gray-700',
                            default => 'bg-gray-100 text-gray-600'
                        };
                    @endphp
                    <tr class="border-b border-bankos-border/50 dark:border-bankos-dark-border/50 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="py-3 px-4">{{ $i + 1 }}</td>
                        <td class="py-3 px-4 font-mono text-xs">{{ $t->reference ?? '-' }}</td>
                        <td class="py-3 px-4 font-medium text-bankos-text dark:text-bankos-dark-text">{{ $t->customer_name }}</td>
                        <td class="py-3 px-4 text-bankos-text-sec">{{ $t->account_number }}</td>
                        <td class="py-3 px-4"><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ ucfirst($t->type) }}</span></td>
                        <td class="py-3 px-4"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadge }}">{{ ucfirst($t->status) }}</span></td>
                        <td class="py-3 px-4 text-right font-semibold">₦{{ number_format($t->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-6 text-center text-bankos-text-sec">No transactions today</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        const typeColors = {
            deposit: '#10b981', withdrawal: '#ef4444', transfer: '#3b82f6',
            repayment: '#8b5cf6', disbursement: '#f59e0b', fee: '#ec4899',
            interest: '#06b6d4', reversal: '#6b7280'
        };
        const sColors = { success: '#10b981', pending: '#f59e0b', failed: '#ef4444', reversed: '#6b7280' };

        // By Type Pie
        new Chart(document.getElementById('txnTypeChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($txnByType->keys()->map(fn($t) => ucfirst($t))) !!},
                datasets: [{
                    data: {!! json_encode($txnByType->values()) !!},
                    backgroundColor: {!! json_encode($txnByType->keys()->map(fn($t) => $typeColors[$t] ?? '#6b7280')->values()) !!},
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // By Status Pie
        new Chart(document.getElementById('txnStatusChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($txnByStatus->keys()->map(fn($s) => ucfirst($s))) !!},
                datasets: [{
                    data: {!! json_encode($txnByStatus->values()) !!},
                    backgroundColor: {!! json_encode($txnByStatus->keys()->map(fn($s) => $sColors[$s] ?? '#6b7280')->values()) !!},
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // 30 Day Trend
        const trendData = @json($txnTrend);
        new Chart(document.getElementById('txnTrendChart'), {
            type: 'line',
            data: {
                labels: trendData.map(d => d.day),
                datasets: [{
                    label: 'Count',
                    data: trendData.map(d => d.count),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y'
                }, {
                    label: 'Volume (₦)',
                    data: trendData.map(d => d.volume),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.1)',
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true, position: 'left' },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } }
                }
            }
        });

        // Hourly Distribution
        new Chart(document.getElementById('hourlyChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($hourlyData->keys()->map(fn($h) => sprintf('%02d:00', $h))) !!},
                datasets: [{
                    label: 'Transactions',
                    data: {!! json_encode($hourlyData->values()) !!},
                    backgroundColor: '#6366f1',
                    borderRadius: 4
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // Volume by Type
        new Chart(document.getElementById('volumeTypeChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($volumeByType->keys()->map(fn($t) => ucfirst($t))) !!},
                datasets: [{
                    label: 'Volume (₦)',
                    data: {!! json_encode($volumeByType->values()) !!},
                    backgroundColor: {!! json_encode($volumeByType->keys()->map(fn($t) => $typeColors[$t] ?? '#6b7280')->values()) !!},
                    borderRadius: 6
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    </script>
    @endpush
</x-app-layout>
