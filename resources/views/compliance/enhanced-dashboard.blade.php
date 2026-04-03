<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Compliance & Risk Dashboard</h1>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mt-1">AML, KYC, sanctions screening, regulatory compliance overview</p>
            </div>
        </div>
    </x-slot>

    {{-- Date Filter --}}
    <form method="GET" action="{{ route('compliance.enhanced-dashboard') }}" class="card p-4 flex flex-wrap items-end gap-4 mb-6">
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">Start Date</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="input input-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">End Date</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="input input-sm">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('compliance.enhanced-dashboard') }}" class="btn btn-secondary btn-sm">Reset</a>
    </form>

    {{-- Row 1: AML & KYC KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">AML Alerts (Open)</p>
                    <p class="text-2xl font-extrabold {{ $amlAlertsOpen > 0 ? 'text-red-600' : 'text-bankos-text dark:text-bankos-dark-text' }} mt-1">{{ number_format($amlAlertsOpen) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $amlAlertsCritical }} critical &middot; {{ $amlAlertsThisMonth }} this period</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">KYC Compliance</p>
                    <p class="text-2xl font-extrabold {{ $kycComplianceRate >= 90 ? 'text-green-600' : ($kycComplianceRate >= 70 ? 'text-amber-600' : 'text-red-600') }} mt-1">{{ $kycComplianceRate }}%</p>
                </div>
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ number_format($approvedKyc) }} of {{ number_format($totalCustomers) }} customers &middot; {{ $pendingKyc }} pending</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">PEP Customers</p>
                    <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($pepCount) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Politically exposed persons flagged</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Sanctions Hits</p>
                    <p class="text-2xl font-extrabold {{ $sanctionsPending > 0 ? 'text-red-600' : 'text-bankos-text dark:text-bankos-dark-text' }} mt-1">{{ number_format($sanctionsHits) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $sanctionsPending }} pending review</p>
        </div>
    </div>

    {{-- Row 2: Transaction Monitoring & NDIC --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Large Txns Today</p>
                    <p class="text-2xl font-extrabold {{ $largeTransactionsToday > 0 ? 'text-accent-crimson' : 'text-bankos-text dark:text-bankos-dark-text' }} mt-1">{{ number_format($largeTransactionsToday) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/30 text-accent-crimson">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Value: ₦{{ number_format($largeTransactionsValue) }} &middot; Period: {{ $largeTransactionsPeriod }}</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">NDIC Coverage</p>
                    <p class="text-2xl font-extrabold text-bankos-primary mt-1">{{ $ndicCoverageRate }}%</p>
                </div>
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-bankos-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ number_format($coveredDepositors) }} of {{ number_format($totalDepositors) }} depositors insured</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Expiring Documents</p>
                    <p class="text-2xl font-extrabold {{ $expiringDocuments > 0 ? 'text-amber-600' : 'text-bankos-text dark:text-bankos-dark-text' }} mt-1">{{ number_format($expiringDocuments) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Next 30 days &middot; {{ $expiredDocuments }} already expired</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Regulatory Reports</p>
                    <p class="text-2xl font-extrabold {{ $reportsOverdue > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">{{ $reportsPending }}</p>
                </div>
                <div class="p-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $reportsSubmitted }} submitted &middot; {{ $reportsOverdue }} overdue</p>
        </div>
    </div>

    {{-- Row 3: Risk Rating & CBN --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5 border-l-4 border-green-400">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Low Risk Customers</p>
            <p class="text-2xl font-extrabold text-green-600 mt-1">{{ number_format($riskLow) }}</p>
        </div>
        <div class="card p-5 border-l-4 border-amber-400">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Medium Risk Customers</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($riskMedium) }}</p>
        </div>
        <div class="card p-5 border-l-4 border-red-400">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">High Risk Customers</p>
            <p class="text-2xl font-extrabold text-red-600 mt-1">{{ number_format($riskHigh) }}</p>
        </div>
        <div class="card p-5 border-l-4 border-indigo-400">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">CBN Reports</p>
            <p class="text-2xl font-extrabold text-indigo-600 mt-1">{{ $cbnReportsSubmitted }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">Submitted YTD &middot; {{ $cbnReportsdue }} pending</p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- AML Alert Trend --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">AML Alert Trend (Last 30 Days)</h3>
            <canvas id="amlTrendChart" height="200"></canvas>
        </div>

        {{-- Risk Rating Distribution --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Customer Risk Rating Distribution</h3>
            <canvas id="riskPieChart" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Large Transaction Trend --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Large Transaction Trend (>&#8358;5M)</h3>
            <canvas id="largeTxnChart" height="200"></canvas>
        </div>

        {{-- KYC Level Breakdown --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">KYC Level Breakdown</h3>
            <canvas id="kycPieChart" height="200"></canvas>
        </div>
    </div>

    {{-- Recent AML Alerts Table --}}
    <div class="card p-6">
        <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Recent AML Alerts</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-bankos-border dark:border-bankos-dark-border">
                        <th class="text-left py-2 px-3 text-xs text-bankos-text-sec uppercase">Date</th>
                        <th class="text-left py-2 px-3 text-xs text-bankos-text-sec uppercase">Type</th>
                        <th class="text-left py-2 px-3 text-xs text-bankos-text-sec uppercase">Severity</th>
                        <th class="text-left py-2 px-3 text-xs text-bankos-text-sec uppercase">Status</th>
                        <th class="text-left py-2 px-3 text-xs text-bankos-text-sec uppercase">Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAlerts as $alert)
                    <tr class="border-b border-bankos-border/50 dark:border-bankos-dark-border/50 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                        <td class="py-2 px-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($alert->created_at)->format('d M H:i') }}</td>
                        <td class="py-2 px-3">{{ str_replace('_', ' ', $alert->alert_type ?? '-') }}</td>
                        <td class="py-2 px-3">
                            @php $sevColors = ['critical'=>'bg-red-100 text-red-700','high'=>'bg-orange-100 text-orange-700','medium'=>'bg-amber-100 text-amber-700','low'=>'bg-gray-100 text-gray-700']; @endphp
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $sevColors[$alert->severity ?? 'low'] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($alert->severity ?? 'low') }}</span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="text-xs font-medium {{ ($alert->status ?? '') === 'open' ? 'text-red-600' : (($alert->status ?? '') === 'escalated' ? 'text-amber-600' : 'text-green-600') }}">{{ ucfirst($alert->status ?? '-') }}</span>
                        </td>
                        <td class="py-2 px-3 max-w-xs truncate">{{ $alert->description ?? $alert->details ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-6 text-center text-bankos-text-sec">No AML alerts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // AML Alert Trend
        new Chart(document.getElementById('amlTrendChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($amlTrend->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))) !!},
                datasets: [{
                    label: 'Alerts',
                    data: {!! json_encode($amlTrend->pluck('total')) !!},
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220,38,38,0.1)',
                    fill: true, tension: 0.3, pointRadius: 3
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // Risk Rating Pie
        new Chart(document.getElementById('riskPieChart'), {
            type: 'doughnut',
            data: {
                labels: ['Low Risk', 'Medium Risk', 'High Risk'],
                datasets: [{
                    data: [{{ $riskLow }}, {{ $riskMedium }}, {{ $riskHigh }}],
                    backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Large Transaction Trend
        new Chart(document.getElementById('largeTxnChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($largeTxnTrend->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))) !!},
                datasets: [{
                    label: 'Count',
                    data: {!! json_encode($largeTxnTrend->pluck('total')) !!},
                    backgroundColor: '#ef4444'
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // KYC Level Pie
        new Chart(document.getElementById('kycPieChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($kycBreakdown->keys()->map(fn($k) => 'Level ' . $k)) !!},
                datasets: [{
                    data: {!! json_encode($kycBreakdown->values()) !!},
                    backgroundColor: ['#94a3b8', '#3b82f6', '#22c55e', '#f59e0b', '#8b5cf6'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    </script>
    @endpush
</x-app-layout>
