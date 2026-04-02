<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Fixed Assets Dashboard</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Asset register, book values, and depreciation tracking</p>
            </div>
            <a href="{{ route('fixed-assets.index') }}" class="btn btn-primary text-sm">View All Assets</a>
        </div>
    </x-slot>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-blue-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Assets</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">{{ number_format($totalAssets) }}</h3>
            <p class="text-xs text-bankos-muted mt-1">{{ $activeAssets }} active</p>
        </div>

        <div class="card p-5 border-l-4 border-l-indigo-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Cost</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">
                @if($totalCost >= 1_000_000_000) ₦{{ number_format($totalCost / 1_000_000_000, 2) }}B
                @elseif($totalCost >= 1_000_000) ₦{{ number_format($totalCost / 1_000_000, 2) }}M
                @else ₦{{ number_format($totalCost, 0) }} @endif
            </h3>
        </div>

        <div class="card p-5 border-l-4 border-l-green-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Book Value</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">
                @if($totalBookValue >= 1_000_000_000) ₦{{ number_format($totalBookValue / 1_000_000_000, 2) }}B
                @elseif($totalBookValue >= 1_000_000) ₦{{ number_format($totalBookValue / 1_000_000, 2) }}M
                @else ₦{{ number_format($totalBookValue, 0) }} @endif
            </h3>
        </div>

        <div class="card p-5 border-l-4 border-l-amber-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Acc. Depreciation</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">
                @if($totalDepreciation >= 1_000_000) ₦{{ number_format($totalDepreciation / 1_000_000, 2) }}M
                @else ₦{{ number_format($totalDepreciation, 0) }} @endif
            </h3>
        </div>

        <div class="card p-5 border-l-4 border-l-red-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Fully Depreciated</p>
            <h3 class="text-2xl font-bold mt-2 {{ $fullyDepreciated > 0 ? 'text-red-600' : 'text-bankos-text dark:text-white' }}">{{ number_format($fullyDepreciated) }}</h3>
        </div>

        <div class="card p-5 border-l-4 border-l-purple-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Monthly Charge</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">
                @if($monthlyDeprCharge >= 1_000_000) ₦{{ number_format($monthlyDeprCharge / 1_000_000, 2) }}M
                @else ₦{{ number_format($monthlyDeprCharge, 0) }} @endif
            </h3>
        </div>
    </div>

    {{-- Secondary --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Active</p>
            <h4 class="text-xl font-bold mt-1 text-green-600">{{ number_format($activeAssets) }}</h4>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Disposed</p>
            <h4 class="text-xl font-bold mt-1 text-red-600">{{ number_format($disposedCount) }}</h4>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Depreciation %</p>
            <h4 class="text-xl font-bold mt-1 text-amber-600">
                {{ $totalCost > 0 ? number_format(($totalDepreciation / $totalCost) * 100, 1) : 0 }}%
            </h4>
        </div>
    </div>

    {{-- Chart: Assets by Category --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-1 text-bankos-text dark:text-white">Assets by Category (Count)</h3>
            <p class="text-xs text-bankos-muted mb-4">Active assets only</p>
            <div class="relative h-72 w-full">
                <canvas id="categoryCntChart"></canvas>
            </div>
        </div>

        <div class="card p-6">
            <h3 class="font-bold text-lg mb-1 text-bankos-text dark:text-white">Net Book Value by Category</h3>
            <p class="text-xs text-bankos-muted mb-4">In millions (₦M)</p>
            <div class="relative h-72 w-full">
                <canvas id="categoryNbvChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
        Chart.defaults.color = isDark ? '#94A3B8' : '#64748B';
        Chart.defaults.borderColor = gridColor;

        const catData = @json($byCategory);
        const catLabels = catData.map(c => c.name);
        const barColors = ['#3B82F6', '#7C3AED', '#10B981', '#F59E0B', '#EF4444', '#06B6D4', '#EC4899', '#8B5CF6'];

        // Count chart
        new Chart(document.getElementById('categoryCntChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: catLabels.length ? catLabels : ['No categories'],
                datasets: [{ label: 'Assets', data: catData.map(c => c.count), backgroundColor: barColors, borderRadius: 4 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4], color: gridColor } }, x: { grid: { display: false } } }
            }
        });

        // NBV chart
        new Chart(document.getElementById('categoryNbvChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: catLabels.length ? catLabels : ['No categories'],
                datasets: [{ label: 'NBV (₦M)', data: catData.map(c => (parseFloat(c.total_nbv) / 1000000).toFixed(2)), backgroundColor: barColors, borderRadius: 4 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4], color: gridColor } }, x: { grid: { display: false } } }
            }
        });
    });
    </script>
</x-app-layout>
