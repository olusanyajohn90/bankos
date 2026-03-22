<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div class="flex items-center gap-3">
                <a href="{{ route('branches.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                        {{ $branch->name }}
                    </h2>
                    <p class="text-sm text-bankos-text-sec mt-0.5">
                        {{ $branch->branch_code }} &bull; {{ $branch->city }}, {{ $branch->state }}
                        @if($branch->status === 'active')
                            &bull; <span class="text-green-600 font-medium">Active</span>
                        @else
                            &bull; <span class="text-red-500 font-medium">Inactive</span>
                        @endif
                    </p>
                </div>
            </div>
            @can('branches.edit')
            <a href="{{ route('branches.edit', $branch) }}" class="btn btn-secondary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Edit Branch
            </a>
            @endcan
        </div>
    </x-slot>

    {{-- ─── Filter Bar ─────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('branches.show', $branch) }}" id="filter-form"
          class="card p-4 mb-6 flex flex-wrap items-end gap-4">

        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Period</label>
            <select name="period" onchange="toggleCustomDates(this.value); document.getElementById('filter-form').submit()"
                    class="input-field text-sm min-w-[160px]">
                @foreach([
                    'this_month'   => 'This Month',
                    'today'        => 'Today',
                    'this_week'    => 'This Week',
                    'last_month'   => 'Last Month',
                    'this_quarter' => 'This Quarter',
                    'this_year'    => 'This Year',
                    'custom'       => 'Custom Range',
                ] as $val => $label)
                    <option value="{{ $val }}" @selected($period === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div id="custom-dates" class="flex items-end gap-3 {{ $period !== 'custom' ? 'hidden' : '' }}">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">From</label>
                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                       class="input-field text-sm">
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">To</label>
                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                       class="input-field text-sm">
            </div>
            <button type="submit" class="btn btn-primary text-sm">Apply</button>
        </div>

        <div class="ml-auto text-xs text-bankos-text-sec self-center">
            {{ $startDate->format('d M Y') }} &ndash; {{ $endDate->format('d M Y') }}
        </div>
    </form>

    {{-- ─── KPI Row 1: Customers & Loans ──────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

        {{-- Total Customers --}}
        <div class="card p-5 flex flex-col gap-2">
            <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Total Customers</p>
            <p class="text-3xl font-bold text-bankos-text">{{ number_format($totalCustomers) }}</p>
            <p class="text-xs text-bankos-text-sec">
                <span class="font-semibold text-bankos-primary">+{{ number_format($newCustomers) }}</span>
                new this period
            </p>
        </div>

        {{-- New This Period --}}
        <div class="card p-5 flex flex-col gap-2">
            <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">New Customers</p>
            <p class="text-3xl font-bold text-bankos-text">{{ number_format($newCustomers) }}</p>
            <p class="text-xs text-bankos-text-sec">Joined in selected period</p>
        </div>

        {{-- Active + Overdue Loans --}}
        <div class="card p-5 flex flex-col gap-2">
            <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Active Loans</p>
            <p class="text-3xl font-bold text-bankos-text">{{ number_format($activeLoans) }}</p>
            <p class="text-xs text-bankos-text-sec">Active &amp; overdue combined</p>
        </div>

        {{-- Overdue Loans + PAR --}}
        <div class="card p-5 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Overdue Loans</p>
                @php
                    $parColor = $parRatio < 5 ? 'bg-green-100 text-green-700' : ($parRatio < 10 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
                @endphp
                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $parColor }}">
                    PAR {{ number_format($parRatio, 1) }}%
                </span>
            </div>
            <p class="text-3xl font-bold {{ $overdueLoans > 0 ? 'text-red-600' : 'text-bankos-text' }}">
                {{ number_format($overdueLoans) }}
            </p>
            <p class="text-xs text-bankos-text-sec">Portfolio at Risk ratio</p>
        </div>
    </div>

    {{-- ─── KPI Row 2: Financials ───────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        {{-- Total Disbursed --}}
        <div class="card p-5 flex flex-col gap-2">
            <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Disbursed (Period)</p>
            <p class="text-2xl font-bold text-bankos-text">&#8358;{{ number_format($totalDisbursed, 2) }}</p>
            <p class="text-xs text-bankos-text-sec">Loan principal disbursed</p>
        </div>

        {{-- Total Outstanding --}}
        <div class="card p-5 flex flex-col gap-2">
            <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Outstanding Balance</p>
            <p class="text-2xl font-bold text-bankos-text">&#8358;{{ number_format($totalOutstanding, 2) }}</p>
            <p class="text-xs text-bankos-text-sec">Active &amp; overdue loans</p>
        </div>

        {{-- Total Deposits --}}
        <div class="card p-5 flex flex-col gap-2">
            <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Total Deposits</p>
            <p class="text-2xl font-bold text-bankos-text">&#8358;{{ number_format($totalDeposits, 2) }}</p>
            <p class="text-xs text-bankos-text-sec">Savings accounts balance</p>
        </div>

        {{-- Collections --}}
        <div class="card p-5 flex flex-col gap-2">
            <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Collections (Period)</p>
            <p class="text-2xl font-bold text-green-600">&#8358;{{ number_format($collections, 2) }}</p>
            <p class="text-xs text-bankos-text-sec">Repayments received</p>
        </div>
    </div>

    {{-- ─── Chart + Loan Status ────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Bar Chart --}}
        <div class="card p-5 lg:col-span-2">
            <h3 class="font-semibold text-bankos-text mb-4">6-Month Disbursements vs Collections</h3>
            <div class="relative" style="height: 260px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        {{-- Loan Status Breakdown --}}
        <div class="card p-5">
            <h3 class="font-semibold text-bankos-text mb-4">Loan Status Breakdown</h3>
            <div class="space-y-3">
                @foreach(['active' => ['Active','green'], 'overdue' => ['Overdue','red'], 'closed' => ['Closed','gray'], 'pending' => ['Pending','blue'], 'restructured' => ['Restructured','yellow']] as $status => [$label, $color])
                    @if(isset($loanByStatus[$status]))
                    @php $row = $loanByStatus[$status]; @endphp
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-2.5 h-2.5 rounded-full
                                @if($color === 'green') bg-green-500
                                @elseif($color === 'red') bg-red-500
                                @elseif($color === 'gray') bg-gray-400
                                @elseif($color === 'blue') bg-blue-500
                                @else bg-yellow-500
                                @endif"></span>
                            <span class="text-bankos-text font-medium">{{ $label }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-bankos-text">{{ number_format($row->count) }}</span>
                            <span class="text-xs text-bankos-text-sec ml-1">loans</span>
                        </div>
                    </div>
                    <div class="text-xs text-bankos-text-sec text-right -mt-2">
                        &#8358;{{ number_format($row->outstanding ?? 0, 2) }} outstanding
                    </div>
                    @endif
                @endforeach
                @if($loanByStatus->isEmpty())
                    <p class="text-sm text-bankos-text-sec text-center py-4">No loan data available.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── Top Borrowers + Recent Transactions ───────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Top 10 Borrowers --}}
        <div class="card p-0 overflow-hidden">
            <div class="px-5 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-semibold text-bankos-text">Top 10 Borrowers by Outstanding</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/50 text-xs uppercase tracking-wider text-bankos-text-sec border-b border-bankos-border dark:border-bankos-dark-border">
                            <th class="px-4 py-3 text-left font-semibold">#</th>
                            <th class="px-4 py-3 text-left font-semibold">Customer</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-right font-semibold">Outstanding</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($topBorrowers as $i => $loan)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-4 py-3 text-bankos-text-sec">{{ $i + 1 }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-bankos-text">{{ $loan->customer?->full_name ?? '—' }}</p>
                                <p class="text-xs text-bankos-text-sec font-mono">{{ $loan->loan_number }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if($loan->status === 'active')
                                    <span class="badge badge-active text-[10px] uppercase tracking-wider">Active</span>
                                @elseif($loan->status === 'overdue')
                                    <span class="badge badge-overdue text-[10px] uppercase tracking-wider">Overdue</span>
                                @else
                                    <span class="badge text-[10px] uppercase tracking-wider bg-gray-100 text-gray-600">{{ ucfirst($loan->status) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-bankos-text">
                                &#8358;{{ number_format($loan->outstanding_balance, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-bankos-text-sec text-sm">No active loans.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="card p-0 overflow-hidden">
            <div class="px-5 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-semibold text-bankos-text">Recent 10 Transactions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/50 text-xs uppercase tracking-wider text-bankos-text-sec border-b border-bankos-border dark:border-bankos-dark-border">
                            <th class="px-4 py-3 text-left font-semibold">Customer</th>
                            <th class="px-4 py-3 text-left font-semibold">Type</th>
                            <th class="px-4 py-3 text-right font-semibold">Amount</th>
                            <th class="px-4 py-3 text-right font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($recentTxns as $txn)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium text-bankos-text">{{ $txn->account?->customer?->full_name ?? '—' }}</p>
                                <p class="text-xs text-bankos-text-sec font-mono">{{ $txn->account?->account_number }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="capitalize text-bankos-text-sec text-xs">{{ $txn->type }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold
                                {{ $txn->amount >= 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ $txn->amount >= 0 ? '' : '-' }}&#8358;{{ number_format(abs($txn->amount), 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-bankos-text-sec whitespace-nowrap">
                                {{ $txn->created_at->format('d M Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-bankos-text-sec text-sm">No transactions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ─── Staff Section ───────────────────────────────────────────────── --}}
    <div class="card p-5 mb-6">
        <h3 class="font-semibold text-bankos-text mb-4">Branch Staff ({{ $staff->count() }})</h3>
        @if($staff->isEmpty())
            <p class="text-sm text-bankos-text-sec">No staff assigned to this branch.</p>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($staff as $user)
            <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50/60 dark:bg-bankos-dark-bg/30 border border-bankos-border dark:border-bankos-dark-border">
                <div class="w-10 h-10 rounded-full bg-bankos-primary/10 text-bankos-primary flex items-center justify-center font-bold text-sm shrink-0">
                    {{ strtoupper(substr($user->name ?? '', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-bankos-text text-sm truncate">
                        {{ $user->name }}
                    </p>
                    @if($user->roles->isNotEmpty())
                        <p class="text-xs text-bankos-primary font-medium truncate">
                            {{ $user->roles->pluck('name')->join(', ') }}
                        </p>
                    @endif
                    <p class="text-xs text-bankos-text-sec truncate">{{ $user->email }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ─── Chart.js ────────────────────────────────────────────────────── --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        const labels      = @json($trendMonths->pluck('label'));
        const disbursed   = @json($trendMonths->pluck('disbursed'));
        const collections = @json($trendMonths->pluck('collections'));

        const isDark = document.documentElement.classList.contains('dark');
        const gridColor  = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
        const labelColor = isDark ? '#9ca3af' : '#6b7280';

        new Chart(document.getElementById('trendChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Disbursements (₦)',
                        data: disbursed,
                        backgroundColor: 'rgba(59,130,246,0.75)',
                        borderRadius: 4,
                        borderSkipped: false,
                    },
                    {
                        label: 'Collections (₦)',
                        data: collections,
                        backgroundColor: 'rgba(34,197,94,0.75)',
                        borderRadius: 4,
                        borderSkipped: false,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: labelColor, font: { size: 12 } },
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ₦' + Number(ctx.raw).toLocaleString('en-NG', { minimumFractionDigits: 2 }),
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        ticks: { color: labelColor },
                    },
                    y: {
                        grid: { color: gridColor },
                        ticks: {
                            color: labelColor,
                            callback: v => '₦' + Number(v).toLocaleString('en-NG', { notation: 'compact' }),
                        },
                    },
                },
            },
        });
    })();

    function toggleCustomDates(val) {
        const el = document.getElementById('custom-dates');
        if (val === 'custom') {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    }
    </script>
    @endpush
</x-app-layout>
