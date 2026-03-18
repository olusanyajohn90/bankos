<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Savings Interest Accrual Report</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Projected interest liability on active savings accounts as of {{ \Carbon\Carbon::parse($asOfDate)->format('d M Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="flex justify-between items-center mb-6">
        <form method="GET" action="{{ route('reports.savings-interest') }}" class="flex items-center gap-4 bg-white dark:bg-bankos-dark-bg p-2 rounded-lg shadow-sm border border-bankos-border dark:border-bankos-dark-border">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec ml-2">As of Date</label>
                <input type="date" name="as_of_date" value="{{ $asOfDate }}" class="form-input text-sm border-none shadow-none focus:ring-bankos-primary">
            </div>
            <button type="submit" class="btn btn-primary text-sm py-2 px-4">Run</button>
        </form>

        <button class="btn btn-secondary text-sm flex items-center gap-2 print:hidden bg-white shadow-sm" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Report
        </button>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card p-6 border-t-4 border-t-bankos-primary shadow-md">
            <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Total Deposits (Interest-Bearing)</p>
            <p class="text-3xl font-extrabold text-bankos-text mt-2">₦{{ number_format($totalBalance, 2) }}</p>
            <p class="text-xs text-bankos-muted mt-1">Across {{ $byProduct->sum('account_count') }} accounts</p>
        </div>
        <div class="card p-6 border-t-4 border-t-emerald-500 shadow-md">
            <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Monthly Interest Accrual</p>
            <p class="text-3xl font-extrabold text-emerald-600 mt-2">₦{{ number_format($monthlyAccrual, 2) }}</p>
            <p class="text-xs text-bankos-muted mt-1">Estimated liability for current month</p>
        </div>
        <div class="card p-6 border-t-4 border-t-blue-500 shadow-md">
            <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Annual Interest Projection</p>
            <p class="text-3xl font-extrabold text-blue-600 mt-2">₦{{ number_format($annualAccrual, 2) }}</p>
            <p class="text-xs text-bankos-muted mt-1">Full-year liability at current balances</p>
        </div>
    </div>

    {{-- By Product Summary --}}
    <div class="card p-0 overflow-hidden mb-8 shadow-md">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text">Interest Liability by Savings Product</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase text-bankos-text-sec">
                        <th class="px-6 py-3 font-semibold">Product</th>
                        <th class="px-6 py-3 font-semibold text-right">Accounts</th>
                        <th class="px-6 py-3 font-semibold text-right">Total Balance</th>
                        <th class="px-6 py-3 font-semibold text-right">Rate (p.a.)</th>
                        <th class="px-6 py-3 font-semibold text-right">Monthly Accrual</th>
                        <th class="px-6 py-3 font-semibold text-right">Annual Accrual</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($byProduct as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-3 font-medium text-bankos-text">{{ $row['product']->name }}</td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-text-sec">{{ number_format($row['account_count']) }}</td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-text">₦{{ number_format($row['total_balance'], 2) }}</td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-text-sec">{{ number_format($row['interest_rate'], 2) }}%</td>
                        <td class="px-6 py-3 text-right font-mono text-emerald-600 font-semibold">₦{{ number_format($row['monthly_accrual'], 2) }}</td>
                        <td class="px-6 py-3 text-right font-mono text-blue-600">₦{{ number_format($row['annual_accrual'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-bankos-muted">No interest-bearing savings products found.</td>
                    </tr>
                    @endforelse
                    @if($byProduct->isNotEmpty())
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 font-semibold border-t-2 border-bankos-border">
                        <td class="px-6 py-3 text-bankos-text">Total</td>
                        <td class="px-6 py-3 text-right font-mono">{{ number_format($byProduct->sum('account_count')) }}</td>
                        <td class="px-6 py-3 text-right font-mono">₦{{ number_format($byProduct->sum('total_balance'), 2) }}</td>
                        <td class="px-6 py-3"></td>
                        <td class="px-6 py-3 text-right font-mono text-emerald-600">₦{{ number_format($monthlyAccrual, 2) }}</td>
                        <td class="px-6 py-3 text-right font-mono text-blue-600">₦{{ number_format($annualAccrual, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top accounts detail --}}
    <div class="card p-0 overflow-hidden shadow-md">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text">Top 50 Accounts by Monthly Accrual</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase text-bankos-text-sec">
                        <th class="px-6 py-3 font-semibold">Account / Customer</th>
                        <th class="px-6 py-3 font-semibold">Product</th>
                        <th class="px-6 py-3 font-semibold text-right">Balance</th>
                        <th class="px-6 py-3 font-semibold text-right">Rate</th>
                        <th class="px-6 py-3 font-semibold text-right">Monthly Interest</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($topAccounts as $acc)
                    @php
                        $rate    = (float)($acc->savingsProduct?->interest_rate ?? 0);
                        $monthly = max(0, (float)$acc->available_balance) * ($rate / 100 / 12);
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-3">
                            <p class="font-medium text-bankos-text">{{ $acc->customer?->full_name ?? '—' }}</p>
                            <p class="text-xs text-bankos-muted font-mono">{{ $acc->account_number }}</p>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 px-2 py-0.5 rounded-full">{{ $acc->savingsProduct?->name ?? '—' }}</span>
                        </td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-text">₦{{ number_format($acc->available_balance, 2) }}</td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-text-sec">{{ number_format($rate, 2) }}%</td>
                        <td class="px-6 py-3 text-right font-mono font-semibold text-emerald-600">₦{{ number_format($monthly, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-bankos-muted">No accounts found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
