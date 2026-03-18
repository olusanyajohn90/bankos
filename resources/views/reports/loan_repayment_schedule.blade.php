<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Loan Repayment Schedule</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Full amortization table for a specific loan</p>
            </div>
        </div>
    </x-slot>

    <div class="flex justify-between items-center mb-6">
        <form method="GET" action="{{ route('reports.loan-repayment-schedule') }}" class="flex items-center gap-3 flex-wrap">
            <select name="loan_number" class="input text-sm py-2 pr-10 min-w-[280px]" onchange="this.form.submit()">
                <option value="">— Select a loan —</option>
                @foreach($activeLoans as $l)
                    <option value="{{ $l->loan_number }}" {{ $loanNumber === $l->loan_number ? 'selected' : '' }}>
                        {{ $l->loan_number }} — {{ $l->customer?->full_name ?? '?' }} (₦{{ number_format($l->principal_amount, 0) }})
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary text-sm">Load</button>
        </form>

        @if($loan)
        <button class="btn btn-secondary text-sm flex items-center gap-2 print:hidden" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print
        </button>
        @endif
    </div>

    @if($loan)
    {{-- Loan Summary --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="card p-4">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Borrower</p>
            <p class="font-bold text-bankos-text mt-1">{{ $loan->customer?->full_name ?? '—' }}</p>
            <p class="text-xs text-bankos-muted font-mono">{{ $loan->loan_number }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Principal</p>
            <p class="font-bold text-bankos-text mt-1">₦{{ number_format($loan->principal_amount, 2) }}</p>
            <p class="text-xs text-bankos-muted">{{ $loan->loanProduct?->name ?? '—' }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Interest Rate</p>
            <p class="font-bold text-bankos-text mt-1">{{ number_format($loan->interest_rate, 2) }}% p.m.</p>
            <p class="text-xs text-bankos-muted">{{ $loan->tenure_days }} months tenure</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Outstanding</p>
            <p class="font-bold text-bankos-primary mt-1">₦{{ number_format($loan->outstanding_balance, 2) }}</p>
            <p class="text-xs {{ $loan->status === 'overdue' ? 'text-red-500' : 'text-bankos-muted' }}">{{ ucfirst($loan->status) }}</p>
        </div>
    </div>

    {{-- Amortization Table --}}
    <div class="card p-0 overflow-hidden shadow-md">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-bankos-text">Amortization Schedule</h3>
            <div class="flex gap-4 text-xs text-bankos-text-sec">
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span> Paid</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span> Overdue</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span> Upcoming</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b border-bankos-border text-xs uppercase text-bankos-text-sec">
                        <th class="px-6 py-3 font-semibold">#</th>
                        <th class="px-6 py-3 font-semibold">Due Date</th>
                        <th class="px-6 py-3 font-semibold text-right">Instalment</th>
                        <th class="px-6 py-3 font-semibold text-right">Principal</th>
                        <th class="px-6 py-3 font-semibold text-right">Interest</th>
                        <th class="px-6 py-3 font-semibold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @php $schedule = $loan->amortization_schedule; @endphp
                    @foreach($schedule as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors
                        {{ $row['status'] === 'paid'    ? 'bg-green-50/40 dark:bg-green-900/10'   : '' }}
                        {{ $row['status'] === 'overdue' ? 'bg-red-50/40 dark:bg-red-900/10'       : '' }}">
                        <td class="px-6 py-3 font-mono text-bankos-text-sec">{{ $row['number'] }}</td>
                        <td class="px-6 py-3 text-bankos-text">{{ \Carbon\Carbon::parse($row['due_date'])->format('d M Y') }}</td>
                        <td class="px-6 py-3 text-right font-mono font-semibold text-bankos-text">₦{{ number_format($row['amount'], 2) }}</td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-text-sec">₦{{ number_format($row['principal'], 2) }}</td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-text-sec">₦{{ number_format($row['interest'], 2) }}</td>
                        <td class="px-6 py-3 text-center">
                            @if($row['status'] === 'paid')
                                <span class="inline-flex items-center gap-1 text-xs text-green-700 bg-green-100 dark:bg-green-900/30 px-2 py-0.5 rounded-full font-semibold">Paid</span>
                            @elseif($row['status'] === 'overdue')
                                <span class="inline-flex items-center gap-1 text-xs text-red-700 bg-red-100 dark:bg-red-900/30 px-2 py-0.5 rounded-full font-semibold">Overdue</span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs text-gray-600 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full">Upcoming</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-bankos-border bg-gray-50 dark:bg-bankos-dark-bg/50">
                    <tr class="font-semibold">
                        <td colspan="2" class="px-6 py-3 text-bankos-text">Total</td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-primary">₦{{ number_format($loan->total_payable, 2) }}</td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-text-sec">₦{{ number_format($loan->principal_amount, 2) }}</td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-text-sec">₦{{ number_format($loan->total_interest, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @else
    <div class="card p-16 text-center text-bankos-muted">
        <svg class="mx-auto w-12 h-12 text-bankos-border mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <p class="font-medium">Select a loan above to view its repayment schedule</p>
    </div>
    @endif
</x-app-layout>
