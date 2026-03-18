<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center">
                    Loan Portfolio Report
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Overview of lending operations and exposure as of {{ now()->format('d M Y') }}</p>
            </div>
        </div>
    </x-slot>

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Total Loans</h3>
                    <p class="text-2xl font-bold text-bankos-text mt-1">{{ number_format($totalLoansCount) }}</p>
                </div>
                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-full text-bankos-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Active & Overdue</h3>
                    <p class="text-2xl font-bold text-bankos-text mt-1">{{ number_format($activeLoansCount) }}</p>
                </div>
                <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-full text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Total Principal Disbursed</h3>
                    <p class="text-2xl font-bold text-bankos-text mt-1">₦{{ number_format($totalPrincipal, 2) }}</p>
                </div>
                <div class="p-3 bg-bankos-light dark:bg-gray-800 rounded-full text-bankos-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Total Outstanding</h3>
                    <p class="text-2xl font-bold text-bankos-text mt-1">₦{{ number_format($totalOutstanding, 2) }}</p>
                </div>
                <div class="p-3 bg-bankos-light dark:bg-gray-800 rounded-full text-bankos-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 print:hidden">
        <div class="card lg:col-span-1 border border-bankos-border dark:border-bankos-dark-border">
            <h3 class="text-sm font-semibold text-bankos-text mb-6">Distribution by Status (Count)</h3>
            <div class="relative h-64 w-full">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
        
        <div class="card lg:col-span-2 border border-bankos-border dark:border-bankos-dark-border overflow-hidden p-0 h-full">
            <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="text-sm font-semibold text-bankos-text">Quick Summary By Status</h3>
            </div>
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-3 font-semibold">Status</th>
                        <th class="px-6 py-3 font-semibold text-right">Count</th>
                        <th class="px-6 py-3 font-semibold text-right">Outstanding (₦)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @php $statuses = ['pending', 'active', 'overdue', 'closed', 'restructured']; @endphp
                    @foreach($statuses as $status)
                        @php 
                            $statusLoans = $loans->filter(fn($l) => $l->status === $status);
                        @endphp
                        <tr>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full" style="background-color: {{ ['pending' => '#fcd34d', 'active' => '#4ade80', 'overdue' => '#f87171', 'closed' => '#9ca3af', 'restructured' => '#60a5fa'][$status] }}"></div>
                                    <span class="capitalize bankos-text font-medium">{{ $status }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-right font-mono">{{ number_format($statusLoans->count()) }}</td>
                            <td class="px-6 py-3 text-right font-mono text-bankos-text-sec">{{ number_format($statusLoans->sum('outstanding_balance'), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed List -->
    <div class="card p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text tracking-wider">All Facilities List</h3>
            <button class="btn btn-secondary text-xs flex items-center gap-2 print:hidden" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print List
            </button>
        </div>
        
        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Reference</th>
                        <th class="px-6 py-4 font-semibold">Customer</th>
                        <th class="px-6 py-4 font-semibold">Product</th>
                        <th class="px-6 py-4 font-semibold text-right">Principal (₦)</th>
                        <th class="px-6 py-4 font-semibold text-right">Outstanding (₦)</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($loans as $loan)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-3 font-mono text-xs text-bankos-primary font-medium">
                            <a href="{{ route('loans.show', $loan) }}" class="hover:underline">{{ $loan->loan_number ?? 'APP-'.substr($loan->id, 0, 8) }}</a>
                        </td>
                        <td class="px-6 py-3">
                            @if($loan->customer)
                            <a href="{{ route('customers.show', $loan->customer_id) }}" class="font-medium text-bankos-text hover:text-bankos-primary">
                                {{ $loan->customer->first_name }} {{ $loan->customer->last_name }}
                            </a>
                            @else
                            <span class="text-bankos-muted text-xs italic">Unknown</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-xs text-bankos-text-sec">
                            {{ $loan->loanProduct?->name ?? '—' }}
                        </td>
                        <td class="px-6 py-3 text-right font-mono">
                            {{ number_format($loan->principal_amount, 2) }}
                        </td>
                        <td class="px-6 py-3 text-right font-mono {{ current_balance_color($loan->outstanding_balance, 'debit') }}">
                            {{ number_format($loan->outstanding_balance, 2) }}
                        </td>
                        <td class="px-6 py-3">
                            <span class="badge badge-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-bankos-muted">
                            <p>No loans found in portfolio.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = @json($chartData);
            
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData.data,
                        backgroundColor: chartData.colors,
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                boxWidth: 8,
                                color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#4b5563'
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>

@php
function current_balance_color($amount, $normal_balance) {
    if ($amount <= 0) return 'text-gray-300 dark:text-gray-600';
    return 'text-bankos-text font-medium';
}
@endphp
