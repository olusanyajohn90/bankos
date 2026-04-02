<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Operations & Analytics Dashboard</h1>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mt-1">Operational metrics, branch performance, GL overview and system health</p>
            </div>
        </div>
    </x-slot>

    {{-- ── Filters ────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('operations.dashboard') }}" class="card p-4 flex flex-wrap items-end gap-4 mb-6">
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">Start Date</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="input input-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">End Date</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="input input-sm">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('operations.dashboard') }}" class="btn btn-secondary btn-sm">Reset</a>
    </form>

    {{-- ── Row 1: Primary KPIs ───────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Daily Transactions</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($dailyTxnCount) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Volume: ₦{{ number_format($dailyTxnVolume, 0) }}</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Cash Position</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($cashPosition, 0) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Total across all accounts</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Fee Revenue (Month)</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($feeRevenue, 0) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Interest Income (Month)</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($interestIncome, 0) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-cyan-50 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 2: Operational KPIs ───────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-5 mb-6">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Active Agents</p>
            <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($activeAgents) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Agent Float</p>
            <p class="text-lg font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($agentFloat, 0) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Customers</p>
            <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($totalCustomers) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Active Loans</p>
            <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($activeLoans) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Pending TXNs</p>
            <p class="text-2xl font-extrabold {{ $pendingTxns > 0 ? 'text-amber-600' : 'text-green-600' }} mt-1">{{ number_format($pendingTxns) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Failed Jobs</p>
            <p class="text-2xl font-extrabold {{ $failedJobs > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">{{ number_format($failedJobs) }}</p>
        </div>
    </div>

    {{-- ── Disbursements Today KPI ───────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Disbursements Today</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($disbursementsToday, 0) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                </div>
            </div>
        </div>
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Revenue Breakdown (Month)</p>
                    <div class="flex gap-4 mt-2">
                        <div>
                            <p class="text-xs text-bankos-text-sec">Fees</p>
                            <p class="text-lg font-bold text-purple-600">₦{{ number_format($revenueBreakdown->get('fee', 0), 0) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-bankos-text-sec">Interest</p>
                            <p class="text-lg font-bold text-cyan-600">₦{{ number_format($revenueBreakdown->get('interest', 0), 0) }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Charts Row 1: GL + Volume Trend ───────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">GL Balance Overview (Top 10)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-bankos-border dark:border-bankos-dark-border">
                            <th class="text-left py-2 px-3 font-semibold text-bankos-text-sec">GL Account</th>
                            <th class="text-left py-2 px-3 font-semibold text-bankos-text-sec">Category</th>
                            <th class="text-right py-2 px-3 font-semibold text-bankos-text-sec">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($glBalances as $gl)
                        <tr class="border-b border-bankos-border/50 dark:border-bankos-dark-border/50">
                            <td class="py-2 px-3">
                                <p class="font-medium text-bankos-text dark:text-bankos-dark-text">{{ $gl->name }}</p>
                                <p class="text-xs text-bankos-text-sec">{{ $gl->account_number }}</p>
                            </td>
                            <td class="py-2 px-3 text-bankos-text-sec">{{ ucfirst($gl->category ?? '-') }}</td>
                            <td class="py-2 px-3 text-right font-semibold {{ $gl->balance < 0 ? 'text-red-600' : 'text-bankos-text dark:text-bankos-dark-text' }}">₦{{ number_format($gl->balance, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="py-4 text-center text-bankos-text-sec">No GL accounts</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Daily Volume Trend (30 Days)</h3>
            <canvas id="volumeTrendChart" height="280"></canvas>
        </div>
    </div>

    {{-- ── Charts Row 2: Branch Performance ──────────────────────── --}}
    <div class="card p-5 mb-6">
        <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Branch Performance Comparison</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-bankos-border dark:border-bankos-dark-border">
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">Branch</th>
                        <th class="text-right py-3 px-4 font-semibold text-bankos-text-sec">Customers</th>
                        <th class="text-right py-3 px-4 font-semibold text-bankos-text-sec">Accounts</th>
                        <th class="text-right py-3 px-4 font-semibold text-bankos-text-sec">Total Deposits</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branchPerformance as $bp)
                    <tr class="border-b border-bankos-border/50 dark:border-bankos-dark-border/50 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="py-3 px-4 font-medium text-bankos-text dark:text-bankos-dark-text">{{ $bp->name }}</td>
                        <td class="py-3 px-4 text-right">{{ number_format($bp->customer_count) }}</td>
                        <td class="py-3 px-4 text-right">{{ number_format($bp->account_count) }}</td>
                        <td class="py-3 px-4 text-right font-semibold">₦{{ number_format($bp->total_deposits, 0) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-6 text-center text-bankos-text-sec">No branch data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Teller Activity ───────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Teller Activity Today</h3>
            <canvas id="tellerChart" height="280"></canvas>
        </div>

        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Revenue Breakdown</h3>
            <canvas id="revenueChart" height="280"></canvas>
        </div>
    </div>

    @push('scripts')
    <script>
        // Daily Volume Trend
        const volData = @json($dailyVolumeTrend);
        new Chart(document.getElementById('volumeTrendChart'), {
            type: 'line',
            data: {
                labels: volData.map(d => d.day),
                datasets: [{
                    label: 'Count',
                    data: volData.map(d => d.count),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y'
                }, {
                    label: 'Volume (₦)',
                    data: volData.map(d => d.volume),
                    borderColor: '#10b981',
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

        // Teller Activity
        const tellerData = @json($tellerActivity);
        new Chart(document.getElementById('tellerChart'), {
            type: 'bar',
            data: {
                labels: tellerData.map(t => t.name),
                datasets: [{
                    label: 'Transactions',
                    data: tellerData.map(t => t.txn_count),
                    backgroundColor: '#6366f1',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } }
            }
        });

        // Revenue Breakdown
        const revLabels = {!! json_encode($revenueBreakdown->keys()->map(fn($k) => ucfirst($k))) !!};
        const revData = {!! json_encode($revenueBreakdown->values()) !!};
        new Chart(document.getElementById('revenueChart'), {
            type: 'doughnut',
            data: {
                labels: revLabels.length ? revLabels : ['No Data'],
                datasets: [{
                    data: revData.length ? revData : [1],
                    backgroundColor: revLabels.length ? ['#8b5cf6', '#06b6d4', '#f59e0b'] : ['#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    </script>
    @endpush
</x-app-layout>
