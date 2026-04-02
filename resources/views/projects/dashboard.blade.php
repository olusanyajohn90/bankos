<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Projects Dashboard</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Overview of all projects, tasks, and team workload</p>
            </div>
            <a href="{{ route('projects.index') }}" class="btn btn-primary text-sm">View All Projects</a>
        </div>
    </x-slot>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-blue-500">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </div>
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Active Projects</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ number_format($activeProjects) }}</h3>
            <p class="text-xs text-bankos-muted mt-1">of {{ $totalProjects }} total</p>
        </div>

        <div class="card p-5 border-l-4 border-l-indigo-500">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Tasks</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ number_format($totalTasks) }}</h3>
            <p class="text-xs text-bankos-muted mt-1">{{ $completedTasks }} completed</p>
        </div>

        <div class="card p-5 border-l-4 border-l-green-500">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Completion Rate</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0 }}%</h3>
            <p class="text-xs text-bankos-muted mt-1">{{ $completedTasks }}/{{ $totalTasks }}</p>
        </div>

        <div class="card p-5 border-l-4 border-l-red-500">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Overdue Tasks</p>
            <h3 class="text-2xl font-bold mt-1 {{ $overdueTasks > 0 ? 'text-red-600' : 'text-bankos-text dark:text-white' }}">{{ number_format($overdueTasks) }}</h3>
            <p class="text-xs text-bankos-muted mt-1">past due date</p>
        </div>

        @if($sprintProgress)
        <div class="card p-5 border-l-4 border-l-purple-500">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                </div>
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Sprint</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ $sprintProgress['percent'] }}%</h3>
            <p class="text-xs text-bankos-muted mt-1">{{ $sprintProgress['name'] }}</p>
        </div>
        @else
        <div class="card p-5 border-l-4 border-l-gray-400">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Sprint</p>
            <h3 class="text-lg font-bold mt-2 text-bankos-muted">No active sprint</h3>
        </div>
        @endif
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Tasks by Status --}}
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-1 text-bankos-text dark:text-white">Tasks by Status</h3>
            <p class="text-xs text-bankos-muted mb-4">Distribution of all tasks</p>
            <div class="relative h-72 w-full">
                <canvas id="taskStatusChart"></canvas>
            </div>
        </div>

        {{-- Team Workload --}}
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-1 text-bankos-text dark:text-white">Team Workload</h3>
            <p class="text-xs text-bankos-muted mb-4">Open tasks per team member</p>
            <div class="relative h-72 w-full">
                <canvas id="workloadChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Sprint Progress Bar + Recent Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        @if($sprintProgress)
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-4 text-bankos-text dark:text-white">Sprint Progress</h3>
            <p class="text-sm font-medium text-bankos-text dark:text-white mb-2">{{ $sprintProgress['name'] }}</p>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-2">
                <div class="bg-purple-600 h-3 rounded-full transition-all" style="width: {{ $sprintProgress['percent'] }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-bankos-muted">
                <span>{{ $sprintProgress['done'] }}/{{ $sprintProgress['total'] }} tasks done</span>
                @if($sprintProgress['end_date'])
                <span>Ends {{ \Carbon\Carbon::parse($sprintProgress['end_date'])->format('M d') }}</span>
                @endif
            </div>
        </div>
        @endif

        <div class="{{ $sprintProgress ? 'lg:col-span-2' : 'lg:col-span-3' }} card p-6">
            <h3 class="font-bold text-lg mb-4 text-bankos-text dark:text-white">Recent Activity</h3>
            <div class="space-y-3 max-h-80 overflow-y-auto">
                @forelse($recentActivity as $activity)
                <div class="flex items-start gap-3 text-sm">
                    <div class="w-7 h-7 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-xs font-bold text-bankos-text-sec flex-shrink-0 mt-0.5">
                        {{ strtoupper(substr($activity->user_name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-bankos-text dark:text-white">
                            <span class="font-semibold">{{ $activity->user_name }}</span>
                            <span class="text-bankos-text-sec">{{ str_replace('_', ' ', $activity->action) }}</span>
                            <span class="font-medium text-bankos-primary">{{ $activity->project_code }}: {{ Str::limit($activity->task_title, 40) }}</span>
                        </p>
                        <p class="text-xs text-bankos-muted">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-bankos-muted text-sm">No recent activity.</p>
                @endforelse
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
        const textColor = isDark ? '#94A3B8' : '#64748B';
        Chart.defaults.color = textColor;
        Chart.defaults.borderColor = gridColor;

        // Tasks by Status (doughnut)
        const statusData = @json($tasksByStatus);
        const statusLabels = Object.keys(statusData).map(s => s.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()));
        const statusColors = { 'open': '#94A3B8', 'in_progress': '#3B82F6', 'review': '#F59E0B', 'done': '#10B981', 'blocked': '#EF4444' };
        const colors = Object.keys(statusData).map(s => statusColors[s] || '#6B7280');

        new Chart(document.getElementById('taskStatusChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: statusLabels.length ? statusLabels : ['No tasks'],
                datasets: [{ data: Object.values(statusData).length ? Object.values(statusData) : [1], backgroundColor: colors.length ? colors : ['#E5E7EB'], borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'right' } } }
        });

        // Team Workload (bar)
        const workloadData = @json($workload);
        new Chart(document.getElementById('workloadChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: workloadData.map(w => w.name),
                datasets: [{ label: 'Open Tasks', data: workloadData.map(w => w.count), backgroundColor: '#3B82F6', borderRadius: 4 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, grid: { borderDash: [2, 4], color: gridColor } }, y: { grid: { display: false } } }
            }
        });
    });
    </script>
</x-app-layout>
