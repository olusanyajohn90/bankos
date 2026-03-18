<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('workflows.index') }}" class="text-bankos-text-sec hover:text-bankos-primary text-sm">Workflows</a>
                    <span class="text-bankos-muted">/</span>
                    <span class="text-bankos-text text-sm font-medium">{{ $workflow->process_name }}</span>
                </div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight flex items-center gap-3">
                    {{ $workflow->process_name }}
                    <span class="text-sm font-normal px-2.5 py-0.5 rounded-full {{ $workflow->statusBadgeClasses() }}">
                        {{ ucfirst($workflow->status) }}
                    </span>
                    @if($workflow->status === 'pending' && $workflow->due_at)
                    <span class="text-xs px-2 py-0.5 rounded-full border {{ $workflow->slaBadgeClasses() }} font-semibold">
                        {{ $workflow->slaLabel() }}
                    </span>
                    @endif
                </h2>
                <p class="text-xs text-bankos-muted mt-1 font-mono">ID: {{ $workflow->id }}</p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── LEFT: Subject Details + Approval Panel ── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Step Progress --}}
            @if($workflow->total_steps > 1)
            <div class="card p-5">
                <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-4">Approval Progress</p>
                <div class="flex items-start gap-0">
                    @foreach($stepDefs as $i => $step)
                    @php
                        $stepNum    = $i + 1;
                        $isDone     = $stepNum < $workflow->step || ($stepNum <= $workflow->step && $workflow->status === 'approved');
                        $isCurrent  = $stepNum === $workflow->step && $workflow->status === 'pending';
                        $isRejected = $stepNum === $workflow->step && $workflow->status === 'rejected';
                    @endphp
                    <div class="flex flex-col items-center flex-1">
                        <div class="flex items-center w-full">
                            @if($i > 0)
                            <div class="h-0.5 flex-1 {{ $isDone ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                            @endif
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                                {{ $isDone     ? 'bg-green-500 text-white' :
                                   ($isCurrent  ? 'bg-bankos-primary text-white ring-4 ring-bankos-primary/20' :
                                   ($isRejected ? 'bg-red-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-bankos-muted')) }}">
                                @if($isDone)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @elseif($isRejected)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                @else
                                    {{ $stepNum }}
                                @endif
                            </div>
                            @if($i < count($stepDefs) - 1)
                            <div class="h-0.5 flex-1 {{ $isDone ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                            @endif
                        </div>
                        <p class="text-xs text-center mt-2 text-bankos-text-sec {{ $isCurrent ? 'font-semibold text-bankos-primary' : '' }}">{{ $step['label'] }}</p>
                        <p class="text-xs text-center text-bankos-muted">{{ $step['role'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Subject Detail Card --}}
            <div class="card p-0">
                <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
                    <h3 class="font-bold text-bankos-text">{{ class_basename($workflow->subject_type) }} Details</h3>
                </div>
                <div class="p-6">
                    @if($workflow->subject_type === \App\Models\Loan::class && $workflow->subject)
                    @php $loan = $workflow->subject; @endphp
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div><dt class="text-bankos-muted text-xs uppercase">Loan Number</dt><dd class="font-semibold text-bankos-primary mt-0.5">{{ $loan->loan_number }}</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">Customer</dt><dd class="font-semibold mt-0.5">{{ $loan->customer?->first_name }} {{ $loan->customer?->last_name }}</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">Principal</dt><dd class="font-semibold text-lg mt-0.5">₦{{ number_format((float) $loan->principal_amount, 2) }}</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">Tenure</dt><dd class="font-semibold mt-0.5">{{ $loan->tenure_days }} months</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">Product</dt><dd class="font-semibold mt-0.5">{{ $loan->loanProduct?->name ?? '—' }}</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">Interest Rate</dt><dd class="font-semibold mt-0.5">{{ $loan->interest_rate }}% p/m</dd></div>
                        <div class="col-span-2"><dt class="text-bankos-muted text-xs uppercase">Purpose</dt><dd class="mt-0.5">{{ $loan->purpose }}</dd></div>
                    </dl>
                    <div class="mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                        <a href="{{ route('loans.show', $loan->id) }}" class="btn btn-secondary text-xs" target="_blank">Open Loan File ↗</a>
                    </div>

                    @elseif($workflow->subject_type === \App\Models\Customer::class && $workflow->subject)
                    @php $customer = $workflow->subject; @endphp
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div><dt class="text-bankos-muted text-xs uppercase">Customer No.</dt><dd class="font-semibold text-bankos-primary mt-0.5">{{ $customer->customer_number }}</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">Full Name</dt><dd class="font-semibold mt-0.5">{{ $customer->first_name }} {{ $customer->last_name }}</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">Email</dt><dd class="mt-0.5">{{ $customer->email ?? '—' }}</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">Phone</dt><dd class="mt-0.5">{{ $customer->phone }}</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">KYC Status</dt>
                            <dd class="mt-0.5"><span class="badge badge-pending text-xs">{{ ucfirst($customer->kyc_status) }}</span></dd>
                        </div>
                        <div><dt class="text-bankos-muted text-xs uppercase">Documents</dt><dd class="mt-0.5">{{ $customer->kycDocuments?->count() ?? 0 }} uploaded</dd></div>
                    </dl>
                    <div class="mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                        <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-secondary text-xs" target="_blank">Open Customer Profile ↗</a>
                    </div>

                    @elseif($workflow->subject_type === \App\Models\LoanTopup::class && $workflow->subject)
                    @php $topup = $workflow->subject; @endphp
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div><dt class="text-bankos-muted text-xs uppercase">Original Loan</dt><dd class="font-semibold text-bankos-primary mt-0.5">{{ $topup->loan?->loan_number }}</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">Customer</dt><dd class="font-semibold mt-0.5">{{ $topup->loan?->customer?->first_name }} {{ $topup->loan?->customer?->last_name }}</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">Top-up Amount</dt><dd class="font-bold text-lg text-bankos-primary mt-0.5">₦{{ number_format((float) $topup->topup_amount, 2) }}</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">New Tenure</dt><dd class="font-semibold mt-0.5">{{ $topup->new_tenure }} months</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">New Rate</dt><dd class="font-semibold mt-0.5">{{ $topup->new_rate }}% p/m</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">Requested By</dt><dd class="mt-0.5">{{ $topup->requestedBy?->name ?? '—' }}</dd></div>
                        <div class="col-span-2"><dt class="text-bankos-muted text-xs uppercase">Reason</dt><dd class="mt-0.5">{{ $topup->reason }}</dd></div>
                    </dl>
                    <div class="mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border flex gap-2">
                        <a href="{{ route('loans.topups.index', $topup->loan_id) }}" class="btn btn-secondary text-xs" target="_blank">Open Top-up Page ↗</a>
                        @if($workflow->status === 'pending')
                        <a href="{{ route('loans.topups.index', $topup->loan_id) }}" class="btn btn-primary text-xs">Approve / Reject on Full Page →</a>
                        @endif
                    </div>

                    @elseif($workflow->subject_type === \App\Models\LoanRestructure::class && $workflow->subject)
                    @php $restructure = $workflow->subject; @endphp
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div><dt class="text-bankos-muted text-xs uppercase">Original Loan</dt><dd class="font-semibold text-bankos-primary mt-0.5">{{ $restructure->loan?->loan_number }}</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">Customer</dt><dd class="font-semibold mt-0.5">{{ $restructure->loan?->customer?->first_name }} {{ $restructure->loan?->customer?->last_name }}</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">Previous Outstanding</dt><dd class="font-semibold mt-0.5">₦{{ number_format((float) $restructure->previous_outstanding, 2) }}</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">New Tenure</dt><dd class="font-semibold mt-0.5">{{ $restructure->new_tenure }} months</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">New Rate</dt><dd class="font-semibold mt-0.5">{{ $restructure->new_rate }}% p/m</dd></div>
                        <div><dt class="text-bankos-muted text-xs uppercase">Requested By</dt><dd class="mt-0.5">{{ $restructure->requestedBy?->name ?? '—' }}</dd></div>
                        <div class="col-span-2"><dt class="text-bankos-muted text-xs uppercase">Reason</dt><dd class="mt-0.5">{{ $restructure->reason }}</dd></div>
                    </dl>
                    <div class="mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border flex gap-2">
                        <a href="{{ route('loans.restructures.index', $restructure->loan_id) }}" class="btn btn-secondary text-xs" target="_blank">Open Restructure Page ↗</a>
                        @if($workflow->status === 'pending')
                        <a href="{{ route('loans.restructures.index', $restructure->loan_id) }}" class="btn btn-primary text-xs">Approve / Reject on Full Page →</a>
                        @endif
                    </div>

                    @else
                    <p class="text-bankos-muted text-sm">Subject details unavailable.</p>
                    @endif
                </div>
            </div>

            {{-- Action Panel --}}
            @if($workflow->status === 'pending')
            @php $canAction = auth()->user()->hasRole($workflow->assigned_role) || auth()->user()->hasRole('tenant_admin'); @endphp
            @if($canAction && !in_array($workflow->subject_type, [\App\Models\LoanTopup::class, \App\Models\LoanRestructure::class]))
            <div class="card p-0 border-bankos-primary/30 dark:border-bankos-primary/20">
                <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-bankos-primary/5">
                    <h3 class="font-bold text-bankos-primary flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Action Required — {{ $workflow->currentStepLabel() }}
                    </h3>
                    <p class="text-xs text-bankos-text-sec mt-1">Your role: <strong class="text-bankos-primary">{{ $workflow->assigned_role }}</strong></p>
                </div>
                <div class="p-6" x-data="{ action: '' }">
                    <form method="POST" action="{{ route('workflows.action', $workflow->id) }}">
                        @csrf
                        <input type="hidden" name="action" :value="action" x-bind:value="action">

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-bankos-text mb-1">Notes / Decision Rationale</label>
                            <textarea name="notes" rows="3"
                                class="input w-full text-sm"
                                placeholder="Add notes, conditions, or reason for your decision..."></textarea>
                            <p class="text-xs text-bankos-muted mt-1">Notes are recorded in the audit trail.</p>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" @click="action = 'approve'"
                                class="btn btn-primary flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Approve
                                @if($workflow->step < $workflow->total_steps)
                                    <span class="text-xs opacity-80">(advance to step {{ $workflow->step + 1 }})</span>
                                @endif
                            </button>
                            <button type="submit" @click="action = 'reject'"
                                class="btn btn-danger flex items-center gap-2"
                                onclick="return confirm('Reject this workflow? This cannot be undone.')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Reject
                            </button>
                            <button type="submit" @click="action = 'comment'"
                                class="btn btn-secondary flex items-center gap-2 ml-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                Comment Only
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @elseif(!$canAction)
            <div class="card p-5 border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/10">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    <strong>Awaiting action</strong> — This workflow requires the <code class="bg-yellow-100 dark:bg-yellow-800 px-1 rounded">{{ $workflow->assigned_role }}</code> role to proceed.
                </p>

                {{-- Comment-only form for observers --}}
                <form method="POST" action="{{ route('workflows.action', $workflow->id) }}" class="mt-4">
                    @csrf
                    <input type="hidden" name="action" value="comment">
                    <div class="flex gap-2">
                        <input type="text" name="notes" class="input text-sm flex-1" placeholder="Add a comment for the approver...">
                        <button type="submit" class="btn btn-secondary text-sm">Add Comment</button>
                    </div>
                </form>
            </div>
            @endif
            @endif

        </div>

        {{-- ── RIGHT: Audit Trail + Workflow Meta ── --}}
        <div class="space-y-5">

            {{-- Workflow Meta --}}
            <div class="card p-5">
                <h3 class="font-bold text-bankos-text text-sm mb-4">Workflow Details</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Process</dt>
                        <dd class="font-medium text-right">{{ $workflow->process_name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Status</dt>
                        <dd><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $workflow->statusBadgeClasses() }}">{{ ucfirst($workflow->status) }}</span></dd>
                    </div>
                    @if($workflow->total_steps > 1)
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Current Step</dt>
                        <dd class="font-medium">{{ $workflow->step }} of {{ $workflow->total_steps }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Assigned Role</dt>
                        <dd class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ $workflow->assigned_role }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Submitted</dt>
                        <dd class="text-right text-xs">{{ $workflow->started_at->format('d M Y') }}<br>{{ $workflow->started_at->format('H:i') }}</dd>
                    </div>
                    @if($workflow->due_at)
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">SLA Deadline</dt>
                        <dd class="text-right text-xs {{ $workflow->isOverdue() ? 'text-red-600 font-semibold' : '' }}">
                            {{ $workflow->due_at->format('d M Y') }}<br>{{ $workflow->due_at->format('H:i') }}
                        </dd>
                    </div>
                    @endif
                    @if($workflow->ended_at)
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Resolved</dt>
                        <dd class="text-right text-xs">{{ $workflow->ended_at->format('d M Y, H:i') }}</dd>
                    </div>
                    @endif
                    @if($workflow->actionedBy)
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Actioned By</dt>
                        <dd class="font-medium text-right">{{ $workflow->actionedBy->name }}</dd>
                    </div>
                    @endif
                    @if($workflow->notes)
                    <div class="pt-2 border-t border-bankos-border dark:border-bankos-dark-border">
                        <dt class="text-bankos-muted text-xs uppercase mb-1">Final Notes</dt>
                        <dd class="text-sm bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">{{ $workflow->notes }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Audit Trail / Comment Timeline --}}
            <div class="card p-5">
                <h3 class="font-bold text-bankos-text text-sm mb-4 flex items-center justify-between">
                    Audit Trail
                    <span class="text-xs text-bankos-muted font-normal">{{ $workflow->comments->count() }} entries</span>
                </h3>

                @if($workflow->comments->isEmpty())
                    <p class="text-xs text-bankos-muted text-center py-4">No audit entries yet.</p>
                @else
                <div class="space-y-0 relative">
                    {{-- Vertical connector line --}}
                    <div class="absolute left-3.5 top-4 bottom-4 w-px bg-gray-200 dark:bg-gray-700"></div>

                    @foreach($workflow->comments as $comment)
                    @php
                        $dotColor = match($comment->action) {
                            'approved'   => 'bg-green-500',
                            'rejected'   => 'bg-red-500',
                            'escalated'  => 'bg-orange-500',
                            'reassigned' => 'bg-blue-500',
                            default      => 'bg-gray-400',
                        };
                        $labelColor = match($comment->action) {
                            'approved'   => 'text-green-600',
                            'rejected'   => 'text-red-600',
                            'escalated'  => 'text-orange-600',
                            default      => 'text-bankos-text-sec',
                        };
                    @endphp
                    <div class="flex gap-3 pb-5 relative">
                        <div class="w-7 h-7 rounded-full {{ $dotColor }} flex items-center justify-center shrink-0 z-10 ring-2 ring-white dark:ring-bankos-dark-card">
                            @if($comment->action === 'approved')
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            @elseif($comment->action === 'rejected')
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                            @elseif($comment->action === 'escalated')
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            @else
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-bankos-text">{{ $comment->user?->name ?? 'System' }}</span>
                                <span class="text-xs font-semibold {{ $labelColor }}">{{ $comment->actionLabel() }}</span>
                                <span class="text-xs text-bankos-muted ml-auto">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-bankos-text-sec mt-0.5 break-words">{{ $comment->comment }}</p>
                            <p class="text-xs text-bankos-muted mt-0.5">{{ $comment->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Quick comment box (always visible for any authenticated user) --}}
                @if($workflow->status === 'pending')
                <form method="POST" action="{{ route('workflows.action', $workflow->id) }}" class="mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                    @csrf
                    <input type="hidden" name="action" value="comment">
                    <div class="flex gap-2">
                        <input type="text" name="notes" class="input text-sm flex-1" placeholder="Add a note to the audit trail...">
                        <button type="submit" class="btn btn-secondary text-sm whitespace-nowrap">Add Note</button>
                    </div>
                </form>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
