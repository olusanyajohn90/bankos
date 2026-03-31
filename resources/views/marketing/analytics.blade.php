<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Marketing Analytics</h1>
            <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">Campaign performance metrics and trends</p>
        </div>
    </x-slot>

    {{-- Rate Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <p class="text-xs text-bankos-muted mb-1">Total Sent</p>
            <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ number_format($totalSent) }}</p>
        </div>
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <p class="text-xs text-bankos-muted mb-1">Delivery Rate</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $deliveryRate }}%</p>
            <p class="text-xs text-bankos-muted">{{ number_format($totalDelivered) }} delivered</p>
        </div>
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <p class="text-xs text-bankos-muted mb-1">Open Rate</p>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $openRate }}%</p>
            <p class="text-xs text-bankos-muted">{{ number_format($totalOpened) }} opened</p>
        </div>
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <p class="text-xs text-bankos-muted mb-1">Conversion Rate</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $conversionRate }}%</p>
            <p class="text-xs text-bankos-muted">{{ number_format($totalConverted) }} converted</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Channel Breakdown --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
            <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Channel Breakdown</h3>
            <div class="relative" style="height: 280px;">
                <canvas id="channelChart"></canvas>
            </div>
        </div>

        {{-- Campaign Performance Over Time --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
            <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Monthly Campaign Performance</h3>
            <div class="relative" style="height: 280px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Top Performing Campaigns --}}
    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Top Performing Campaigns</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-bankos-bg dark:bg-bankos-dark-bg">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Campaign</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Channel</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Sent</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Delivered</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Delivery %</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Opened</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Converted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($topCampaigns as $c)
                    <tr class="hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="px-6 py-3">
                            <a href="{{ route('marketing.campaigns.show', $c->id) }}" class="font-medium text-bankos-primary hover:underline">{{ $c->name }}</a>
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $c->channel === 'sms' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : ($c->channel === 'email' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400') }}">
                                {{ strtoupper($c->channel) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right">{{ number_format($c->sent_count) }}</td>
                        <td class="px-6 py-3 text-right">{{ number_format($c->delivered_count) }}</td>
                        <td class="px-6 py-3 text-right font-medium text-green-600">{{ $c->sent_count > 0 ? round(($c->delivered_count / $c->sent_count) * 100, 1) : 0 }}%</td>
                        <td class="px-6 py-3 text-right">{{ number_format($c->opened_count) }}</td>
                        <td class="px-6 py-3 text-right">{{ number_format($c->converted_count) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-bankos-muted">No campaigns with sent data yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Channel Pie Chart
        const channelData = @json($channelStats);
        if (channelData.length > 0) {
            const channelColors = { sms: '#3b82f6', email: '#8b5cf6', whatsapp: '#22c55e' };
            new Chart(document.getElementById('channelChart'), {
                type: 'doughnut',
                data: {
                    labels: channelData.map(d => d.channel.toUpperCase()),
                    datasets: [{
                        data: channelData.map(d => d.count),
                        backgroundColor: channelData.map(d => channelColors[d.channel] || '#6b7280'),
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
                    }
                }
            });
        }

        // Monthly Line Chart
        const monthlyData = @json($monthlyStats);
        if (monthlyData.length > 0) {
            new Chart(document.getElementById('monthlyChart'), {
                type: 'line',
                data: {
                    labels: monthlyData.map(d => d.month),
                    datasets: [
                        {
                            label: 'Sent',
                            data: monthlyData.map(d => d.sent),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59,130,246,0.1)',
                            fill: true,
                            tension: 0.3,
                        },
                        {
                            label: 'Delivered',
                            data: monthlyData.map(d => d.delivered),
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34,197,94,0.1)',
                            fill: true,
                            tension: 0.3,
                        },
                        {
                            label: 'Opened',
                            data: monthlyData.map(d => d.opened),
                            borderColor: '#8b5cf6',
                            backgroundColor: 'rgba(139,92,246,0.1)',
                            fill: true,
                            tension: 0.3,
                        },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
                    }
                }
            });
        }
    });
    </script>
</x-app-layout>
