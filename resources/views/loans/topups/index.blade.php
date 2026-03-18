<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <a href="{{ route('loans.show', $loan) }}" class="text-sm text-bankos-primary hover:underline flex items-center gap-1 mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    Back to {{ $loan->loan_number }}
                </a>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Top-up Requests</h2>
                <p class="text-sm text-bankos-text-sec mt-1">{{ $loan->customer?->first_name }} {{ $loan->customer?->last_name }} &middot; {{ $loan->loan_number }}</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @foreach($topups as $req)
        @php
            $oldPrincipal = (float) ($loan->outstanding_principal ?? $loan->principal_amount);
            $topupAmount = (float) $req->topup_amount;
            $newPrincipal = $oldPrincipal + $topupAmount;

            $currentRemainingMonths = max(1, $loan->remaining_months ?? $loan->duration);
            
            // CURRENT side
            $currentInterest      = $oldPrincipal * ($loan->interest_rate / 100 / 12) * $currentRemainingMonths;
            $currentTotalPayable  = $oldPrincipal + $currentInterest;
            $oldMonthlyInstalment = round($currentTotalPayable / $currentRemainingMonths, 2);

            // PROPOSED side
            $newInterestAmount    = $newPrincipal * ($req->new_rate / 100 / 12) * $req->new_tenure;
            $newTotalOutstanding  = $newPrincipal + $newInterestAmount;
            $newMonthlyInstalment = round($newTotalOutstanding / $req->new_tenure, 2);

            $newMaturityDate = now()->addMonths($req->new_tenure);
            $instDiff        = $newMonthlyInstalment - $oldMonthlyInstalment;
        @endphp

        <div class="card p-0 overflow-hidden">
            {{-- Card Header --}}
            <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div>
                        <p class="font-semibold text-sm">Submitted {{ $req->created_at->format('d M Y') }} by {{ $req->requestedBy?->name ?? 'Unknown' }}</p>
                        <p class="text-xs text-bankos-muted mt-0.5">{{ $req->reason }}</p>
                    </div>
                </div>
                <div>
                    @if($req->status === 'pending')
                        <span class="badge badge-pending">Pending Review</span>
                    @elseif($req->status === 'approved')
                        <span class="badge badge-active">Approved</span>
                    @else
                        <span class="badge badge-danger">Rejected</span>
                    @endif
                </div>
            </div>

            {{-- Outcome Preview --}}
            <div class="p-6">
                @if($req->status === 'pending')
                <div class="rounded-lg bg-blue-50/60 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 px-4 py-2.5 text-xs text-blue-700 dark:text-blue-300 flex items-center gap-2 mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                    New loan will be booked with principal ₦{{ number_format($newPrincipal, 2) }} (Current Outstanding + Top-up). ₦{{ number_format($topupAmount, 2) }} will be disbursed to customer.
                </div>
                @endif

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="rounded-lg border border-bankos-border dark:border-bankos-dark-border p-4 bg-green-50 dark:bg-green-900/10">
                        <p class="text-[10px] uppercase font-bold text-bankos-muted mb-1">Top-up Funds</p>
                        <p class="text-xl font-bold text-green-700 dark:text-green-400">₦{{ number_format($topupAmount, 2) }}</p>
                        <p class="text-xs mt-1 text-bankos-muted">To be disbursed</p>
                    </div>
                    <div class="rounded-lg border border-bankos-border dark:border-bankos-dark-border p-4">
                        <p class="text-[10px] uppercase font-bold text-bankos-muted mb-1">New Monthly Instalment</p>
                        <p class="text-xl font-bold text-bankos-text dark:text-white">₦{{ number_format($newMonthlyInstalment, 2) }}</p>
                        <p class="text-xs mt-1 {{ $instDiff > 0 ? 'text-bankos-warning' : 'text-bankos-success' }}">
                            {{ $instDiff > 0 ? '▲' : '▼' }} ₦{{ number_format(abs($instDiff), 2) }} vs current
                        </p>
                    </div>
                    <div class="rounded-lg border border-bankos-border dark:border-bankos-dark-border p-4">
                        <p class="text-[10px] uppercase font-bold text-bankos-muted mb-1">New Total Tenure</p>
                        <p class="text-xl font-bold">{{ $req->new_tenure }} <span class="text-sm font-normal text-bankos-muted">months</span></p>
                        <p class="text-xs mt-1 text-bankos-muted">
                            was {{ $currentRemainingMonths }} months remaining
                        </p>
                    </div>
                    <div class="rounded-lg border border-bankos-border dark:border-bankos-dark-border p-4">
                        <p class="text-[10px] uppercase font-bold text-bankos-muted mb-1">New Maturity Date</p>
                        <p class="text-xl font-bold">{{ $newMaturityDate->format('M Y') }}</p>
                        <p class="text-xs text-bankos-muted mt-1">{{ $newMaturityDate->format('d M Y') }}</p>
                    </div>
                </div>

                <table class="w-full text-sm border border-bankos-border dark:border-bankos-dark-border rounded-lg overflow-hidden">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 text-xs uppercase tracking-wider text-bankos-text-sec">
                            <th class="px-4 py-3 font-semibold text-left">Parameter</th>
                            <th class="px-4 py-3 font-semibold text-right">Current Loan</th>
                            <th class="px-4 py-3 font-semibold text-right text-bankos-primary">Topped-up Loan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        <tr>
                            <td class="px-4 py-3 text-bankos-muted">Principal Amount</td>
                            <td class="px-4 py-3 text-right font-medium">₦{{ number_format($oldPrincipal, 2) }} <span class="text-xs text-bankos-muted">(Outstanding)</span></td>
                            <td class="px-4 py-3 text-right font-bold text-bankos-primary">₦{{ number_format($newPrincipal, 2) }} <span class="text-xs text-bankos-muted">(Combined)</span></td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 text-bankos-muted">Interest Rate</td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($loan->interest_rate, 2) }}% p.a.</td>
                            <td class="px-4 py-3 text-right font-bold text-bankos-primary">{{ number_format($req->new_rate, 2) }}% p.a.</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 text-bankos-muted">Interest Amount</td>
                            <td class="px-4 py-3 text-right font-medium">₦{{ number_format($currentInterest, 2) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-amber-600">₦{{ number_format($newInterestAmount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 text-bankos-muted">Total Payable</td>
                            <td class="px-4 py-3 text-right font-medium">₦{{ number_format($currentTotalPayable, 2) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-bankos-primary">₦{{ number_format($newTotalOutstanding, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Actions (pending only) --}}
            @if($req->status === 'pending')
            @if(auth()->user()->can('loans.approve_l1') || auth()->user()->hasRole('tenant_admin') || auth()->user()->hasRole('super_admin'))
            <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20 flex items-center justify-between gap-4" x-data="{ showReject: false }">
                <div x-show="!showReject" class="flex gap-3">
                    <form action="{{ route('loan.topups.approve', $req) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary bg-bankos-success border-bankos-success hover:bg-green-700 text-white flex items-center gap-2"
                            onclick="return confirm('Approve this top-up? The current loan will be closed and ₦{{ number_format($topupAmount, 2) }} disbursed.')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Approve Top-up & Disburse
                        </button>
                    </form>
                    <button @click="showReject = true" class="btn btn-secondary text-red-600 border-red-200 hover:bg-red-50">
                        Reject
                    </button>
                </div>
                <div x-show="showReject" x-transition class="flex-1">
                    <form action="{{ route('loan.topups.reject', $req) }}" method="POST" class="flex items-start gap-3">
                        @csrf
                        <textarea name="rejection_reason" rows="2" class="form-input flex-1 text-sm" required placeholder="State reason for rejection..."></textarea>
                        <div class="flex gap-2 mt-0.5">
                            <button type="submit" class="btn btn-primary bg-red-600 border-red-600 hover:bg-red-700 text-white text-sm">Confirm Reject</button>
                            <button type="button" @click="showReject = false" class="btn btn-secondary text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
                @if($req->officer_notes)
                <p class="text-xs text-bankos-muted italic ml-auto">Officer note: {{ $req->officer_notes }}</p>
                @endif
            </div>
            @endif
            @else
            <div class="px-6 py-3 border-t border-bankos-border dark:border-bankos-dark-border text-xs text-bankos-muted">
                Reviewed by {{ $req->reviewedBy?->name ?? 'Unknown' }} on {{ $req->reviewed_at?->format('d M Y, g:ia') }}
                @if($req->officer_notes) &middot; <em>{{ $req->officer_notes }}</em> @endif
            </div>
            @endif
        </div>
        @endforeach

        @if($topups->isEmpty())
        <div class="card p-12 text-center text-bankos-muted">
            <p class="text-sm">No top-up requests found for this loan.</p>
        </div>
        @endif
    </div>
</x-app-layout>
