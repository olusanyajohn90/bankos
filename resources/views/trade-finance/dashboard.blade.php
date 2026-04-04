<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Trade Finance Dashboard</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Letters of credit, guarantees and trade instruments</p>
            </div>
            <a href="{{ route('trade-finance.create') }}" class="btn btn-primary text-sm">New Instrument</a>
        </div>
    </x-slot>

    @if(isset($error))
        <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/30 p-4 border border-red-200 dark:border-red-800">
            <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
        </div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-blue-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Instruments</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">{{ number_format($totalInstruments) }}</h3>
            <p class="text-xs text-bankos-muted mt-1">{{ $activeInstruments }} active</p>
        </div>
        <div class="card p-5 border-l-4 border-l-green-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Exposure</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">
                @if($totalExposure >= 1_000_000_000) ₦{{ number_format($totalExposure / 1_000_000_000, 2) }}B
                @elseif($totalExposure >= 1_000_000) ₦{{ number_format($totalExposure / 1_000_000, 2) }}M
                @else ₦{{ number_format($totalExposure, 0) }} @endif
            </h3>
        </div>
        <div class="card p-5 border-l-4 border-l-indigo-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Commissions</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">₦{{ number_format($totalCommissions, 0) }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-amber-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Expiring (30d)</p>
            <h3 class="text-2xl font-bold mt-2 {{ $expiringSoon > 0 ? 'text-amber-600' : 'text-bankos-text dark:text-white' }}">{{ $expiringSoon }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-purple-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Drafts</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">{{ $draftCount }}</h3>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Avg Instrument Value</p>
            <h4 class="text-xl font-bold mt-1 text-indigo-600">₦{{ number_format($avgInstrumentValue, 0) }}</h4>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Active</p>
            <h4 class="text-xl font-bold mt-1 text-green-600">{{ $activeInstruments }}</h4>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase">Expiring Soon</p>
            <h4 class="text-xl font-bold mt-1 text-amber-600">{{ $expiringSoon }}</h4>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">By Instrument Type</h3>
            <canvas id="byTypeChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">Status Distribution</h3>
            <canvas id="byStatusChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">Monthly Issuance Trend</h3>
            <canvas id="monthlyChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">Top Beneficiaries</h3>
            @if($topBeneficiaries->count())
            <div class="space-y-3">
                @foreach($topBeneficiaries as $b)
                <div class="flex justify-between items-center">
                    <span class="text-sm text-bankos-text-sec">{{ $b->beneficiary_name }}</span>
                    <span class="text-sm font-bold">₦{{ number_format($b->total, 0) }} ({{ $b->count }})</span>
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
        const typeLabels = @json($byType->pluck('type')->map(fn($t) => ucfirst(str_replace('_',' ',$t))));
        new Chart(document.getElementById('byTypeChart'), {
            type: 'doughnut',
            data: { labels: typeLabels, datasets: [{ data: @json($byType->pluck('total')), backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444'] }] },
            options: { responsive: true }
        });
        new Chart(document.getElementById('byStatusChart'), {
            type: 'pie',
            data: { labels: @json($byStatus->pluck('status')->map(fn($s) => ucfirst($s))), datasets: [{ data: @json($byStatus->pluck('count')), backgroundColor: ['#94a3b8','#3b82f6','#f59e0b','#10b981','#ef4444','#6b7280'] }] },
            options: { responsive: true }
        });
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: { labels: @json($monthlyTrend->pluck('month')), datasets: [{ label: 'Instruments', data: @json($monthlyTrend->pluck('count')), borderColor: '#3b82f6', tension: 0.3, fill: false }] },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    });
    </script>
</x-app-layout>
