@extends('layouts.app')
@section('title', $ticket->ticket_number)
@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ showEscalate: false }">

    <div>
        <a href="{{ route('support.tickets.index') }}" class="text-xs text-gray-400 hover:text-gray-600">← Tickets</a>
        <div class="flex items-start justify-between gap-4 flex-wrap mt-1">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $ticket->subject }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $ticket->ticket_number }} · {{ $ticket->channel }} · {{ $ticket->created_at->format('d M Y H:i') }}</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <span class="px-3 py-1.5 rounded-full text-sm font-bold {{ $ticket->priorityColor() }}">{{ ucfirst($ticket->priority) }}</span>
                <span class="px-3 py-1.5 rounded-full text-sm font-bold {{ $ticket->statusColor() }}">{{ ucwords(str_replace('_',' ',$ticket->status)) }}</span>
            </div>
        </div>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    @if($ticket->isOverdue())
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm font-semibold">
            ⚠ SLA Breached — Resolution was due {{ $ticket->sla_resolution_due_at->diffForHumans() }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Info panel --}}
        <div class="space-y-4">
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Ticket Info</h2>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-xs text-gray-400">Requester</dt><dd class="font-medium text-gray-800">{{ $ticket->requester_name }}</dd></div>
                    @if($ticket->requester_phone)<div><dt class="text-xs text-gray-400">Phone</dt><dd class="text-gray-700">{{ $ticket->requester_phone }}</dd></div>@endif
                    @if($ticket->account_number)<div><dt class="text-xs text-gray-400">Account</dt><dd class="font-mono font-semibold text-gray-800">{{ $ticket->account_number }}</dd></div>@endif
                    <div><dt class="text-xs text-gray-400">Category</dt><dd class="text-gray-700">{{ $ticket->category?->name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Team</dt><dd class="text-gray-700">{{ $ticket->team?->name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Agent</dt><dd class="text-gray-700">{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Opened by</dt><dd class="text-gray-700">{{ $ticket->createdBy?->name }}</dd></div>
                    @if($ticket->sla_response_due_at)
                        <div><dt class="text-xs text-gray-400">Response due</dt>
                            <dd class="{{ $ticket->isResponseOverdue() ? 'text-red-600 font-semibold' : 'text-gray-700' }}">{{ $ticket->sla_response_due_at->format('d M H:i') }}</dd></div>
                    @endif
                    @if($ticket->sla_resolution_due_at)
                        <div><dt class="text-xs text-gray-400">Resolution due</dt>
                            <dd class="{{ $ticket->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-700' }}">{{ $ticket->sla_resolution_due_at->format('d M H:i') }}</dd></div>
                    @endif
                    @if($ticket->first_responded_at)
                        <div><dt class="text-xs text-gray-400">First response</dt><dd class="text-gray-700">{{ $ticket->first_responded_at->format('d M H:i') }}</dd></div>
                    @endif
                </dl>
            </div>

            {{-- Assign --}}
            @if($ticket->isOpen())
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Assign</h2>
                <form action="{{ route('support.tickets.assign', $ticket) }}" method="POST" class="space-y-3">
                    @csrf
                    <select name="team_id" class="form-input w-full text-sm">
                        <option value="">— Team —</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ $ticket->team_id === $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    <select name="assigned_to" class="form-input w-full text-sm">
                        <option value="">— Agent —</option>
                        @foreach($agents as $a)
                            <option value="{{ $a->id }}" {{ $ticket->assigned_to == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                        @endforeach
                    </select>
                    <button class="w-full btn text-sm bg-gray-700 hover:bg-gray-800 text-white py-2 rounded-lg">Assign</button>
                </form>
            </div>

            {{-- Quick actions --}}
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Actions</h2>
                <div class="space-y-2">
                    <form action="{{ route('support.tickets.resolve', $ticket) }}" method="POST">
                        @csrf
                        <button class="w-full btn text-sm bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg">Mark Resolved</button>
                    </form>
                    <button @click="showEscalate = !showEscalate" class="w-full btn text-sm bg-orange-500 hover:bg-orange-600 text-white py-2 rounded-lg">Escalate</button>
                    <form action="{{ route('support.tickets.close', $ticket) }}" method="POST" onsubmit="return confirm('Close this ticket?')">
                        @csrf
                        <button class="w-full btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 rounded-lg">Close</button>
                    </form>
                </div>
                <div x-show="showEscalate" x-transition class="mt-3">
                    <form action="{{ route('support.tickets.escalate', $ticket) }}" method="POST" class="space-y-2">
                        @csrf
                        <select name="escalated_to" required class="form-input w-full text-sm">
                            <option value="">— Escalate to —</option>
                            @foreach($agents as $a)
                                <option value="{{ $a->id }}">{{ $a->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="reason" class="form-input w-full text-sm" placeholder="Escalation reason">
                        <button class="w-full btn text-sm bg-orange-600 text-white py-2 rounded-lg">Confirm Escalate</button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        {{-- Right: Thread --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Original description --}}
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Description</h2>
                <p class="text-sm text-gray-800 whitespace-pre-line">{{ $ticket->description }}</p>
            </div>

            {{-- Thread --}}
            @if($ticket->replies->isNotEmpty())
            <div class="space-y-3">
                @foreach($ticket->replies as $reply)
                    <div class="card p-4 {{ $reply->is_internal ? 'bg-yellow-50 border border-yellow-100' : '' }}">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-800">{{ $reply->author?->name }}</span>
                                @if($reply->is_internal)<span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded-full font-medium">Internal note</span>@endif
                                @if($reply->type !== 'reply' && $reply->type !== 'internal_note')
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-xs rounded-full">{{ ucwords(str_replace('_',' ',$reply->type)) }}</span>
                                @endif
                            </div>
                            <span class="text-xs text-gray-400">{{ $reply->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $reply->body }}</p>
                    </div>
                @endforeach
            </div>
            @endif

            {{-- Reply form --}}
            @if($ticket->isOpen())
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Add Reply</h2>
                <form action="{{ route('support.tickets.reply', $ticket) }}" method="POST" class="space-y-3">
                    @csrf
                    <textarea name="body" rows="4" required class="form-input w-full text-sm resize-none" placeholder="Type your reply…"></textarea>
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300">
                                Internal note (hidden from customer)
                            </label>
                        </div>
                        <div class="flex items-center gap-2">
                            <select name="status" class="form-input text-sm">
                                <option value="">Keep status</option>
                                @foreach(['open','in_progress','pending','resolved'] as $s)
                                    <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>→ {{ ucwords(str_replace('_',' ',$s)) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Send Reply</button>
                        </div>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
