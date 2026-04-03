<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Payroll Dashboard</h1>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mt-1">Salary analytics, deductions & payroll run status &mdash; {{ now()->format('F Y') }}</p>
            </div>
        </div>
    </x-slot>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Payroll</p>
            <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($totalPayrollAmount, 2) }}</p>
            <p class="text-xs text-bankos-text-sec mt-2">Net pay this month</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Gross Salary</p>
            <p class="text-2xl font-extrabold text-bankos-primary mt-1">₦{{ number_format($totalGross, 2) }}</p>
            <p class="text-xs text-bankos-text-sec mt-2">Before deductions</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Deductions</p>
            <p class="text-2xl font-extrabold text-red-600 mt-1">₦{{ number_format($totalDeductions, 2) }}</p>
            <p class="text-xs text-bankos-text-sec mt-2">PAYE + Pension + NHF + Others</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Staff Count</p>
            <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($staffCount) }}</p>
            <p class="text-xs text-bankos-text-sec mt-2">Active employees on payroll</p>
        </div>
    </div>

    {{-- Deductions & Advances --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5 border-l-4 border-blue-400">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">PAYE Tax</p>
            <p class="text-xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($payeTotal, 2) }}</p>
        </div>
        <div class="card p-5 border-l-4 border-green-400">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Pension</p>
            <p class="text-xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($pensionTotal, 2) }}</p>
        </div>
        <div class="card p-5 border-l-4 border-amber-400">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">NHF</p>
            <p class="text-xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">₦{{ number_format($nhfTotal, 2) }}</p>
        </div>
        <div class="card p-5 border-l-4 border-purple-400">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Salary Advances</p>
            <p class="text-xl font-extrabold {{ $pendingAdvances > 0 ? 'text-amber-600' : 'text-bankos-text dark:text-bankos-dark-text' }} mt-1">{{ $pendingAdvances }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">₦{{ number_format($advancesAmount) }} pending</p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Gross vs Net Salary</h3>
            <canvas id="grossNetChart" height="200"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Deductions Breakdown</h3>
            <canvas id="deductionsChart" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Staff by Pay Grade</h3>
            <canvas id="payGradeChart" height="200"></canvas>
        </div>

        {{-- Recent Payroll Runs --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Recent Payroll Runs</h3>
            <div class="space-y-2">
                @forelse($recentRuns as $run)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-bankos-dark-bg">
                    <div>
                        <p class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">{{ $run->pay_period_start ? \Carbon\Carbon::parse($run->pay_period_start)->format('M Y') : 'N/A' }}</p>
                        <p class="text-xs text-bankos-text-sec">{{ $run->headcount ?? 0 }} staff</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $run->status === 'completed' ? 'bg-green-100 text-green-700' : ($run->status === 'draft' ? 'bg-gray-100 text-gray-700' : 'bg-amber-100 text-amber-700') }}">{{ ucfirst($run->status) }}</span>
                </div>
                @empty
                <p class="text-sm text-bankos-text-sec text-center py-4">No payroll runs found.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Top Earners --}}
    <div class="card p-6">
        <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Top Earners</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-bankos-border dark:border-bankos-dark-border">
                        <th class="text-left py-2 px-3 text-xs text-bankos-text-sec uppercase">#</th>
                        <th class="text-left py-2 px-3 text-xs text-bankos-text-sec uppercase">Name</th>
                        <th class="text-right py-2 px-3 text-xs text-bankos-text-sec uppercase">Gross</th>
                        <th class="text-right py-2 px-3 text-xs text-bankos-text-sec uppercase">Net</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topEarners as $i => $earner)
                    <tr class="border-b border-bankos-border/50 dark:border-bankos-dark-border/50">
                        <td class="py-2 px-3 text-xs">{{ $i + 1 }}</td>
                        <td class="py-2 px-3 font-medium">{{ $earner->name }}</td>
                        <td class="py-2 px-3 text-right">₦{{ number_format($earner->gross_pay, 2) }}</td>
                        <td class="py-2 px-3 text-right">₦{{ number_format($earner->net_pay, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-4 text-center text-bankos-text-sec">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Gross vs Net (payroll trend)
        new Chart(document.getElementById('grossNetChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($payrollTrend->pluck('pay_period_start')->map(fn($d) => $d ? \Carbon\Carbon::parse($d)->format('M Y') : '?')->reverse()->values()) !!},
                datasets: [
                    { label: 'Gross', data: {!! json_encode($payrollTrend->pluck('total_gross')->reverse()->values()) !!}, backgroundColor: '#3b82f6' },
                    { label: 'Net', data: {!! json_encode($payrollTrend->pluck('total_net')->reverse()->values()) !!}, backgroundColor: '#22c55e' }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
        });

        // Deductions Pie
        new Chart(document.getElementById('deductionsChart'), {
            type: 'doughnut',
            data: {
                labels: ['PAYE', 'Pension', 'NHF', 'Other'],
                datasets: [{
                    data: [{{ $payeTotal }}, {{ $pensionTotal }}, {{ $nhfTotal }}, {{ max(0, $totalDeductions - $payeTotal - $pensionTotal - $nhfTotal) }}],
                    backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b', '#8b5cf6'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Pay Grade Bar
        new Chart(document.getElementById('payGradeChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($byPayGrade->keys()) !!},
                datasets: [{
                    label: 'Staff',
                    data: {!! json_encode($byPayGrade->values()) !!},
                    backgroundColor: '#8b5cf6'
                }]
            },
            options: { responsive: true, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });
    </script>
    @endpush
</x-app-layout>
