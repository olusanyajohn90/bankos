<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('mandates.index') }}" class="text-bankos-muted hover:text-bankos-primary transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </a>
                    <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                        Pending Mandate Approvals
                    </h2>
                </div>
                <p class="text-sm text-bankos-text-sec">Transactions awaiting multi-signatory authorisation</p>
            </div>
            @if($approvals->total() > 0)
            <span class="badge badge-pending text-sm px-3 py-1.5">
                {{ $approvals->total() }} pending
            </span>
            @endif
        </div>
    </x-slot>

    {{-- Back link --}}
    <div class="mb-5">
        <a href="{{ route('mandates.index') }}" class="inline-flex items-center gap-1.5 text-sm text-bankos-muted hover:text-bankos-primary transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Back to Mandates
        </a>
    </div>

    @if($approvals->isEmpty())
    <div class="card p-12 text-center">
        <svg class="w-14 h-14 mx-auto mb-4 text-bankos-success/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="font-bold text-lg text-bankos-text dark:text-white mb-2">All clear!</h3>
        <p class="text-bankos-muted">There are no pending mandate approvals at this time.</p>
    </div>

    @else
    <div class="space-y-5">
        @foreach($approvals as $approval)

        @php
            $signingRuleLabels = [
                'sole'        => 'Sole Signatory',
                'any_one'     => 'Any One',
                'any_two'     => 'Any Two',
                'a_and_b'     => 'A and B',
                'a_and_any_b' => 'A and Any B',
                'all'         => 'All Signatories',
            ];
        @endphp

        <div class="card p-0 overflow-hidden" x-data="{ showApprove: false, showReject: false }">

            {{-- Card header --}}
            <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20 flex flex-wrap gap-3 items-center justify-between">
                <div class="flex items-center gap-3">
                    <div>
                        <p class="font-bold text-bankos-primary font-mono text-sm">{{ $approval->account?->account_number }}</p>
                        <p class="text-xs text-bankos-muted">{{ $approval->account?->customer?->full_name ?? '—' }}</p>
                    </div>
                    <span class="badge badge-pending">Pending</span>
                </div>
                <div class="text-right text-xs text-bankos-muted">
                    <p>Required rule: <span class="font-semibold text-bankos-text dark:text-white">{{ $signingRuleLabels[$approval->mandate->signing_rule] ?? ucfirst(str_replace('_', ' ', $approval->mandate->signing_rule)) }}</span></p>
                    <p class="mt-0.5">{{ $approval->created_at->format('d M Y, H:i') }}</p>
                </div>
            </div>

            {{-- Card body --}}
            <div class="px-6 py-5">

                {{-- Amount + description --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                    <div>
                        <p class="text-xs text-bankos-muted uppercase tracking-wider mb-1">Amount</p>
                        <p class="text-2xl font-bold text-bankos-text dark:text-white">₦{{ number_format($approval->amount, 2) }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs text-bankos-muted uppercase tracking-wider mb-1">Transaction Description</p>
                        <p class="font-medium text-bankos-text dark:text-white">{{ $approval->transaction_description }}</p>
                        <p class="text-xs text-bankos-muted mt-1">
                            Requested by <span class="font-medium">{{ $approval->requested_by?->name ?? 'System' }}</span>
                            &bull; {{ $approval->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>

                {{-- Actions already recorded --}}
                @if($approval->actions->isNotEmpty())
                <div class="mb-5 p-3 rounded-lg bg-gray-50 dark:bg-bankos-dark-bg/40 border border-bankos-border dark:border-bankos-dark-border">
                    <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-2">Actions Recorded</p>
                    <div class="space-y-2">
                        @foreach($approval->actions as $action)
                        <div class="flex items-start gap-2 text-xs">
                            @if($action->action === 'approve')
                                <svg class="w-3.5 h-3.5 text-bankos-success mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-bankos-success font-semibold">Approved</span>
                            @else
                                <svg class="w-3.5 h-3.5 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-red-500 font-semibold">Rejected</span>
                            @endif
                            <div class="flex-1">
                                <span class="text-bankos-text dark:text-white font-medium">{{ $action->actioned_by?->name ?? 'Unknown' }}</span>
                                @if($action->signatory)
                                    <span class="text-bankos-muted">&mdash; {{ $action->signatory->signatory_name }}</span>
                                @endif
                                <span class="text-bankos-muted ml-1">&bull; {{ $action->created_at->diffForHumans() }}</span>
                                @if($action->notes)
                                <p class="text-bankos-text-sec mt-0.5 italic">{{ $action->notes }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Approve / Reject buttons --}}
                <div class="flex flex-wrap gap-3">
                    <button type="button"
                        @click="showApprove = !showApprove; showReject = false"
                        class="btn btn-primary text-sm flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Approve
                    </button>
                    <button type="button"
                        @click="showReject = !showReject; showApprove = false"
                        class="btn btn-danger text-sm flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        Reject
                    </button>
                </div>

                {{-- Approve form (hidden by default) --}}
                <div x-show="showApprove"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="mt-4 p-4 rounded-lg border border-green-200 dark:border-green-800 bg-green-50/60 dark:bg-green-900/10"
                    style="display:none">
                    <form action="{{ route('mandates.approvals.approve', $approval) }}" method="POST">
                        @csrf
                        <label class="block text-sm font-medium text-bankos-text dark:text-white mb-1.5">
                            Notes <span class="font-normal text-bankos-muted">(optional)</span>
                        </label>
                        <textarea name="notes" rows="2" class="form-input w-full text-sm mb-3"
                            placeholder="Add any notes for the audit trail..."></textarea>
                        <div class="flex gap-2">
                            <button type="submit" class="btn btn-primary text-sm">Confirm Approval</button>
                            <button type="button" @click="showApprove = false" class="btn btn-secondary text-sm">Cancel</button>
                        </div>
                    </form>
                </div>

                {{-- Reject form (hidden by default) --}}
                <div x-show="showReject"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="mt-4 p-4 rounded-lg border border-red-200 dark:border-red-800 bg-red-50/60 dark:bg-red-900/10"
                    style="display:none">
                    <form action="{{ route('mandates.approvals.reject', $approval) }}" method="POST">
                        @csrf
                        <label class="block text-sm font-medium text-bankos-text dark:text-white mb-1.5">
                            Reason for rejection <span class="text-red-500">*</span>
                        </label>
                        <textarea name="notes" rows="2" class="form-input w-full text-sm mb-3"
                            placeholder="State the reason for rejection..." required></textarea>
                        <div class="flex gap-2">
                            <button type="submit" class="btn btn-danger text-sm">Confirm Rejection</button>
                            <button type="button" @click="showReject = false" class="btn btn-secondary text-sm">Cancel</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        @endforeach
    </div>

    @if($approvals->hasPages())
    <div class="mt-6">
        {{ $approvals->links() }}
    </div>
    @endif

    @endif
</x-app-layout>
