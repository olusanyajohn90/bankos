<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Cash Management Dashboard</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Daily cash positions, vault cash and nostro balances</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('cash-management.create') }}" class="btn btn-primary text-sm">Record Position</a>
                <a href="{{ route('cash-management.forecast') }}" class="btn btn-outline text-sm">Forecast</a>
            </div>
        </div>
    </x-slot>

    @if(isset($error))
        <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/30 p-4 border border-red-200 dark:border-red-800">
            <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
        </div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-blue-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Current Balance</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">
                @if($latestBalance >= 1_000_000_000) ₦{{ number_format($latestBalance / 1_000_000_000, 2) }}B
                @elseif($latestBalance >= 1_000_000) ₦{{ number_format($latestBalance / 1_000_000, 2) }}M
                @else ₦{{ number_format($latestBalance, 0) }} @endif
            </h3>
        </div>
        <div class="card p-5 border-l-4 border-l-green-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Today Inflows</p>
            <h3 class="text-2xl font-bold mt-2 text-green-600">₦{{ number_format($todayInflows, 0) }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-red-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Today Outflows</p>
            <h3 class="text-2xl font-bold mt-2 text-red-600">₦{{ number_format($todayOutflows, 0) }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-indigo-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Vault Cash</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">₦{{ number_format($latestVaultCash, 0) }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-purple-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Nostro Balance</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">₦{{ number_format($latestNostro, 0) }}</h3>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Net Position</p>
            <h4 class="text-xl font-bold mt-1 {{ $netPosition >= 0 ? 'text-green-600' : 'text-red-600' }}">₦{{ number_format($netPosition, 0) }}</h4>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Avg Monthly Balance</p>
            <h4 class="text-xl font-bold mt-1 text-indigo-600">₦{{ number_format($avgMonthlyBalance, 0) }}</h4>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Month High</p>
            <h4 class="text-xl font-bold mt-1 text-green-600">₦{{ number_format($maxBalance, 0) }}</h4>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Month Low</p>
            <h4 class="text-xl font-bold mt-1 text-red-600">₦{{ number_format($minBalance, 0) }}</h4>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">30-Day Balance Trend</h3>
            <canvas id="trendChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">Weekly Inflows vs Outflows</h3>
            <canvas id="flowChart" height="250"></canvas>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: @json($dailyTrend->pluck('date')),
                datasets: [{
                    label: 'Closing Balance',
                    data: @json($dailyTrend->pluck('closing_balance')),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    tension: 0.3,
                    fill: true,
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: false } } }
        });

        new Chart(document.getElementById('flowChart'), {
            type: 'bar',
            data: {
                labels: @json($weeklyFlow->pluck('day')),
                datasets: [
                    { label: 'Inflows', data: @json($weeklyFlow->pluck('total_inflows')), backgroundColor: 'rgba(16,185,129,0.7)', borderRadius: 4 },
                    { label: 'Outflows', data: @json($weeklyFlow->pluck('total_outflows')), backgroundColor: 'rgba(239,68,68,0.7)', borderRadius: 4 },
                ]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    });
    </script>
</x-app-layout>
