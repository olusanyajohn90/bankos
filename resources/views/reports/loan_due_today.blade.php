<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Loans Due Today by Officer</h2>
                <p class="text-sm text-bankos-text-sec mt-1">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="flex justify-between items-center mb-6 print:hidden">
        <form method="GET" class="flex items-center gap-4 bg-white dark:bg-bankos-dark-bg p-2 rounded-lg shadow-sm border border-bankos-border dark:border-bankos-dark-border">
            <div class="flex items-center gap-2 ml-2">
                <label class="text-xs font-semibold text-bankos-text-sec">Date</label>
                <input type="date" name="date" value="{{ $date }}" class="form-input text-sm border-none shadow-none">
            </div>
            <div class="h-6 w-px bg-bankos-border"></div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec">Officer</label>
                <select name="officer_id" class="form-select text-sm border-none shadow-none">
                    <option value="">All Officers</option>
                    @foreach ($allOfficers as $off)
                        <option value="{{ $off->id }}" {{ $officerFilter == $off->id ? 'selected' : '' }}>{{ $off->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary text-sm py-2 px-4">Filter</button>
        </form>
        <button class="btn btn-secondary text-sm flex items-center gap-2" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print
        </button>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Loans Due</p>
            <p class="text-2xl font-extrabold text-bankos-primary mt-1">{{ $totalLoans }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Installment Due</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">&#8358;{{ number_format($totalDue, 2) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Outstanding Balance</p>
            <p class="text-2xl font-extrabold text-blue-600 mt-1">&#8358;{{ number_format($totalOutstanding, 2) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Overdue</p>
            <p class="text-2xl font-extrabold text-red-600 mt-1">{{ $overdueCount }}</p>
        </div>
    </div>

    {{-- Grouped by Officer --}}
    @forelse ($byOfficer as $officerId => $officerLoans)
        @php $officer = $officers->get($officerId); @endphp
        <div class="card mb-6">
            <div class="flex items-center justify-between p-4 border-b border-bankos-border dark:border-bankos-dark-border">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-bankos-primary/10 flex items-center justify-center">
                        <span class="text-sm font-bold text-bankos-primary">{{ $officer ? strtoupper(substr($officer->name, 0, 2)) : 'NA' }}</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">{{ $officer->name ?? 'Unassigned' }}</h3>
                        <p class="text-xs text-bankos-text-sec">{{ $officerLoans->count() }} loan(s) &middot; &#8358;{{ number_format($officerLoans->sum('installment_amount'), 2) }} due</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-bankos-bg dark:bg-bankos-dark-bg text-bankos-text-sec">
                            <th class="text-left px-4 py-2 font-semibold">Loan #</th>
                            <th class="text-left px-4 py-2 font-semibold">Customer</th>
                            <th class="text-left px-4 py-2 font-semibold">Product</th>
                            <th class="text-right px-4 py-2 font-semibold">Principal</th>
                            <th class="text-right px-4 py-2 font-semibold">Outstanding</th>
                            <th class="text-right px-4 py-2 font-semibold">Installment Due</th>
                            <th class="text-center px-4 py-2 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($officerLoans as $loan)
                        <tr class="border-t border-bankos-border/50 dark:border-bankos-dark-border/50 hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50">
                            <td class="px-4 py-2.5 font-mono text-xs">{{ $loan->loan_number }}</td>
                            <td class="px-4 py-2.5">{{ $loan->customer->first_name ?? '' }} {{ $loan->customer->last_name ?? '' }}</td>
                            <td class="px-4 py-2.5">{{ $loan->product->name ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-right font-mono">&#8358;{{ number_format($loan->principal_amount, 2) }}</td>
                            <td class="px-4 py-2.5 text-right font-mono">&#8358;{{ number_format($loan->outstanding_balance, 2) }}</td>
                            <td class="px-4 py-2.5 text-right font-mono font-semibold">&#8358;{{ number_format($loan->installment_amount, 2) }}</td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $loan->status === 'overdue' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' }}">
                                    {{ ucfirst($loan->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="card p-12 text-center">
            <svg class="mx-auto mb-4 text-bankos-muted" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 15h8M9 9h.01M15 9h.01"/></svg>
            <p class="text-bankos-text-sec">No loans due on {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
        </div>
    @endforelse
</x-app-layout>
