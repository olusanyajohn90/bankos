<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Bank Performance Analytics</h2>
                <p class="text-sm text-bankos-text-sec mt-1">
                    {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}
                </p>
            </div>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"
               class="btn btn-secondary text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export CSV
            </a>
        </div>
    </x-slot>

    {{-- ── Period Selector ── --}}
    <div class="flex items-center gap-1 mb-6 bg-white dark:bg-bankos-dark-surface border border-bankos-border dark:border-bankos-dark-border rounded-xl p-1 w-fit shadow-sm">
        @foreach(['7d' => '7D', '30d' => '30D', '90d' => '90D', '365d' => '1Y'] as $val => $label)
        <a href="{{ route('portal-analytics.index', ['period' => $val]) }}"
           class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-colors
                  {{ $period === $val
                        ? 'bg-bankos-primary text-white shadow'
                        : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-100 dark:hover:bg-bankos-dark-bg' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- ── Row 1: 4 KPI Cards ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        {{-- Total Customers --}}
        <div class="card p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 grid place-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                         stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                @if($newCustomers > 0)
                <span class="flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 dark:bg-green-900/30 px-2 py-0.5 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
                    +{{ number_format($newCustomers) }}
                </span>
                @endif
            </div>
            <p class="text-3xl font-black text-bankos-text dark:text-bankos-dark-text tracking-tight">{{ number_format($totalCustomers) }}</p>
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wide mt-1">Total Customers</p>
            <p class="text-xs text-bankos-text-sec mt-2">
                <span class="font-semibold text-green-600">+{{ number_format($newCustomers) }}</span> new this period
            </p>
        </div>

        {{-- Portal Activation Rate --}}
        <div class="card p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-900/30 grid place-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                         stroke="#a855f7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-bankos-muted">{{ number_format($portalActive) }} active</span>
            </div>
            <p class="text-3xl font-black text-bankos-text dark:text-bankos-dark-text tracking-tight">{{ $portalActivationRate }}%</p>
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wide mt-1">Portal Activation</p>
            <div class="mt-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                <div class="h-1.5 rounded-full bg-purple-500 transition-all" style="width:{{ min($portalActivationRate, 100) }}%"></div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-1.5">{{ number_format($portalActive) }} of {{ number_format($totalCustomers) }} customers</p>
        </div>

        {{-- Transaction Volume --}}
        <div class="card p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 grid place-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                         stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-bankos-muted">{{ number_format($totalTxnCount) }} txns</span>
            </div>
            <p class="text-2xl font-black text-bankos-text dark:text-bankos-dark-text tracking-tight">
                ₦{{ $totalTxnVolume >= 1000000 ? number_format($totalTxnVolume / 1000000, 1) . 'M' : number_format($totalTxnVolume, 0) }}
            </p>
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wide mt-1">Transaction Volume</p>
            <p class="text-xs text-bankos-text-sec mt-2">
                <span class="text-red-500">↓ ₦{{ $totalTxnDebit >= 1000000 ? number_format($totalTxnDebit / 1000000, 1) . 'M' : number_format($totalTxnDebit, 0) }}</span>
                debits this period
            </p>
        </div>

        {{-- Loan Book --}}
        <div class="card p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/30 grid place-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                         stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
                @if($nplRatio > 5)
                <span class="text-xs font-bold text-red-600 bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded-full">
                    NPL {{ $nplRatio }}%
                </span>
                @else
                <span class="text-xs font-semibold text-green-600 bg-green-50 dark:bg-green-900/30 px-2 py-0.5 rounded-full">
                    NPL {{ $nplRatio }}%
                </span>
                @endif
            </div>
            <p class="text-2xl font-black text-bankos-text dark:text-bankos-dark-text tracking-tight">
                ₦{{ $loanBook >= 1000000 ? number_format($loanBook / 1000000, 1) . 'M' : number_format($loanBook, 0) }}
            </p>
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wide mt-1">Active Loan Book</p>
            <p class="text-xs text-bankos-text-sec mt-2">
                {{ number_format($activeLoans) }} active loans
                @if($nplRatio > 5)
                · <span class="text-red-500 font-semibold">NPL above threshold</span>
                @endif
            </p>
        </div>
    </div>

    {{-- ── Row 2: Charts ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Left: Daily Transaction Volume Line Chart --}}
        <div class="lg:col-span-2 card p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-bankos-text dark:text-bankos-dark-text">Transaction Activity</h3>
                    <p class="text-xs text-bankos-muted mt-0.5">Credits vs Debits over period</p>
                </div>
                <div class="flex items-center gap-4 text-xs">
                    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-0.5 bg-blue-500 rounded"></span> Credits</span>
                    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-0.5 bg-red-400 rounded"></span> Debits</span>
                </div>
            </div>
            <div class="relative h-56">
                <canvas id="txnVolumeChart"></canvas>
            </div>
        </div>

        {{-- Right: Loan Portfolio Doughnut --}}
        <div class="card p-5">
            <div class="mb-4">
                <h3 class="text-sm font-bold text-bankos-text dark:text-bankos-dark-text">Loan Portfolio</h3>
                <p class="text-xs text-bankos-muted mt-0.5">Status distribution</p>
            </div>
            <div class="relative h-48 flex items-center justify-center">
                <canvas id="loanDoughnutChart"></canvas>
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="text-center">
                        <p class="text-xs text-bankos-muted leading-none">Book</p>
                        <p class="text-sm font-black text-bankos-text dark:text-bankos-dark-text leading-tight">
                            ₦{{ $loanBook >= 1000000 ? number_format($loanBook / 1000000, 1) . 'M' : number_format($loanBook, 0) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-2">
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500 flex-shrink-0"></span>
                    <span class="text-bankos-text-sec">Active</span>
                    <span class="font-bold ml-auto">{{ number_format($activeLoans) }}</span>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 flex-shrink-0"></span>
                    <span class="text-bankos-text-sec">Settled</span>
                    <span class="font-bold ml-auto">{{ number_format($settledLoans) }}</span>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 flex-shrink-0"></span>
                    <span class="text-bankos-text-sec">Written Off</span>
                    <span class="font-bold ml-auto">{{ number_format($writtenOffLoans) }}</span>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-gray-400 flex-shrink-0"></span>
                    <span class="text-bankos-text-sec">Pending</span>
                    <span class="font-bold ml-auto">{{ number_format($pendingLoans) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 3: 3 Metric Cards ── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        {{-- Customer Breakdown: KYC Tiers --}}
        <div class="card p-5">
            <h3 class="text-sm font-bold text-bankos-text dark:text-bankos-dark-text mb-4">Customer Breakdown</h3>
            @php
                $kycTotal = array_sum($kycDistribution);
                $kycColors = ['level_1' => 'bg-blue-400', 'level_2' => 'bg-purple-400', 'level_3' => 'bg-emerald-400'];
                $kycLabels = ['level_1' => 'KYC Tier 1', 'level_2' => 'KYC Tier 2', 'level_3' => 'KYC Tier 3'];
            @endphp
            <div class="space-y-3">
                @foreach($kycDistribution as $tier => $count)
                @php $pct = $kycTotal > 0 ? round(($count / $kycTotal) * 100, 1) : 0; @endphp
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs font-semibold text-bankos-text-sec">{{ $kycLabels[$tier] }}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-bankos-muted">{{ number_format($count) }}</span>
                            <span class="text-xs font-bold text-bankos-text dark:text-bankos-dark-text w-10 text-right">{{ $pct }}%</span>
                        </div>
                    </div>
                    <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $kycColors[$tier] }}" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4 pt-3 border-t border-bankos-border dark:border-bankos-dark-border flex justify-between text-xs">
                <span class="text-bankos-muted">KYC Pending Review</span>
                <span class="font-bold {{ $kycPending > 0 ? 'text-amber-600' : 'text-bankos-text-sec' }}">{{ number_format($kycPending) }}</span>
            </div>
        </div>

        {{-- Fee Revenue --}}
        <div class="card p-5">
            <h3 class="text-sm font-bold text-bankos-text dark:text-bankos-dark-text mb-4">Fee Revenue</h3>
            <div class="flex flex-col items-center justify-center h-28">
                <p class="text-4xl font-black text-bankos-text dark:text-bankos-dark-text tracking-tight">
                    ₦{{ $feeRevenue >= 1000000 ? number_format($feeRevenue / 1000000, 2) . 'M' : number_format($feeRevenue, 0) }}
                </p>
                <p class="text-xs text-bankos-muted mt-2">This period</p>
                @php
                    $feeCount = $typeBreakdown->firstWhere('type', 'fee');
                @endphp
                @if($feeCount)
                <p class="text-xs text-bankos-text-sec mt-1">from {{ number_format($feeCount->cnt) }} transactions</p>
                @endif
            </div>
            <div class="mt-4 pt-3 border-t border-bankos-border dark:border-bankos-dark-border">
                <div class="flex justify-between text-xs">
                    <span class="text-bankos-muted">Repayments collected</span>
                    <span class="font-bold text-green-600">₦{{ $repaymentThisPeriod >= 1000000 ? number_format($repaymentThisPeriod / 1000000, 1) . 'M' : number_format($repaymentThisPeriod, 0) }}</span>
                </div>
            </div>
        </div>

        {{-- Portal Engagement --}}
        <div class="card p-5">
            <h3 class="text-sm font-bold text-bankos-text dark:text-bankos-dark-text mb-4">Portal Engagement</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-bankos-border dark:border-bankos-dark-border">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-red-50 dark:bg-red-900/20 grid place-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/></svg>
                        </div>
                        <span class="text-xs text-bankos-text-sec">Open Disputes</span>
                    </div>
                    <span class="font-bold text-sm {{ $disputesOpen > 0 ? 'text-red-600' : 'text-bankos-text-sec' }}">{{ number_format($disputesOpen) }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-bankos-border dark:border-bankos-dark-border">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-900/20 grid place-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>
                        </div>
                        <span class="text-xs text-bankos-text-sec">Active Investments</span>
                    </div>
                    <span class="font-bold text-sm text-bankos-text dark:text-bankos-dark-text">{{ number_format($investmentsActive) }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-bankos-border dark:border-bankos-dark-border">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-900/20 grid place-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                        </div>
                        <span class="text-xs text-bankos-text-sec">Pending Referrals</span>
                    </div>
                    <span class="font-bold text-sm {{ $referralsPending > 0 ? 'text-amber-600' : 'text-bankos-text-sec' }}">{{ number_format($referralsPending) }}</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-green-50 dark:bg-green-900/20 grid place-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        </div>
                        <span class="text-xs text-bankos-text-sec">Investments Book</span>
                    </div>
                    <span class="font-bold text-sm text-green-600">₦{{ $investmentsBook >= 1000000 ? number_format($investmentsBook / 1000000, 1) . 'M' : number_format($investmentsBook, 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 4: Transaction Type Breakdown Table ── --}}
    <div class="card mb-6">
        <div class="px-5 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="text-sm font-bold text-bankos-text dark:text-bankos-dark-text">Transaction Type Breakdown</h3>
            <p class="text-xs text-bankos-muted mt-0.5">All transaction types for the period, sorted by volume</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase tracking-wider text-bankos-text-sec">
                    <tr>
                        <th class="px-5 py-3 text-left">Transaction Type</th>
                        <th class="px-5 py-3 text-right">Count</th>
                        <th class="px-5 py-3 text-right">Total Amount</th>
                        <th class="px-5 py-3 text-right">% of Volume</th>
                        <th class="px-5 py-3 text-center">Type</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @php
                        $creditTypesList = ['deposit', 'disbursement', 'interest'];
                        $grandTotal = $typeBreakdown->sum('total');
                    @endphp
                    @forelse($typeBreakdown as $row)
                    @php
                        $isCr = in_array($row->type, $creditTypesList);
                        $rowPct = $grandTotal > 0 ? round(($row->total / $grandTotal) * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-semibold text-bankos-text dark:text-bankos-dark-text capitalize">
                                {{ str_replace('_', ' ', $row->type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right text-bankos-text-sec font-mono">{{ number_format($row->cnt) }}</td>
                        <td class="px-5 py-3.5 text-right font-bold {{ $isCr ? 'text-green-600' : 'text-red-500' }}">
                            ₦{{ number_format($row->total, 2) }}
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-20 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $isCr ? 'bg-green-400' : 'bg-red-300' }}" style="width:{{ $rowPct }}%"></div>
                                </div>
                                <span class="text-xs text-bankos-muted w-10 text-right">{{ $rowPct }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-block text-xs font-bold px-2 py-0.5 rounded-full
                                {{ $isCr ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' }}">
                                {{ $isCr ? 'CR' : 'DR' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-sm text-bankos-muted">No transactions found for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($typeBreakdown->isNotEmpty())
                <tfoot class="bg-gray-50 dark:bg-gray-800 border-t border-bankos-border dark:border-bankos-dark-border">
                    <tr>
                        <td class="px-5 py-3 text-xs font-bold text-bankos-text dark:text-bankos-dark-text uppercase">Total</td>
                        <td class="px-5 py-3 text-right text-xs font-bold text-bankos-text dark:text-bankos-dark-text font-mono">{{ number_format($typeBreakdown->sum('cnt')) }}</td>
                        <td class="px-5 py-3 text-right text-xs font-bold text-bankos-text dark:text-bankos-dark-text">₦{{ number_format($grandTotal, 2) }}</td>
                        <td class="px-5 py-3 text-right text-xs font-bold text-bankos-muted">100%</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ── Row 5: Deposit Summary + Lending Summary ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Deposit Summary --}}
        <div class="card p-5">
            <h3 class="text-sm font-bold text-bankos-text dark:text-bankos-dark-text mb-4">Deposit Summary</h3>
            <div class="space-y-4">
                {{-- Savings --}}
                <div class="flex items-center justify-between p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/40">
                    <div>
                        <p class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wide">Savings Accounts</p>
                        <p class="text-xl font-black text-blue-800 dark:text-blue-300 mt-0.5">
                            ₦{{ $totalDeposits >= 1000000 ? number_format($totalDeposits / 1000000, 2) . 'M' : number_format($totalDeposits, 0) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-blue-500">{{ number_format($totalSavingsCount) }} accounts</p>
                        <p class="text-xs text-blue-400 mt-0.5">Available balance</p>
                    </div>
                </div>
                {{-- Current --}}
                <div class="flex items-center justify-between p-3 rounded-lg bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800/40">
                    <div>
                        <p class="text-xs font-semibold text-purple-700 dark:text-purple-400 uppercase tracking-wide">Current Accounts</p>
                        <p class="text-xl font-black text-purple-800 dark:text-purple-300 mt-0.5">
                            ₦{{ $totalCurrentDeposits >= 1000000 ? number_format($totalCurrentDeposits / 1000000, 2) . 'M' : number_format($totalCurrentDeposits, 0) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-purple-500">{{ number_format($totalCurrentCount) }} accounts</p>
                        <p class="text-xs text-purple-400 mt-0.5">Available balance</p>
                    </div>
                </div>
                {{-- Domiciliary --}}
                <div class="flex items-center justify-between p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/40">
                    <div>
                        <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wide">Domiciliary</p>
                        <p class="text-xl font-black text-emerald-800 dark:text-emerald-300 mt-0.5">
                            ₦{{ $totalDomiciliaryDeposits >= 1000000 ? number_format($totalDomiciliaryDeposits / 1000000, 2) . 'M' : number_format($totalDomiciliaryDeposits, 0) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-emerald-400 mt-0.5">Available balance</p>
                    </div>
                </div>
                {{-- New this period --}}
                <div class="flex items-center justify-between pt-3 border-t border-bankos-border dark:border-bankos-dark-border text-xs">
                    <span class="text-bankos-muted">New account deposits (period)</span>
                    <span class="font-bold text-green-600">
                        ₦{{ $depositGrowth >= 1000000 ? number_format($depositGrowth / 1000000, 1) . 'M' : number_format($depositGrowth, 0) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Lending Summary --}}
        <div class="card p-5">
            <h3 class="text-sm font-bold text-bankos-text dark:text-bankos-dark-text mb-4">Lending Summary</h3>
            @php
                $lendingPipeline = [
                    ['label' => 'Applications', 'count' => $loanApplications, 'amount' => null, 'color' => 'bg-gray-400', 'text' => 'text-gray-600'],
                    ['label' => 'Approved', 'count' => $approvedApplications, 'amount' => null, 'color' => 'bg-blue-400', 'text' => 'text-blue-600'],
                    ['label' => 'Active', 'count' => $activeLoans, 'amount' => $loanBook, 'color' => 'bg-green-400', 'text' => 'text-green-600'],
                    ['label' => 'Settled', 'count' => $settledLoans, 'amount' => null, 'color' => 'bg-emerald-300', 'text' => 'text-emerald-600'],
                    ['label' => 'NPL / Written Off', 'count' => $writtenOffLoans, 'amount' => $nplAmount, 'color' => 'bg-red-400', 'text' => 'text-red-600'],
                ];
            @endphp
            <div class="space-y-3">
                @foreach($lendingPipeline as $step)
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full {{ $step['color'] }} flex-shrink-0"></div>
                    <div class="flex-1 flex items-center justify-between">
                        <span class="text-sm text-bankos-text-sec">{{ $step['label'] }}</span>
                        <div class="flex items-center gap-3">
                            @if($step['amount'] !== null)
                            <span class="text-xs text-bankos-muted font-mono">
                                ₦{{ $step['amount'] >= 1000000 ? number_format($step['amount'] / 1000000, 1) . 'M' : number_format($step['amount'], 0) }}
                            </span>
                            @endif
                            <span class="font-bold text-sm {{ $step['text'] }} w-12 text-right">{{ number_format($step['count']) }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4 pt-3 border-t border-bankos-border dark:border-bankos-dark-border space-y-2">
                <div class="flex justify-between text-xs">
                    <span class="text-bankos-muted">Disbursed this period</span>
                    <span class="font-bold text-green-600">
                        ₦{{ $totalDisbursed >= 1000000 ? number_format($totalDisbursed / 1000000, 1) . 'M' : number_format($totalDisbursed, 0) }}
                    </span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-bankos-muted">Approval rate</span>
                    <span class="font-bold {{ $approvalRate >= 60 ? 'text-green-600' : 'text-amber-600' }}">{{ $approvalRate }}%</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-bankos-muted">NPL ratio</span>
                    <span class="font-bold {{ $nplRatio > 5 ? 'text-red-600' : 'text-green-600' }}">{{ $nplRatio }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Chart.js Scripts ── --}}
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // ── Daily Transaction Volume Line Chart ────────────────────────────
        const dailyData = @json($dailyVolumes);

        const labels  = dailyData.map(d => d.date);
        const credits = dailyData.map(d => parseFloat(d.credits));
        const debits  = dailyData.map(d => parseFloat(d.debits));

        const txnCtx = document.getElementById('txnVolumeChart');
        if (txnCtx) {
            new Chart(txnCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Credits',
                            data: credits,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59,130,246,0.08)',
                            borderWidth: 2,
                            pointRadius: labels.length > 30 ? 0 : 3,
                            pointHoverRadius: 5,
                            fill: true,
                            tension: 0.35,
                        },
                        {
                            label: 'Debits',
                            data: debits,
                            borderColor: '#f87171',
                            backgroundColor: 'rgba(248,113,113,0.06)',
                            borderWidth: 2,
                            pointRadius: labels.length > 30 ? 0 : 3,
                            pointHoverRadius: 5,
                            fill: true,
                            tension: 0.35,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.dataset.label + ': ₦' + new Intl.NumberFormat('en-NG').format(ctx.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 10 },
                                maxRotation: 45,
                                maxTicksLimit: 10,
                            },
                        },
                        y: {
                            grid: { color: 'rgba(0,0,0,0.04)' },
                            ticks: {
                                font: { size: 10 },
                                callback: function(v) {
                                    if (v >= 1000000) return '₦' + (v / 1000000).toFixed(1) + 'M';
                                    if (v >= 1000)    return '₦' + (v / 1000).toFixed(0) + 'K';
                                    return '₦' + v;
                                }
                            },
                        },
                    },
                },
            });
        }

        // ── Loan Portfolio Doughnut ───────────────────────────────────────
        const loanCtx = document.getElementById('loanDoughnutChart');
        if (loanCtx) {
            new Chart(loanCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Active', 'Settled', 'Written Off', 'Pending'],
                    datasets: [{
                        data: [
                            {{ $activeLoans }},
                            {{ $settledLoans }},
                            {{ $writtenOffLoans }},
                            {{ $pendingLoans }},
                        ],
                        backgroundColor: ['#3b82f6', '#22c55e', '#ef4444', '#9ca3af'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 6,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.label + ': ' + ctx.parsed + ' loans';
                                }
                            }
                        }
                    },
                },
            });
        }
    });
    </script>
    @endpush

</x-app-layout>
