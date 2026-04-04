<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Open Banking Dashboard</h2>
                <p class="text-sm text-bankos-text-sec mt-1">API clients, usage metrics and endpoint monitoring</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('open-banking.clients.create') }}" class="btn btn-primary text-sm">New Client</a>
                <a href="{{ route('open-banking.documentation') }}" class="btn btn-outline text-sm">API Docs</a>
            </div>
        </div>
    </x-slot>

    @if(isset($error))<div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/30 p-4 border border-red-200 dark:border-red-800"><p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p></div>@endif

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-blue-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Active Clients</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">{{ $activeClients }}</h3>
            <p class="text-xs text-bankos-muted mt-1">{{ $inactiveClients }} inactive</p>
        </div>
        <div class="card p-5 border-l-4 border-l-green-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Requests</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">{{ number_format($totalRequests) }}</h3>
            <p class="text-xs text-bankos-muted mt-1">{{ $todayRequests }} today</p>
        </div>
        <div class="card p-5 border-l-4 border-l-indigo-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Avg Response</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">{{ number_format($avgResponseTime, 0) }}ms</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-{{ $errorRate > 5 ? 'red' : 'amber' }}-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Error Rate</p>
            <h3 class="text-2xl font-bold mt-2 {{ $errorRate > 5 ? 'text-red-600' : 'text-bankos-text dark:text-white' }}">{{ number_format($errorRate, 1) }}%</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-purple-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Today Requests</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">{{ number_format($todayRequests) }}</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">Daily API Requests (14 days)</h3>
            <canvas id="dailyChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">Status Code Distribution</h3>
            <canvas id="statusChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">Top Endpoints</h3>
            @if($topEndpoints->count())
            <div class="space-y-2">
                @foreach($topEndpoints as $ep)
                <div class="flex justify-between items-center">
                    <span class="text-xs font-mono text-bankos-text-sec truncate mr-2">{{ $ep->endpoint }}</span>
                    <span class="text-sm font-bold whitespace-nowrap">{{ number_format($ep->hits) }}</span>
                </div>
                @endforeach
            </div>
            @else <p class="text-bankos-muted text-sm">No data yet</p> @endif
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">Top Clients by Usage</h3>
            @if($topClients->count())
            <div class="space-y-2">
                @foreach($topClients as $tc)
                <div class="flex justify-between items-center">
                    <span class="text-sm">{{ $tc->name }}</span>
                    <span class="text-sm font-bold">{{ number_format($tc->total_requests) }}</span>
                </div>
                @endforeach
            </div>
            @else <p class="text-bankos-muted text-sm">No data yet</p> @endif
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        new Chart(document.getElementById('dailyChart'), {
            type: 'bar',
            data: { labels: @json($dailyRequests->pluck('date')), datasets: [{ label: 'Requests', data: @json($dailyRequests->pluck('count')), backgroundColor: 'rgba(59,130,246,0.7)', borderRadius: 4 }] },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: { labels: @json($statusCodeDist->pluck('group')), datasets: [{ data: @json($statusCodeDist->pluck('count')), backgroundColor: ['#10b981','#3b82f6','#f59e0b','#ef4444'] }] },
            options: { responsive: true }
        });
    });
    </script>
</x-app-layout>
