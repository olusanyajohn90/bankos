<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('fixed-deposits.index') }}"
                   class="text-bankos-muted hover:text-bankos-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight flex items-center gap-3">
                        {{ $fixedDeposit->fd_number }}
                        @if($fixedDeposit->status === 'active')
                            <span class="badge badge-active text-xs">Active</span>
                        @elseif($fixedDeposit->status === 'matured')
                            <span class="badge bg-amber-100 text-amber-800 border border-amber-200 text-xs">Matured</span>
                        @elseif($fixedDeposit->status === 'liquidated')
                            <span class="badge bg-gray-100 text-gray-600 border border-gray-200 text-xs">Liquidated</span>
                        @elseif($fixedDeposit->status === 'rolled_over')
                            <span class="badge bg-blue-100 text-blue-700 border border-blue-200 text-xs">Rolled Over</span>
                        @endif
                    </h2>
                    <div class="flex items-center gap-2 mt-1 text-sm text-bankos-text-sec">
                        <span class="font-mono text-bankos-primary">{{ $fixedDeposit->product?->name ?? '—' }}</span>
                        <span>·</span>
                        @if($fixedDeposit->customer)
                        <a href="{{ route('customers.show', $fixedDeposit->customer) }}"
                           class="hover:text-bankos-primary hover:underline font-medium">
                            {{ $fixedDeposit->customer->first_name }} {{ $fixedDeposit->customer->last_name }}
                        </a>
                        @else
                        <span class="text-bankos-muted text-xs italic">Unknown Customer</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-200 text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-800 dark:text-red-200 text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: FD Details (2/3 width) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Summary Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="card p-4 text-center">
                    <p class="text-xs text-bankos-muted uppercase tracking-wider">Principal</p>
                    <p class="text-xl font-bold text-bankos-text dark:text-white mt-1">
                        ₦{{ number_format($fixedDeposit->principal_amount, 2) }}
                    </p>
                </div>
                <div class="card p-4 text-center">
                    <p class="text-xs text-bankos-muted uppercase tracking-wider">Interest Rate</p>
                    <p class="text-xl font-bold text-bankos-success mt-1">
                        {{ number_format($fixedDeposit->interest_rate, 2) }}%
                    </p>
                    <p class="text-[10px] text-bankos-muted">per annum</p>
                </div>
                <div class="card p-4 text-center">
                    <p class="text-xs text-bankos-muted uppercase tracking-wider">Accrued Interest</p>
                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                        ₦{{ number_format($fixedDeposit->accrued_interest, 2) }}
                    </p>
                    <p class="text-[10px] text-bankos-muted">of ₦{{ number_format($fixedDeposit->expected_interest, 2) }}</p>
                </div>
                <div class="card p-4 text-center">
                    @if($fixedDeposit->status === 'active')
                        <p class="text-xs text-bankos-muted uppercase tracking-wider">Days Remaining</p>
                        <p class="text-xl font-bold {{ $fixedDeposit->days_remaining <= 7 ? 'text-red-600' : ($fixedDeposit->days_remaining <= 30 ? 'text-amber-600' : 'text-bankos-text dark:text-white') }} mt-1">
                            {{ $fixedDeposit->days_remaining }}
                        </p>
                        <p class="text-[10px] text-bankos-muted">days to maturity</p>
                    @else
                        <p class="text-xs text-bankos-muted uppercase tracking-wider">Paid Interest</p>
                        <p class="text-xl font-bold text-bankos-success mt-1">
                            ₦{{ number_format($fixedDeposit->paid_interest, 2) }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- FD Details Card --}}
            <div class="card p-6">
                <h3 class="font-semibold text-bankos-text dark:text-white mb-5 text-base">Deposit Details</h3>
                <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div>
                        <p class="text-bankos-muted text-xs uppercase tracking-wider mb-1">FD Number</p>
                        <p class="font-bold font-mono text-bankos-primary">{{ $fixedDeposit->fd_number }}</p>
                    </div>
                    <div>
                        <p class="text-bankos-muted text-xs uppercase tracking-wider mb-1">Product</p>
                        <p class="font-semibold text-bankos-text dark:text-white">{{ $fixedDeposit->product?->name ?? '—' }}</p>
                        <p class="text-xs text-bankos-muted mt-0.5">{{ ucwords(str_replace('_', ' ', $fixedDeposit->product?->interest_payment ?? '')) }}</p>
                    </div>
                    <div>
                        <p class="text-bankos-muted text-xs uppercase tracking-wider mb-1">Customer</p>
                        @if($fixedDeposit->customer)
                        <a href="{{ route('customers.show', $fixedDeposit->customer) }}"
                           class="font-semibold text-bankos-primary hover:underline">
                            {{ $fixedDeposit->customer->first_name }} {{ $fixedDeposit->customer->last_name }}
                        </a>
                        <p class="text-xs text-bankos-muted mt-0.5 font-mono">{{ $fixedDeposit->customer->customer_number }}</p>
                        @else
                        <span class="text-bankos-muted text-xs italic">Unknown Customer</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-bankos-muted text-xs uppercase tracking-wider mb-1">Source Account</p>
                        <p class="font-semibold text-bankos-text dark:text-white font-mono">
                            {{ $fixedDeposit->sourceAccount?->account_number }}
                        </p>
                    </div>
                    <div>
                        <p class="text-bankos-muted text-xs uppercase tracking-wider mb-1">Start Date</p>
                        <p class="font-semibold text-bankos-text dark:text-white">{{ $fixedDeposit->start_date->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-bankos-muted text-xs uppercase tracking-wider mb-1">Maturity Date</p>
                        <p class="font-semibold text-bankos-text dark:text-white">{{ $fixedDeposit->maturity_date->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-bankos-muted text-xs uppercase tracking-wider mb-1">Tenure</p>
                        <p class="font-semibold text-bankos-text dark:text-white">{{ number_format($fixedDeposit->tenure_days) }} days</p>
                    </div>
                    <div>
                        <p class="text-bankos-muted text-xs uppercase tracking-wider mb-1">Auto Rollover</p>
                        <p class="font-semibold {{ $fixedDeposit->auto_rollover ? 'text-bankos-success' : 'text-bankos-muted' }}">
                            {{ $fixedDeposit->auto_rollover ? 'Yes' : 'No' }}
                        </p>
                    </div>
                    @if($fixedDeposit->branch)
                    <div>
                        <p class="text-bankos-muted text-xs uppercase tracking-wider mb-1">Branch</p>
                        <p class="font-semibold text-bankos-text dark:text-white">{{ $fixedDeposit->branch->name }}</p>
                    </div>
                    @endif
                    @if($fixedDeposit->createdBy)
                    <div>
                        <p class="text-bankos-muted text-xs uppercase tracking-wider mb-1">Booked By</p>
                        <p class="font-semibold text-bankos-text dark:text-white">{{ $fixedDeposit->createdBy->name }}</p>
                        <p class="text-xs text-bankos-muted">{{ $fixedDeposit->created_at->format('d M Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Interest Breakdown --}}
            <div class="card p-6">
                <h3 class="font-semibold text-bankos-text dark:text-white mb-5 text-base">Interest Breakdown</h3>
                <div class="space-y-3">
                    @php
                        $progress = $fixedDeposit->expected_interest > 0
                            ? min(100, ($fixedDeposit->accrued_interest / $fixedDeposit->expected_interest) * 100)
                            : 0;
                    @endphp
                    <div class="flex justify-between text-sm">
                        <span class="text-bankos-text-sec">Accrual Progress</span>
                        <span class="font-semibold text-bankos-text dark:text-white">{{ number_format($progress, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div class="bg-bankos-primary h-2 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                        <div>
                            <p class="text-xs text-bankos-muted">Expected</p>
                            <p class="font-semibold text-bankos-text dark:text-white mt-1">₦{{ number_format($fixedDeposit->expected_interest, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-bankos-muted">Accrued</p>
                            <p class="font-semibold text-blue-600 dark:text-blue-400 mt-1">₦{{ number_format($fixedDeposit->accrued_interest, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-bankos-muted">Paid Out</p>
                            <p class="font-semibold text-bankos-success mt-1">₦{{ number_format($fixedDeposit->paid_interest, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Liquidation Details (if liquidated) --}}
            @if($fixedDeposit->status === 'liquidated')
            <div class="card p-6 border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-bankos-text dark:text-white mb-5 text-base flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-500"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Liquidation Details
                </h3>
                <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div>
                        <p class="text-bankos-muted text-xs uppercase tracking-wider mb-1">Liquidated At</p>
                        <p class="font-semibold text-bankos-text dark:text-white">{{ $fixedDeposit->liquidated_at->format('d F Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-bankos-muted text-xs uppercase tracking-wider mb-1">Payout Amount</p>
                        <p class="font-bold text-bankos-success text-lg">₦{{ number_format($fixedDeposit->liquidation_amount, 2) }}</p>
                    </div>
                    @if($fixedDeposit->penalty_amount > 0)
                    <div>
                        <p class="text-bankos-muted text-xs uppercase tracking-wider mb-1">Early Penalty</p>
                        <p class="font-semibold text-red-600">- ₦{{ number_format($fixedDeposit->penalty_amount, 2) }}</p>
                    </div>
                    @endif
                    @if($fixedDeposit->liquidation_reason)
                    <div class="col-span-2">
                        <p class="text-bankos-muted text-xs uppercase tracking-wider mb-1">Reason</p>
                        <p class="text-bankos-text dark:text-gray-300">{{ $fixedDeposit->liquidation_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- RIGHT: Timeline & Actions --}}
        <div class="space-y-6">

            {{-- Liquidate Action --}}
            @if(in_array($fixedDeposit->status, ['active', 'matured']))
            @php
                $isEarly = now()->startOfDay()->lt($fixedDeposit->maturity_date);
                $penaltyRate = $isEarly ? $fixedDeposit->product->early_liquidation_penalty : 0;
                $grossInterest = $fixedDeposit->accrued_interest;
                $penaltyAmount = round($grossInterest * ($penaltyRate / 100), 2);
                $netInterest   = $grossInterest - $penaltyAmount;
                $payout        = $fixedDeposit->principal_amount + $netInterest;
            @endphp
            <div class="card p-6" x-data="{ showLiquidateForm: false }">
                <h3 class="font-semibold text-bankos-text dark:text-white mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-500"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Liquidate FD
                </h3>

                {{-- Penalty preview (early liquidation) --}}
                @if($isEarly && $penaltyRate > 0)
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3 mb-4 text-sm">
                    <p class="font-semibold text-amber-800 dark:text-amber-300 mb-2">Early Liquidation Warning</p>
                    <div class="space-y-1 text-amber-700 dark:text-amber-400">
                        <div class="flex justify-between">
                            <span>Gross Interest:</span>
                            <span class="font-semibold">₦{{ number_format($grossInterest, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Penalty ({{ number_format($penaltyRate, 1) }}%):</span>
                            <span class="font-semibold text-red-600">- ₦{{ number_format($penaltyAmount, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-amber-200 pt-1 mt-1">
                            <span class="font-semibold">Net Payout:</span>
                            <span class="font-bold text-bankos-success">₦{{ number_format($payout, 2) }}</span>
                        </div>
                    </div>
                </div>
                @else
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3 mb-4 text-sm">
                    <p class="text-xs text-green-700 dark:text-green-400 uppercase tracking-wider font-semibold mb-1">Payout on Liquidation</p>
                    <p class="text-2xl font-bold text-bankos-success">₦{{ number_format($payout, 2) }}</p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">Principal + ₦{{ number_format($netInterest, 2) }} interest</p>
                </div>
                @endif

                <button @click="showLiquidateForm = !showLiquidateForm"
                        class="w-full btn btn-secondary text-red-600 border-red-200 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm">
                    <span x-text="showLiquidateForm ? 'Cancel' : 'Proceed with Liquidation'"></span>
                </button>

                <div x-show="showLiquidateForm" x-transition class="mt-4">
                    <form action="{{ route('fixed-deposits.liquidate', $fixedDeposit) }}" method="POST">
                        @csrf
                        @method('POST')
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-bankos-text-sec mb-2">Reason (optional)</label>
                            <textarea name="reason" rows="3"
                                      class="form-input w-full text-sm resize-none"
                                      placeholder="e.g. Customer requested early withdrawal..."></textarea>
                        </div>
                        <button type="submit"
                                onclick="return confirm('Confirm liquidation of {{ $fixedDeposit->fd_number }}? This action cannot be undone.')"
                                class="w-full btn bg-red-600 hover:bg-red-700 text-white text-sm">
                            Confirm Liquidation
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Timeline --}}
            <div class="card p-6">
                <h3 class="font-semibold text-bankos-text dark:text-white mb-5 text-base">Timeline</h3>
                <div class="space-y-4">

                    {{-- Created --}}
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full bg-bankos-primary/10 text-bankos-primary flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </div>
                            <div class="w-px flex-1 bg-bankos-border dark:bg-bankos-dark-border mt-1"></div>
                        </div>
                        <div class="pb-4">
                            <p class="text-sm font-semibold text-bankos-text dark:text-white">FD Booked</p>
                            <p class="text-xs text-bankos-muted mt-0.5">{{ $fixedDeposit->created_at->format('d M Y H:i') }}</p>
                            <p class="text-xs text-bankos-muted">₦{{ number_format($fixedDeposit->principal_amount, 2) }} principal</p>
                        </div>
                    </div>

                    {{-- Start Date --}}
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full {{ $fixedDeposit->start_date->isPast() ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }} flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </div>
                            <div class="w-px flex-1 bg-bankos-border dark:bg-bankos-dark-border mt-1"></div>
                        </div>
                        <div class="pb-4">
                            <p class="text-sm font-semibold text-bankos-text dark:text-white">Start Date</p>
                            <p class="text-xs text-bankos-muted mt-0.5">{{ $fixedDeposit->start_date->format('d M Y') }}</p>
                        </div>
                    </div>

                    {{-- Maturity --}}
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full
                                {{ $fixedDeposit->status === 'matured' ? 'bg-amber-100 text-amber-600' : ($fixedDeposit->maturity_date->isPast() ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400') }}
                                flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            @if($fixedDeposit->status === 'liquidated')
                            <div class="w-px flex-1 bg-bankos-border dark:bg-bankos-dark-border mt-1"></div>
                            @endif
                        </div>
                        <div class="{{ $fixedDeposit->status === 'liquidated' ? 'pb-4' : '' }}">
                            <p class="text-sm font-semibold text-bankos-text dark:text-white">Maturity Date</p>
                            <p class="text-xs text-bankos-muted mt-0.5">{{ $fixedDeposit->maturity_date->format('d M Y') }}</p>
                            @if($fixedDeposit->status === 'active' && !$fixedDeposit->maturity_date->isPast())
                                <p class="text-xs font-medium {{ $fixedDeposit->days_remaining <= 7 ? 'text-red-600' : 'text-bankos-muted' }} mt-0.5">
                                    {{ $fixedDeposit->days_remaining }} days remaining
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Liquidation event --}}
                    @if($fixedDeposit->status === 'liquidated' && $fixedDeposit->liquidated_at)
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-bankos-text dark:text-white">Liquidated</p>
                            <p class="text-xs text-bankos-muted mt-0.5">{{ $fixedDeposit->liquidated_at->format('d M Y H:i') }}</p>
                            <p class="text-xs font-semibold text-bankos-success mt-0.5">₦{{ number_format($fixedDeposit->liquidation_amount, 2) }} paid out</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Quick Info --}}
            <div class="card p-5">
                <h3 class="font-semibold text-bankos-text dark:text-white mb-4 text-sm uppercase tracking-wider">Product Rules</h3>
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-bankos-muted">Early Liquidation</span>
                        <span class="{{ $fixedDeposit->product->allow_early_liquidation ? 'text-green-600 font-medium' : 'text-red-500' }}">
                            {{ $fixedDeposit->product->allow_early_liquidation ? 'Allowed' : 'Not Allowed' }}
                        </span>
                    </div>
                    @if($fixedDeposit->product->early_liquidation_penalty > 0)
                    <div class="flex justify-between items-center">
                        <span class="text-bankos-muted">Penalty Rate</span>
                        <span class="font-semibold text-amber-600">{{ number_format($fixedDeposit->product->early_liquidation_penalty, 2) }}% of interest</span>
                    </div>
                    @endif
                    <div class="flex justify-between items-center">
                        <span class="text-bankos-muted">Top-up Allowed</span>
                        <span class="{{ $fixedDeposit->product->allow_top_up ? 'text-green-600 font-medium' : 'text-bankos-muted' }}">
                            {{ $fixedDeposit->product->allow_top_up ? 'Yes' : 'No' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-bankos-muted">Interest Payment</span>
                        <span class="font-medium text-bankos-text dark:text-white">
                            {{ ucwords(str_replace('_', ' ', $fixedDeposit->product->interest_payment)) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
