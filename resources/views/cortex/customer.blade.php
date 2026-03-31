<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold leading-tight flex items-center gap-3">
                    <span class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-xl shadow-lg shadow-indigo-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                    </span>
                    Cortex&trade; Customer Deep Dive
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">
                    Full AI analysis for <strong>{{ $customer->first_name }} {{ $customer->last_name }}</strong>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary text-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    Customer Profile
                </a>
                <a href="{{ route('cortex.dashboard') }}" class="btn btn-secondary text-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    Command Center
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Top Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- CLV Card -->
        <div class="card p-5 ring-1 ring-indigo-500/10">
            <div class="flex items-center gap-2 mb-3">
                <span class="p-1.5 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </span>
                <span class="text-xs font-medium text-bankos-muted uppercase tracking-wider">Customer Value</span>
            </div>
            <p class="text-2xl font-bold">&#8358;{{ number_format($clv['current_value'], 2) }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">Projected 12m: &#8358;{{ number_format($clv['projected_value_12m'], 2) }}</p>
            <div class="mt-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ $clv['segment'] === 'high' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' : ($clv['segment'] === 'medium' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400') }}">
                    {{ ucfirst($clv['segment']) }} Value Segment
                </span>
            </div>
        </div>

        <!-- Churn Risk Gauge -->
        <div class="card p-5 ring-1 {{ $churn['churn_probability'] >= 0.6 ? 'ring-red-500/20' : ($churn['churn_probability'] >= 0.3 ? 'ring-amber-500/20' : 'ring-green-500/10') }}">
            <div class="flex items-center gap-2 mb-3">
                <span class="p-1.5 {{ $churn['churn_probability'] >= 0.6 ? 'bg-red-100 dark:bg-red-900/40 text-red-600' : ($churn['churn_probability'] >= 0.3 ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-600' : 'bg-green-100 dark:bg-green-900/40 text-green-600') }} rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="22" y1="11" x2="16" y2="11"></line></svg>
                </span>
                <span class="text-xs font-medium text-bankos-muted uppercase tracking-wider">Churn Risk</span>
            </div>
            <p class="text-2xl font-bold {{ $churn['churn_probability'] >= 0.6 ? 'text-red-600' : ($churn['churn_probability'] >= 0.3 ? 'text-amber-600' : 'text-green-600') }}">
                {{ round($churn['churn_probability'] * 100) }}%
            </p>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-2">
                <div class="h-2 rounded-full {{ $churn['churn_probability'] >= 0.6 ? 'bg-red-500' : ($churn['churn_probability'] >= 0.3 ? 'bg-amber-500' : 'bg-green-500') }}"
                    style="width: {{ $churn['churn_probability'] * 100 }}%"></div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ ucfirst($churn['risk_level']) }} risk level</p>
        </div>

        <!-- Fraud Risk -->
        <div class="card p-5 ring-1 {{ $fraud['risk_level'] === 'high' ? 'ring-red-500/20' : ($fraud['risk_level'] === 'medium' ? 'ring-amber-500/20' : 'ring-green-500/10') }}">
            <div class="flex items-center gap-2 mb-3">
                <span class="p-1.5 {{ $fraud['risk_level'] === 'high' ? 'bg-red-100 dark:bg-red-900/40 text-red-600' : ($fraud['risk_level'] === 'medium' ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-600' : 'bg-green-100 dark:bg-green-900/40 text-green-600') }} rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </span>
                <span class="text-xs font-medium text-bankos-muted uppercase tracking-wider">Fraud Risk</span>
            </div>
            <p class="text-2xl font-bold {{ $fraud['risk_level'] === 'high' ? 'text-red-600' : ($fraud['risk_level'] === 'medium' ? 'text-amber-600' : 'text-green-600') }}">
                {{ ucfirst($fraud['risk_level']) }}
            </p>
            <p class="text-xs text-bankos-text-sec mt-1">{{ count($fraud['alerts']) }} alert(s), {{ count($fraud['suspicious_transactions']) }} suspicious txn(s)</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $fraud['transactions_analyzed'] }} transactions analyzed</p>
        </div>

        <!-- Activity -->
        <div class="card p-5 ring-1 ring-blue-500/10">
            <div class="flex items-center gap-2 mb-3">
                <span class="p-1.5 bg-blue-100 dark:bg-blue-900/40 text-blue-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                </span>
                <span class="text-xs font-medium text-bankos-muted uppercase tracking-wider">Activity</span>
            </div>
            @php $trend = $churn['activity_trend'] ?? []; @endphp
            <p class="text-2xl font-bold">{{ $trend['recent_90d'] ?? 0 }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">Transactions (90 days)</p>
            @if(($trend['change_pct'] ?? 0) != 0)
            <p class="text-xs mt-2 {{ ($trend['change_pct'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ ($trend['change_pct'] ?? 0) > 0 ? '+' : '' }}{{ $trend['change_pct'] ?? 0 }}% vs prior 90d
            </p>
            @endif
        </div>
    </div>

    <!-- Transaction Pattern Chart -->
    <div class="card p-6 mb-8">
        <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-indigo-500"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            Transaction Pattern (6 Months)
        </h3>
        <div style="height: 280px;">
            <canvas id="txnPatternChart"></canvas>
        </div>
    </div>

    <!-- AI Review & Recommendations Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Full AI Review -->
        <div class="lg:col-span-2 card p-6 ring-1 ring-indigo-500/10">
            <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                <span class="p-1.5 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                </span>
                Cortex AI Review
            </h3>
            <div class="prose dark:prose-invert prose-indigo prose-sm max-w-none prose-headings:font-bold prose-h3:text-indigo-700 dark:prose-h3:text-indigo-400 prose-ul:mt-0">
                {!! \Illuminate\Support\Str::markdown($review) !!}
            </div>
        </div>

        <!-- Product Recommendations -->
        <div class="card p-6">
            <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                <span class="p-1.5 bg-purple-100 dark:bg-purple-900/40 text-purple-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                </span>
                Product Recommendations
            </h3>
            <div class="space-y-4">
                @foreach($recommendations as $rec)
                <div class="p-4 rounded-xl bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/10 dark:to-indigo-900/10 border border-purple-200/50 dark:border-purple-800/30">
                    <div class="flex items-start justify-between mb-2">
                        <h4 class="font-semibold text-sm text-purple-900 dark:text-purple-300">{{ $rec['product'] }}</h4>
                        <span class="text-xs font-bold {{ $rec['confidence'] >= 0.8 ? 'text-green-600' : ($rec['confidence'] >= 0.6 ? 'text-blue-600' : 'text-gray-500') }}">
                            {{ round($rec['confidence'] * 100) }}% match
                        </span>
                    </div>
                    <p class="text-xs text-bankos-text-sec mb-2">{{ $rec['reason'] }}</p>
                    <p class="text-xs font-medium text-indigo-600">Est. value: &#8358;{{ number_format($rec['estimated_value'], 2) }}</p>
                </div>
                @endforeach
            </div>

            <!-- CLV Drivers -->
            <div class="mt-6 pt-6 border-t border-bankos-border dark:border-bankos-dark-border">
                <h4 class="font-semibold text-sm mb-3">Value Drivers</h4>
                <ul class="space-y-2">
                    @foreach($clv['drivers'] as $driver)
                    <li class="text-xs text-bankos-text-sec flex items-start gap-2">
                        <span class="text-indigo-500 mt-0.5">&bull;</span>
                        {{ $driver }}
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Retention Actions -->
            @if(!empty($churn['retention_actions']))
            <div class="mt-6 pt-6 border-t border-bankos-border dark:border-bankos-dark-border">
                <h4 class="font-semibold text-sm mb-3">Retention Actions</h4>
                <ul class="space-y-2">
                    @foreach($churn['retention_actions'] as $action)
                    <li class="text-xs text-bankos-text-sec flex items-start gap-2">
                        <span class="text-amber-500 mt-0.5">&bull;</span>
                        {{ $action }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>

    <!-- Fraud Details (if any) -->
    @if(count($fraud['alerts']) > 0 || count($fraud['suspicious_transactions']) > 0)
    <div class="card p-6 mb-8 ring-1 ring-red-500/10">
        <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
            <span class="p-1.5 bg-red-100 dark:bg-red-900/40 text-red-600 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            </span>
            Fraud Alert Details
        </h3>

        @if(count($fraud['alerts']) > 0)
        <div class="mb-4">
            <h4 class="text-sm font-medium mb-2">Alerts</h4>
            <div class="space-y-2">
                @foreach($fraud['alerts'] as $alert)
                <div class="flex items-start gap-3 p-3 rounded-lg bg-red-50/50 dark:bg-red-900/10 border border-red-200/50 dark:border-red-800/30">
                    <span class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0
                        {{ $alert['severity'] === 'high' ? 'bg-red-500' : ($alert['severity'] === 'medium' ? 'bg-amber-500' : 'bg-blue-500') }}"></span>
                    <div>
                        <p class="text-sm font-medium">{{ ucfirst(str_replace('_', ' ', $alert['type'])) }}</p>
                        <p class="text-xs text-bankos-text-sec">{{ $alert['description'] }}</p>
                    </div>
                    <span class="ml-auto text-xs font-semibold px-2 py-0.5 rounded-full
                        {{ $alert['severity'] === 'high' ? 'bg-red-100 text-red-700' : ($alert['severity'] === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                        {{ ucfirst($alert['severity']) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(count($fraud['suspicious_transactions']) > 0)
        <div>
            <h4 class="text-sm font-medium mb-2">Suspicious Transactions</h4>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-bankos-muted uppercase border-b border-bankos-border dark:border-bankos-dark-border">
                        <th class="text-left py-2 px-3">ID</th>
                        <th class="text-right py-2 px-3">Amount</th>
                        <th class="text-left py-2 px-3">Date</th>
                        <th class="text-left py-2 px-3">Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fraud['suspicious_transactions'] as $txn)
                    <tr class="border-t border-bankos-border/50 dark:border-bankos-dark-border/50">
                        <td class="py-2 px-3 font-mono text-xs">#{{ $txn['id'] }}</td>
                        <td class="py-2 px-3 text-right font-semibold">&#8358;{{ number_format($txn['amount'], 2) }}</td>
                        <td class="py-2 px-3 text-bankos-text-sec">{{ $txn['date'] }}</td>
                        <td class="py-2 px-3 text-xs text-bankos-text-sec">{{ $txn['reason'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('txnPatternChart')?.getContext('2d');
        if (!ctx) return;

        const data = @json($transactionPattern);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.month),
                datasets: [
                    {
                        label: 'Inflow',
                        data: data.map(d => d.inflow),
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderRadius: 4,
                        barPercentage: 0.6,
                    },
                    {
                        label: 'Outflow',
                        data: data.map(d => d.outflow),
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderRadius: 4,
                        barPercentage: 0.6,
                    },
                    {
                        label: 'Transaction Count',
                        data: data.map(d => d.count),
                        type: 'line',
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1',
                        pointRadius: 4,
                        pointBackgroundColor: '#6366f1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Amount (\u20A6)' },
                        ticks: {
                            callback: function(value) {
                                return '\u20A6' + value.toLocaleString();
                            }
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        title: { display: true, text: 'Count' },
                        grid: { drawOnChartArea: false },
                    }
                },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.label === 'Transaction Count') {
                                    return context.dataset.label + ': ' + context.raw;
                                }
                                return context.dataset.label + ': \u20A6' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });
    </script>
</x-app-layout>
