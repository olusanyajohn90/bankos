<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Treasury Dashboard</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Money market placements, FX deals and maturity management</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('treasury.placements.create') }}" class="btn btn-primary text-sm">New Placement</a>
                <a href="{{ route('treasury.fx-deals.create') }}" class="btn btn-outline text-sm">Book FX Deal</a>
            </div>
        </div>
    </x-slot>

    @if(isset($error))
        <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/30 p-4 border border-red-200 dark:border-red-800">
            <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-blue-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Active Placements</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">{{ number_format($activePlacements) }}</h3>
            <p class="text-xs text-bankos-muted mt-1">{{ number_format($totalPlacements) }} total</p>
        </div>
        <div class="card p-5 border-l-4 border-l-green-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Placement Value</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">
                @if($totalPlacementValue >= 1_000_000_000) ₦{{ number_format($totalPlacementValue / 1_000_000_000, 2) }}B
                @elseif($totalPlacementValue >= 1_000_000) ₦{{ number_format($totalPlacementValue / 1_000_000, 2) }}M
                @else ₦{{ number_format($totalPlacementValue, 0) }} @endif
            </h3>
        </div>
        <div class="card p-5 border-l-4 border-l-indigo-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Expected Interest</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">
                @if($totalExpectedInterest >= 1_000_000) ₦{{ number_format($totalExpectedInterest / 1_000_000, 2) }}M
                @else ₦{{ number_format($totalExpectedInterest, 0) }} @endif
            </h3>
            <p class="text-xs text-bankos-muted mt-1">Avg rate: {{ number_format($avgInterestRate, 2) }}%</p>
        </div>
        <div class="card p-5 border-l-4 border-l-amber-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">FX Deals</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">{{ number_format($totalFxDeals) }}</h3>
            <p class="text-xs text-bankos-muted mt-1">{{ $pendingFxDeals }} pending</p>
        </div>
        <div class="card p-5 border-l-4 border-l-purple-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">FX Volume</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">
                @if($totalFxVolume >= 1_000_000_000) ₦{{ number_format($totalFxVolume / 1_000_000_000, 2) }}B
                @elseif($totalFxVolume >= 1_000_000) ₦{{ number_format($totalFxVolume / 1_000_000, 2) }}M
                @else ₦{{ number_format($totalFxVolume, 0) }} @endif
            </h3>
        </div>
    </div>

    {{-- Secondary KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Accrued Interest</p>
            <h4 class="text-xl font-bold mt-1 text-green-600">₦{{ number_format($totalAccruedInterest, 0) }}</h4>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Maturing (7 days)</p>
            <h4 class="text-xl font-bold mt-1 {{ $maturingSoon > 0 ? 'text-amber-600' : 'text-gray-600' }}">{{ $maturingSoon }}</h4>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Today FX Volume</p>
            <h4 class="text-xl font-bold mt-1 text-indigo-600">₦{{ number_format($todayFxVolume, 0) }}</h4>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Pending FX</p>
            <h4 class="text-xl font-bold mt-1 text-amber-600">{{ $pendingFxDeals }}</h4>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">Maturity Profile (30 Days)</h3>
            <canvas id="maturityChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">FX by Currency Pair</h3>
            <canvas id="fxCurrencyChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">Placements by Type</h3>
            <canvas id="placementTypeChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">FX Deal Types</h3>
            <canvas id="fxTypeChart" height="250"></canvas>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Maturity profile
        new Chart(document.getElementById('maturityChart'), {
            type: 'bar',
            data: {
                labels: @json($maturityProfile->pluck('date')),
                datasets: [{
                    label: 'Maturing Principal',
                    data: @json($maturityProfile->pluck('total')),
                    backgroundColor: 'rgba(59,130,246,0.7)',
                    borderRadius: 6,
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // FX by currency
        new Chart(document.getElementById('fxCurrencyChart'), {
            type: 'doughnut',
            data: {
                labels: @json($fxByCurrency->pluck('currency_pair')),
                datasets: [{
                    data: @json($fxByCurrency->pluck('volume')),
                    backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f97316','#6366f1'],
                }]
            },
            options: { responsive: true }
        });

        // Placements by type
        new Chart(document.getElementById('placementTypeChart'), {
            type: 'pie',
            data: {
                labels: @json($placementsByType->pluck('type')),
                datasets: [{
                    data: @json($placementsByType->pluck('total')),
                    backgroundColor: ['#3b82f6','#10b981'],
                }]
            },
            options: { responsive: true }
        });

        // FX deal types
        new Chart(document.getElementById('fxTypeChart'), {
            type: 'bar',
            data: {
                labels: @json($fxByType->pluck('deal_type')),
                datasets: [{
                    label: 'Count',
                    data: @json($fxByType->pluck('count')),
                    backgroundColor: ['#8b5cf6','#f59e0b','#ef4444'],
                    borderRadius: 6,
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    });
    </script>
</x-app-layout>
