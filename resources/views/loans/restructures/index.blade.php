<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <a href="{{ route('loans.show', $loan) }}" class="text-sm text-bankos-primary hover:underline flex items-center gap-1 mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    Back to {{ $loan->loan_number }}
                </a>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Restructure History</h2>
                <p class="text-sm text-bankos-text-sec mt-1">{{ $loan->customer?->first_name }} {{ $loan->customer?->last_name }} &middot; {{ $loan->loan_number }}</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @foreach($restructures as $req)
        @php
            // Outstanding principal at time of request
            $principal = (float) $req->previous_outstanding;

            // Remaining months from live loan data: original_tenure minus paid installments (from DB)
            $currentRemainingMonths = max(1, $loan->remaining_months);

            // CURRENT side: interest = principal × (annual_rate / 12) × remaining_months
            $currentInterest      = $principal * ($req->previous_rate / 100 / 12) * $currentRemainingMonths;
            $currentTotalPayable  = $principal + $currentInterest;
            $oldMonthlyInstalment = round($currentTotalPayable / $currentRemainingMonths, 2);

            // PROPOSED side: same formula with new rate and new tenure
            $newInterestAmount    = $principal * ($req->new_rate / 100 / 12) * $req->new_tenure;
            $newTotalOutstanding  = $principal + $newInterestAmount;
            $newMonthlyInstalment = round($newTotalOutstanding / $req->new_tenure, 2);

            $newMaturityDate = now()->addMonths($req->new_tenure);
            $instDiff        = $newMonthlyInstalment - $oldMonthlyInstalment;
            $interestSaving  = $currentInterest - $newInterestAmount;
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                    Based on outstanding principal of ₦{{ number_format($principal, 2) }} with {{ $currentRemainingMonths }} months remaining. New schedule resets from approval date.
                </div>
                @endif

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    {{-- New Monthly Instalment --}}
                    <div class="rounded-lg border border-bankos-border dark:border-bankos-dark-border p-4">
                        <p class="text-[10px] uppercase font-bold text-bankos-muted mb-1">New Monthly Instalment</p>
                        <p class="text-xl font-bold text-bankos-text dark:text-white">₦{{ number_format($newMonthlyInstalment, 2) }}</p>
                        <p class="text-xs mt-1 {{ $instDiff > 0 ? 'text-red-500' : 'text-bankos-success' }}">
                            {{ $instDiff > 0 ? '▲' : '▼' }} ₦{{ number_format(abs($instDiff), 2) }} vs current
                        </p>
                    </div>
                    {{-- New Total Payable --}}
                    <div class="rounded-lg border border-bankos-border dark:border-bankos-dark-border p-4">
                        <p class="text-[10px] uppercase font-bold text-bankos-muted mb-1">New Total Payable</p>
                        <p class="text-xl font-bold">₦{{ number_format($newTotalOutstanding, 2) }}</p>
                        <p class="text-xs mt-1 {{ $interestSaving > 0 ? 'text-bankos-success' : 'text-red-500' }}">
                            {{ $interestSaving > 0 ? 'Saves' : 'Extra' }} ₦{{ number_format(abs($interestSaving), 2) }} in interest
                        </p>
                    </div>
                    {{-- Remaining Months --}}
                    <div class="rounded-lg border border-bankos-border dark:border-bankos-dark-border p-4">
                        <p class="text-[10px] uppercase font-bold text-bankos-muted mb-1">Tenure Change</p>
                        <p class="text-xl font-bold">{{ $req->new_tenure }} <span class="text-sm font-normal text-bankos-muted">months</span></p>
                        <p class="text-xs mt-1 {{ $req->new_tenure > $currentRemainingMonths ? 'text-bankos-warning' : 'text-bankos-success' }}">
                            was {{ $currentRemainingMonths }} months remaining
                            ({{ $req->new_tenure > $currentRemainingMonths ? '+' : '' }}{{ $req->new_tenure - $currentRemainingMonths }} mo)
                        </p>
                    </div>
                    {{-- New Maturity Date --}}
                    <div class="rounded-lg border border-bankos-border dark:border-bankos-dark-border p-4">
                        <p class="text-[10px] uppercase font-bold text-bankos-muted mb-1">New Maturity Date</p>
                        <p class="text-xl font-bold">{{ $newMaturityDate->format('M Y') }}</p>
                        <p class="text-xs text-bankos-muted mt-1">{{ $newMaturityDate->format('d M Y') }}</p>
                    </div>
                </div>

                {{-- Side-by-side comparison --}}
                <table class="w-full text-sm border border-bankos-border dark:border-bankos-dark-border rounded-lg overflow-hidden">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 text-xs uppercase tracking-wider text-bankos-text-sec">
                            <th class="px-4 py-3 font-semibold text-left">Parameter</th>
                            <th class="px-4 py-3 font-semibold text-right">Current ({{ $currentRemainingMonths }} mo remaining)</th>
                            <th class="px-4 py-3 font-semibold text-right text-bankos-primary">Proposed ({{ $req->new_tenure }} mo)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        <tr>
                            <td class="px-4 py-3 text-bankos-muted">Outstanding Principal</td>
                            <td class="px-4 py-3 text-right font-medium" colspan="2">₦{{ number_format($principal, 2) }} <span class="text-xs text-bankos-muted">(basis for both calculations)</span></td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 text-bankos-muted">Interest Rate</td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($req->previous_rate, 2) }}% p.a.</td>
                            <td class="px-4 py-3 text-right font-bold text-bankos-primary">{{ number_format($req->new_rate, 2) }}% p.a.</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 text-bankos-muted">Interest Amount</td>
                            <td class="px-4 py-3 text-right font-medium text-amber-600">₦{{ number_format($currentInterest, 2) }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ $newInterestAmount < $currentInterest ? 'text-bankos-success' : 'text-amber-600' }}">
                                ₦{{ number_format($newInterestAmount, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 text-bankos-muted">Total Payable</td>
                            <td class="px-4 py-3 text-right font-medium">₦{{ number_format($currentTotalPayable, 2) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-bankos-primary">₦{{ number_format($newTotalOutstanding, 2) }}</td>
                        </tr>
                        <tr class="bg-gray-50/50 dark:bg-bankos-dark-bg/20">
                            <td class="px-4 py-3 text-bankos-muted font-semibold">Monthly Instalment</td>
                            <td class="px-4 py-3 text-right font-bold">₦{{ number_format($oldMonthlyInstalment, 2) }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ $newMonthlyInstalment > $oldMonthlyInstalment ? 'text-red-500' : 'text-bankos-success' }}">
                                ₦{{ number_format($newMonthlyInstalment, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Actions (pending only) --}}
            @if($req->status === 'pending')
            @if(auth()->user()->can('loans.approve_l1') || auth()->user()->hasRole('tenant_admin'))
            <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20 flex items-center justify-between gap-4" x-data="{ showReject: false }">
                <div x-show="!showReject" class="flex gap-3">
                    <form action="{{ route('loan.restructures.approve', $req) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary bg-bankos-success border-bankos-success hover:bg-green-700 text-white flex items-center gap-2"
                            onclick="return confirm('Approve this restructure? The loan terms will be updated immediately and a new schedule generated.')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Approve Restructure
                        </button>
                    </form>
                    <button @click="showReject = true" class="btn btn-secondary text-red-600 border-red-200 hover:bg-red-50">
                        Reject
                    </button>
                </div>
                <div x-show="showReject" x-transition class="flex-1">
                    <form action="{{ route('loan.restructures.reject', $req) }}" method="POST" class="flex items-start gap-3">
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

        @if($restructures->isEmpty())
        <div class="card p-12 text-center text-bankos-muted">
            <p class="text-sm">No restructure requests found for this loan.</p>
        </div>
        @endif
    </div>
</x-app-layout>
