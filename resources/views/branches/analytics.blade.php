<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Branch Analytics</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Network-wide performance comparison — {{ $startDate->format('d M Y') }} to {{ $endDate->format('d M Y') }}</p>
            </div>
            <a href="{{ route('branches.index') }}" class="btn btn-secondary text-xs flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Manage Branches
            </a>
        </div>
    </x-slot>

    {{-- ── Filter Bar ─────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('branches.analytics') }}" id="filterForm" class="card mb-6 p-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">Period</label>
                <select name="period" onchange="this.form.submit()" class="form-input text-sm">
                    @foreach(['today'=>'Today','this_week'=>'This Week','this_month'=>'This Month','last_month'=>'Last Month','this_quarter'=>'This Quarter','this_year'=>'This Year','custom'=>'Custom Range'] as $val=>$label)
                        <option value="{{ $val }}" {{ $period===$val?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div id="customDates" class="{{ $period === 'custom' ? '' : 'hidden' }} flex gap-2">
                <div>
                    <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">From</label>
                    <input type="date" name="start_date" value="{{ request('start_date', $startDate->toDateString()) }}" class="form-input text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">To</label>
                    <input type="date" name="end_date" value="{{ request('end_date', $endDate->toDateString()) }}" class="form-input text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">State</label>
                <select name="state" onchange="this.form.submit()" class="form-input text-sm">
                    <option value="all" {{ $stateFilter==='all'?'selected':'' }}>All States</option>
                    @foreach($states as $s)
                        <option value="{{ $s }}" {{ $stateFilter===$s?'selected':'' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary text-sm">Apply</button>
        </div>
    </form>

    {{-- ── Network KPI Cards ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
        @php
        $kpis = [
            ['label'=>'Total Customers',   'value'=>number_format($totals['customers']),            'mono'=>false, 'color'=>'text-bankos-primary'],
            ['label'=>'Active Loans',      'value'=>number_format($totals['activeLoans']),           'mono'=>false, 'color'=>'text-green-600'],
            ['label'=>'Disbursed (Period)','value'=>'₦'.number_format($totals['disbursed'],0),      'mono'=>true,  'color'=>'text-bankos-text'],
            ['label'=>'Outstanding',       'value'=>'₦'.number_format($totals['outstanding'],0),    'mono'=>true,  'color'=>'text-bankos-text'],
            ['label'=>'Deposits',          'value'=>'₦'.number_format($totals['deposits'],0),       'mono'=>true,  'color'=>'text-green-600'],
            ['label'=>'Collections',       'value'=>'₦'.number_format($totals['collections'],0),    'mono'=>true,  'color'=>'text-bankos-text'],
            ['label'=>'Network PAR',       'value'=>number_format($totals['par'],1).'%',            'mono'=>true,  'color'=>$totals['par']>10?'text-red-600':($totals['par']>5?'text-yellow-600':'text-green-600')],
        ];
        @endphp
        @foreach($kpis as $k)
        <div class="card p-4">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">{{ $k['label'] }}</p>
            <p class="text-xl font-extrabold mt-1 {{ $k['color'] }} {{ $k['mono']?'font-mono':'' }}">{{ $k['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── Charts Row ─────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Network Trend Chart --}}
        <div class="card lg:col-span-2">
            <h3 class="text-sm font-semibold text-bankos-text mb-4">Network Trend — Disbursements vs Collections (6 Months)</h3>
            <div class="relative h-64">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        {{-- PAR by Branch Horizontal Bar --}}
        <div class="card">
            <h3 class="text-sm font-semibold text-bankos-text mb-4">PAR by Branch (%)</h3>
            <div class="relative h-64">
                <canvas id="parChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Branch Comparison Table ─────────────────────────────────────────── --}}
    <div class="card p-0 overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border flex items-center justify-between">
            <h3 class="text-sm font-semibold text-bankos-text">Branch-by-Branch Performance</h3>
            <span class="text-xs text-bankos-muted">{{ $branchMetrics->count() }} branches</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 text-xs uppercase tracking-wider text-bankos-text-sec border-b border-bankos-border dark:border-bankos-dark-border">
                        <th class="px-6 py-3 font-semibold">Branch</th>
                        <th class="px-6 py-3 font-semibold">State</th>
                        <th class="px-6 py-3 font-semibold text-right">Customers</th>
                        <th class="px-6 py-3 font-semibold text-right">New</th>
                        <th class="px-6 py-3 font-semibold text-right">Active Loans</th>
                        <th class="px-6 py-3 font-semibold text-right">Disbursed (₦)</th>
                        <th class="px-6 py-3 font-semibold text-right">Outstanding (₦)</th>
                        <th class="px-6 py-3 font-semibold text-right">Collections (₦)</th>
                        <th class="px-6 py-3 font-semibold text-right">Deposits (₦)</th>
                        <th class="px-6 py-3 font-semibold text-right">PAR</th>
                        <th class="px-6 py-3 font-semibold text-right">Staff</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($branchMetrics->sortByDesc('outstanding') as $m)
                    @php $par = $m['par']; @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-3">
                            <a href="{{ route('branches.show', $m['branch']) }}" class="font-semibold text-bankos-primary hover:underline">{{ $m['branch']->name }}</a>
                            @if($m['branch']->manager)
                                <p class="text-xs text-bankos-muted mt-0.5">{{ $m['branch']->manager->name }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-bankos-text-sec text-xs">{{ $m['branch']->state }}</td>
                        <td class="px-6 py-3 text-right font-mono">{{ number_format($m['totalCustomers']) }}</td>
                        <td class="px-6 py-3 text-right font-mono text-green-600">{{ $m['newCustomers'] > 0 ? '+'.$m['newCustomers'] : '—' }}</td>
                        <td class="px-6 py-3 text-right font-mono">{{ number_format($m['activeLoans']) }}</td>
                        <td class="px-6 py-3 text-right font-mono">{{ $m['disbursed'] > 0 ? number_format($m['disbursed'],0) : '—' }}</td>
                        <td class="px-6 py-3 text-right font-mono font-semibold">{{ $m['outstanding'] > 0 ? number_format($m['outstanding'],0) : '—' }}</td>
                        <td class="px-6 py-3 text-right font-mono text-green-600">{{ $m['collections'] > 0 ? number_format($m['collections'],0) : '—' }}</td>
                        <td class="px-6 py-3 text-right font-mono">{{ $m['deposits'] > 0 ? number_format($m['deposits'],0) : '—' }}</td>
                        <td class="px-6 py-3 text-right font-mono font-bold {{ $par>10?'text-red-600':($par>5?'text-yellow-600':'text-green-600') }}">
                            {{ number_format($par,1) }}%
                        </td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-muted">{{ $m['staffCount'] }}</td>
                        <td class="px-6 py-3 text-right">
                            <a href="{{ route('branches.show', $m['branch']) }}" class="text-bankos-primary hover:underline text-xs">View →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-t-2 border-bankos-border dark:border-bankos-dark-border font-bold text-sm">
                        <td class="px-6 py-3 text-bankos-text" colspan="2">NETWORK TOTAL</td>
                        <td class="px-6 py-3 text-right font-mono">{{ number_format($totals['customers']) }}</td>
                        <td></td>
                        <td class="px-6 py-3 text-right font-mono">{{ number_format($totals['activeLoans']) }}</td>
                        <td class="px-6 py-3 text-right font-mono">{{ number_format($totals['disbursed'],0) }}</td>
                        <td class="px-6 py-3 text-right font-mono">{{ number_format($totals['outstanding'],0) }}</td>
                        <td class="px-6 py-3 text-right font-mono text-green-600">{{ number_format($totals['collections'],0) }}</td>
                        <td class="px-6 py-3 text-right font-mono">{{ number_format($totals['deposits'],0) }}</td>
                        <td class="px-6 py-3 text-right font-mono {{ $totals['par']>10?'text-red-600':($totals['par']>5?'text-yellow-600':'text-green-600') }}">{{ number_format($totals['par'],1) }}%</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ── Rankings ─────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Top by Disbursement --}}
        <div class="card p-0 overflow-hidden">
            <div class="px-5 py-3 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
                <h3 class="text-sm font-semibold text-bankos-text">Top Disbursements</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($topByDisbursement as $i => $m)
                <div class="flex items-center gap-3 px-5 py-3">
                    <span class="w-6 h-6 rounded-full {{ $i===0?'bg-yellow-100 text-yellow-700':($i===1?'bg-gray-100 text-gray-600':'bg-orange-50 text-orange-600') }} text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $i+1 }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-bankos-text truncate">{{ $m['branch']->name }}</p>
                        <p class="text-xs text-bankos-muted">{{ $m['branch']->state }}</p>
                    </div>
                    <span class="text-sm font-mono font-semibold text-bankos-primary">₦{{ number_format($m['disbursed'],0) }}</span>
                </div>
                @endforeach
                @if($topByDisbursement->isEmpty())
                    <p class="px-5 py-8 text-center text-bankos-muted text-sm">No disbursements this period.</p>
                @endif
            </div>
        </div>

        {{-- Top by Customers --}}
        <div class="card p-0 overflow-hidden">
            <div class="px-5 py-3 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
                <h3 class="text-sm font-semibold text-bankos-text">Largest Customer Base</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($topByCustomers as $i => $m)
                <div class="flex items-center gap-3 px-5 py-3">
                    <span class="w-6 h-6 rounded-full {{ $i===0?'bg-yellow-100 text-yellow-700':($i===1?'bg-gray-100 text-gray-600':'bg-orange-50 text-orange-600') }} text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $i+1 }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-bankos-text truncate">{{ $m['branch']->name }}</p>
                        <p class="text-xs text-bankos-muted">{{ $m['branch']->state }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-mono font-semibold text-bankos-text">{{ number_format($m['totalCustomers']) }}</span>
                        @if($m['newCustomers'] > 0)
                            <p class="text-xs text-green-600 font-medium">+{{ $m['newCustomers'] }} new</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Highest PAR (risk) --}}
        <div class="card p-0 overflow-hidden">
            <div class="px-5 py-3 border-b border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/10">
                <h3 class="text-sm font-semibold text-red-700 dark:text-red-400">Highest PAR (Risk Watch)</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($highestPAR as $i => $m)
                <div class="flex items-center gap-3 px-5 py-3">
                    <span class="w-6 h-6 rounded-full {{ $m['par']>10?'bg-red-100 text-red-700':($m['par']>5?'bg-yellow-100 text-yellow-700':'bg-green-100 text-green-700') }} text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $i+1 }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-bankos-text truncate">{{ $m['branch']->name }}</p>
                        <p class="text-xs text-bankos-muted">{{ $m['overdueLoans'] }} overdue loan(s)</p>
                    </div>
                    <span class="text-sm font-mono font-bold {{ $m['par']>10?'text-red-600':($m['par']>5?'text-yellow-600':'text-green-600') }}">{{ number_format($m['par'],1) }}%</span>
                </div>
                @endforeach
                @if($highestPAR->isEmpty())
                    <p class="px-5 py-8 text-center text-bankos-muted text-sm">No data.</p>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
        const textColor = isDark ? '#9ca3af' : '#6b7280';

        // Period filter — show/hide custom date inputs
        const periodSel = document.querySelector('select[name="period"]');
        const customDates = document.getElementById('customDates');
        if (periodSel) {
            periodSel.addEventListener('change', function () {
                customDates.classList.toggle('hidden', this.value !== 'custom');
                if (this.value !== 'custom') this.form.submit();
            });
        }

        // Trend chart
        const trend = @json($trendMonths);
        new Chart(document.getElementById('trendChart'), {
            type: 'bar',
            data: {
                labels: trend.map(t => t.label),
                datasets: [
                    {
                        label: 'Disbursed',
                        data: trend.map(t => t.disbursed),
                        backgroundColor: 'rgba(59,130,246,0.7)',
                        borderRadius: 4,
                    },
                    {
                        label: 'Collections',
                        data: trend.map(t => t.collections),
                        backgroundColor: 'rgba(16,185,129,0.7)',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { color: textColor, boxWidth: 10, padding: 16 } } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: textColor } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor, callback: v => '₦'+Intl.NumberFormat('en',{notation:'compact'}).format(v) } }
                }
            }
        });

        // PAR horizontal bar
        const parData = @json($branchMetrics->map(fn($m) => ['name' => $m['branch']->branch_code, 'par' => $m['par']])->values());
        new Chart(document.getElementById('parChart'), {
            type: 'bar',
            data: {
                labels: parData.map(d => d.name),
                datasets: [{
                    label: 'PAR %',
                    data: parData.map(d => d.par),
                    backgroundColor: parData.map(d => d.par > 10 ? 'rgba(239,68,68,0.75)' : d.par > 5 ? 'rgba(234,179,8,0.75)' : 'rgba(34,197,94,0.75)'),
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: textColor, callback: v => v+'%' } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor } }
                }
            }
        });
    });
    </script>
    @endpush
</x-app-layout>
