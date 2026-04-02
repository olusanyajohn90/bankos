<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Banking Products Dashboard</h2>
        <p class="text-sm text-bankos-text-sec mt-1">Fixed deposits, standing orders, overdrafts, and cheque books</p>
    </x-slot>

    {{-- Fixed Deposits Section --}}
    <h3 class="font-bold text-lg text-bankos-text dark:text-white mb-4 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-600"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        Fixed Deposits
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
        <a href="{{ route('fixed-deposits.index') }}" class="card p-5 border-l-4 border-l-blue-500 hover:shadow-md transition-shadow">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Active FDs</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ number_format($fdCount) }}</h3>
        </a>
        <div class="card p-5 border-l-4 border-l-indigo-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Value</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">
                @if($fdTotalValue >= 1_000_000_000) ₦{{ number_format($fdTotalValue / 1_000_000_000, 2) }}B
                @elseif($fdTotalValue >= 1_000_000) ₦{{ number_format($fdTotalValue / 1_000_000, 2) }}M
                @else ₦{{ number_format($fdTotalValue, 0) }} @endif
            </h3>
        </div>
        <div class="card p-5 border-l-4 border-l-green-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Avg Interest Rate</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ number_format($fdAvgRate, 2) }}%</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-amber-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Maturing (30d)</p>
            <h3 class="text-2xl font-bold mt-1 {{ $fdMaturing > 0 ? 'text-amber-600' : 'text-bankos-text dark:text-white' }}">{{ number_format($fdMaturing) }}</h3>
        </div>
    </div>

    {{-- Standing Orders Section --}}
    <h3 class="font-bold text-lg text-bankos-text dark:text-white mb-4 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-purple-600"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
        Standing Orders
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-5 mb-8">
        <a href="{{ route('standing-orders.index') }}" class="card p-5 border-l-4 border-l-purple-500 hover:shadow-md transition-shadow">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Active Orders</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ number_format($soActiveCount) }}</h3>
        </a>
        <div class="card p-5 border-l-4 border-l-violet-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Value</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">
                @if($soTotalValue >= 1_000_000) ₦{{ number_format($soTotalValue / 1_000_000, 2) }}M
                @else ₦{{ number_format($soTotalValue, 0) }} @endif
            </h3>
        </div>
        <div class="card p-5 border-l-4 border-l-orange-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Due Today</p>
            <h3 class="text-2xl font-bold mt-1 {{ $soDueToday > 0 ? 'text-orange-600' : 'text-bankos-text dark:text-white' }}">{{ number_format($soDueToday) }}</h3>
        </div>
    </div>

    {{-- Overdrafts Section --}}
    <h3 class="font-bold text-lg text-bankos-text dark:text-white mb-4 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-600"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Overdraft Facilities
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
        <a href="{{ route('overdrafts.index') }}" class="card p-5 border-l-4 border-l-red-500 hover:shadow-md transition-shadow">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Active Facilities</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ number_format($odCount) }}</h3>
        </a>
        <div class="card p-5 border-l-4 border-l-red-400">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Limit</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">
                @if($odLimit >= 1_000_000) ₦{{ number_format($odLimit / 1_000_000, 2) }}M
                @else ₦{{ number_format($odLimit, 0) }} @endif
            </h3>
        </div>
        <div class="card p-5 border-l-4 border-l-orange-400">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Utilized</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">
                @if($odUsed >= 1_000_000) ₦{{ number_format($odUsed / 1_000_000, 2) }}M
                @else ₦{{ number_format($odUsed, 0) }} @endif
            </h3>
        </div>
        <div class="card p-5 border-l-4 border-l-pink-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Utilization Rate</p>
            <h3 class="text-2xl font-bold mt-1 {{ $odUtilRate >= 80 ? 'text-red-600' : 'text-bankos-text dark:text-white' }}">{{ $odUtilRate }}%</h3>
        </div>
    </div>

    {{-- Cheque Books Section --}}
    <h3 class="font-bold text-lg text-bankos-text dark:text-white mb-4 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-cyan-600"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Cheque Books
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
        <a href="{{ route('cheques.index') }}" class="card p-5 border-l-4 border-l-cyan-500 hover:shadow-md transition-shadow">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Books Issued</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ number_format($cbIssued) }}</h3>
            <p class="text-xs text-bankos-muted mt-1">{{ $cbActive }} active</p>
        </a>
        <div class="card p-5 border-l-4 border-l-teal-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Leaves</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ number_format($cbTotalLeaves) }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-emerald-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Used Leaves</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ number_format($cbUsedLeaves) }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-green-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Remaining</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ number_format($cbTotalLeaves - $cbUsedLeaves) }}</h3>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-1 text-bankos-text dark:text-white">Fixed Deposits by Status</h3>
            <p class="text-xs text-bankos-muted mb-4">Distribution</p>
            <div class="relative h-72 w-full">
                <canvas id="fdStatusChart"></canvas>
            </div>
        </div>
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-1 text-bankos-text dark:text-white">Standing Orders by Frequency</h3>
            <p class="text-xs text-bankos-muted mb-4">Active orders</p>
            <div class="relative h-72 w-full">
                <canvas id="soFreqChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const isDark = document.documentElement.classList.contains('dark');
        Chart.defaults.color = isDark ? '#94A3B8' : '#64748B';
        Chart.defaults.borderColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
        const pieColors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#7C3AED', '#06B6D4'];

        // FD by Status
        const fdData = @json($fdByStatus);
        const fdLabels = Object.keys(fdData).map(s => s.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()));
        new Chart(document.getElementById('fdStatusChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: fdLabels.length ? fdLabels : ['No data'],
                datasets: [{ data: Object.values(fdData).length ? Object.values(fdData) : [1], backgroundColor: pieColors, borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'right' } } }
        });

        // SO by Frequency
        const soData = @json($soByFreq);
        const soLabels = Object.keys(soData).map(s => s.replace(/\b\w/g, l => l.toUpperCase()));
        new Chart(document.getElementById('soFreqChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: soLabels.length ? soLabels : ['No data'],
                datasets: [{ data: Object.values(soData).length ? Object.values(soData) : [1], backgroundColor: pieColors, borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'right' } } }
        });
    });
    </script>
</x-app-layout>
