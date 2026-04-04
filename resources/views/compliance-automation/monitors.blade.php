<x-app-layout>
    <x-slot name="header">Compliance Monitors</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        <div class="flex items-center justify-between">
            <p class="text-sm text-bankos-muted">Real-time compliance health check monitors with automated threshold tracking.</p>
            <form method="POST" action="{{ route('compliance-auto.run-checks') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-bankos-primary text-white rounded-lg text-sm hover:bg-bankos-primary/90 transition-colors flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                    Run Checks Now
                </button>
            </form>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5 text-center">
                <p class="text-3xl font-bold text-green-600">{{ $monitors->where('status', 'passing')->count() }}</p>
                <p class="text-xs font-semibold text-bankos-muted uppercase mt-1">Passing</p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5 text-center">
                <p class="text-3xl font-bold text-amber-500">{{ $monitors->where('status', 'warning')->count() }}</p>
                <p class="text-xs font-semibold text-bankos-muted uppercase mt-1">Warning</p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5 text-center">
                <p class="text-3xl font-bold text-red-600">{{ $monitors->where('status', 'failing')->count() }}</p>
                <p class="text-xs font-semibold text-bankos-muted uppercase mt-1">Failing</p>
            </div>
        </div>

        {{-- Monitor Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($monitors as $mon)
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-sm text-bankos-text dark:text-bankos-dark-text">{{ $mon->name }}</h3>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $mon->status === 'passing' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400' :
                           ($mon->status === 'warning' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-400' :
                           'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400') }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $mon->status === 'passing' ? 'bg-green-500' : ($mon->status === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></span>
                        {{ ucfirst($mon->status) }}
                    </span>
                </div>

                @if($mon->description)
                <p class="text-xs text-bankos-muted mb-3">{{ $mon->description }}</p>
                @endif

                <div class="flex items-end gap-4">
                    <div>
                        <p class="text-xs text-bankos-muted">Current</p>
                        <p class="text-2xl font-bold {{ $mon->status === 'passing' ? 'text-green-600' : ($mon->status === 'warning' ? 'text-amber-500' : 'text-red-600') }}">
                            {{ $mon->current_value }}{{ $mon->check_type === 'str_response' || $mon->check_type === 'data_breach' ? 'hrs' : '%' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-bankos-muted">Threshold</p>
                        <p class="text-lg font-medium text-bankos-text dark:text-bankos-dark-text">
                            {{ $mon->threshold_value }}{{ $mon->check_type === 'str_response' || $mon->check_type === 'data_breach' ? 'hrs' : '%' }}
                        </p>
                    </div>
                    <div class="flex-1">
                        <canvas class="monitor-spark" data-value="{{ $mon->current_value }}" data-threshold="{{ $mon->threshold_value }}" data-status="{{ $mon->status }}" height="40"></canvas>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-3 pt-3 border-t border-bankos-border dark:border-bankos-dark-border text-xs text-bankos-muted">
                    <span>{{ ucfirst($mon->frequency ?? 'daily') }}</span>
                    <span>Last: {{ $mon->last_checked_at ? $mon->last_checked_at->diffForHumans() : 'Never' }}</span>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12 text-bankos-muted">No monitors configured.</div>
            @endforelse
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.monitor-spark').forEach(canvas => {
                const value = parseFloat(canvas.dataset.value);
                const threshold = parseFloat(canvas.dataset.threshold);
                const status = canvas.dataset.status;

                const color = status === 'passing' ? '#22c55e' : (status === 'warning' ? '#f59e0b' : '#ef4444');

                // Generate some fake historical data points around the current value
                const points = [];
                for (let i = 0; i < 7; i++) {
                    points.push(value + (Math.random() - 0.5) * value * 0.1);
                }
                points.push(value);

                new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: points.map((_, i) => ''),
                        datasets: [{
                            data: points,
                            borderColor: color,
                            borderWidth: 2,
                            fill: false,
                            pointRadius: 0,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { enabled: false } },
                        scales: { x: { display: false }, y: { display: false } }
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
