<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">CRM Dashboard</h1>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mt-1">Pipeline, interactions, conversions & follow-ups</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('crm.leads') }}" class="btn btn-primary btn-sm">All Leads</a>
                <a href="{{ route('crm.interactions') }}" class="btn btn-secondary btn-sm">Interactions</a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm mb-4">{{ session('success') }}</div>
    @endif

    {{-- Row 1: Core KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Leads</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($totalLeads) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $activeLeads }} active &middot; {{ $lost ?? 0 }} lost</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Converted</p>
                    <p class="text-2xl font-extrabold text-green-600 mt-1">{{ number_format($converted) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $conversionRate ?? 0 }}% conversion rate</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Avg Sales Cycle</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ $avgSalesCycle ?? 0 }} days</p>
                </div>
                <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Lead to conversion average</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Follow-up Rate</p>
                    <p class="text-2xl font-extrabold {{ ($followUpRate ?? 0) >= 80 ? 'text-green-600' : 'text-amber-600' }} mt-1">{{ $followUpRate ?? 0 }}%</p>
                </div>
                <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $completedFollowUps ?? 0 }}/{{ $totalFollowUps ?? 0 }} completed</p>
        </div>
    </div>

    {{-- Conversion Funnel --}}
    <div class="card p-6 mb-6">
        <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Conversion Funnel</h3>
        <div class="flex items-end gap-2 justify-center h-40">
            @php
                $funnelData = [
                    ['label' => 'New', 'value' => $newLeads ?? 0, 'color' => 'bg-blue-400'],
                    ['label' => 'Contacted', 'value' => $contacted ?? 0, 'color' => 'bg-indigo-400'],
                    ['label' => 'Qualified', 'value' => $qualified ?? 0, 'color' => 'bg-purple-400'],
                    ['label' => 'Converted', 'value' => $converted, 'color' => 'bg-green-400'],
                ];
                $maxFunnel = max(1, max(array_column($funnelData, 'value')));
            @endphp
            @foreach($funnelData as $step)
            <div class="flex flex-col items-center flex-1 max-w-[120px]">
                <p class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text mb-1">{{ number_format($step['value']) }}</p>
                <div class="w-full {{ $step['color'] }} rounded-t-lg" style="height:{{ max(8, round(($step['value'] / $maxFunnel) * 120)) }}px"></div>
                <p class="text-xs text-bankos-text-sec mt-1.5 font-medium">{{ $step['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Pipeline Kanban --}}
    <div class="card p-6 mb-6">
        <h2 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Sales Pipeline</h2>
        <div class="flex gap-3 overflow-x-auto pb-2">
            @foreach($pipelineData as $col)
                <div class="flex-shrink-0 w-44">
                    <div class="rounded-xl border-2 p-3" style="border-color: {{ $col['stage']->color }}20; background: {{ $col['stage']->color }}10">
                        <div class="flex items-center gap-1.5 mb-2">
                            <div class="w-2.5 h-2.5 rounded-full" style="background: {{ $col['stage']->color }}"></div>
                            <span class="text-xs font-bold text-bankos-text dark:text-bankos-dark-text truncate">{{ $col['stage']->name }}</span>
                        </div>
                        <p class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ $col['count'] }}</p>
                        <p class="text-xs text-bankos-text-sec">₦{{ number_format($col['total_value']) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Lead Source Breakdown</h3>
            <canvas id="leadSourceChart" height="200"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">CRM Activity Trend (30 Days)</h3>
            <canvas id="activityTrendChart" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Revenue by Pipeline Stage --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Revenue by Pipeline Stage</h3>
            <canvas id="revenueStageChart" height="200"></canvas>
        </div>

        {{-- My Follow-ups --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">My Follow-ups</h3>
            <div class="space-y-2">
                @forelse($myFollowUps as $f)
                    @php $overdue = $f->due_at->isPast(); @endphp
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $overdue ? 'bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800' : 'bg-gray-50 dark:bg-bankos-dark-bg' }}">
                        <div>
                            <p class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">{{ $f->title }}</p>
                            <p class="text-xs {{ $overdue ? 'text-red-600 font-semibold' : 'text-bankos-text-sec' }}">
                                {{ $overdue ? 'Overdue: ' : '' }}{{ $f->due_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                        <form action="{{ route('crm.follow-ups.complete', $f) }}" method="POST">
                            @csrf
                            <button class="text-xs text-green-600 hover:text-green-800 font-medium">Done</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-bankos-text-sec text-center py-4">No pending follow-ups.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Interactions --}}
    <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">Recent Interactions</h3>
            <a href="{{ route('crm.interactions') }}" class="text-xs text-bankos-primary hover:underline">View all</a>
        </div>
        <div class="space-y-3">
            @forelse($recentInt as $i)
                @php
                    $typeIcon = match($i->interaction_type) {
                        'call' => '📞', 'meeting' => '🤝', 'email' => '📧',
                        'whatsapp' => '💬', 'visit' => '🏢', default => '📝'
                    };
                @endphp
                <div class="flex items-start gap-3">
                    <span class="text-lg">{{ $typeIcon }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-bankos-text dark:text-bankos-dark-text font-medium truncate">{{ $i->summary }}</p>
                        <p class="text-xs text-bankos-text-sec">{{ $i->createdBy?->name }} &middot; {{ $i->interacted_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-bankos-text-sec text-center py-4">No interactions yet.</p>
            @endforelse
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Lead Source Pie
        new Chart(document.getElementById('leadSourceChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(($leadSources ?? collect())->keys()) !!},
                datasets: [{
                    data: {!! json_encode(($leadSources ?? collect())->values()) !!},
                    backgroundColor: ['#3b82f6','#22c55e','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#f97316'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Activity Trend
        new Chart(document.getElementById('activityTrendChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode(($activityTrend ?? collect())->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))) !!},
                datasets: [{
                    label: 'Interactions',
                    data: {!! json_encode(($activityTrend ?? collect())->pluck('total')) !!},
                    borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.1)',
                    fill: true, tension: 0.3, pointRadius: 3
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // Revenue by Stage
        new Chart(document.getElementById('revenueStageChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode(collect($revenueByStage ?? [])->pluck('name')) !!},
                datasets: [{
                    label: 'Revenue (₦)',
                    data: {!! json_encode(collect($revenueByStage ?? [])->pluck('value')) !!},
                    backgroundColor: {!! json_encode(collect($revenueByStage ?? [])->pluck('color')) !!}
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    </script>
    @endpush
</x-app-layout>
