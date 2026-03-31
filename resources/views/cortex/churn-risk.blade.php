<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold leading-tight flex items-center gap-3">
                    <span class="p-2 bg-amber-100 dark:bg-amber-900/40 text-amber-600 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="22" y1="11" x2="16" y2="11"></line></svg>
                    </span>
                    Cortex&trade; Churn Prediction
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Identify at-risk customers before they leave and take proactive retention actions</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('cortex.dashboard') }}" class="btn btn-secondary text-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    Back to Command Center
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @php
            $highChurn = collect($churnData)->where('churn_probability', '>=', 0.6)->count();
            $mediumChurn = collect($churnData)->where('churn_probability', '>=', 0.3)->where('churn_probability', '<', 0.6)->count();
            $lowChurn = collect($churnData)->where('churn_probability', '<', 0.3)->count();
        @endphp
        <div class="card p-5 ring-1 ring-red-500/10">
            <div class="flex items-center gap-3">
                <span class="p-2 bg-red-100 dark:bg-red-900/40 text-red-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </span>
                <div>
                    <p class="text-2xl font-bold text-red-600">{{ $highChurn }}</p>
                    <p class="text-xs text-bankos-text-sec">High risk (&ge;60%)</p>
                </div>
            </div>
        </div>
        <div class="card p-5 ring-1 ring-amber-500/10">
            <div class="flex items-center gap-3">
                <span class="p-2 bg-amber-100 dark:bg-amber-900/40 text-amber-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </span>
                <div>
                    <p class="text-2xl font-bold text-amber-600">{{ $mediumChurn }}</p>
                    <p class="text-xs text-bankos-text-sec">Medium risk (30-59%)</p>
                </div>
            </div>
        </div>
        <div class="card p-5 ring-1 ring-green-500/10">
            <div class="flex items-center gap-3">
                <span class="p-2 bg-green-100 dark:bg-green-900/40 text-green-600 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </span>
                <div>
                    <p class="text-2xl font-bold text-green-600">{{ $lowChurn }}</p>
                    <p class="text-xs text-bankos-text-sec">Low risk (&lt;30%)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Churn Table -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/50">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold">Customer Churn Analysis</h3>
                <span class="text-xs text-bankos-muted">{{ count($churnData) }} customers analyzed</span>
            </div>
        </div>

        @if(empty($churnData))
            <div class="p-12 text-center text-bankos-muted">
                <p class="text-sm">No customer data available for churn analysis.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-bankos-muted uppercase tracking-wider border-b border-bankos-border dark:border-bankos-dark-border">
                            <th class="text-left py-3 px-4">Customer</th>
                            <th class="text-left py-3 px-4" style="min-width: 180px;">Churn Probability</th>
                            <th class="text-left py-3 px-4">Risk Factors</th>
                            <th class="text-left py-3 px-4">Activity Trend</th>
                            <th class="text-left py-3 px-4">Suggested Action</th>
                            <th class="text-right py-3 px-4">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($churnData as $item)
                        <tr class="border-t border-bankos-border/50 dark:border-bankos-dark-border/50 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50">
                            <td class="py-3 px-4">
                                <a href="{{ route('customers.show', $item['customer_id']) }}" class="font-medium text-indigo-600 hover:underline">{{ $item['customer_name'] }}</a>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                        <div class="h-2.5 rounded-full transition-all duration-500
                                            {{ $item['churn_probability'] >= 0.6 ? 'bg-red-500' : ($item['churn_probability'] >= 0.3 ? 'bg-amber-500' : 'bg-green-500') }}"
                                            style="width: {{ $item['churn_probability'] * 100 }}%">
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold w-10 text-right {{ $item['churn_probability'] >= 0.6 ? 'text-red-600' : ($item['churn_probability'] >= 0.3 ? 'text-amber-600' : 'text-green-600') }}">
                                        {{ round($item['churn_probability'] * 100) }}%
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="space-y-1">
                                    @foreach(array_slice($item['risk_factors'] ?? [], 0, 2) as $factor)
                                    <p class="text-xs text-bankos-text-sec flex items-start gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1 flex-shrink-0"></span>
                                        {{ $factor }}
                                    </p>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @php $trend = $item['activity_trend'] ?? []; @endphp
                                <div class="text-xs">
                                    <span class="text-bankos-muted">90d:</span>
                                    <span class="font-medium">{{ $trend['recent_90d'] ?? 0 }} txns</span>
                                    @if(($trend['change_pct'] ?? 0) != 0)
                                        <span class="{{ ($trend['change_pct'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            ({{ ($trend['change_pct'] ?? 0) > 0 ? '+' : '' }}{{ $trend['change_pct'] ?? 0 }}%)
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if(!empty($item['retention_actions']))
                                    <p class="text-xs text-bankos-text-sec">{{ $item['retention_actions'][0] }}</p>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('cortex.customer', $item['customer_id']) }}" class="inline-flex items-center gap-1 text-indigo-600 hover:underline text-xs font-medium">
                                    Deep Dive
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
