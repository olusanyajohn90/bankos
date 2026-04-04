<x-app-layout>
    <x-slot name="header">{{ $framework->name }}</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm text-bankos-muted">
            <a href="{{ route('compliance-auto.frameworks') }}" class="hover:text-bankos-primary">Frameworks</a>
            <span>/</span>
            <span class="text-bankos-text dark:text-bankos-dark-text">{{ $framework->name }}</span>
        </div>

        {{-- Score + Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Score card --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 flex flex-col items-center justify-center">
                <div class="relative w-40 h-40">
                    <canvas id="frameworkScoreGauge" width="160" height="160"></canvas>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <span class="text-4xl font-bold {{ $framework->compliance_score >= 80 ? 'text-green-600' : ($framework->compliance_score >= 60 ? 'text-amber-500' : 'text-red-600') }}">{{ $framework->compliance_score }}</span>
                            <span class="text-xl text-bankos-muted">%</span>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-bankos-muted mt-2">Compliance Score</p>
                <p class="text-xs text-bankos-muted mt-1">Last: {{ $framework->last_assessed_at ? $framework->last_assessed_at->format('M d, Y H:i') : 'Never' }}</p>
            </div>

            {{-- Status Pie --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                <h3 class="font-semibold text-sm text-bankos-text dark:text-bankos-dark-text mb-4">Controls by Status</h3>
                <canvas id="statusPieChart" height="200"></canvas>
            </div>

            {{-- Category Bar --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                <h3 class="font-semibold text-sm text-bankos-text dark:text-bankos-dark-text mb-4">Controls by Category</h3>
                <canvas id="categoryBarChart" height="200"></canvas>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-4">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <select name="status" class="rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    <option value="">All Statuses</option>
                    <option value="compliant" {{ request('status') === 'compliant' ? 'selected' : '' }}>Compliant</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="non_compliant" {{ request('status') === 'non_compliant' ? 'selected' : '' }}>Non-Compliant</option>
                    <option value="not_assessed" {{ request('status') === 'not_assessed' ? 'selected' : '' }}>Not Assessed</option>
                </select>
                <select name="category" class="rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    <option value="">All Categories</option>
                    @foreach($categories->keys() as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-bankos-primary text-white rounded-lg text-sm hover:bg-bankos-primary/90 transition-colors">Filter</button>
                <a href="{{ route('compliance-auto.frameworks.show', $framework->id) }}" class="px-4 py-2 bg-gray-100 dark:bg-bankos-dark-bg text-bankos-text-sec dark:text-bankos-dark-text-sec rounded-lg text-sm hover:bg-gray-200 transition-colors">Reset</a>
            </form>
        </div>

        {{-- Controls Table --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Controls ({{ $controls->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Ref</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Title</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Category</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Status</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Priority</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Last Checked</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($controls as $ctrl)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg cursor-pointer" onclick="window.location='{{ route('compliance-auto.controls.show', $ctrl->id) }}'">
                            <td class="px-4 py-3 font-mono text-xs">{{ $ctrl->control_ref }}</td>
                            <td class="px-4 py-3 text-bankos-text dark:text-bankos-dark-text">{{ $ctrl->title }}</td>
                            <td class="px-4 py-3 text-bankos-muted">{{ $ctrl->category }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $ctrl->status === 'compliant' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400' :
                                       ($ctrl->status === 'partial' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-400' :
                                       ($ctrl->status === 'non_compliant' ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400' :
                                       'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400')) }}">
                                    {{ str_replace('_', ' ', ucfirst($ctrl->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $ctrl->priority == 1 ? 'bg-red-100 text-red-800' :
                                       ($ctrl->priority == 2 ? 'bg-orange-100 text-orange-800' :
                                       ($ctrl->priority == 3 ? 'bg-amber-100 text-amber-800' :
                                       'bg-blue-100 text-blue-800')) }}">
                                    {{ $ctrl->priorityLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-bankos-muted">{{ $ctrl->last_checked_at ? $ctrl->last_checked_at->diffForHumans() : 'Never' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-bankos-muted">No controls found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const score = {{ $framework->compliance_score }};
            const color = score >= 80 ? '#22c55e' : (score >= 60 ? '#f59e0b' : '#ef4444');

            // Gauge
            new Chart(document.getElementById('frameworkScoreGauge').getContext('2d'), {
                type: 'doughnut',
                data: { datasets: [{ data: [score, 100 - score], backgroundColor: [color, '#e5e7eb'], borderWidth: 0, cutout: '78%' }] },
                options: { responsive: false, plugins: { legend: { display: false }, tooltip: { enabled: false } }, rotation: -90, circumference: 180 }
            });

            // Status Pie
            const statusData = @json($statusCounts);
            new Chart(document.getElementById('statusPieChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData).map(s => s.replace('_', ' ')),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: Object.keys(statusData).map(s => {
                            if (s === 'compliant') return '#22c55e';
                            if (s === 'partial') return '#f59e0b';
                            if (s === 'non_compliant') return '#ef4444';
                            return '#9ca3af';
                        }),
                        borderWidth: 2, borderColor: '#fff'
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } } } }
            });

            // Category Bar
            const catData = @json($categories);
            new Chart(document.getElementById('categoryBarChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: Object.keys(catData),
                    datasets: [{
                        label: 'Controls',
                        data: Object.values(catData),
                        backgroundColor: '#6366f1',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { stepSize: 1 } },
                        y: { ticks: { font: { size: 10 } } }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
