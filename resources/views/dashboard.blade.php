<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
            {{ __('Command Center') }}
        </h2>
        <p class="text-sm text-bankos-text-sec mt-1">Real-time metrics, portfolio performance & module overview</p>
    </x-slot>

    {{-- Row 1 — Primary KPI Cards (6) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-5 mb-8">

        {{-- Total Customers --}}
        <a href="{{ route('customers.index') }}" class="card p-5 flex flex-col justify-between hover:shadow-md transition-shadow group border-l-4 border-l-blue-500">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                @if($customerGrowth > 0)
                    <span class="text-xs font-semibold text-green-600 bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-full flex items-center gap-0.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        {{ $customerGrowth }}%
                    </span>
                @elseif($customerGrowth < 0)
                    <span class="text-xs font-semibold text-red-600 bg-red-50 dark:bg-red-900/20 px-2 py-0.5 rounded-full flex items-center gap-0.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        {{ abs($customerGrowth) }}%
                    </span>
                @endif
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Customers</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ number_format($totalCustomers) }}</h3>
            <p class="text-xs text-bankos-muted mt-1">Monthly growth</p>
        </a>

        {{-- Total Deposits --}}
        <a href="{{ route('accounts.index') }}" class="card p-5 flex flex-col justify-between hover:shadow-md transition-shadow group border-l-4 border-l-indigo-500">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                </div>
                @if($depositTrend >= 0)
                    <span class="text-xs font-semibold text-green-600 bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-full flex items-center gap-0.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        {{ $depositTrend }}%
                    </span>
                @else
                    <span class="text-xs font-semibold text-red-600 bg-red-50 dark:bg-red-900/20 px-2 py-0.5 rounded-full flex items-center gap-0.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        {{ abs($depositTrend) }}%
                    </span>
                @endif
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Total Deposits</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">
                @if($totalDeposits >= 1_000_000_000) ₦{{ number_format($totalDeposits / 1_000_000_000, 2) }}B
                @elseif($totalDeposits >= 1_000_000) ₦{{ number_format($totalDeposits / 1_000_000, 2) }}M
                @else ₦{{ number_format($totalDeposits, 0) }} @endif
            </h3>
            <p class="text-xs text-bankos-muted mt-1">vs last month</p>
        </a>

        {{-- Loan Portfolio --}}
        <a href="{{ route('loans.index') }}" class="card p-5 flex flex-col justify-between hover:shadow-md transition-shadow group border-l-4 border-l-purple-500">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <span class="text-xs font-semibold {{ $par30 >= 5 ? 'text-red-600 bg-red-50 dark:bg-red-900/20' : 'text-green-600 bg-green-50 dark:bg-green-900/20' }} px-2 py-0.5 rounded-full">
                    PAR {{ number_format($par30, 1) }}%
                </span>
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Loan Portfolio</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">
                @if($loanPortfolio >= 1_000_000_000) ₦{{ number_format($loanPortfolio / 1_000_000_000, 2) }}B
                @elseif($loanPortfolio >= 1_000_000) ₦{{ number_format($loanPortfolio / 1_000_000, 2) }}M
                @else ₦{{ number_format($loanPortfolio, 0) }} @endif
            </h3>
            <p class="text-xs text-bankos-muted mt-1">PAR &gt; 30 Days</p>
        </a>

        {{-- Monthly Revenue --}}
        <div class="card p-5 flex flex-col justify-between border-l-4 border-l-emerald-500">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                </div>
                @if($revenueTrend >= 0)
                    <span class="text-xs font-semibold text-green-600 bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-full flex items-center gap-0.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        {{ $revenueTrend }}%
                    </span>
                @else
                    <span class="text-xs font-semibold text-red-600 bg-red-50 dark:bg-red-900/20 px-2 py-0.5 rounded-full flex items-center gap-0.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        {{ abs($revenueTrend) }}%
                    </span>
                @endif
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Monthly Revenue</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">
                @if($monthlyRevenue >= 1_000_000) ₦{{ number_format($monthlyRevenue / 1_000_000, 2) }}M
                @else ₦{{ number_format($monthlyRevenue, 0) }} @endif
            </h3>
            <p class="text-xs text-bankos-muted mt-1">Fees + Interest</p>
        </div>

        {{-- Active Accounts --}}
        <a href="{{ route('accounts.index') }}" class="card p-5 flex flex-col justify-between hover:shadow-md transition-shadow group border-l-4 border-l-cyan-500">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-cyan-50 dark:bg-cyan-900/20 text-cyan-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Active Accounts</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-text dark:text-white">{{ number_format($totalAccounts) }}</h3>
            <p class="text-xs text-bankos-muted mt-1">All active accounts</p>
        </a>

        {{-- Pending Actions --}}
        <div class="card p-5 flex flex-col justify-between border-l-4 border-l-amber-500">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                @if($pendingActions > 0)
                    <span class="text-xs font-semibold text-amber-600 bg-amber-50 dark:bg-amber-900/20 px-2 py-0.5 rounded-full">Needs attention</span>
                @endif
            </div>
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Pending Actions</p>
            <h3 class="text-2xl font-bold mt-1 {{ $pendingActions > 0 ? 'text-amber-600' : 'text-bankos-text dark:text-white' }}">{{ number_format($pendingActions) }}</h3>
            <p class="text-xs text-bankos-muted mt-1">KYC {{ $pendingKyc }} / Loans {{ $pendingLoans }} / Disputes {{ $pendingDisputes }}</p>
        </div>
    </div>

    {{-- Row 2 — Charts (2 columns) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Deposit vs Loan Trend --}}
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-1 text-bankos-text dark:text-white">Deposit vs Loan Trend</h3>
            <p class="text-xs text-bankos-muted mb-4">Last 6 months (in millions)</p>
            <div class="relative h-72 w-full">
                <canvas id="depositLoanChart"></canvas>
            </div>
        </div>

        {{-- Transaction Volume by Type --}}
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-1 text-bankos-text dark:text-white">Transaction Volume by Type</h3>
            <p class="text-xs text-bankos-muted mb-4">This month</p>
            <div class="relative h-72 w-full">
                <canvas id="txnTypeChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Row 3 — Activity Feed + Quick Stats --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Recent Transactions (2/3) --}}
        <div class="lg:col-span-2 card p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg text-bankos-text dark:text-white">Recent Transactions</h3>
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
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ in_array($txn->type, ['deposit', 'repayment']) ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : '' }}
                                    {{ in_array($txn->type, ['withdrawal', 'disbursement']) ? 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' : '' }}
                                    {{ in_array($txn->type, ['transfer']) ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' : '' }}
                                    {{ !in_array($txn->type, ['deposit','repayment','withdrawal','disbursement','transfer']) ? 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-400' : '' }}
                                ">{{ ucfirst(str_replace('_', ' ', $txn->type)) }}</span>
                            </td>
                            <td class="py-3 px-4 font-bold text-right {{ in_array($txn->type, ['deposit', 'repayment']) ? 'text-green-600' : 'text-bankos-text dark:text-gray-300' }}">
                                {{ in_array($txn->type, ['deposit', 'repayment']) ? '+' : '-' }}₦{{ number_format($txn->amount, 2) }}
                            </td>
                            <td class="py-3 pl-4 text-right">
                                <span class="badge {{ $txn->status === 'success' ? 'badge-active' : ($txn->status === 'pending' ? 'badge-pending' : 'badge-danger') }}">
                                    {{ ucfirst($txn->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-8 text-center text-bankos-muted">No transactions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Quick Stats (1/3) --}}
        <div class="space-y-4">
            <div class="card p-5 border-l-4 border-l-blue-400">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-bankos-muted uppercase font-semibold">Today's Transactions</p>
                        <div class="flex items-baseline gap-2">
                            <span class="text-xl font-bold text-bankos-text dark:text-white">{{ number_format($todayTxnCount) }}</span>
                            <span class="text-xs text-bankos-text-sec">
                                @if($todayTxnVolume >= 1_000_000) ₦{{ number_format($todayTxnVolume / 1_000_000, 1) }}M
                                @else ₦{{ number_format($todayTxnVolume, 0) }} @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-5 border-l-4 border-l-green-400">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-bankos-muted uppercase font-semibold">New Customers</p>
                        <span class="text-xl font-bold text-bankos-text dark:text-white">{{ number_format($newCustomersMonth) }}</span>
                        <span class="text-xs text-bankos-text-sec ml-1">this month</span>
                    </div>
                </div>
            </div>

            <div class="card p-5 border-l-4 border-l-purple-400">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-bankos-muted uppercase font-semibold">Loans Disbursed</p>
                        <div class="flex items-baseline gap-2">
                            <span class="text-xl font-bold text-bankos-text dark:text-white">{{ number_format($loansDisbursedCount) }}</span>
                            <span class="text-xs text-bankos-text-sec">
                                @if($loansDisbursedAmount >= 1_000_000) ₦{{ number_format($loansDisbursedAmount / 1_000_000, 1) }}M
                                @else ₦{{ number_format($loansDisbursedAmount, 0) }} @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-5 border-l-4 border-l-red-400">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-bankos-muted uppercase font-semibold">Overdue Loans</p>
                        <div class="flex items-baseline gap-2">
                            <span class="text-xl font-bold {{ $overdueLoansCount > 0 ? 'text-red-600' : 'text-bankos-text dark:text-white' }}">{{ number_format($overdueLoansCount) }}</span>
                            <span class="text-xs text-bankos-text-sec">
                                @if($overdueLoansAmount >= 1_000_000) ₦{{ number_format($overdueLoansAmount / 1_000_000, 1) }}M
                                @else ₦{{ number_format($overdueLoansAmount, 0) }} @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 4 — Module Summaries --}}
    <h3 class="font-bold text-lg text-bankos-text dark:text-white mb-4">Module Overview</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-4 mb-8">

        {{-- Projects --}}
        <a href="{{ route('projects.index') }}" class="card p-4 text-center hover:shadow-md transition-shadow group hover:border-blue-300 dark:hover:border-blue-700">
            <div class="w-8 h-8 mx-auto mb-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            </div>
            <p class="text-xs font-semibold text-bankos-text dark:text-white">Projects</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $modules['projects']['active'] }} active</p>
            <p class="text-xs text-bankos-muted">{{ $modules['projects']['pending_tasks'] }} tasks pending</p>
        </a>

        {{-- Marketing --}}
        <a href="{{ route('marketing.index') }}" class="card p-4 text-center hover:shadow-md transition-shadow group hover:border-pink-300 dark:hover:border-pink-700">
            <div class="w-8 h-8 mx-auto mb-2 rounded-lg bg-pink-50 dark:bg-pink-900/20 text-pink-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <p class="text-xs font-semibold text-bankos-text dark:text-white">Marketing</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $modules['marketing']['campaigns'] }} campaigns</p>
            <p class="text-xs text-bankos-muted">{{ $modules['marketing']['delivery_rate'] }}% delivery</p>
        </a>

        {{-- Chat --}}
        <a href="{{ route('chat.index') }}" class="card p-4 text-center hover:shadow-md transition-shadow group hover:border-teal-300 dark:hover:border-teal-700">
            <div class="w-8 h-8 mx-auto mb-2 rounded-lg bg-teal-50 dark:bg-teal-900/20 text-teal-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <p class="text-xs font-semibold text-bankos-text dark:text-white">Chat</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $modules['chat']['unread'] }} recent</p>
        </a>

        {{-- Calendar --}}
        <a href="{{ route('calendar.index') }}" class="card p-4 text-center hover:shadow-md transition-shadow group hover:border-orange-300 dark:hover:border-orange-700">
            <div class="w-8 h-8 mx-auto mb-2 rounded-lg bg-orange-50 dark:bg-orange-900/20 text-orange-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <p class="text-xs font-semibold text-bankos-text dark:text-white">Calendar</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $modules['calendar']['upcoming'] }} upcoming</p>
        </a>

        {{-- Insurance --}}
        <a href="{{ route('insurance.index') }}" class="card p-4 text-center hover:shadow-md transition-shadow group hover:border-violet-300 dark:hover:border-violet-700">
            <div class="w-8 h-8 mx-auto mb-2 rounded-lg bg-violet-50 dark:bg-violet-900/20 text-violet-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <p class="text-xs font-semibold text-bankos-text dark:text-white">Insurance</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $modules['insurance']['active'] }} policies</p>
            <p class="text-xs text-bankos-muted">
                @if($modules['insurance']['coverage'] >= 1_000_000) ₦{{ number_format($modules['insurance']['coverage'] / 1_000_000, 1) }}M
                @else ₦{{ number_format($modules['insurance']['coverage'], 0) }} @endif
            </p>
        </a>

        {{-- Support --}}
        <a href="{{ route('support.dashboard') }}" class="card p-4 text-center hover:shadow-md transition-shadow group hover:border-yellow-300 dark:hover:border-yellow-700">
            <div class="w-8 h-8 mx-auto mb-2 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <p class="text-xs font-semibold text-bankos-text dark:text-white">Support</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $modules['support']['open_tickets'] }} open tickets</p>
        </a>

        {{-- HR --}}
        <a href="{{ route('hr.dashboard') }}" class="card p-4 text-center hover:shadow-md transition-shadow group hover:border-lime-300 dark:hover:border-lime-700">
            <div class="w-8 h-8 mx-auto mb-2 rounded-lg bg-lime-50 dark:bg-lime-900/20 text-lime-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <p class="text-xs font-semibold text-bankos-text dark:text-white">HR</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $modules['hr']['employees'] }} staff</p>
            <p class="text-xs text-bankos-muted">{{ $modules['hr']['on_leave'] }} on leave</p>
        </a>

        {{-- Cortex AI --}}
        <a href="{{ route('cortex.dashboard') }}" class="card p-4 text-center hover:shadow-md transition-shadow group hover:border-fuchsia-300 dark:hover:border-fuchsia-700">
            <div class="w-8 h-8 mx-auto mb-2 rounded-lg bg-fuchsia-50 dark:bg-fuchsia-900/20 text-fuchsia-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a4 4 0 0 1 4 4c0 1.95-1.4 3.57-3.25 3.92L12 22"/><path d="M12 2a4 4 0 0 0-4 4c0 1.95 1.4 3.57 3.25 3.92"/><path d="M8.56 13a8 8 0 0 0-2.56 5.84"/><path d="M15.44 13a8 8 0 0 1 2.56 5.84"/></svg>
            </div>
            <p class="text-xs font-semibold text-bankos-text dark:text-white">Cortex AI</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $modules['cortex']['analyses'] }} analyses</p>
            <p class="text-xs text-bankos-muted">{{ $modules['cortex']['alerts'] }} alerts</p>
        </a>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
        const textColor = isDark ? '#94A3B8' : '#64748B';

        Chart.defaults.color = textColor;
        Chart.defaults.borderColor = gridColor;

        // Deposit vs Loan Trend (line chart)
        const dlData = @json($depositLoanTrend);
        new Chart(document.getElementById('depositLoanChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: dlData.labels,
                datasets: [
                    {
                        label: 'Deposits (₦M)',
                        data: dlData.deposits,
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37,99,235,0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: '#2563EB',
                    },
                    {
                        label: 'Loans Disbursed (₦M)',
                        data: dlData.loans,
                        borderColor: '#7C3AED',
                        backgroundColor: 'rgba(124,58,237,0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: '#7C3AED',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 4], color: gridColor } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Transaction Volume by Type (doughnut chart)
        const txnData = @json($txnByType);
        const pieColors = ['#2563EB', '#7C3AED', '#10B981', '#F59E0B', '#EF4444', '#06B6D4', '#EC4899', '#8B5CF6'];
        new Chart(document.getElementById('txnTypeChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: txnData.labels.length ? txnData.labels : ['No transactions'],
                datasets: [{
                    data: txnData.data.length ? txnData.data : [1],
                    backgroundColor: pieColors.slice(0, txnData.labels.length || 1),
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: { legend: { position: 'right' } }
            }
        });
    });
    </script>
</x-app-layout>
