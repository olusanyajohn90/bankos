<x-app-layout>
    <x-slot name="header">Risk Scoring Dashboard</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text">Customer Risk Distribution</h2>
            <form method="POST" action="{{ route('compliance-auto.batch-risk') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-bankos-primary text-white rounded-lg text-sm hover:bg-bankos-primary/90">
                    Batch Score All Customers
                </button>
            </form>
        </div>

        {{-- Stats & Chart --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Distribution Doughnut --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                <h3 class="text-sm font-semibold text-bankos-muted uppercase mb-4">Risk Distribution</h3>
                <div class="flex justify-center">
                    <canvas id="riskDistChart" width="250" height="250"></canvas>
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                    <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Low Risk</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $distribution['low'] }}</p>
                </div>
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                    <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Medium Risk</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $distribution['medium'] }}</p>
                </div>
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                    <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">High Risk</p>
                    <p class="text-3xl font-bold text-orange-600 mt-1">{{ $distribution['high'] }}</p>
                </div>
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                    <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Critical / PEP</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">{{ $distribution['critical'] + $distribution['pep'] }}</p>
                </div>

                {{-- Risk Trend (placeholder line chart) --}}
                <div class="col-span-2 sm:col-span-4 bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                    <h3 class="text-sm font-semibold text-bankos-muted uppercase mb-3">Risk Score Trend (Last 6 Months)</h3>
                    <canvas id="riskTrendChart" height="120"></canvas>
                </div>
            </div>
        </div>

        {{-- High-Risk Customers Table --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="px-4 py-3 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">High-Risk Customers</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Customer</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Score</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Risk Level</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Assessed</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($highRisk as $rs)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                            <td class="px-4 py-3">
                                <div class="font-medium text-bankos-text dark:text-bankos-dark-text">{{ $rs->customer->first_name ?? '' }} {{ $rs->customer->last_name ?? '' }}</div>
                                <div class="text-xs text-bankos-muted">{{ $rs->customer->customer_number ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-gray-200 dark:bg-bankos-dark-bg rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $rs->overall_score >= 80 ? 'bg-red-500' : ($rs->overall_score >= 60 ? 'bg-orange-500' : 'bg-yellow-500') }}" style="width: {{ $rs->overall_score }}%"></div>
                                    </div>
                                    <span class="text-xs font-mono">{{ $rs->overall_score }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $badge = match($rs->risk_level) {
                                        'critical' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
                                        'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-400',
                                        'pep' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-400',
                                        default => 'bg-yellow-100 text-yellow-700',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">{{ strtoupper($rs->risk_level) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-bankos-muted">{{ $rs->last_assessed_at?->diffForHumans() ?? 'Never' }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('compliance-auto.customer-risk', $rs->customer_id) }}" class="text-bankos-primary hover:underline text-xs font-medium">View Details</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-bankos-muted">No high-risk customers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('riskDistChart'), {
            type: 'doughnut',
            data: {
                labels: ['Low', 'Medium', 'High', 'Critical', 'PEP'],
                datasets: [{
                    data: [{{ $distribution['low'] }}, {{ $distribution['medium'] }}, {{ $distribution['high'] }}, {{ $distribution['critical'] }}, {{ $distribution['pep'] }}],
                    backgroundColor: ['#22c55e', '#eab308', '#f97316', '#ef4444', '#a855f7'],
                    borderWidth: 0,
                }]
            },
            options: { responsive: false, plugins: { legend: { position: 'bottom', labels: { padding: 15, font: { size: 11 } } } } }
        });

        new Chart(document.getElementById('riskTrendChart'), {
            type: 'line',
            data: {
                labels: ['Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr'],
                datasets: [{
                    label: 'Avg Risk Score',
                    data: [28, 31, 30, 34, 33, {{ $scores->avg('overall_score') ? round($scores->avg('overall_score'), 1) : 30 }}],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.1)',
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } }, plugins: { legend: { display: false } } }
        });
    </script>
    @endpush
</x-app-layout>
