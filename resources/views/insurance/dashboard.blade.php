<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Insurance Dashboard</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Policy overview, coverage, and claims tracking</p>
            </div>
            <a href="{{ route('insurance.index') }}" class="btn btn-primary text-sm">View All Policies</a>
        </div>
    </x-slot>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-violet-500">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-violet-50 dark:bg-violet-900/20 text-violet-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Policies</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ number_format($totalPolicies) }}</h3>
            <p class="text-xs text-bankos-muted mt-1">{{ $activePolicies }} active</p>
        </div>

        <div class="card p-5 border-l-4 border-l-green-500">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Premiums</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">
                @if($totalPremiums >= 1_000_000) ₦{{ number_format($totalPremiums / 1_000_000, 2) }}M
                @else ₦{{ number_format($totalPremiums, 0) }} @endif
            </h3>
            <p class="text-xs text-bankos-muted mt-1">Active policies</p>
        </div>

        <div class="card p-5 border-l-4 border-l-blue-500">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Coverage</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">
                @if($totalCoverage >= 1_000_000_000) ₦{{ number_format($totalCoverage / 1_000_000_000, 2) }}B
                @elseif($totalCoverage >= 1_000_000) ₦{{ number_format($totalCoverage / 1_000_000, 2) }}M
                @else ₦{{ number_format($totalCoverage, 0) }} @endif
            </h3>
            <p class="text-xs text-bankos-muted mt-1">Sum assured</p>
        </div>

        <div class="card p-5 border-l-4 border-l-amber-500">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Expiring Soon</p>
            <h3 class="text-2xl font-bold mt-1 {{ $expiringSoon > 0 ? 'text-amber-600' : 'text-bankos-text dark:text-white' }}">{{ number_format($expiringSoon) }}</h3>
            <p class="text-xs text-bankos-muted mt-1">Next 30 days</p>
        </div>
    </div>

    {{-- Secondary stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Active</p>
            <h4 class="text-xl font-bold mt-1 text-green-600">{{ number_format($activePolicies) }}</h4>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Claims</p>
            <h4 class="text-xl font-bold mt-1 text-blue-600">{{ number_format($claimsCount) }}</h4>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Lapsed</p>
            <h4 class="text-xl font-bold mt-1 text-red-600">{{ number_format($lapsedCount) }}</h4>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Expiring</p>
            <h4 class="text-xl font-bold mt-1 text-amber-600">{{ number_format($expiringSoon) }}</h4>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-1 text-bankos-text dark:text-white">Policies by Type</h3>
            <p class="text-xs text-bankos-muted mb-4">Product distribution</p>
            <div class="relative h-72 w-full">
                <canvas id="policyTypeChart"></canvas>
            </div>
        </div>

        <div class="card p-6">
            <h3 class="font-bold text-lg mb-1 text-bankos-text dark:text-white">Policies by Provider</h3>
            <p class="text-xs text-bankos-muted mb-4">Insurer distribution</p>
            <div class="relative h-72 w-full">
                <canvas id="providerChart"></canvas>
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

        const pieColors = ['#7C3AED', '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#06B6D4', '#EC4899'];

        // By Type
        const typeData = @json($byType);
        const typeLabels = Object.keys(typeData).map(s => s.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()));
        new Chart(document.getElementById('policyTypeChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: typeLabels.length ? typeLabels : ['No data'],
                datasets: [{ data: Object.values(typeData).length ? Object.values(typeData) : [1], backgroundColor: pieColors, borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'right' } } }
        });

        // By Provider
        const provData = @json($byProvider);
        new Chart(document.getElementById('providerChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: Object.keys(provData),
                datasets: [{ label: 'Policies', data: Object.values(provData), backgroundColor: '#7C3AED', borderRadius: 4 }]
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
