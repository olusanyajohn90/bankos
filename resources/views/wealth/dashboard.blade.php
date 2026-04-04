<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Wealth Management Dashboard</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Investment portfolios, AUM and performance tracking</p>
            </div>
            <a href="{{ route('wealth.portfolios.create') }}" class="btn btn-primary text-sm">New Portfolio</a>
        </div>
    </x-slot>

    @if(isset($error))
        <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/30 p-4 border border-red-200 dark:border-red-800"><p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p></div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-blue-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total AUM</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">
                @if($totalAum >= 1_000_000_000) ₦{{ number_format($totalAum / 1_000_000_000, 2) }}B
                @elseif($totalAum >= 1_000_000) ₦{{ number_format($totalAum / 1_000_000, 2) }}M
                @else ₦{{ number_format($totalAum, 0) }} @endif
            </h3>
        </div>
        <div class="card p-5 border-l-4 border-l-green-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Portfolios</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">{{ $activePortfolios }}</h3>
            <p class="text-xs text-bankos-muted mt-1">{{ $totalPortfolios }} total</p>
        </div>
        <div class="card p-5 border-l-4 border-l-indigo-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Cost</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">₦{{ number_format($totalCost / max($totalCost, 1) >= 1000000 ? $totalCost / 1000000 : $totalCost, $totalCost >= 1000000 ? 2 : 0) }}{{ $totalCost >= 1000000 ? 'M' : '' }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-{{ $totalPnl >= 0 ? 'green' : 'red' }}-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Unrealized P&L</p>
            <h3 class="text-2xl font-bold mt-2 {{ $totalPnl >= 0 ? 'text-green-600' : 'text-red-600' }}">₦{{ number_format($totalPnl, 0) }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-amber-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Holdings</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">{{ $totalHoldings }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-purple-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Maturing (30d)</p>
            <h3 class="text-2xl font-bold mt-2 {{ $maturingHoldings > 0 ? 'text-amber-600' : 'text-bankos-text dark:text-white' }}">{{ $maturingHoldings }}</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">Asset Allocation</h3>
            <canvas id="allocationChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">Risk Profile Distribution</h3>
            <canvas id="riskChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">AUM Trend</h3>
            <canvas id="aumChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">Top Performers</h3>
            @if($topPerformers->count())
            <div class="space-y-3">
                @foreach($topPerformers as $tp)
                <div class="flex justify-between items-center p-2 rounded-lg bg-gray-50 dark:bg-bankos-dark-bg">
                    <div>
                        <p class="text-sm font-medium">{{ $tp->portfolio_name }}</p>
                        <p class="text-xs text-bankos-muted">{{ $tp->customer->first_name ?? '' }} {{ $tp->customer->last_name ?? '' }}</p>
                    </div>
                    <span class="text-sm font-bold {{ $tp->unrealized_pnl >= 0 ? 'text-green-600' : 'text-red-600' }}">₦{{ number_format($tp->unrealized_pnl, 0) }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-bankos-muted text-sm">No data yet</p>
            @endif
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        new Chart(document.getElementById('allocationChart'), {
            type: 'doughnut',
            data: {
                labels: @json($assetAllocation->pluck('asset_type')->map(fn($t) => ucfirst(str_replace('_',' ',$t)))),
                datasets: [{ data: @json($assetAllocation->pluck('total_value')), backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'] }]
            },
            options: { responsive: true }
        });

        new Chart(document.getElementById('riskChart'), {
            type: 'pie',
            data: {
                labels: @json($byRiskProfile->pluck('risk_profile')->map(fn($r) => ucfirst($r))),
                datasets: [{ data: @json($byRiskProfile->pluck('count')), backgroundColor: ['#10b981','#f59e0b','#ef4444'] }]
            },
            options: { responsive: true }
        });

        new Chart(document.getElementById('aumChart'), {
            type: 'line',
            data: {
                labels: @json($monthlyTrend->pluck('month')),
                datasets: [{ label: 'AUM', data: @json($monthlyTrend->pluck('aum')), borderColor: '#3b82f6', tension: 0.3, fill: true, backgroundColor: 'rgba(59,130,246,0.1)' }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: false } } }
        });
    });
    </script>
</x-app-layout>
