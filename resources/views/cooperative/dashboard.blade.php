<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Cooperative Dashboard</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Members, shares, contributions, and dividends overview</p>
            </div>
            <a href="{{ route('cooperative.shares.index') }}" class="btn btn-primary text-sm">View Shares</a>
        </div>
    </x-slot>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-teal-500">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-teal-50 dark:bg-teal-900/20 text-teal-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Members</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ number_format($totalMembers) }}</h3>
            <p class="text-xs text-bankos-muted mt-1">Active shareholders</p>
        </div>

        <div class="card p-5 border-l-4 border-l-blue-500">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Shares Value</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">
                @if($totalSharesValue >= 1_000_000) ₦{{ number_format($totalSharesValue / 1_000_000, 2) }}M
                @else ₦{{ number_format($totalSharesValue, 0) }} @endif
            </h3>
        </div>

        <div class="card p-5 border-l-4 border-l-green-500">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                </div>
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Dividends Paid</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">
                @if($dividendDistributed >= 1_000_000) ₦{{ number_format($dividendDistributed / 1_000_000, 2) }}M
                @else ₦{{ number_format($dividendDistributed, 0) }} @endif
            </h3>
        </div>

        <div class="card p-5 border-l-4 border-l-amber-500">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Compliance Rate</p>
            <h3 class="text-2xl font-bold mt-1 {{ $complianceRate >= 80 ? 'text-green-600' : 'text-amber-600' }}">{{ $complianceRate }}%</h3>
            <p class="text-xs text-bankos-muted mt-1">This month contributions</p>
        </div>
    </div>

    {{-- Secondary --}}
    <div class="grid grid-cols-2 lg:grid-cols-2 gap-4 mb-8">
        <a href="{{ route('cooperative.exits.index') }}" class="card p-4 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-500 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </div>
            <div>
                <p class="text-xs text-bankos-muted uppercase font-semibold">Pending Exits</p>
                <span class="text-xl font-bold {{ $pendingExits > 0 ? 'text-red-600' : 'text-bankos-text dark:text-white' }}">{{ number_format($pendingExits) }}</span>
            </div>
        </a>
        <a href="{{ route('cooperative.dividends.index') }}" class="card p-4 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-500 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div>
                <p class="text-xs text-bankos-muted uppercase font-semibold">Recent Dividends</p>
                <span class="text-xl font-bold text-bankos-text dark:text-white">{{ $recentDividends->count() }}</span>
            </div>
        </a>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-1 text-bankos-text dark:text-white">Share Distribution by Product</h3>
            <p class="text-xs text-bankos-muted mb-4">Value breakdown</p>
            <div class="relative h-72 w-full">
                <canvas id="shareDistChart"></canvas>
            </div>
        </div>

        <div class="card p-6">
            <h3 class="font-bold text-lg mb-1 text-bankos-text dark:text-white">Monthly Contributions Trend</h3>
            <p class="text-xs text-bankos-muted mb-4">Last 6 months (in millions)</p>
            <div class="relative h-72 w-full">
                <canvas id="contribTrendChart"></canvas>
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

        const pieColors = ['#0D9488', '#3B82F6', '#F59E0B', '#EF4444', '#7C3AED', '#EC4899'];

        // Share Distribution
        const shareData = @json($sharesByProduct);
        new Chart(document.getElementById('shareDistChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(shareData).length ? Object.keys(shareData) : ['No data'],
                datasets: [{ data: Object.values(shareData).length ? Object.values(shareData) : [1], backgroundColor: pieColors, borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'right' } } }
        });

        // Contributions Trend
        const contribData = @json($contribTrend);
        new Chart(document.getElementById('contribTrendChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: contribData.labels,
                datasets: [{
                    label: 'Contributions (₦M)',
                    data: contribData.data,
                    borderColor: '#0D9488',
                    backgroundColor: 'rgba(13,148,136,0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#0D9488',
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4], color: gridColor } }, x: { grid: { display: false } } }
            }
        });
    });
    </script>
</x-app-layout>
