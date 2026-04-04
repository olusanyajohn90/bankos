<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Risk Management Dashboard</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Risk assessments, limits utilization and breach alerts</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('risk-management.assessments.create') }}" class="btn btn-primary text-sm">New Assessment</a>
                <a href="{{ route('risk-management.breach-alerts') }}" class="btn btn-outline text-sm {{ $breachedLimits > 0 ? 'text-red-600 border-red-300' : '' }}">Breach Alerts{{ $breachedLimits > 0 ? ' ('.$breachedLimits.')' : '' }}</a>
            </div>
        </div>
    </x-slot>

    @if(isset($error))<div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/30 p-4 border border-red-200 dark:border-red-800"><p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p></div>@endif

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-blue-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase">Open Assessments</p>
            <h3 class="text-2xl font-bold mt-2">{{ $openAssessments }}</h3>
            <p class="text-xs text-bankos-muted mt-1">{{ $totalAssessments }} total</p>
        </div>
        <div class="card p-5 border-l-4 border-l-red-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase">Critical</p>
            <h3 class="text-2xl font-bold mt-2 text-red-600">{{ $criticalAssessments }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-amber-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase">High</p>
            <h3 class="text-2xl font-bold mt-2 text-amber-600">{{ $highAssessments }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-green-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase">Mitigated</p>
            <h3 class="text-2xl font-bold mt-2 text-green-600">{{ $mitigatedCount }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-indigo-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase">Total Exposure</p>
            <h3 class="text-2xl font-bold mt-2">₦{{ number_format($totalExposure / max($totalExposure,1) >= 1000000 ? $totalExposure/1000000 : $totalExposure, $totalExposure >= 1000000 ? 2 : 0) }}{{ $totalExposure >= 1000000 ? 'M' : '' }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-{{ $breachedLimits > 0 ? 'red' : 'green' }}-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase">Breached Limits</p>
            <h3 class="text-2xl font-bold mt-2 {{ $breachedLimits > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $breachedLimits }}</h3>
            <p class="text-xs text-bankos-muted mt-1">{{ $warningLimits }} warning</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Risk by Type</h3>
            <canvas id="typeChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Risk by Severity</h3>
            <canvas id="severityChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Limits Utilization (Top 10)</h3>
            @if($limitsUtilization->count())
            <div class="space-y-3">
                @foreach($limitsUtilization as $l)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>{{ $l->name }}</span>
                        <span class="font-bold {{ $l->status == 'breached' ? 'text-red-600' : ($l->status == 'warning' ? 'text-amber-600' : '') }}">{{ number_format($l->utilization_pct, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $l->status == 'breached' ? 'bg-red-500' : ($l->status == 'warning' ? 'bg-amber-500' : 'bg-green-500') }}" style="width: {{ min($l->utilization_pct, 100) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else <p class="text-bankos-muted text-sm">No limits configured.</p> @endif
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Recent Assessments</h3>
            @if($recentAssessments->count())
            <div class="space-y-2">
                @foreach($recentAssessments as $ra)
                <a href="{{ route('risk-management.assessments.show', $ra->id) }}" class="block p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium">{{ $ra->title }}</span>
                        @php $sc = ['low'=>'badge-green','medium'=>'badge-amber','high'=>'badge-red','critical'=>'badge-red']; @endphp
                        <span class="badge {{ $sc[$ra->severity] ?? 'badge-gray' }}">{{ ucfirst($ra->severity) }}</span>
                    </div>
                    <p class="text-xs text-bankos-muted">{{ ucfirst(str_replace('_',' ',$ra->risk_type)) }} - {{ $ra->created_at->diffForHumans() }}</p>
                </a>
                @endforeach
            </div>
            @else <p class="text-bankos-muted text-sm">No assessments yet.</p> @endif
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        new Chart(document.getElementById('typeChart'), {
            type: 'bar',
            data: { labels: @json($byType->pluck('risk_type')->map(fn($t) => ucfirst(str_replace('_',' ',$t)))), datasets: [{ label: 'Count', data: @json($byType->pluck('count')), backgroundColor: ['#3b82f6','#06b6d4','#f59e0b','#ef4444','#8b5cf6'], borderRadius: 6 }] },
            options: { responsive: true, indexAxis: 'y', plugins: { legend: { display: false } } }
        });
        new Chart(document.getElementById('severityChart'), {
            type: 'doughnut',
            data: { labels: @json($bySeverity->pluck('severity')->map(fn($s) => ucfirst($s))), datasets: [{ data: @json($bySeverity->pluck('count')), backgroundColor: ['#10b981','#f59e0b','#ef4444','#991b1b'] }] },
            options: { responsive: true }
        });
    });
    </script>
</x-app-layout>
