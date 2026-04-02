<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold leading-tight flex items-center gap-3">
                    <span class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-xl shadow-lg shadow-indigo-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                    </span>
                    BankOS Cortex&trade; AI Command Center
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Real-time AI-powered portfolio intelligence and risk monitoring</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-full {{ empty(config('services.anthropic.api_key')) ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' : 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' }}">
                    <span class="w-2 h-2 rounded-full {{ empty(config('services.anthropic.api_key')) ? 'bg-amber-500' : 'bg-green-500' }} animate-pulse"></span>
                    {{ empty(config('services.anthropic.api_key')) ? 'Cortex Standard' : 'Cortex AI Active' }}
                </span>
            </div>
        </div>
    </x-slot>

    <!-- Hero gradient bar -->
    <div class="h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-full mb-8 opacity-80"></div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Analyzed -->
        <div class="card p-6 ring-1 ring-indigo-500/10">
            <div class="flex items-center justify-between mb-4">
                <span class="p-2 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </span>
                <span class="text-xs font-medium text-bankos-muted uppercase tracking-wider">Customers</span>
            </div>
            <p class="text-3xl font-bold">{{ number_format($totalCustomers) }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">{{ number_format($portfolioSummary['total_customers']) }} analyzed</p>
        </div>

        <!-- High Risk Alerts -->
        <div class="card p-6 ring-1 ring-red-500/10">
            <div class="flex items-center justify-between mb-4">
                <span class="p-2 bg-red-100 dark:bg-red-900/40 text-red-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </span>
                <span class="text-xs font-medium text-bankos-muted uppercase tracking-wider">High Risk</span>
            </div>
            <p class="text-3xl font-bold text-red-600">{{ $portfolioSummary['risk_distribution']['high'] }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">{{ $overdueLoans }} overdue loan(s)</p>
        </div>

        <!-- Churn Risk -->
        <div class="card p-6 ring-1 ring-amber-500/10">
            <div class="flex items-center justify-between mb-4">
                <span class="p-2 bg-amber-100 dark:bg-amber-900/40 text-amber-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="22" y1="11" x2="16" y2="11"></line></svg>
                </span>
                <span class="text-xs font-medium text-bankos-muted uppercase tracking-wider">Churn Risk</span>
            </div>
            <p class="text-3xl font-bold text-amber-600">{{ count($churnWatchlist) }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">Customers at risk of leaving</p>
        </div>

        <!-- Portfolio Health -->
        <div class="card p-6 ring-1 ring-green-500/10">
            <div class="flex items-center justify-between mb-4">
                <span class="p-2 bg-green-100 dark:bg-green-900/40 text-green-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                </span>
                <span class="text-xs font-medium text-bankos-muted uppercase tracking-wider">Health Score</span>
            </div>
            <p class="text-3xl font-bold {{ $portfolioSummary['health_score'] >= 70 ? 'text-green-600' : ($portfolioSummary['health_score'] >= 40 ? 'text-amber-600' : 'text-red-600') }}">{{ $portfolioSummary['health_score'] }}/100</p>
            <p class="text-xs text-bankos-text-sec mt-1">Outstanding: &#8358;{{ number_format($totalOutstanding, 2) }}</p>
        </div>
    </div>

    <!-- Charts & Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Risk Distribution Chart -->
        <div class="card p-6">
            <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-indigo-500"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                Risk Distribution
            </h3>
            <div class="relative" style="height: 250px;">
                <canvas id="riskChart"></canvas>
            </div>
            <div class="flex justify-center gap-6 mt-4 text-xs">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-green-500"></span> Low: {{ $portfolioSummary['risk_distribution']['low'] }}</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Medium: {{ $portfolioSummary['risk_distribution']['medium'] }}</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500"></span> High: {{ $portfolioSummary['risk_distribution']['high'] }}</span>
            </div>
        </div>

        <!-- Fraud Alerts -->
        <div class="card p-6 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-lg flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-500"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    Fraud Alerts
                </h3>
                <a href="{{ route('cortex.fraud-alerts') }}" class="text-xs text-indigo-600 hover:underline font-medium">View All &rarr;</a>
            </div>
            @if(empty($fraudAlerts))
                <div class="text-center py-8 text-bankos-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto mb-3 text-green-400"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <p class="text-sm font-medium">No fraud alerts detected</p>
                    <p class="text-xs text-bankos-muted mt-1">All monitored accounts appear clean</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs text-bankos-muted uppercase tracking-wider border-b border-bankos-border dark:border-bankos-dark-border">
                                <th class="text-left py-2 px-3">Customer</th>
                                <th class="text-left py-2 px-3">Risk</th>
                                <th class="text-left py-2 px-3">Alert Type</th>
                                <th class="text-right py-2 px-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($fraudAlerts, 0, 5) as $alert)
                            <tr class="border-b border-bankos-border/50 dark:border-bankos-dark-border/50 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50">
                                <td class="py-2.5 px-3 font-medium">{{ $alert['customer_name'] }}</td>
                                <td class="py-2.5 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $alert['risk_level'] === 'high' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' : ($alert['risk_level'] === 'medium' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' : 'bg-green-100 text-green-700') }}">
                                        {{ ucfirst($alert['risk_level']) }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-bankos-text-sec">
                                    @if(!empty($alert['alerts']))
                                        {{ $alert['alerts'][0]['type'] ?? 'Unknown' }}
                                    @else
                                        Suspicious patterns
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <a href="{{ route('cortex.customer', $alert['customer_id']) }}" class="text-indigo-600 hover:underline text-xs font-medium">Investigate</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Watchlist & Churn -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- High Risk Watchlist -->
        <div class="card p-6">
            <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-500"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                High Risk Watchlist
            </h3>
            @if(empty($portfolioSummary['watchlist']))
                <div class="text-center py-8 text-bankos-muted">
                    <p class="text-sm">No high-risk customers identified</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach(array_slice($portfolioSummary['watchlist'], 0, 5) as $item)
                    <a href="{{ route('cortex.customer', $item['id']) }}" class="flex items-center justify-between p-3 rounded-lg bg-red-50/50 dark:bg-red-900/10 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors border border-red-200/50 dark:border-red-800/30">
                        <div>
                            <p class="font-medium text-sm">{{ $item['name'] }}</p>
                            <p class="text-xs text-bankos-text-sec">{{ implode(', ', $item['reasons']) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-red-600">&#8358;{{ number_format($item['outstanding'], 2) }}</p>
                            <p class="text-xs text-bankos-muted">outstanding</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Churn Watchlist -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-lg flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-500"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="22" y1="11" x2="16" y2="11"></line></svg>
                    Churn Watchlist
                </h3>
                <a href="{{ route('cortex.churn-risk') }}" class="text-xs text-indigo-600 hover:underline font-medium">View All &rarr;</a>
            </div>
            @if(empty($churnWatchlist))
                <div class="text-center py-8 text-bankos-muted">
                    <p class="text-sm">No significant churn risk detected</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach(array_slice($churnWatchlist, 0, 5) as $item)
                    <a href="{{ route('cortex.customer', $item['customer_id']) }}" class="flex items-center justify-between p-3 rounded-lg bg-amber-50/50 dark:bg-amber-900/10 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors border border-amber-200/50 dark:border-amber-800/30">
                        <div>
                            <p class="font-medium text-sm">{{ $item['customer_name'] }}</p>
                            <p class="text-xs text-bankos-text-sec">
                                @if(!empty($item['risk_factors']))
                                    {{ $item['risk_factors'][0] }}
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-1">
                                <div class="h-2 rounded-full {{ $item['churn_probability'] >= 0.6 ? 'bg-red-500' : 'bg-amber-500' }}" style="width: {{ $item['churn_probability'] * 100 }}%"></div>
                            </div>
                            <p class="text-xs font-semibold {{ $item['churn_probability'] >= 0.6 ? 'text-red-600' : 'text-amber-600' }}">{{ round($item['churn_probability'] * 100) }}%</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Concentration Risks -->
    @if(!empty($portfolioSummary['concentration_risks']))
    <div class="card p-6 mb-8 ring-1 ring-amber-500/20">
        <h3 class="font-semibold text-lg mb-3 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-500"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            Concentration Risks
        </h3>
        <ul class="space-y-2">
            @foreach($portfolioSummary['concentration_risks'] as $risk)
            <li class="text-sm text-bankos-text-sec flex items-start gap-2">
                <span class="text-amber-500 mt-0.5">&bull;</span>
                {{ $risk }}
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('riskChart')?.getContext('2d');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Low Risk', 'Medium Risk', 'High Risk'],
                datasets: [{
                    data: [{{ $portfolioSummary['risk_distribution']['low'] }}, {{ $portfolioSummary['risk_distribution']['medium'] }}, {{ $portfolioSummary['risk_distribution']['high'] }}],
                    backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                    borderWidth: 0,
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + context.raw + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    });
    </script>
</x-app-layout>
