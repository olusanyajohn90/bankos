<x-app-layout>
    <x-slot name="header">Compliance Automation - Command Center</x-slot>

    <div class="space-y-6">

        {{-- Flash --}}
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Hero Banner --}}
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 rounded-xl p-6 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
            <div class="absolute bottom-0 left-1/2 w-48 h-48 bg-white/5 rounded-full translate-y-1/2"></div>
            <div class="relative flex flex-col lg:flex-row items-start lg:items-center gap-6">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold">BankOS Compliance Automation</h1>
                    <p class="text-white/80 mt-1 text-sm max-w-xl">{{ $narrative }}</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="relative w-32 h-32">
                        <canvas id="overallScoreGauge" width="128" height="128"></canvas>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <span class="text-3xl font-bold">{{ $overallScore }}</span>
                                <span class="text-lg">%</span>
                            </div>
                        </div>
                    </div>
                    <span class="text-sm text-white/70 mt-1">Overall Score</span>
                </div>
                <div>
                    <form method="POST" action="{{ route('compliance-auto.run-checks') }}">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors backdrop-blur-sm border border-white/20">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline mr-1"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                            Run All Checks
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Total Controls</p>
                <p class="text-3xl font-bold text-bankos-text dark:text-bankos-dark-text mt-1">{{ $totalControls }}</p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Compliant</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ $compliantControls }}</p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Evidence Collected</p>
                <p class="text-3xl font-bold text-blue-600 mt-1">{{ $evidenceCount }}</p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Monitors Passing</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ $passingMonitors }}<span class="text-lg text-bankos-muted">/{{ $passingMonitors + $warningMonitors + $failingMonitors }}</span></p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Alerts</p>
                <p class="text-3xl font-bold text-red-600 mt-1">{{ $failingMonitors + $warningMonitors }}</p>
            </div>
        </div>

        {{-- Framework Cards --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text">Regulatory Frameworks</h2>
                <a href="{{ route('compliance-auto.frameworks') }}" class="text-sm text-bankos-primary hover:underline">View All</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse($frameworks as $fw)
                <a href="{{ route('compliance-auto.frameworks.show', $fw->id) }}" class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text text-sm">{{ $fw->name }}</h3>
                        <span class="text-2xl font-bold {{ $fw->compliance_score >= 80 ? 'text-green-600' : ($fw->compliance_score >= 60 ? 'text-amber-500' : 'text-red-600') }}">{{ $fw->compliance_score }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-3">
                        <div class="h-2.5 rounded-full {{ $fw->compliance_score >= 80 ? 'bg-green-500' : ($fw->compliance_score >= 60 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $fw->compliance_score }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-bankos-muted">
                        <span>{{ $fw->total_controls }} controls</span>
                        <span>{{ $fw->compliant_controls }} compliant</span>
                        <span>Last: {{ $fw->last_assessed_at ? $fw->last_assessed_at->diffForHumans() : 'Never' }}</span>
                    </div>
                </a>
                @empty
                <div class="col-span-full text-center py-8 text-bankos-muted">No frameworks configured.</div>
                @endforelse
            </div>
        </div>

        {{-- Monitor Status Grid --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text">Monitor Health Checks</h2>
                <a href="{{ route('compliance-auto.monitors') }}" class="text-sm text-bankos-primary hover:underline">View All</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                @foreach($monitors as $mon)
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full {{ $mon->status === 'passing' ? 'bg-green-500' : ($mon->status === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></div>
                        <span class="text-xs font-medium text-bankos-text dark:text-bankos-dark-text truncate">{{ $mon->name }}</span>
                    </div>
                    <p class="text-xl font-bold {{ $mon->status === 'passing' ? 'text-green-600' : ($mon->status === 'warning' ? 'text-amber-500' : 'text-red-600') }}">
                        {{ $mon->current_value }}{{ $mon->check_type === 'str_response' || $mon->check_type === 'data_breach' ? 'hrs' : '%' }}
                    </p>
                    <p class="text-xs text-bankos-muted mt-1">Threshold: {{ $mon->threshold_value }}{{ $mon->check_type === 'str_response' || $mon->check_type === 'data_breach' ? 'hrs' : '%' }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Alerts --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border">
                <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border">
                    <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Recent Alerts</h3>
                </div>
                <div class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($alerts as $alert)
                    <div class="p-4 flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0 {{ $alert->event_type === 'breach' ? 'bg-red-500' : ($alert->event_type === 'warning' ? 'bg-amber-500' : 'bg-gray-400') }}"></div>
                        <div>
                            <p class="text-sm text-bankos-text dark:text-bankos-dark-text">{{ $alert->description }}</p>
                            <p class="text-xs text-bankos-muted mt-1">{{ $alert->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center text-sm text-bankos-muted">No recent alerts</div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Audit Trail --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border">
                <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border flex items-center justify-between">
                    <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Recent Activity</h3>
                    <a href="{{ route('compliance-auto.audit-trail') }}" class="text-xs text-bankos-primary hover:underline">View All</a>
                </div>
                <div class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($recentTrail as $entry)
                    <div class="p-4 flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0
                            {{ $entry->event_type === 'breach' ? 'bg-red-500' :
                               ($entry->event_type === 'warning' ? 'bg-amber-500' :
                               ($entry->event_type === 'check_passed' ? 'bg-green-500' :
                               ($entry->event_type === 'evidence_added' ? 'bg-blue-500' :
                               ($entry->event_type === 'framework_scored' ? 'bg-indigo-500' : 'bg-gray-400')))) }}"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-bankos-text dark:text-bankos-dark-text truncate">{{ $entry->description }}</p>
                            <p class="text-xs text-bankos-muted mt-1">{{ $entry->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center text-sm text-bankos-muted">No recent activity</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const score = {{ $overallScore }};
            const ctx = document.getElementById('overallScoreGauge').getContext('2d');

            const color = score >= 80 ? '#22c55e' : (score >= 60 ? '#f59e0b' : '#ef4444');

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [score, 100 - score],
                        backgroundColor: [color, 'rgba(255,255,255,0.15)'],
                        borderWidth: 0,
                        cutout: '78%'
                    }]
                },
                options: {
                    responsive: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    rotation: -90,
                    circumference: 180,
                    animation: { animateRotate: true }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
