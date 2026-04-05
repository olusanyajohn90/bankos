<x-app-layout>
    <x-slot name="header">Behavioral Analytics</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        {{-- Anomaly Alerts --}}
        @if(isset($anomalyAlerts) && $anomalyAlerts->count() > 0)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <h3 class="text-sm font-semibold text-red-800 mb-2">Anomaly Alerts ({{ $anomalyAlerts->count() }})</h3>
            <div class="space-y-2">
                @foreach($anomalyAlerts->take(5) as $alert)
                <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-red-100">
                    <div>
                        <span class="font-medium text-sm text-red-800">{{ $alert->customer->first_name ?? '' }} {{ $alert->customer->last_name ?? '' }}</span>
                        <span class="text-xs text-red-600 ml-2">{{ $alert->anomaly_count_30d }} anomalies in 30 days</span>
                    </div>
                    <span class="text-xs font-mono font-bold text-red-700">Risk: {{ $alert->behavior_risk_score }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Behavior Profiles --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="px-4 py-3 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Customer Behavior Profiles</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Customer</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Avg Monthly Vol</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Avg Txn Size</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Anomalies (30d)</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Behavior Risk</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Last Computed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($profiles ?? collect() as $p)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                            <td class="px-4 py-3">
                                <span class="font-medium">{{ $p->customer->first_name ?? '' }} {{ $p->customer->last_name ?? '' }}</span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">NGN {{ number_format($p->transaction_patterns['avg_monthly_volume'] ?? 0, 2) }}</td>
                            <td class="px-4 py-3 font-mono text-xs">NGN {{ number_format($p->transaction_patterns['avg_txn_size'] ?? 0, 2) }}</td>
                            <td class="px-4 py-3">
                                @if($p->anomaly_count_30d > 0)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">{{ $p->anomaly_count_30d }}</span>
                                @else
                                <span class="text-xs text-bankos-muted">0</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $p->behavior_risk_score >= 50 ? 'bg-red-500' : ($p->behavior_risk_score >= 25 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ min(100, $p->behavior_risk_score) }}%"></div>
                                    </div>
                                    <span class="text-xs font-mono">{{ $p->behavior_risk_score }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-bankos-muted">{{ $p->profile_computed_at?->diffForHumans() ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-bankos-muted">No behavior profiles found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
