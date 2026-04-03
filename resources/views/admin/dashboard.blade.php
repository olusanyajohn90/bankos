<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Administration Dashboard</h1>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mt-1">System overview, user activity, security & audit</p>
            </div>
        </div>
    </x-slot>

    {{-- Row 1: System Overview --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Users</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($totalUsers) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $activeUsers }} active &middot; {{ $totalUsers - $activeUsers }} inactive</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Roles</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($totalRoles) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $totalPermissions }} permissions defined</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Logins Today</p>
                    <p class="text-2xl font-extrabold text-green-600 mt-1">{{ number_format($loginsToday) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $activeSessions }} active sessions &middot; {{ $uniqueLoginsThisWeek }} unique this week</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Audit Actions</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($auditActionsToday) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Logged today</p>
        </div>
    </div>

    {{-- Row 2: Security & Features --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5 {{ $failedLoginsToday > 10 ? 'border-l-4 border-red-400' : '' }}">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Failed Logins Today</p>
            <p class="text-2xl font-extrabold {{ $failedLoginsToday > 10 ? 'text-red-600' : 'text-bankos-text dark:text-bankos-dark-text' }} mt-1">{{ number_format($failedLoginsToday) }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">{{ $failedLoginsWeek }} this week</p>
        </div>

        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Locked Accounts</p>
            <p class="text-2xl font-extrabold {{ $lockedAccounts > 0 ? 'text-amber-600' : 'text-bankos-text dark:text-bankos-dark-text' }} mt-1">{{ number_format($lockedAccounts) }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">Inactive/disabled users</p>
        </div>

        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Without 2FA</p>
            <p class="text-2xl font-extrabold {{ $usersWithout2fa > 0 ? 'text-amber-600' : 'text-green-600' }} mt-1">{{ number_format($usersWithout2fa) }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">Active users without two-factor</p>
        </div>

        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Feature Flags</p>
            <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ $featureFlagsEnabled }}/{{ $featureFlagsTotal }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">Enabled features</p>
        </div>
    </div>

    {{-- Row 3: Storage, API, Tenant --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Storage Used</p>
            <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ $storageFormatted }} MB</p>
            <p class="text-xs text-bankos-text-sec mt-1">Document storage</p>
        </div>

        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">API Calls Today</p>
            <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($apiCallsToday) }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">{{ number_format($apiCallsThisMonth) }} this month</p>
        </div>

        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Tenant</p>
            <p class="text-lg font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1 truncate">{{ $tenant->name ?? 'N/A' }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">{{ $tenant->slug ?? '' }}</p>
        </div>

        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Subscription</p>
            <p class="text-lg font-extrabold {{ ($subscription->status ?? '') === 'active' ? 'text-green-600' : 'text-amber-600' }} mt-1">{{ ucfirst($subscription->plan ?? 'N/A') }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">{{ ucfirst($subscription->status ?? 'unknown') }}</p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Login Trend (Last 14 Days)</h3>
            <canvas id="loginTrendChart" height="200"></canvas>
        </div>

        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Users by Role</h3>
            <canvas id="usersByRoleChart" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Audit Activity (Last 7 Days)</h3>
            <canvas id="auditTrendChart" height="200"></canvas>
        </div>

        {{-- Database Stats --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Database Record Counts</h3>
            <div class="grid grid-cols-2 gap-3">
                @foreach($dbStats as $table => $count)
                <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 dark:bg-bankos-dark-bg">
                    <span class="text-xs font-medium text-bankos-text-sec capitalize">{{ str_replace('_', ' ', $table) }}</span>
                    <span class="text-sm font-bold text-bankos-text dark:text-bankos-dark-text">{{ number_format($count) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Top Audit Actions & Recent Logs --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Top Audit Actions (7 Days)</h3>
            <div class="space-y-2">
                @forelse($auditByAction as $action => $count)
                <div class="flex items-center gap-3">
                    <span class="w-32 text-xs font-medium text-bankos-text-sec truncate">{{ $action }}</span>
                    <div class="flex-1 h-2 bg-gray-100 dark:bg-bankos-dark-bg rounded-full overflow-hidden">
                        @php $maxAction = $auditByAction->max() ?: 1; @endphp
                        <div class="h-full bg-bankos-primary rounded-full" style="width:{{ round($count / $maxAction * 100) }}%"></div>
                    </div>
                    <span class="w-10 text-xs font-bold text-bankos-text dark:text-bankos-dark-text text-right">{{ number_format($count) }}</span>
                </div>
                @empty
                <p class="text-sm text-bankos-text-sec text-center py-4">No audit data.</p>
                @endforelse
            </div>
        </div>

        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Recent Audit Log</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-bankos-border dark:border-bankos-dark-border">
                            <th class="text-left py-2 px-2 text-xs text-bankos-text-sec uppercase">Time</th>
                            <th class="text-left py-2 px-2 text-xs text-bankos-text-sec uppercase">Action</th>
                            <th class="text-left py-2 px-2 text-xs text-bankos-text-sec uppercase">User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAuditLogs as $log)
                        <tr class="border-b border-bankos-border/50 dark:border-bankos-dark-border/50">
                            <td class="py-1.5 px-2 text-xs whitespace-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i') }}</td>
                            <td class="py-1.5 px-2 text-xs">{{ $log->action ?? '-' }}</td>
                            <td class="py-1.5 px-2 text-xs">{{ $log->user_name ?? $log->user_id ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="py-4 text-center text-bankos-text-sec">No recent logs.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('loginTrendChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($loginTrend->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))) !!},
                datasets: [{
                    label: 'Logins',
                    data: {!! json_encode($loginTrend->pluck('total')) !!},
                    borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.1)',
                    fill: true, tension: 0.3, pointRadius: 3
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('usersByRoleChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($usersByRole->keys()) !!},
                datasets: [{
                    data: {!! json_encode($usersByRole->values()) !!},
                    backgroundColor: ['#3b82f6','#8b5cf6','#22c55e','#f59e0b','#ef4444','#ec4899','#06b6d4','#f97316'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        new Chart(document.getElementById('auditTrendChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($auditTrend->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))) !!},
                datasets: [{
                    label: 'Actions',
                    data: {!! json_encode($auditTrend->pluck('total')) !!},
                    backgroundColor: '#f59e0b'
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    </script>
    @endpush
</x-app-layout>
