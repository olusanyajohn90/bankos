@extends('layouts.app')

@section('title', 'Approval Request — ' . $approvalRequest->reference)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Back + Header --}}
    <div>
        <a href="{{ route('hr.approvals.requests') }}" class="text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1 mb-2">
            ← Back to Requests
        </a>
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $approvalRequest->reference }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ ucwords(str_replace('_', ' ', $approvalRequest->action_type)) }}
                    @if($approvalRequest->amount) · ₦{{ number_format($approvalRequest->amount) }} @endif
                    · Initiated by {{ $approvalRequest->initiatedBy?->name ?? '—' }}
                    · {{ $approvalRequest->created_at->format('d M Y, H:i') }}
                </p>
            </div>
            @php
                $statusBadge = match($approvalRequest->status) {
                    'pending', 'in_review' => 'bg-amber-100 text-amber-800',
                    'approved' => 'bg-green-100 text-green-700',
                    'rejected' => 'bg-red-100 text-red-700',
                    'cancelled' => 'bg-gray-100 text-gray-500',
                    default => 'bg-gray-100 text-gray-500',
                };
            @endphp
            <span class="px-3 py-1.5 rounded-full text-sm font-bold {{ $statusBadge }}">
                {{ ucfirst(str_replace('_', ' ', $approvalRequest->status)) }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Summary card --}}
    <div class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Request Summary</h2>
        <p class="text-base text-gray-800 leading-relaxed">{{ $approvalRequest->summary }}</p>
        @if($approvalRequest->metadata)
            <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                @foreach($approvalRequest->metadata as $k => $v)
                    <div>
                        <span class="text-gray-400">{{ ucwords(str_replace('_', ' ', $k)) }}</span>
                        <p class="font-medium text-gray-700 mt-0.5">{{ $v }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Approval Chain --}}
    <div class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Approval Chain — Step {{ $approvalRequest->current_step }} of {{ $approvalRequest->total_steps }}</h2>
        <div class="space-y-3">
            @foreach($approvalRequest->steps->sortBy('step_number') as $step)
                @php
                    $isCurrentStep = $step->step_number === $approvalRequest->current_step && $approvalRequest->isPending();
                    $stepColor = match($step->status) {
                        'approved' => 'border-green-400 bg-green-50',
                        'rejected' => 'border-red-400 bg-red-50',
                        'pending'  => $isCurrentStep ? 'border-amber-400 bg-amber-50' : 'border-gray-200 bg-gray-50',
                        default    => 'border-gray-200 bg-gray-50',
                    };
                    $dotColor = match($step->status) {
                        'approved' => 'bg-green-500',
                        'rejected' => 'bg-red-500',
                        'pending'  => $isCurrentStep ? 'bg-amber-400' : 'bg-gray-300',
                        default    => 'bg-gray-300',
                    };
                @endphp
                <div class="relative pl-8">
                    <div class="absolute left-0 top-3 w-3.5 h-3.5 rounded-full {{ $dotColor }} ring-2 ring-white"></div>
                    <div class="rounded-xl border {{ $stepColor }} p-4">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div>
                                <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Step {{ $step->step_number }}</span>
                                <p class="font-semibold text-gray-900 text-sm">{{ $step->step_name }}</p>
                                @if($step->assigned_to && $step->assignedTo)
                                    <p class="text-xs text-gray-400 mt-0.5">Assigned to: {{ $step->assignedTo->name }}</p>
                                @elseif($step->assigned_role)
                                    <p class="text-xs text-gray-400 mt-0.5">Role: {{ $step->assigned_role }}</p>
                                @endif
                            </div>
                            @if($step->status === 'approved')
                                <div class="text-right">
                                    <span class="text-xs font-bold text-green-700">✓ Approved</span>
                                    <p class="text-xs text-gray-400">by {{ $step->actionedBy?->name ?? '—' }} on {{ $step->actioned_at?->format('d M Y') }}</p>
                                    @if($step->notes) <p class="text-xs text-gray-500 italic mt-1">{{ $step->notes }}</p> @endif
                                </div>
                            @elseif($step->status === 'rejected')
                                <div class="text-right">
                                    <span class="text-xs font-bold text-red-700">✗ Rejected</span>
                                    <p class="text-xs text-gray-400">by {{ $step->actionedBy?->name ?? '—' }} on {{ $step->actioned_at?->format('d M Y') }}</p>
                                    @if($step->notes) <p class="text-xs text-red-600 italic mt-1">{{ $step->notes }}</p> @endif
                                </div>
                            @elseif($isCurrentStep)
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">Awaiting Action</span>
                            @else
                                <span class="text-xs text-gray-400">Waiting</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Action Buttons --}}
    @if($approvalRequest->isPending())
        @php
            $currentStepRecord = $approvalRequest->currentStepRecord();
            $canAct = !$currentStepRecord?->assigned_to
                || $currentStepRecord->assigned_to === auth()->id()
                || auth()->user()->hasAnyRole(['super_admin','tenant_admin']);
        @endphp
        @if($canAct)
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Your Decision — Step {{ $approvalRequest->current_step }}: {{ $currentStepRecord?->step_name }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <form action="{{ route('hr.approvals.requests.approve', $approvalRequest) }}" method="POST">
                    @csrf
                    <textarea name="notes" rows="2" placeholder="Optional approval notes…" class="form-input w-full text-sm mb-2 resize-none"></textarea>
                    <button type="submit" class="w-full btn text-sm font-semibold text-white bg-green-600 hover:bg-green-700 py-2.5 rounded-lg">
                        ✓ Approve Step {{ $approvalRequest->current_step }}
                    </button>
                </form>
                <form action="{{ route('hr.approvals.requests.reject', $approvalRequest) }}" method="POST">
                    @csrf
                    <textarea name="notes" rows="2" required placeholder="Reason for rejection (required)…" class="form-input w-full text-sm mb-2 resize-none"></textarea>
                    <button type="submit" onclick="return confirm('Reject this request?')" class="w-full btn text-sm font-semibold text-white bg-red-600 hover:bg-red-700 py-2.5 rounded-lg">
                        ✗ Reject Request
                    </button>
                </form>
            </div>
            @if($approvalRequest->initiated_by === auth()->id())
                <form action="{{ route('hr.approvals.requests.cancel', $approvalRequest) }}" method="POST" class="mt-3">
                    @csrf
                    <button onclick="return confirm('Cancel this request?')" class="text-xs text-gray-400 hover:text-red-500">Cancel Request</button>
                </form>
            @endif
        </div>
        @endif
    @endif

    @if($approvalRequest->isApproved() || $approvalRequest->isRejected())
    <div class="card p-5 {{ $approvalRequest->isApproved() ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }} border rounded-xl">
        <p class="text-sm font-semibold {{ $approvalRequest->isApproved() ? 'text-green-800' : 'text-red-800' }}">
            {{ $approvalRequest->isApproved() ? '✓ Fully Approved' : '✗ Rejected' }}
            @if($approvalRequest->finalActionedBy) — by {{ $approvalRequest->finalActionedBy->name }} @endif
            @if($approvalRequest->final_actioned_at) on {{ $approvalRequest->final_actioned_at->format('d M Y, H:i') }} @endif
        </p>
        @if($approvalRequest->final_notes)
            <p class="text-xs text-gray-600 mt-1 italic">{{ $approvalRequest->final_notes }}</p>
        @endif
    </div>
    @endif

</div>
@endsection
