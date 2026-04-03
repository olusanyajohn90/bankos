<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Leave Dashboard</h1>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mt-1">Leave balances, requests & trends &mdash; {{ now()->format('F Y') }}</p>
            </div>
        </div>
    </x-slot>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">On Leave Today</p>
            <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $staffOnLeave->count() }}</p>
            <p class="text-xs text-bankos-text-sec mt-2">Staff currently away</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Pending Requests</p>
            <p class="text-2xl font-extrabold {{ $pendingCount > 0 ? 'text-amber-600' : 'text-bankos-text dark:text-bankos-dark-text' }} mt-1">{{ $pendingCount }}</p>
            <p class="text-xs text-bankos-text-sec mt-2">Awaiting approval</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Leave Types</p>
            <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ count($leaveBalances) }}</p>
            <p class="text-xs text-bankos-text-sec mt-2">Active leave categories</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Top Leave Days</p>
            <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ $topLeaveTakers->first()->total_days ?? 0 }}</p>
            <p class="text-xs text-bankos-text-sec mt-2">Most days taken ({{ $topLeaveTakers->first()->name ?? 'N/A' }})</p>
        </div>
    </div>

    {{-- Leave Balance Overview --}}
    <div class="card p-6 mb-6">
        <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Leave Balance by Type</h3>
        <div class="space-y-3">
            @foreach($leaveBalances as $bal)
            <div class="flex items-center gap-4">
                <span class="w-28 text-xs font-medium text-bankos-text-sec truncate">{{ $bal['type'] }}</span>
                <div class="flex-1 h-3 bg-gray-100 dark:bg-bankos-dark-bg rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $bal['pct'] >= 80 ? 'bg-red-500' : ($bal['pct'] >= 50 ? 'bg-amber-400' : 'bg-green-500') }}" style="width:{{ min($bal['pct'], 100) }}%"></div>
                </div>
                <span class="w-20 text-xs font-bold text-bankos-text dark:text-bankos-dark-text text-right">{{ $bal['pct'] }}%</span>
                <span class="w-24 text-xs text-bankos-text-sec text-right">{{ $bal['used'] }}/{{ $bal['total'] }} days</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Leave Trend (Last 6 Months)</h3>
            <canvas id="leaveTrendChart" height="200"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Leave by Type</h3>
            <canvas id="leaveTypeChart" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Staff on Leave Today --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Staff on Leave Today</h3>
            <div class="space-y-2">
                @forelse($staffOnLeave as $leave)
                <div class="flex items-center justify-between p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                    <div>
                        <p class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">{{ $leave->staffProfile->user->name ?? 'N/A' }}</p>
                        <p class="text-xs text-bankos-text-sec">{{ $leave->leaveType->name ?? '' }} &middot; {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('d M') }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-bankos-text-sec text-center py-4">No staff on leave today.</p>
                @endforelse
            </div>
        </div>

        {{-- Top Leave Takers --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Top Leave Takers ({{ now()->year }})</h3>
            <div class="space-y-2">
                @forelse($topLeaveTakers as $i => $taker)
                <div class="flex items-center gap-3">
                    <span class="w-6 text-xs font-bold text-bankos-text-sec">{{ $i + 1 }}</span>
                    <span class="flex-1 text-sm font-medium text-bankos-text dark:text-bankos-dark-text truncate">{{ $taker->name }}</span>
                    <span class="text-sm font-bold text-bankos-primary">{{ $taker->total_days }} days</span>
                </div>
                @empty
                <p class="text-sm text-bankos-text-sec text-center py-4">No data.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Pending Requests --}}
    <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">Pending Leave Requests</h3>
            <a href="{{ route('hr.leave.requests.index') }}" class="text-xs text-bankos-primary hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-bankos-border dark:border-bankos-dark-border">
                        <th class="text-left py-2 px-3 text-xs text-bankos-text-sec uppercase">Staff</th>
                        <th class="text-left py-2 px-3 text-xs text-bankos-text-sec uppercase">Type</th>
                        <th class="text-left py-2 px-3 text-xs text-bankos-text-sec uppercase">Dates</th>
                        <th class="text-left py-2 px-3 text-xs text-bankos-text-sec uppercase">Days</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $req)
                    <tr class="border-b border-bankos-border/50 dark:border-bankos-dark-border/50">
                        <td class="py-2 px-3 font-medium">{{ $req->staffProfile->user->name ?? 'N/A' }}</td>
                        <td class="py-2 px-3">{{ $req->leaveType->name ?? '-' }}</td>
                        <td class="py-2 px-3 text-xs">{{ \Carbon\Carbon::parse($req->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</td>
                        <td class="py-2 px-3 font-bold">{{ $req->days_count ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-4 text-center text-bankos-text-sec">No pending requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('leaveTrendChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($leaveTrend->pluck('month')) !!},
                datasets: [{
                    label: 'Leave Requests',
                    data: {!! json_encode($leaveTrend->pluck('total')) !!},
                    borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true, tension: 0.3, pointRadius: 4
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('leaveTypeChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($leaveByType->keys()) !!},
                datasets: [{
                    data: {!! json_encode($leaveByType->values()) !!},
                    backgroundColor: ['#3b82f6','#22c55e','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    </script>
    @endpush
</x-app-layout>
