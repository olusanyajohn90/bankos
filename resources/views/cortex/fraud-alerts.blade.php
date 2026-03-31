<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold leading-tight flex items-center gap-3">
                    <span class="p-2 bg-red-100 dark:bg-red-900/40 text-red-600 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    </span>
                    Cortex&trade; Fraud Detection
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">AI-powered transaction monitoring and anomaly detection</p>
            </div>
            <a href="{{ route('cortex.dashboard') }}" class="btn btn-secondary text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                Back to Command Center
            </a>
        </div>
    </x-slot>

    <!-- Filter Bar -->
    <div class="card p-4 mb-6 flex items-center gap-4 flex-wrap">
        <span class="text-sm font-medium text-bankos-text-sec">Filter by risk:</span>
        <a href="{{ route('cortex.fraud-alerts') }}" class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ $filterLevel === 'all' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 hover:bg-gray-200' }}">All</a>
        <a href="{{ route('cortex.fraud-alerts', ['level' => 'high']) }}" class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ $filterLevel === 'high' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 hover:bg-gray-200' }}">High</a>
        <a href="{{ route('cortex.fraud-alerts', ['level' => 'medium']) }}" class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ $filterLevel === 'medium' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 hover:bg-gray-200' }}">Medium</a>
        <span class="ml-auto text-xs text-bankos-muted">{{ count($alerts) }} alert(s) found</span>
    </div>

    @if(empty($alerts))
        <div class="card p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto mb-4 text-green-400"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <h3 class="text-lg font-semibold mb-2">All Clear</h3>
            <p class="text-sm text-bankos-text-sec max-w-md mx-auto">No suspicious activity detected across monitored accounts. Cortex continuously analyzes transaction patterns for anomalies.</p>
        </div>
    @else
        <div class="card overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                    <tr class="text-xs text-bankos-muted uppercase tracking-wider">
                        <th class="text-left py-3 px-4">Customer</th>
                        <th class="text-left py-3 px-4">Risk Level</th>
                        <th class="text-left py-3 px-4">Alert Details</th>
                        <th class="text-center py-3 px-4">Txns Analyzed</th>
                        <th class="text-center py-3 px-4">Suspicious</th>
                        <th class="text-right py-3 px-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alerts as $alert)
                    @if($filterLevel === 'all' || $filterLevel === $alert['risk_level'])
                    <tr class="border-t border-bankos-border/50 dark:border-bankos-dark-border/50 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="py-3 px-4">
                            <a href="{{ route('customers.show', $alert['customer_id']) }}" class="font-medium text-indigo-600 hover:underline">{{ $alert['customer_name'] }}</a>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ $alert['risk_level'] === 'high' || $alert['risk_level'] === 'critical' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' : '' }}
                                {{ $alert['risk_level'] === 'medium' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' : '' }}
                                {{ $alert['risk_level'] === 'low' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' : '' }}
                            ">
                                {{ ucfirst($alert['risk_level']) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="space-y-1">
                                @foreach(array_slice($alert['alerts'] ?? [], 0, 3) as $detail)
                                <p class="text-xs text-bankos-text-sec flex items-start gap-1.5">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full mt-1 flex-shrink-0
                                        {{ ($detail['severity'] ?? 'low') === 'high' ? 'bg-red-500' : (($detail['severity'] ?? 'low') === 'medium' ? 'bg-amber-500' : 'bg-blue-500') }}
                                    "></span>
                                    {{ $detail['description'] ?? 'Unknown alert' }}
                                </p>
                                @endforeach
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center text-bankos-muted">{{ $alert['transactions_analyzed'] ?? 0 }}</td>
                        <td class="py-3 px-4 text-center">
                            @if(count($alert['suspicious_transactions'] ?? []) > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">
                                    {{ count($alert['suspicious_transactions']) }}
                                </span>
                            @else
                                <span class="text-bankos-muted">0</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('cortex.customer', $alert['customer_id']) }}" class="inline-flex items-center gap-1 text-indigo-600 hover:underline text-xs font-medium">
                                Investigate
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </a>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-app-layout>
