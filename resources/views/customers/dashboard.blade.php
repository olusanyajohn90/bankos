<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Customers Dashboard</h1>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mt-1">Customer base analytics, demographics and growth trends</p>
            </div>
        </div>
    </x-slot>

    {{-- ── Filters ────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('customers.dashboard') }}" class="card p-4 flex flex-wrap items-end gap-4 mb-6">
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">Start Date</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="input input-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">End Date</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="input input-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">Gender</label>
            <select name="gender" class="input input-sm">
                <option value="">All</option>
                <option value="male" {{ $filterGender == 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ $filterGender == 'female' ? 'selected' : '' }}>Female</option>
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
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">KYC Tier</label>
            <select name="kyc_tier" class="input input-sm">
                <option value="">All Tiers</option>
                <option value="level_1" {{ $filterKycTier == 'level_1' ? 'selected' : '' }}>Level 1</option>
                <option value="level_2" {{ $filterKycTier == 'level_2' ? 'selected' : '' }}>Level 2</option>
                <option value="level_3" {{ $filterKycTier == 'level_3' ? 'selected' : '' }}>Level 3</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('customers.dashboard') }}" class="btn btn-secondary btn-sm">Reset</a>
    </form>

    {{-- ── Row 1: Primary KPI Cards ──────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Customers</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($totalCustomers) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
            </div>
            <p class="text-xs mt-2 {{ $monthlyGrowth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $monthlyGrowth >= 0 ? '+' : '' }}{{ $monthlyGrowth }}% vs last month
            </p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">New This Month</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($customersThisMonth) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $newCustomersToday }} registered today</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Avg Balance / Customer</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($avgBalancePerCustomer, 0) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">KYC Completion</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ $kycCompletionRate }}%</p>
                </div>
                <div class="p-3 rounded-lg bg-cyan-50 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Level 2 or 3 KYC verified</p>
        </div>
    </div>

    {{-- ── Row 2: Status Cards ───────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Active</p>
            <p class="text-2xl font-extrabold text-green-600 mt-1">{{ number_format($activeCustomers) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Inactive</p>
            <p class="text-2xl font-extrabold text-gray-500 mt-1">{{ number_format($inactiveCustomers) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Dormant</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($dormantCustomers) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Blacklisted</p>
            <p class="text-2xl font-extrabold text-red-600 mt-1">{{ number_format($blacklistedCustomers) }}</p>
        </div>
    </div>

    {{-- ── Charts Row 1 ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Customers by Gender</h3>
            <canvas id="genderChart" height="280"></canvas>
        </div>

        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Customers by Age Group</h3>
            <canvas id="ageChart" height="280"></canvas>
        </div>
    </div>

    {{-- ── Charts Row 2 ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">KYC Tier Distribution</h3>
            <canvas id="kycChart" height="280"></canvas>
        </div>

        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Customer Type Distribution</h3>
            <canvas id="typeChart" height="280"></canvas>
        </div>
    </div>

    {{-- ── Charts Row 3 ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">New Customer Acquisition (12 Months)</h3>
            <canvas id="acquisitionChart" height="280"></canvas>
        </div>

        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Customers by Branch</h3>
            <canvas id="branchChart" height="220"></canvas>
        </div>
    </div>

    {{-- ── Top 10 Customers Table ────────────────────────────────── --}}
    <div class="card p-5 mb-6">
        <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Top 10 Customers by Total Balance</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-bankos-border dark:border-bankos-dark-border">
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">#</th>
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">Customer</th>
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">Customer No.</th>
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">Status</th>
                        <th class="text-right py-3 px-4 font-semibold text-bankos-text-sec">Accounts</th>
                        <th class="text-right py-3 px-4 font-semibold text-bankos-text-sec">Total Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCustomers as $i => $c)
                    <tr class="border-b border-bankos-border/50 dark:border-bankos-dark-border/50 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="py-3 px-4">{{ $i + 1 }}</td>
                        <td class="py-3 px-4 font-medium text-bankos-text dark:text-bankos-dark-text">{{ $c->full_name }}</td>
                        <td class="py-3 px-4 text-bankos-text-sec">{{ $c->customer_number }}</td>
                        <td class="py-3 px-4"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $c->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($c->status) }}</span></td>
                        <td class="py-3 px-4 text-right">{{ $c->account_count }}</td>
                        <td class="py-3 px-4 text-right font-semibold">₦{{ number_format($c->total_balance, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-6 text-center text-bankos-text-sec">No data available</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        const palette = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#f97316'];

        // Gender Pie
        new Chart(document.getElementById('genderChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($customersByGender->keys()->map(fn($g) => ucfirst($g))) !!},
                datasets: [{
                    data: {!! json_encode($customersByGender->values()) !!},
                    backgroundColor: ['#3b82f6', '#ec4899', '#6b7280'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Age Group Bar
        new Chart(document.getElementById('ageChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($sortedAgeGroups->keys()) !!},
                datasets: [{
                    label: 'Customers',
                    data: {!! json_encode($sortedAgeGroups->values()) !!},
                    backgroundColor: '#6366f1',
                    borderRadius: 6
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // KYC Tier Doughnut
        new Chart(document.getElementById('kycChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($customersByKyc->keys()->map(fn($k) => str_replace('_', ' ', ucfirst($k)))) !!},
                datasets: [{
                    data: {!! json_encode($customersByKyc->values()) !!},
                    backgroundColor: ['#f59e0b', '#3b82f6', '#10b981'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Customer Type Pie
        new Chart(document.getElementById('typeChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($customersByType->keys()->map(fn($t) => ucfirst($t))) !!},
                datasets: [{
                    data: {!! json_encode($customersByType->values()) !!},
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Acquisition Trend Line
        new Chart(document.getElementById('acquisitionChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($acquisitionTrend->keys()) !!},
                datasets: [{
                    label: 'New Customers',
                    data: {!! json_encode($acquisitionTrend->values()) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // By Branch Bar
        new Chart(document.getElementById('branchChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($customersByBranch->keys()) !!},
                datasets: [{
                    label: 'Customers',
                    data: {!! json_encode($customersByBranch->values()) !!},
                    backgroundColor: '#10b981',
                    borderRadius: 6
                }]
            },
            options: { responsive: true, maintainAspectRatio: true, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });
    </script>
    @endpush
</x-app-layout>
