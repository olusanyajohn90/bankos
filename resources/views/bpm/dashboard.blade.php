<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">BPM Dashboard</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Process automation, workflow instances and bottleneck detection</p>
            </div>
            <a href="{{ route('bpm.processes.create') }}" class="btn btn-primary text-sm">New Process</a>
        </div>
    </x-slot>

    @if(isset($error))<div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/30 p-4 border border-red-200 dark:border-red-800"><p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p></div>@endif

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-blue-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Processes</p><h3 class="text-2xl font-bold mt-2">{{ $activeProcesses }}</h3><p class="text-xs text-bankos-muted mt-1">{{ $totalProcesses }} total</p></div>
        <div class="card p-5 border-l-4 border-l-green-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Active Instances</p><h3 class="text-2xl font-bold mt-2 text-green-600">{{ $activeInstances }}</h3></div>
        <div class="card p-5 border-l-4 border-l-indigo-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Completed</p><h3 class="text-2xl font-bold mt-2">{{ $completedInstances }}</h3></div>
        <div class="card p-5 border-l-4 border-l-amber-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">On Hold</p><h3 class="text-2xl font-bold mt-2 text-amber-600">{{ $onHoldInstances }}</h3></div>
        <div class="card p-5 border-l-4 border-l-purple-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Avg Completion</p><h3 class="text-2xl font-bold mt-2">{{ number_format($avgCompletionHours, 0) }}h</h3></div>
        <div class="card p-5 border-l-4 border-l-{{ $bottlenecks > 0 ? 'red' : 'green' }}-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Bottlenecks</p><h3 class="text-2xl font-bold mt-2 {{ $bottlenecks > 0 ? 'text-red-600' : '' }}">{{ $bottlenecks }}</h3></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">By Category</h3>
            <canvas id="categoryChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Instance Status</h3>
            <canvas id="instanceChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Monthly Instances</h3>
            <canvas id="trendChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Top Processes</h3>
            @if($topProcesses->count())
            <div class="space-y-3">
                @foreach($topProcesses as $tp)
                <div class="flex justify-between items-center p-2 rounded-lg bg-gray-50 dark:bg-bankos-dark-bg">
                    <div><p class="text-sm font-medium">{{ $tp->name }}</p><p class="text-xs text-bankos-muted">Avg: {{ $tp->avg_completion_hours ?? 'N/A' }}h</p></div>
                    <span class="text-sm font-bold">{{ $tp->total_instances }} instances</span>
                </div>
                @endforeach
            </div>
            @else <p class="text-bankos-muted text-sm">No data yet.</p> @endif
        </div>
    </div>

    @if($recentInstances->count())
    <div class="card overflow-hidden">
        <h3 class="p-4 text-lg font-semibold border-b border-bankos-border dark:border-bankos-dark-border">Recent Instances</h3>
        <table class="bankos-table w-full text-sm">
            <thead><tr><th>Process</th><th>Step</th><th>Status</th><th>Initiated By</th><th>Started</th><th></th></tr></thead>
            <tbody>
            @foreach($recentInstances as $ri)
                <tr>
                    <td class="font-medium">{{ $ri->process->name ?? 'N/A' }}</td>
                    <td>Step {{ $ri->current_step + 1 }}</td>
                    <td><span class="badge {{ $ri->status=='active' ? 'badge-green' : ($ri->status=='completed' ? 'badge-blue' : 'badge-amber') }}">{{ ucfirst($ri->status) }}</span></td>
                    <td>{{ $ri->initiator->name ?? 'N/A' }}</td>
                    <td>{{ $ri->created_at->diffForHumans() }}</td>
                    <td><a href="{{ route('bpm.instances.show', $ri->id) }}" class="text-bankos-primary hover:underline">View</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        new Chart(document.getElementById('categoryChart'), { type: 'bar', data: { labels: @json($byCategory->pluck('category')->map(fn($c) => ucfirst(str_replace('_',' ',$c)))), datasets: [{ label: 'Processes', data: @json($byCategory->pluck('count')), backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#6b7280'], borderRadius: 6 }] }, options: { responsive: true, plugins: { legend: { display: false } } } });
        new Chart(document.getElementById('instanceChart'), { type: 'doughnut', data: { labels: @json($instancesByStatus->pluck('status')->map(fn($s) => ucfirst($s))), datasets: [{ data: @json($instancesByStatus->pluck('count')), backgroundColor: ['#10b981','#3b82f6','#ef4444','#f59e0b'] }] }, options: { responsive: true } });
        new Chart(document.getElementById('trendChart'), { type: 'line', data: { labels: @json($monthlyTrend->pluck('month')), datasets: [{ label: 'Instances', data: @json($monthlyTrend->pluck('count')), borderColor: '#3b82f6', tension: 0.3, fill: true, backgroundColor: 'rgba(59,130,246,0.1)' }] }, options: { responsive: true, scales: { y: { beginAtZero: true } } } });
    });
    </script>
</x-app-layout>
