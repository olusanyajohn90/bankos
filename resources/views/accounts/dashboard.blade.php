<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Accounts Dashboard</h1>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mt-1">Account portfolio analytics, deposit trends and balance distribution</p>
            </div>
        </div>
    </x-slot>

    {{-- ── Filters ────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('accounts.dashboard') }}" class="card p-4 flex flex-wrap items-end gap-4 mb-6">
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">Start Date</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="input input-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">End Date</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="input input-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">Account Type</label>
            <select name="account_type" class="input input-sm">
                <option value="">All Types</option>
                <option value="savings" {{ $filterType == 'savings' ? 'selected' : '' }}>Savings</option>
                <option value="current" {{ $filterType == 'current' ? 'selected' : '' }}>Current</option>
                <option value="fixed_deposit" {{ $filterType == 'fixed_deposit' ? 'selected' : '' }}>Fixed Deposit</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">Branch</label>
            <select name="branch_id" class="input input-sm">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $filterBranch == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('accounts.dashboard') }}" class="btn btn-secondary btn-sm">Reset</a>
    </form>

    {{-- ── Row 1: Primary KPI Cards ──────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Accounts</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($totalAccounts) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ number_format($activeAccounts) }} active</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Deposits</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($totalDeposits, 2) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Available balance sum</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Average Balance</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($avgBalance, 0) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Opened This Month</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($openedThisMonth) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-cyan-50 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 2: Secondary KPIs ─────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-5 mb-6">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Dormant (90d)</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($dormantAccounts) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Frozen</p>
            <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ number_format($frozenAccounts) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Closed (Month)</p>
            <p class="text-2xl font-extrabold text-red-600 mt-1">{{ number_format($closedThisMonth) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">PND Active</p>
            <p class="text-2xl font-extrabold text-orange-600 mt-1">{{ number_format($pndAccounts) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Ledger Balance</p>
            <p class="text-lg font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($totalLedgerBalance, 0) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Active Accounts</p>
            <p class="text-2xl font-extrabold text-green-600 mt-1">{{ number_format($activeAccounts) }}</p>
        </div>
    </div>

    {{-- ── Charts Row 1 ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Accounts by Type</h3>
            <canvas id="accountTypeChart" height="280"></canvas>
        </div>

        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Accounts by Status</h3>
            <canvas id="accountStatusChart" height="280"></canvas>
        </div>
    </div>

    {{-- ── Charts Row 2 ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Account Opening Trend (12 Months)</h3>
            <canvas id="openingTrendChart" height="280"></canvas>
        </div>

        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Balance Distribution</h3>
            <canvas id="balanceDistChart" height="280"></canvas>
        </div>
    </div>

    {{-- ── Accounts by Branch ────────────────────────────────────── --}}
    <div class="card p-5 mb-6">
        <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Accounts by Branch</h3>
        <canvas id="branchAccountChart" height="250"></canvas>
    </div>

    {{-- ── Top 10 Accounts Table ─────────────────────────────────── --}}
    <div class="card p-5 mb-6">
        <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Top 10 Accounts by Balance</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-bankos-border dark:border-bankos-dark-border">
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">#</th>
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">Account No.</th>
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">Customer</th>
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">Type</th>
                        <th class="text-right py-3 px-4 font-semibold text-bankos-text-sec">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topAccounts as $i => $a)
                    <tr class="border-b border-bankos-border/50 dark:border-bankos-dark-border/50 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="py-3 px-4">{{ $i + 1 }}</td>
                        <td class="py-3 px-4 font-mono text-sm">{{ $a->account_number }}</td>
                        <td class="py-3 px-4 font-medium text-bankos-text dark:text-bankos-dark-text">{{ $a->customer_name }}</td>
                        <td class="py-3 px-4"><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ ucfirst($a->type) }}</span></td>
                        <td class="py-3 px-4 text-right font-semibold">₦{{ number_format($a->available_balance, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-6 text-center text-bankos-text-sec">No data available</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        const typeColors = { savings: '#3b82f6', current: '#10b981', fixed_deposit: '#f59e0b', fixed: '#f59e0b' };
        const statusColors = { active: '#10b981', dormant: '#f59e0b', closed: '#6b7280', frozen: '#3b82f6' };

        // Accounts by Type Pie
        new Chart(document.getElementById('accountTypeChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($accountsByType->keys()->map(fn($t) => ucfirst(str_replace('_', ' ', $t)))) !!},
                datasets: [{
                    data: {!! json_encode($accountsByType->values()) !!},
                    backgroundColor: {!! json_encode($accountsByType->keys()->map(fn($t) => $typeColors[$t] ?? '#6b7280')->values()) !!},
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Accounts by Status Bar
        new Chart(document.getElementById('accountStatusChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($accountsByStatus->keys()->map(fn($s) => ucfirst($s))) !!},
                datasets: [{
                    label: 'Accounts',
                    data: {!! json_encode($accountsByStatus->values()) !!},
                    backgroundColor: {!! json_encode($accountsByStatus->keys()->map(fn($s) => $statusColors[$s] ?? '#6b7280')->values()) !!},
                    borderRadius: 6
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // Opening Trend Line
        new Chart(document.getElementById('openingTrendChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($openingTrend->keys()) !!},
                datasets: [{
                    label: 'Accounts Opened',
                    data: {!! json_encode($openingTrend->values()) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // Balance Distribution Bar
        new Chart(document.getElementById('balanceDistChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($sortedBalanceDistribution->keys()) !!},
                datasets: [{
                    label: 'Accounts',
                    data: {!! json_encode($sortedBalanceDistribution->values()) !!},
                    backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444'],
                    borderRadius: 6
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // Accounts by Branch
        new Chart(document.getElementById('branchAccountChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($accountsByBranch->keys()) !!},
                datasets: [{
                    label: 'Accounts',
                    data: {!! json_encode($accountsByBranch->values()) !!},
                    backgroundColor: '#6366f1',
                    borderRadius: 6
                }]
            },
            options: { responsive: true, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });
    </script>
    @endpush
</x-app-layout>
