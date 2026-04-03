<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Performance Dashboard</h1>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mt-1">Review cycles, ratings & top performers</p>
            </div>
        </div>
    </x-slot>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Review Cycle</p>
            <p class="text-lg font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1 truncate">{{ $currentCycle->title ?? 'No Active Cycle' }}</p>
            <p class="text-xs mt-2"><span class="font-semibold px-2 py-0.5 rounded-full text-xs {{ $cycleStatus === 'active' ? 'bg-green-100 text-green-700' : ($cycleStatus === 'closed' ? 'bg-gray-100 text-gray-600' : 'bg-amber-100 text-amber-700') }}">{{ ucfirst($cycleStatus) }}</span></p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Average Rating</p>
            <p class="text-2xl font-extrabold {{ $avgRating >= 4 ? 'text-green-600' : ($avgRating >= 3 ? 'text-amber-600' : 'text-red-600') }} mt-1">{{ $avgRating }}/5.0</p>
            <p class="text-xs text-bankos-text-sec mt-2">Across completed reviews</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Reviews Completed</p>
            <p class="text-2xl font-extrabold text-green-600 mt-1">{{ $completedReviews }}</p>
            <p class="text-xs text-bankos-text-sec mt-2">of {{ $totalReviews }} total</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Pending Reviews</p>
            <p class="text-2xl font-extrabold {{ $pendingReviews > 0 ? 'text-amber-600' : 'text-bankos-text dark:text-bankos-dark-text' }} mt-1">{{ $pendingReviews }}</p>
            <p class="text-xs text-bankos-text-sec mt-2">Awaiting completion</p>
        </div>
    </div>

    {{-- Progress Bar --}}
    @if($totalReviews > 0)
    <div class="card p-5 mb-6">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">Review Completion Progress</p>
            <p class="text-sm font-bold text-bankos-primary">{{ $totalReviews > 0 ? round(($completedReviews / $totalReviews) * 100) : 0 }}%</p>
        </div>
        <div class="w-full h-3 bg-gray-100 dark:bg-bankos-dark-bg rounded-full overflow-hidden">
            <div class="h-full bg-bankos-primary rounded-full" style="width:{{ $totalReviews > 0 ? round(($completedReviews / $totalReviews) * 100) : 0 }}%"></div>
        </div>
    </div>
    @endif

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Ratings Distribution (1-5)</h3>
            <canvas id="ratingsChart" height="200"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Performance by Department</h3>
            <canvas id="deptPerfChart" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Top Performers --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Top Performers</h3>
            <div class="space-y-2">
                @forelse($topPerformers as $i => $perf)
                <div class="flex items-center gap-3 p-2.5 rounded-lg {{ $i < 3 ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-50 dark:bg-bankos-dark-bg' }}">
                    <span class="w-6 h-6 rounded-full {{ $i < 3 ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }} text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                    <span class="flex-1 text-sm font-medium text-bankos-text dark:text-bankos-dark-text truncate">{{ $perf->name }}</span>
                    <span class="text-sm font-bold text-green-600">{{ number_format($perf->overall_rating, 1) }}</span>
                </div>
                @empty
                <p class="text-sm text-bankos-text-sec text-center py-4">No completed reviews yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Review Cycles --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Review Cycles</h3>
            <div class="space-y-2">
                @forelse($reviewCycles as $cycle)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-bankos-dark-bg">
                    <div>
                        <p class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">{{ $cycle->title ?? 'Untitled' }}</p>
                        <p class="text-xs text-bankos-text-sec">{{ $cycle->start_date ? \Carbon\Carbon::parse($cycle->start_date)->format('d M Y') : '' }} - {{ $cycle->end_date ? \Carbon\Carbon::parse($cycle->end_date)->format('d M Y') : '' }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $cycle->status === 'active' ? 'bg-green-100 text-green-700' : ($cycle->status === 'closed' ? 'bg-gray-100 text-gray-600' : 'bg-amber-100 text-amber-700') }}">{{ ucfirst($cycle->status) }}</span>
                </div>
                @empty
                <p class="text-sm text-bankos-text-sec text-center py-4">No review cycles found.</p>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Ratings Distribution Bar
        new Chart(document.getElementById('ratingsChart'), {
            type: 'bar',
            data: {
                labels: ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
                datasets: [{
                    label: 'Reviews',
                    data: [
                        {{ $ratingsDistribution[1] ?? 0 }},
                        {{ $ratingsDistribution[2] ?? 0 }},
                        {{ $ratingsDistribution[3] ?? 0 }},
                        {{ $ratingsDistribution[4] ?? 0 }},
                        {{ $ratingsDistribution[5] ?? 0 }}
                    ],
                    backgroundColor: ['#ef4444', '#f97316', '#f59e0b', '#22c55e', '#10b981']
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // Department Performance
        new Chart(document.getElementById('deptPerfChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($performanceByDept->pluck('dept')) !!},
                datasets: [{
                    label: 'Avg Rating',
                    data: {!! json_encode($performanceByDept->pluck('avg_rating')) !!},
                    backgroundColor: '#3b82f6'
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, max: 5 } }
            }
        });
    </script>
    @endpush
</x-app-layout>
