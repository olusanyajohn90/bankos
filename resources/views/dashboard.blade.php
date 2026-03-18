<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
            {{ __('Dashboard Overview') }}
        </h2>
        <p class="text-sm text-bankos-text-sec mt-1">Real-time metrics and portfolio performance</p>
    </x-slot>

    <!-- Top KPI Row (4 Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <!-- Total Customers -->
        <a href="{{ route('customers.index') }}" class="card p-6 flex flex-col justify-between hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-bankos-primary flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-bankos-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-bankos-text-sec uppercase tracking-wider">Total Customers</p>
                <h3 class="text-3xl font-bold mt-1 text-bankos-text dark:text-white">{{ number_format($totalCustomers) }}</h3>
                <p class="text-xs text-bankos-muted mt-2">{{ number_format($totalAccounts) }} active accounts</p>
            </div>
        </a>

        <!-- Total Deposits -->
        <a href="{{ route('accounts.index') }}" class="card p-6 flex flex-col justify-between border-t-4 border-t-accent-indigo hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-accent-indigo flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-accent-indigo transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-bankos-text-sec uppercase tracking-wider">Total Deposits</p>
                <h3 class="text-3xl font-bold mt-1 text-bankos-text dark:text-white">
                    @if($totalDeposits >= 1_000_000_000)
                        ₦{{ number_format($totalDeposits / 1_000_000_000, 2) }}B
                    @elseif($totalDeposits >= 1_000_000)
                        ₦{{ number_format($totalDeposits / 1_000_000, 2) }}M
                    @else
                        ₦{{ number_format($totalDeposits, 0) }}
                    @endif
                </h3>
                <p class="text-xs text-bankos-muted mt-2">Across {{ number_format($totalAccounts) }} active accounts</p>
            </div>
        </a>

        <!-- Loan Portfolio -->
        <a href="{{ route('loans.index') }}" class="card p-6 flex flex-col justify-between border-t-4 border-t-accent-purple hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-accent-purple flex items-center justify-center group-hover:bg-purple-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-accent-purple transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-bankos-text-sec uppercase tracking-wider">Loan Portfolio</p>
                <h3 class="text-3xl font-bold mt-1 text-bankos-text dark:text-white">
                    @if($loanPortfolio >= 1_000_000_000)
                        ₦{{ number_format($loanPortfolio / 1_000_000_000, 2) }}B
                    @elseif($loanPortfolio >= 1_000_000)
                        ₦{{ number_format($loanPortfolio / 1_000_000, 2) }}M
                    @else
                        ₦{{ number_format($loanPortfolio, 0) }}
                    @endif
                </h3>
                <p class="text-xs text-bankos-muted mt-2">PAR &gt; 30 Days: {{ number_format($par30, 1) }}%</p>
            </div>
        </a>

        <!-- NPL Ratio -->
        <a href="{{ route('loans.index') }}?status=overdue" class="card p-6 flex flex-col justify-between border-t-4 border-t-accent-crimson hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-lg bg-red-50 dark:bg-red-900/20 text-accent-crimson flex items-center justify-center group-hover:bg-red-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </div>
                @if($nplRatio >= 5)
                <span class="badge badge-danger text-xs px-2 py-1">Above threshold</span>
                @endif
            </div>
            <div>
                <p class="text-sm font-medium text-bankos-text-sec uppercase tracking-wider">NPL Ratio (Risk)</p>
                <h3 class="text-3xl font-bold mt-1 {{ $nplRatio >= 5 ? 'text-accent-crimson' : 'text-bankos-text dark:text-white' }}">
                    {{ number_format($nplRatio, 1) }}%
                </h3>
                <p class="text-xs text-bankos-muted mt-2">Target threshold: &lt; 5.0%</p>
            </div>
        </a>
    </div>

    <!-- Secondary KPI Row (6 smaller cards) -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
        <a href="{{ route('transactions.index') }}" class="card p-4 text-center hover:shadow-md transition-shadow group">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Today's Txns</p>
            <h4 class="text-xl font-bold mt-2 group-hover:text-bankos-primary transition-colors">{{ number_format($todayTxnCount) }}</h4>
        </a>
        <a href="{{ route('transactions.index') }}" class="card p-4 text-center hover:shadow-md transition-shadow group">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Txn Volume</p>
            <h4 class="text-xl font-bold mt-2 group-hover:text-bankos-primary transition-colors">
                @if($todayTxnVolume >= 1_000_000)
                    ₦{{ number_format($todayTxnVolume / 1_000_000, 1) }}M
                @else
                    ₦{{ number_format($todayTxnVolume, 0) }}
                @endif
            </h4>
        </a>
        <a href="{{ route('customers.index') }}?kyc_status=manual_review" class="card p-4 text-center hover:shadow-md transition-shadow group">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Pending KYC</p>
            <h4 class="text-xl font-bold mt-2 {{ $pendingKyc > 0 ? 'text-bankos-warning' : '' }} group-hover:text-bankos-primary transition-colors">{{ number_format($pendingKyc) }}</h4>
        </a>
        <a href="{{ route('loan-applications.index') }}" class="card p-4 text-center hover:shadow-md transition-shadow group">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Loans Awaiting</p>
            <h4 class="text-xl font-bold mt-2 {{ $pendingLoans > 0 ? 'text-accent-indigo' : '' }} group-hover:text-bankos-primary transition-colors">{{ number_format($pendingLoans) }}</h4>
        </a>
        <a href="{{ route('agents.index') }}" class="card p-4 text-center hover:shadow-md transition-shadow group">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Active Agents</p>
            <h4 class="text-xl font-bold mt-2 group-hover:text-bankos-primary transition-colors">{{ number_format($activeAgents) }}</h4>
        </a>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">System Status</p>
            <h4 class="text-xl font-bold mt-2 text-bankos-success flex items-center justify-center gap-1">
                <div class="w-2 h-2 bg-bankos-success rounded-full"></div> Stable
            </h4>
        </div>
    </div>

    <!-- Charts Area -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Chart 1: Deposits vs Disbursed (MTD) -->
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-4">Cashflow: Deposits vs Disbursals (MTD)</h3>
            <div class="relative h-64 w-full">
                <canvas id="cashflowChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: PAR Trend -->
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-4">Portfolio at Risk — Last 6 Months</h3>
            <div class="relative h-64 w-full">
                <canvas id="parChart"></canvas>
            </div>
        </div>

        <!-- Chart 3: Branch Float Distribution -->
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-4">Branch Float Distribution (₦M)</h3>
            <div class="relative h-64 w-full">
                <canvas id="branchChart"></canvas>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg">Recent Transactions</h3>
                <a href="{{ route('transactions.index') }}" class="text-sm font-medium text-bankos-primary hover:underline">View All</a>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-muted">
                            <th class="pb-3 pr-4 font-semibold">Reference</th>
                            <th class="pb-3 px-4 font-semibold">Type</th>
                            <th class="pb-3 px-4 font-semibold text-right">Amount</th>
                            <th class="pb-3 pl-4 font-semibold text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($recentTransactions as $txn)
                        <tr class="border-b border-bankos-border dark:border-bankos-dark-border last:border-0 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50 transition-colors">
                            <td class="py-3 pr-4 font-medium text-bankos-primary font-mono text-xs">{{ $txn->reference }}</td>
                            <td class="py-3 px-4 text-bankos-text-sec capitalize">{{ str_replace('_', ' ', $txn->type) }}</td>
                            <td class="py-3 px-4 font-bold text-right {{ in_array($txn->type, ['deposit', 'repayment']) ? 'text-bankos-success' : 'text-bankos-text dark:text-gray-300' }}">
                                {{ in_array($txn->type, ['deposit', 'repayment']) ? '+' : '-' }}₦{{ number_format($txn->amount, 2) }}
                            </td>
                            <td class="py-3 pl-4 text-right">
                                <span class="badge {{ $txn->status === 'success' ? 'badge-active' : ($txn->status === 'pending' ? 'badge-pending' : 'badge-danger') }}">
                                    {{ ucfirst($txn->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-bankos-muted">No transactions yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cashflowData = @json($cashflowData);
            const parTrendData = @json($parTrend);
            const branchData   = @json($branchPerformance);

            // Cashflow Chart
            new Chart(document.getElementById('cashflowChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: cashflowData.labels.length ? cashflowData.labels : ['No data'],
                    datasets: [
                        {
                            label: 'Deposits (₦M)',
                            data: cashflowData.deposits,
                            backgroundColor: '#2563EB',
                            borderRadius: 4
                        },
                        {
                            label: 'Disbursals (₦M)',
                            data: cashflowData.disbursals,
                            backgroundColor: '#7C3AED',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4], color: '#E5E7EB' } } }
                }
            });

            // PAR Trend Chart
            new Chart(document.getElementById('parChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: parTrendData.labels,
                    datasets: [
                        {
                            label: 'PAR > 30 (%)',
                            data: parTrendData.par30,
                            borderColor: '#F59E0B',
                            backgroundColor: 'rgba(245,158,11,0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'NPL / PAR > 90 (%)',
                            data: parTrendData.par90,
                            borderColor: '#DC2626',
                            backgroundColor: 'rgba(220,38,38,0.05)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4], color: '#E5E7EB' } } }
                }
            });

            // Branch Chart
            new Chart(document.getElementById('branchChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: branchData.labels.length ? branchData.labels : ['No branches'],
                    datasets: [{
                        data: branchData.volumes.length ? branchData.volumes : [1],
                        backgroundColor: ['#2563EB', '#4F46E5', '#7C3AED', '#10B981', '#F59E0B', '#EF4444'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { position: 'right' } }
                }
            });
        });
    </script>
</x-app-layout>
