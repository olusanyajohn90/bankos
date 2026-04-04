<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Mobile Banking Dashboard</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Registered devices, platform breakdown and activity tracking</p>
            </div>
        </div>
    </x-slot>

    @if(isset($error))<div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/30 p-4 border border-red-200 dark:border-red-800"><p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p></div>@endif

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-blue-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Total Devices</p><h3 class="text-2xl font-bold mt-2">{{ number_format($totalDevices) }}</h3></div>
        <div class="card p-5 border-l-4 border-l-green-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Active Devices</p><h3 class="text-2xl font-bold mt-2 text-green-600">{{ number_format($activeDevices) }}</h3><p class="text-xs text-bankos-muted mt-1">{{ $inactiveDevices }} inactive</p></div>
        <div class="card p-5 border-l-4 border-l-indigo-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Active (7d)</p><h3 class="text-2xl font-bold mt-2">{{ number_format($recentActive) }}</h3></div>
        <div class="card p-5 border-l-4 border-l-amber-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Active (30d)</p><h3 class="text-2xl font-bold mt-2">{{ number_format($monthlyActive) }}</h3></div>
        <div class="card p-5 border-l-4 border-l-purple-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Engagement</p><h3 class="text-2xl font-bold mt-2">{{ $totalDevices > 0 ? number_format(($monthlyActive / $totalDevices) * 100, 1) : 0 }}%</h3></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Platform Breakdown</h3>
            <canvas id="platformChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Registration Trend (30d)</h3>
            <canvas id="trendChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">App Version Distribution</h3>
            @if($byVersion->count())
            <div class="space-y-2">
                @foreach($byVersion as $v)
                <div class="flex justify-between items-center">
                    <span class="text-sm font-mono">v{{ $v->app_version }}</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2"><div class="h-2 rounded-full bg-blue-500" style="width: {{ $totalDevices > 0 ? ($v->count / $totalDevices) * 100 : 0 }}%"></div></div>
                        <span class="text-sm font-bold">{{ $v->count }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else <p class="text-bankos-muted text-sm">No version data.</p> @endif
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Recent Registrations</h3>
            @if($recentDevices->count())
            <div class="space-y-2">
                @foreach($recentDevices as $d)
                <div class="flex justify-between items-center text-sm">
                    <div>
                        <p class="font-medium">{{ $d->device_name ?? $d->device_id }}</p>
                        <p class="text-xs text-bankos-muted">{{ ucfirst($d->platform) }} - {{ $d->customer->first_name ?? '' }} {{ $d->customer->last_name ?? '' }}</p>
                    </div>
                    <span class="text-xs text-bankos-muted">{{ $d->created_at->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
            @else <p class="text-bankos-muted text-sm">No recent registrations.</p> @endif
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        new Chart(document.getElementById('platformChart'), {
            type: 'doughnut',
            data: { labels: @json($byPlatform->pluck('platform')->map(fn($p) => ucfirst($p))), datasets: [{ data: @json($byPlatform->pluck('count')), backgroundColor: ['#3b82f6','#10b981','#f59e0b'] }] },
            options: { responsive: true }
        });
        new Chart(document.getElementById('trendChart'), {
            type: 'bar',
            data: { labels: @json($registrationTrend->pluck('date')), datasets: [{ label: 'Registrations', data: @json($registrationTrend->pluck('count')), backgroundColor: 'rgba(59,130,246,0.7)', borderRadius: 4 }] },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    });
    </script>
</x-app-layout>
