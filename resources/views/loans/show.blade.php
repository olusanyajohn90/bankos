<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight flex items-center gap-2">
                    Facility: {{ $loan->loan_reference }}
                    @if($loan->status === 'active')
                        <span class="badge badge-active text-xs">Active</span>
                    @elseif($loan->status === 'pending')
                        <span class="badge badge-pending text-xs">Pending Review</span>
                    @elseif($loan->status === 'approved')
                        <span class="badge bg-blue-100 text-blue-800 text-xs">Approved</span>
                    @elseif($loan->status === 'overdue')
                        <span class="badge badge-danger text-xs">Overdue / Arrears</span>
                    @elseif($loan->status === 'restructured')
                        <span class="badge bg-purple-100 text-purple-700 border border-purple-200 text-xs">Restructured</span>
                    @else
                        <span class="badge bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs">Closed</span>
                    @endif
                </h2>
                <div class="flex items-center gap-2 mt-1 text-sm text-bankos-text-sec">
                    <span class="font-mono text-bankos-primary">{{ $loan->loanProduct?->name ?? '—' }}</span>
                    <span>•</span>
                    <a href="{{ route('customers.show', $loan->customer) }}" class="hover:text-bankos-primary hover:underline font-medium">{{ $loan->customer?->first_name }} {{ $loan->customer?->last_name }}</a>
                </div>
            </div>
            
            <div class="flex gap-2">
                @if($loan->status === 'pending')
                    @if(auth()->user()->can('loans.approve_l1') || auth()->user()->hasRole('tenant_admin') || auth()->user()->hasRole('super_admin'))
                    <form action="{{ route('loans.approve', $loan) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary bg-bankos-success hover:bg-green-700 border-none flex items-center gap-2 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Approve Facility
                        </button>
                    </form>
                    @endif
                @elseif($loan->status === 'approved')
                    @if(auth()->user()->can('loans.disburse') || auth()->user()->hasRole('tenant_admin') || auth()->user()->hasRole('super_admin'))
                    <form action="{{ route('loans.disburse', $loan) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="22"></line><line x1="17" y1="5" x2="12" y2="2"></line><line x1="7" y1="5" x2="12" y2="2"></line></svg>
                            Disburse Funds
                        </button>
                    </form>
                    @endif
                @endif
            </div>
        </div>
    </x-slot>

    {{-- ── Bidirectional Loan Chain Banners ── --}}
    @if($loan->parent_loan_id)
    <div class="mb-4 rounded-lg bg-purple-50 dark:bg-purple-900/10 border border-purple-200 dark:border-purple-700 px-5 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3 text-sm text-purple-800 dark:text-purple-200">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
            <span>This is a <strong>restructured loan</strong> — originated from the settlement of original loan</span>
            <a href="{{ route('loans.show', $loan->parentLoan) }}" class="font-mono font-bold text-purple-700 hover:underline">
                {{ $loan->parentLoan?->loan_number }}
            </a>
        </div>
        <a href="{{ route('loans.show', $loan->parentLoan) }}" class="btn btn-secondary text-xs py-1.5">
            View Original Loan →
        </a>
    </div>
    @endif

    @php $restructuredChild = $loan->restructuredTo; @endphp
    @if($restructuredChild)
    <div class="mb-4 rounded-lg bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700 px-5 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3 text-sm text-amber-800 dark:text-amber-200">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
            <span>This loan was <strong>restructured</strong>. A new loan was created:</span>
            <a href="{{ route('loans.show', $restructuredChild) }}" class="font-mono font-bold text-amber-700 hover:underline">
                {{ $restructuredChild->loan_number }}
            </a>
        </div>
        <a href="{{ route('loans.show', $restructuredChild) }}" class="btn btn-secondary text-xs py-1.5">
            View Restructured Loan →
        </a>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Balance Cards -->
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="card p-6 border-l-4 {{ $loan->status === 'overdue' ? 'border-l-red-500 bg-red-50/50 dark:bg-red-900/10' : 'border-l-bankos-primary' }}">
                <p class="text-sm font-semibold text-bankos-text-sec uppercase tracking-wider mb-2">Total Outstanding</p>
                <h3 class="text-4xl font-bold {{ $loan->status === 'overdue' ? 'text-red-600' : 'text-bankos-text dark:text-white' }} tracking-tight">₦ {{ number_format($loan->outstanding_balance, 2) }}</h3>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-4 overflow-hidden">
                    @php
                        $progress = $loan->total_payable > 0 ? ($loan->amount_paid / $loan->total_payable) * 100 : 0;
                        $outstandingPrincipal  = $loan->outstanding_principal;
                        $outstandingInterest   = max(0, (float)$loan->outstanding_balance - $outstandingPrincipal);
                    @endphp
                    <div class="bg-bankos-success h-2 rounded-full" style="width: {{ $progress }}%"></div>
                </div>
                <p class="text-xs text-bankos-muted mt-2 flex justify-between">
                    <span>{{ number_format($progress, 1) }}% Repaid</span>
                    <span>₦{{ number_format($loan->amount_paid, 2) }} paid</span>
                </p>
                {{-- Principal / Interest breakdown --}}
                <div class="mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border grid grid-cols-2 gap-3">
                    <div class="rounded-lg bg-blue-50 dark:bg-blue-900/10 px-3 py-2">
                        <p class="text-[10px] uppercase font-bold text-blue-500 mb-0.5">Principal Remaining</p>
                        <p class="font-bold text-blue-700 dark:text-blue-300 text-sm">₦{{ number_format($outstandingPrincipal, 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-amber-50 dark:bg-amber-900/10 px-3 py-2">
                        <p class="text-[10px] uppercase font-bold text-amber-500 mb-0.5">Interest Remaining</p>
                        <p class="font-bold text-amber-700 dark:text-amber-300 text-sm">₦{{ number_format($outstandingInterest, 2) }}</p>
                    </div>
                </div>
            </div>

            
            <div class="card p-6 grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[10px] uppercase font-bold text-bankos-text-sec mb-1">Principal</p>
                    <p class="font-medium">₦{{ number_format($loan->principal_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-bankos-text-sec mb-1">Projected Interest</p>
                    <p class="font-medium text-bankos-primary">₦{{ number_format($loan->total_interest, 2) }}</p>
                </div>
                <div class="col-span-2 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                     <p class="text-[10px] uppercase font-bold text-bankos-text-sec mb-1">Maturity Date</p>
                     <p class="font-medium {{ $loan->expected_maturity_date && $loan->expected_maturity_date < now() && $loan->status !== 'closed' ? 'text-red-500' : '' }}">
                        {{ $loan->expected_maturity_date ? \Carbon\Carbon::parse($loan->expected_maturity_date)->format('d M, Y') : 'Pending Disbursal' }}
                     </p>
                </div>
            </div>
        </div>

        <!-- Loan Terms Snapshot + IFRS 9 Classification -->
        <div class="card p-6">
            @php $perf = $loan->performance_class; @endphp
            <div class="rounded-lg border px-4 py-3 mb-5 flex items-center justify-between {{ $perf['badge'] }}">
                <div>
                    <p class="text-[10px] uppercase font-bold tracking-widest opacity-70">Performance Class</p>
                    <p class="font-bold text-lg mt-0.5">{{ $perf['label'] }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] uppercase opacity-70">DPD</p>
                    <p class="font-bold text-2xl">{{ $perf['dpd'] }}</p>
                </div>
            </div>
            @if($perf['stage'] > 0)
            <div class="flex gap-1 mb-1">
                @for($s = 1; $s <= 5; $s++)
                <div class="flex-1 h-2 rounded-full {{ $s <= $perf['stage'] ? ($perf['stage'] >= 4 ? 'bg-red-500' : ($perf['stage'] == 3 ? 'bg-orange-400' : ($perf['stage'] == 2 ? 'bg-yellow-400' : 'bg-green-500'))) : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                @endfor
            </div>
            <p class="text-[10px] text-bankos-muted mb-4">IFRS 9 Stage {{ $perf['stage'] }} &middot; {{ $perf['dpd'] }} Days Past Due</p>
            @endif
            <h3 class="font-bold text-base mb-3 border-b border-bankos-border dark:border-bankos-dark-border pb-2">Facility Terms</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-bankos-text-sec">Interest Rate</span>
                    <span class="font-bold text-bankos-success">{{ number_format($loan->interest_rate, 2) }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-bankos-text-sec">Tenure</span>
                    <span class="font-medium">{{ $loan->duration }} {{ ucfirst($loan->duration_type) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-bankos-text-sec">Purpose</span>
                    <span class="font-medium text-right w-32 truncate" title="{{ $loan->purpose }}">{{ $loan->purpose }}</span>
                </div>
                <div class="flex justify-between pt-3 mt-3 border-t border-dashed border-bankos-border dark:border-bankos-dark-border">
                    <span class="text-bankos-text-sec">Created On</span>
                    <span class="font-mono text-xs">{{ $loan->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Credit Decision Panel --}}
    @include('credit._decision_panel', ['loan' => $loan])

    <!-- Loan Schedule & Repayment -->
    <div x-data="{ showRepayModal: false, showLiquidateModal: false, showRestructureModal: false, showTopupModal: false, liquidateType: 'partial' }">
    <div class="card p-0 overflow-hidden">
        <div class="p-6 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-start bg-gray-50/50 dark:bg-bankos-dark-bg/20">
            <div>
                <h3 class="font-bold text-lg">Amortization Schedule</h3>
                <p class="text-xs text-bankos-muted mt-1">Repayment history and projected schedule.</p>
            </div>
            @if($loan->status === 'active' || $loan->status === 'overdue')
            @if(auth()->user()->can('transactions.create') || auth()->user()->hasRole('tenant_admin'))
            <div class="flex gap-2 flex-wrap justify-end">
                <button @click="showRepayModal = true" class="btn btn-secondary text-sm flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
                    Post Repayment
                </button>
                <button @click="showLiquidateModal = true" class="btn btn-primary text-sm flex items-center gap-1.5 bg-bankos-warning border-bankos-warning hover:bg-yellow-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    Settle Loan
                </button>
                <a href="{{ route('loans.restructures.index', $loan) }}" class="btn btn-secondary text-sm flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>
                    Restructure
                </a>
                <button @click="showRestructureModal = true" class="btn btn-secondary text-sm flex items-center gap-1.5 text-bankos-primary border-bankos-primary/30 hover:bg-bankos-primary/5">
                    + Request Restructure
                </button>
                <a href="{{ route('loans.topups.index', $loan) }}" class="btn btn-secondary text-sm flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    Top-ups
                </a>
                <button @click="showTopupModal = true" class="btn btn-secondary text-sm flex items-center gap-1.5 text-bankos-success border-bankos-success/30 hover:bg-bankos-success/5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Top-up Loan
                </button>
            </div>
            @endif
            @endif
        </div>


        @if($loan->status === 'pending' || $loan->status === 'approved')
            <div class="px-6 py-4 bg-blue-50/50 dark:bg-blue-900/10 border-b border-bankos-border dark:border-bankos-dark-border text-sm text-blue-700 dark:text-blue-300">
                <p class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                    This is an estimated schedule. Actual due dates will be determined upon disbursal.
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                            <th class="px-6 py-4 font-semibold">Installment</th>
                            <th class="px-6 py-4 font-semibold">Expected Timeframe</th>
                            <th class="px-6 py-4 font-semibold text-right">Estimated Amount</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @php
                            $monthlyInstallment = $loan->duration > 0 ? ($loan->total_payable / $loan->duration) : $loan->total_payable;
                        @endphp
                        @for($i = 1; $i <= $loan->duration; $i++)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 font-medium">{{ $i }} of {{ $loan->duration }}</td>
                            <td class="px-6 py-4 text-bankos-muted italic">Month {{ $i }} after disbursal</td>
                            <td class="px-6 py-4 text-right font-bold">₦ {{ number_format($monthlyInstallment, 2) }}</td>
                            <td class="px-6 py-4"><span class="badge bg-gray-100 text-gray-600 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 text-xs shadow-none">Estimated</span></td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        @endif

        @if($loan->amortization_schedule)
            @php $schedule = $loan->amortization_schedule; @endphp
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                            <th class="px-6 py-4 font-semibold">#</th>
                            <th class="px-6 py-4 font-semibold">Due Date</th>
                            <th class="px-6 py-4 font-semibold text-right text-blue-500">Principal</th>
                            <th class="px-6 py-4 font-semibold text-right text-amber-500">Interest</th>
                            <th class="px-6 py-4 font-semibold text-right">Total</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @foreach($schedule as $row)
                        @php
                            $rowClass = match($row['status']) {
                                'paid'    => 'bg-green-50/30 dark:bg-green-900/10',
                                'overdue' => 'bg-red-50/40 dark:bg-red-900/10',
                                default   => '',
                            };
                            $badge = match($row['status']) {
                                'paid'    => '<span class="badge badge-active text-xs">Paid</span>',
                                'overdue' => '<span class="badge badge-danger text-xs">Overdue</span>',
                                default   => '<span class="badge bg-gray-100 text-gray-500 border border-gray-200 text-xs shadow-none">Upcoming</span>',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors {{ $rowClass }}">
                            <td class="px-6 py-3.5 font-medium text-bankos-muted">{{ $row['number'] }} / {{ $loan->duration }}</td>
                            <td class="px-6 py-3.5 whitespace-nowrap {{ $row['status'] === 'overdue' ? 'text-red-600 font-semibold' : '' }}">
                                {{ $row['due_date']->format('d M, Y') }}
                            </td>
                            <td class="px-6 py-3.5 text-right text-blue-600 dark:text-blue-400">₦ {{ number_format($row['principal'], 2) }}</td>
                            <td class="px-6 py-3.5 text-right text-amber-600 dark:text-amber-400">₦ {{ number_format($row['interest'], 2) }}</td>
                            <td class="px-6 py-3.5 text-right font-bold">₦ {{ number_format($row['amount'], 2) }}</td>
                            <td class="px-6 py-3.5">{!! $badge !!}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-t-2 border-bankos-border dark:border-bankos-dark-border text-sm">
                            <td colspan="2" class="px-6 py-4 font-bold">Total Payable</td>
                            <td class="px-6 py-4 text-right font-bold text-blue-600">₦ {{ number_format($loan->principal_amount, 2) }}</td>
                            <td class="px-6 py-4 text-right font-bold text-amber-600">₦ {{ number_format($loan->total_interest, 2) }}</td>
                            <td class="px-6 py-4 text-right font-bold">₦ {{ number_format($loan->total_payable, 2) }}</td>
                            <td class="px-6 py-4 text-xs text-bankos-muted">Outstanding: ₦{{ number_format($loan->outstanding_balance, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Repayment History accordion --}}
            @php
                $repayments = \App\Models\Transaction::where('account_id', $loan->account_id)
                    ->where('type', 'repayment')->orderByDesc('created_at')->get();
            @endphp
            @if($repayments->isNotEmpty())
            <div class="border-t border-bankos-border dark:border-bankos-dark-border">
                <div class="px-6 py-3 bg-gray-50/80 dark:bg-bankos-dark-bg/30 text-xs font-semibold uppercase tracking-wider text-bankos-text-sec">
                    Repayment Transactions ({{ $repayments->count() }})
                </div>
                @foreach($repayments as $rpy)
                <div class="px-6 py-3 flex items-center justify-between border-b border-bankos-border dark:border-bankos-dark-border text-sm last:border-b-0">
                    <div>
                        <p class="font-mono text-xs text-bankos-primary">{{ $rpy->reference }}</p>
                        <p class="text-bankos-muted text-xs mt-0.5">{{ $rpy->description }} &middot; {{ $rpy->created_at->format('d M Y, g:ia') }}</p>
                    </div>
                    <span class="font-bold text-bankos-success">₦ {{ number_format($rpy->amount, 2) }}</span>
                </div>
                @endforeach
            </div>
            @endif
        @endif

    </div>{{-- /card --}}

        {{-- Repayment Modal --}}
        <div x-show="showRepayModal" style="display:none;" class="fixed inset-0 md:left-64 z-[9999] flex items-center justify-center bg-black/50 p-4" x-transition>
            <div @click.away="showRepayModal = false" class="relative w-full max-w-sm rounded-xl bg-white dark:bg-bankos-dark-surface p-6 shadow-2xl ring-1 ring-bankos-border dark:ring-bankos-dark-border" style="max-width:420px">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h3 class="text-lg font-bold">Post Loan Repayment</h3>
                        <p class="text-xs text-bankos-muted mt-1">Outstanding: <strong class="text-bankos-warning">₦{{ number_format($loan->outstanding_balance, 2) }}</strong></p>
                    </div>
                    <button @click="showRepayModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <form action="{{ route('loans.repay', $loan) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Amount (₦) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" step="0.01" min="1" max="{{ $loan->outstanding_balance }}"
                            class="form-input w-full" placeholder="e.g. 8,750.00" required>
                        <p class="text-xs text-bankos-muted mt-1">Max: ₦{{ number_format($loan->outstanding_balance, 2) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Description (optional)</label>
                        <input type="text" name="description" class="form-input w-full" placeholder="e.g. Monthly installment – March 2026">
                    </div>
                    <div class="pt-4 border-t border-bankos-border dark:border-bankos-dark-border flex justify-end gap-3">
                        <button type="button" @click="showRepayModal = false" class="btn btn-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary">Post Repayment</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Liquidation Modal --}}
        <div x-show="showLiquidateModal" style="display:none;" class="fixed inset-0 md:left-64 z-[9999] flex items-center justify-center bg-black/50 p-4" x-transition>
            <div @click.away="showLiquidateModal = false" class="relative w-full max-w-md rounded-xl bg-white dark:bg-bankos-dark-surface p-6 shadow-2xl ring-1 ring-bankos-border dark:ring-bankos-dark-border">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h3 class="text-lg font-bold">Settle / Liquidate Loan</h3>
                        <p class="text-xs text-bankos-muted mt-1">Outstanding: <strong class="text-bankos-warning">₦{{ number_format($loan->outstanding_balance, 2) }}</strong></p>
                    </div>
                    <button @click="showLiquidateModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <!-- Type Toggle -->
                <div class="flex rounded-lg overflow-hidden border border-bankos-border dark:border-bankos-dark-border mb-5">
                    <button type="button" @click="liquidateType = 'partial'"
                        :class="liquidateType === 'partial' ? 'bg-bankos-primary text-white' : 'bg-gray-50 dark:bg-bankos-dark-bg text-bankos-muted'"
                        class="flex-1 py-2 text-sm font-semibold transition-colors">Partial Liquidation</button>
                    <button type="button" @click="liquidateType = 'full'"
                        :class="liquidateType === 'full' ? 'bg-bankos-success text-white' : 'bg-gray-50 dark:bg-bankos-dark-bg text-bankos-muted'"
                        class="flex-1 py-2 text-sm font-semibold transition-colors">Full Settlement</button>
                </div>
                <form action="{{ route('loans.liquidate', $loan) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="type" :value="liquidateType">
                    <!-- Partial: amount field -->
                    <div x-show="liquidateType === 'partial'">
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Lump Sum Amount (₦) <span class="text-red-500">*</span></label>
                        <input type="number" name="gross_amount" step="0.01" min="1" max="{{ $loan->outstanding_balance }}"
                            class="form-input w-full" placeholder="Enter amount above regular installment" x-bind:required="liquidateType === 'partial'">
                        <p class="text-xs text-bankos-muted mt-1">Any amount reduces the outstanding principal ahead of schedule.</p>
                    </div>
                    <!-- Full: show settlement amount + discount -->
                    <div x-show="liquidateType === 'full'" class="space-y-3">
                        <div class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <p class="text-sm font-semibold text-green-800 dark:text-green-300">Full Settlement Amount</p>
                            <p class="text-2xl font-bold text-green-700 dark:text-green-400 mt-1">₦{{ number_format($loan->outstanding_balance, 2) }}</p>
                        </div>
                        <input type="hidden" name="gross_amount" value="{{ $loan->outstanding_balance }}">
                        <div>
                            <label class="block text-sm font-medium text-bankos-text-sec mb-2">Early Settlement Discount (% off interest)</label>
                            <input type="number" name="discount_percent" step="0.5" min="0" max="100" value="0" class="form-input w-full" placeholder="0">
                            <p class="text-xs text-bankos-muted mt-1">Discount reduces the interest portion. Interest = ₦{{ number_format($loan->total_interest, 2) }}.</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Officer Notes (optional)</label>
                        <textarea name="notes" rows="2" class="form-input w-full" placeholder="Reason for early settlement..."></textarea>
                    </div>
                    <div class="pt-4 border-t border-bankos-border dark:border-bankos-dark-border flex justify-end gap-3">
                        <button type="button" @click="showLiquidateModal = false" class="btn btn-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary" :class="liquidateType === 'full' ? 'bg-bankos-success border-bankos-success hover:bg-green-700' : ''"
                            x-text="liquidateType === 'full' ? 'Settle Full Loan' : 'Post Partial Liquidation'">
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Restructure Request Modal --}}
        <div x-show="showRestructureModal" style="display:none;" class="fixed inset-0 md:left-64 z-[9999] flex items-center justify-center bg-black/50 p-4" x-transition>
            <div @click.away="showRestructureModal = false" class="relative w-full max-w-md rounded-xl bg-white dark:bg-bankos-dark-surface p-6 shadow-2xl ring-1 ring-bankos-border dark:ring-bankos-dark-border">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h3 class="text-lg font-bold">Request Loan Restructure</h3>
                        <p class="text-xs text-bankos-muted mt-1">Current: {{ $loan->duration }} months @ {{ number_format($loan->interest_rate, 2) }}% · Outstanding: ₦{{ number_format($loan->outstanding_balance, 2) }}</p>
                    </div>
                    <button @click="showRestructureModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <form action="{{ route('loans.restructures.store', $loan) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-bankos-text-sec mb-2">New Tenure (months) <span class="text-red-500">*</span></label>
                            <input type="number" name="new_tenure" class="form-input w-full" min="1" max="360"
                                placeholder="{{ $loan->duration }}" value="{{ $loan->duration }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-bankos-text-sec mb-2">New Rate (%) <span class="text-red-500">*</span></label>
                            <input type="number" name="new_rate" class="form-input w-full" step="0.1" min="0.1" max="100"
                                placeholder="{{ number_format($loan->interest_rate, 2) }}" value="{{ number_format($loan->interest_rate, 2) }}" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Reason for Restructure <span class="text-red-500">*</span></label>
                        <textarea name="reason" rows="3" class="form-input w-full" required
                            placeholder="Explain the circumstances requiring a restructure (e.g., temporary income reduction, medical emergency)..."></textarea>
                        <p class="text-xs text-bankos-muted mt-1">Minimum 20 characters. This will be reviewed by management.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Officer Notes (internal)</label>
                        <input type="text" name="officer_notes" class="form-input w-full" placeholder="Any internal notes for the reviewer...">
                    </div>
                    <div class="pt-4 border-t border-bankos-border dark:border-bankos-dark-border flex justify-end gap-3">
                        <button type="button" @click="showRestructureModal = false" class="btn btn-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Restructure Request</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Top-up Request Modal --}}
        <div x-show="showTopupModal" style="display:none;" class="fixed inset-0 md:left-64 z-[9999] flex items-center justify-center bg-black/50 p-4" x-transition>
            <div @click.away="showTopupModal = false" class="relative w-full max-w-md rounded-xl bg-white dark:bg-bankos-dark-surface p-6 shadow-2xl ring-1 ring-bankos-border dark:ring-bankos-dark-border">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h3 class="text-lg font-bold">Request Loan Top-up</h3>
                        <p class="text-xs text-bankos-muted mt-1">Current Principal: ₦{{ number_format($loan->outstanding_principal ?? $loan->principal_amount, 2) }}</p>
                    </div>
                    <button @click="showTopupModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <form action="{{ route('loans.topups.store', $loan) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Additional Funds Needed (₦) <span class="text-red-500">*</span></label>
                        <input type="number" name="topup_amount" step="0.01" min="1" class="form-input w-full" placeholder="e.g. 50000" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-bankos-text-sec mb-2">New Total Tenure <span class="text-red-500">*</span></label>
                            <input type="number" name="new_tenure" class="form-input w-full" min="1" max="360"
                                placeholder="{{ $loan->duration }}" value="{{ $loan->duration }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-bankos-text-sec mb-2">New Rate (%) <span class="text-red-500">*</span></label>
                            <input type="number" name="new_rate" class="form-input w-full" step="0.1" min="0.1" max="100"
                                placeholder="{{ number_format($loan->interest_rate, 2) }}" value="{{ number_format($loan->interest_rate, 2) }}" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Reason for Top-up <span class="text-red-500">*</span></label>
                        <textarea name="reason" rows="3" class="form-input w-full" required
                            placeholder="Why do you need additional funds?"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Officer Notes (internal)</label>
                        <input type="text" name="officer_notes" class="form-input w-full" placeholder="Internal notes...">
                    </div>
                    <div class="pt-4 border-t border-bankos-border dark:border-bankos-dark-border flex justify-end gap-3">
                        <button type="button" @click="showTopupModal = false" class="btn btn-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary bg-bankos-success border-none text-white hover:bg-green-700">Submit Top-up Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>{{-- /wrapper --}}

    {{-- ═══ LOAN HISTORY ═══ --}}
    @php
        $allRestructures = $loan->restructures()->with(['requestedBy','reviewedBy','newLoan'])->orderByDesc('created_at')->get();
        $allTopups = $loan->topups()->with(['requestedBy','reviewedBy','newLoan'])->orderByDesc('created_at')->get();
        $allLiquidations = $loan->liquidations()->orderByDesc('created_at')->get();
    @endphp
    @if($allRestructures->isNotEmpty() || $allTopups->isNotEmpty() || $allLiquidations->isNotEmpty())
    <div class="card p-0 overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20">
            <h3 class="font-bold text-sm">Loan History</h3>
            <p class="text-xs text-bankos-muted mt-0.5">All restructures and settlements on this facility</p>
        </div>
        <div class="divide-y divide-bankos-border dark:divide-bankos-dark-border">

            {{-- Restructure Events --}}
            @foreach($allRestructures as $evt)
            <div class="px-6 py-4 flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5
                        {{ $evt->status === 'approved' ? 'bg-green-100' : ($evt->status === 'rejected' ? 'bg-red-100' : 'bg-yellow-100') }}">
                        @if($evt->status === 'approved')
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        @elseif($evt->status === 'rejected')
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-semibold">
                            Restructure Request
                            <span class="ml-2 text-xs font-normal badge {{ $evt->status === 'approved' ? 'badge-active' : ($evt->status === 'rejected' ? 'badge-danger' : 'badge-pending') }}">{{ ucfirst($evt->status) }}</span>
                        </p>
                        <p class="text-xs text-bankos-muted mt-0.5">Submitted {{ $evt->created_at->format('d M Y') }} by {{ $evt->requestedBy?->name ?? 'Unknown' }}</p>
                        <p class="text-xs text-bankos-muted">Proposed: {{ $evt->new_tenure }} months @ {{ $evt->new_rate }}% p.a.</p>
                        @if($evt->reason)
                        <p class="text-xs italic text-bankos-muted mt-1">{{ Str::limit($evt->reason, 100) }}</p>
                        @endif
                        @if($evt->status === 'approved' && $evt->newLoan)
                        <a href="{{ route('loans.show', $evt->newLoan) }}" class="text-xs text-bankos-primary font-semibold hover:underline mt-1 inline-block">
                            → Restructured Loan: {{ $evt->newLoan->loan_number }}
                        </a>
                        @endif

                        @if($evt->status === 'pending' && (auth()->user()->can('loans.approve_l1') || auth()->user()->hasRole('tenant_admin') || auth()->user()->hasRole('super_admin')))
                        <div class="mt-3 flex gap-2">
                            <form action="{{ route('loan.restructures.approve', $evt) }}" method="POST" onsubmit="return confirm('Approve Restructure? This closes the current loan and books a new one.');">
                                @csrf
                                <button type="submit" class="btn btn-primary bg-bankos-success hover:bg-green-700 border-none text-white text-xs py-1.5 px-3 font-semibold shadow-sm">Approve Restructure</button>
                            </form>
                            <form action="{{ route('loan.restructures.reject', $evt) }}" method="POST" onsubmit="return confirm('Reject Restructure?');">
                                @csrf
                                <button type="submit" class="btn btn-secondary text-red-600 border-red-200 hover:bg-red-50 text-xs py-1.5 px-3 shadow-sm">Reject</button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
                @if($evt->reviewed_at)
                <p class="text-xs text-bankos-muted whitespace-nowrap">Reviewed {{ $evt->reviewed_at->format('d M Y') }}</p>
                @endif
            </div>
            @endforeach

            {{-- Top-up Events --}}
            @foreach($allTopups as $evt)
            <div class="px-6 py-4 flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5
                        {{ $evt->status === 'approved' ? 'bg-green-100' : ($evt->status === 'rejected' ? 'bg-red-100' : 'bg-yellow-100') }}">
                        @if($evt->status === 'approved')
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        @elseif($evt->status === 'rejected')
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-semibold">
                            Top-up Request
                            <span class="ml-2 text-xs font-normal badge {{ $evt->status === 'approved' ? 'badge-active' : ($evt->status === 'rejected' ? 'badge-danger' : 'badge-pending') }}">{{ ucfirst($evt->status) }}</span>
                        </p>
                        <p class="text-xs text-bankos-muted mt-0.5">Submitted {{ $evt->created_at->format('d M Y') }} by {{ $evt->requestedBy?->name ?? 'Unknown' }}</p>
                        <p class="text-xs text-bankos-muted">Requested: ₦{{ number_format($evt->topup_amount, 2) }} extra</p>
                        <p class="text-xs text-bankos-muted">Proposed Terms: {{ $evt->new_tenure }} months @ {{ $evt->new_rate }}% p.a.</p>
                        @if($evt->reason)
                        <p class="text-xs italic text-bankos-muted mt-1">{{ Str::limit($evt->reason, 100) }}</p>
                        @endif
                        @if($evt->status === 'approved' && $evt->newLoan)
                        <a href="{{ route('loans.show', $evt->newLoan) }}" class="text-xs text-bankos-primary font-semibold hover:underline mt-1 inline-block">
                            → New Topped-up Loan: {{ $evt->newLoan->loan_number }}
                        </a>
                        @endif

                        @if($evt->status === 'pending' && (auth()->user()->can('loans.approve_l1') || auth()->user()->hasRole('tenant_admin') || auth()->user()->hasRole('super_admin')))
                        <div class="mt-3 flex gap-2">
                            <form action="{{ route('loan.topups.approve', $evt) }}" method="POST" onsubmit="return confirm('Approve Top-up? This closes the current loan and disburses the new funds.');">
                                @csrf
                                <button type="submit" class="btn btn-primary bg-bankos-success hover:bg-green-700 border-none text-white text-xs py-1.5 px-3 font-semibold shadow-sm">Approve Top-up</button>
                            </form>
                            <form action="{{ route('loan.topups.reject', $evt) }}" method="POST" onsubmit="return confirm('Reject Top-up?');">
                                @csrf
                                <button type="submit" class="btn btn-secondary text-red-600 border-red-200 hover:bg-red-50 text-xs py-1.5 px-3 shadow-sm">Reject</button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
                @if($evt->reviewed_at)
                <p class="text-xs text-bankos-muted whitespace-nowrap">Reviewed {{ $evt->reviewed_at->format('d M Y') }}</p>
                @endif
            </div>
            @endforeach

            {{-- Liquidation / Settlement Events --}}
            @foreach($allLiquidations as $lqd)
            <div class="px-6 py-4 flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5"><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">
                            {{ $lqd->type === 'full' ? 'Full Settlement' : 'Partial Liquidation' }}
                            <span class="ml-2 text-xs font-mono text-bankos-muted">{{ $lqd->reference }}</span>
                        </p>
                        <p class="text-xs text-bankos-muted mt-0.5">{{ $lqd->created_at->format('d M Y, g:ia') }}</p>
                        <div class="flex gap-4 mt-1">
                            <span class="text-xs">Collected: <strong>₦{{ number_format($lqd->net_amount, 2) }}</strong></span>
                            @if($lqd->discount_amount > 0)
                            <span class="text-xs text-bankos-success">Interest waived: ₦{{ number_format($lqd->discount_amount, 2) }}</span>
                            @endif
                        </div>
                        @if($lqd->notes)
                        <p class="text-xs italic text-bankos-muted mt-1">{{ $lqd->notes }}</p>
                        @endif
                    </div>
                </div>
                <p class="text-xs text-bankos-muted whitespace-nowrap">₦{{ number_format($lqd->gross_amount, 2) }} gross</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif


</x-app-layout>
