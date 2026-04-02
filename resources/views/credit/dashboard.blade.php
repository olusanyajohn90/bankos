<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Credit & Lending Dashboard</h1>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mt-1">Comprehensive loan portfolio analytics and risk overview</p>
            </div>
        </div>
    </x-slot>

    {{-- ── Filters ────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('credit.dashboard') }}" class="card p-4 flex flex-wrap items-end gap-4 mb-6">
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">Start Date</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="input input-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">End Date</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="input input-sm">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('credit.dashboard') }}" class="btn btn-secondary btn-sm">Reset</a>
    </form>

    {{-- ── Row 1: Primary KPI Cards ──────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        {{-- Total Portfolio --}}
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Loan Portfolio</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($totalPortfolio, 2) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Active + Overdue outstanding</p>
        </div>

        {{-- Active Loans --}}
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Active Loans</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($activeLoansCount) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Currently performing loans</p>
        </div>

        {{-- Overdue Loans --}}
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Overdue Loans</p>
                    <p class="text-2xl font-extrabold text-red-600 dark:text-red-400 mt-1">{{ number_format($overdueLoansCount) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Loans past due date</p>
        </div>

        {{-- NPL Ratio --}}
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">NPL Ratio</p>
                    <p class="text-2xl font-extrabold {{ $nplRatio > 5 ? 'text-red-600' : 'text-bankos-text dark:text-bankos-dark-text' }} mt-1">{{ $nplRatio }}%</p>
                </div>
                <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Non-performing loan ratio</p>
        </div>
    </div>

    {{-- ── Row 2: Secondary KPIs ─────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Avg Loan Size</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($avgLoanSize, 0) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Disbursed This Month</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($disbursementsThisMonth, 0) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-cyan-50 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Collection Rate</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ $collectionRate }}%</p>
                </div>
                <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Repayments vs outstanding this month</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Borrowers</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($totalBorrowers) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 3: Pipeline + IFRS9 + ECL ─────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Pending Applications</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($pendingApps) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Approved (Awaiting Disbursement)</p>
            <p class="text-2xl font-extrabold text-green-600 mt-1">{{ number_format($approvedApps) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Rejected Applications</p>
            <p class="text-2xl font-extrabold text-red-600 mt-1">{{ number_format($rejectedApps) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total ECL Provision</p>
            <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($totalEclProvision, 0) }}</p>
        </div>
    </div>

    {{-- ── IFRS9 Staging ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
        @php
            $stages = [
                'stage_1' => ['label' => 'Stage 1 (Performing)', 'color' => 'green'],
                'stage_2' => ['label' => 'Stage 2 (Watch)', 'color' => 'amber'],
                'stage_3' => ['label' => 'Stage 3 (Impaired)', 'color' => 'red'],
            ];
        @endphp
        @foreach($stages as $key => $meta)
            @php $data = $ifrs9Stages->get($key); @endphp
            <div class="card p-5">
                <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">{{ $meta['label'] }}</p>
                <p class="text-2xl font-extrabold text-{{ $meta['color'] }}-600 mt-1">{{ number_format($data->count ?? 0) }} loans</p>
                <p class="text-sm text-bankos-text-sec mt-1">₦{{ number_format($data->total ?? 0, 0) }} outstanding</p>
            </div>
        @endforeach
    </div>

    {{-- ── Charts Row 1 ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Loans by Status (Pie) --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Loans by Status</h3>
            <canvas id="loansByStatusChart" height="280"></canvas>
        </div>

        {{-- Loans by Product (Bar) --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Loans by Product</h3>
            <canvas id="loansByProductChart" height="280"></canvas>
        </div>
    </div>

    {{-- ── Charts Row 2 ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Disbursement Trend --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Disbursement Trend (12 Months)</h3>
            <canvas id="disbursementTrendChart" height="280"></canvas>
        </div>

        {{-- PAR Analysis --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Portfolio at Risk (PAR) by DPD Bucket</h3>
            <canvas id="parChart" height="280"></canvas>
        </div>
    </div>

    {{-- ── Loan Officer Performance ──────────────────────────────── --}}
    <div class="card p-5 mb-6">
        <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Loan Officer Performance</h3>
        <canvas id="officerChart" height="250"></canvas>
    </div>

    {{-- ── Top 10 Borrowers Table ────────────────────────────────── --}}
    <div class="card p-5 mb-6">
        <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Top 10 Borrowers by Outstanding Balance</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-bankos-border dark:border-bankos-dark-border">
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">#</th>
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">Customer</th>
                        <th class="text-left py-3 px-4 font-semibold text-bankos-text-sec">Customer No.</th>
                        <th class="text-right py-3 px-4 font-semibold text-bankos-text-sec">Loans</th>
                        <th class="text-right py-3 px-4 font-semibold text-bankos-text-sec">Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topBorrowers as $i => $b)
                    <tr class="border-b border-bankos-border/50 dark:border-bankos-dark-border/50 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="py-3 px-4">{{ $i + 1 }}</td>
                        <td class="py-3 px-4 font-medium text-bankos-text dark:text-bankos-dark-text">{{ $b->full_name }}</td>
                        <td class="py-3 px-4 text-bankos-text-sec">{{ $b->customer_number }}</td>
                        <td class="py-3 px-4 text-right">{{ $b->loan_count }}</td>
                        <td class="py-3 px-4 text-right font-semibold">₦{{ number_format($b->total_outstanding, 2) }}</td>
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
        const statusColors = {
            pending: '#f59e0b', approved: '#3b82f6', active: '#10b981',
            overdue: '#ef4444', closed: '#6b7280', written_off: '#991b1b'
        };

        // Loans by Status Pie
        new Chart(document.getElementById('loansByStatusChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($loansByStatus->keys()) !!},
                datasets: [{
                    data: {!! json_encode($loansByStatus->values()) !!},
                    backgroundColor: {!! json_encode($loansByStatus->keys()->map(fn($s) => $statusColors[$s] ?? '#6b7280')->values()) !!},
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Loans by Product Bar
        new Chart(document.getElementById('loansByProductChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($loansByProduct->keys()) !!},
                datasets: [{
                    label: 'Loans',
                    data: {!! json_encode($loansByProduct->values()) !!},
                    backgroundColor: '#3b82f6',
                    borderRadius: 6
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // Disbursement Trend Line
        new Chart(document.getElementById('disbursementTrendChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($disbursementTrend->keys()) !!},
                datasets: [{
                    label: 'Disbursed (₦)',
                    data: {!! json_encode($disbursementTrend->values()) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // PAR Analysis Bar
        new Chart(document.getElementById('parChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($parBuckets)) !!},
                datasets: [{
                    label: 'Outstanding (₦)',
                    data: {!! json_encode(array_values($parBuckets)) !!},
                    backgroundColor: ['#f59e0b', '#f97316', '#ef4444', '#dc2626', '#991b1b'],
                    borderRadius: 6
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // Officer Performance
        new Chart(document.getElementById('officerChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($officerPerformance->pluck('name')) !!},
                datasets: [{
                    label: 'Loans Managed',
                    data: {!! json_encode($officerPerformance->pluck('loan_count')) !!},
                    backgroundColor: '#6366f1',
                    borderRadius: 6
                }, {
                    label: 'Total Disbursed (₦)',
                    data: {!! json_encode($officerPerformance->pluck('total_disbursed')) !!},
                    backgroundColor: '#10b981',
                    borderRadius: 6,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true, position: 'left' },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
