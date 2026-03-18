@extends('layouts.app')
@section('title', 'Support Tickets')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('support.dashboard') }}" class="text-xs text-gray-400 hover:text-gray-600">← Support</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Tickets</h1>
        </div>
        <a href="{{ route('support.tickets.create') }}" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ New Ticket</a>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets…" class="form-input text-sm flex-1 min-w-[180px]">
        <select name="status" class="form-input text-sm">
            <option value="">All Statuses</option>
            @foreach(['open','in_progress','pending','resolved','closed','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="priority" class="form-input text-sm">
            <option value="">All Priorities</option>
            @foreach(['low','medium','high','critical'] as $p)
                <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
        <select name="team_id" class="form-input text-sm">
            <option value="">All Teams</option>
            @foreach($teams as $team)
                <option value="{{ $team->id }}" {{ request('team_id') === $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
            @endforeach
        </select>
        <select name="assigned_to" class="form-input text-sm">
            <option value="">All Agents</option>
            <option value="me" {{ request('assigned_to') === 'me' ? 'selected' : '' }}>My Tickets</option>
        </select>
        <button type="submit" class="btn text-sm bg-gray-700 text-white px-4 py-2 rounded-lg">Filter</button>
        @if(request()->hasAny(['search','status','priority','team_id','assigned_to']))
            <a href="{{ route('support.tickets.index') }}" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Clear</a>
        @endif
    </form>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ticket</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requester</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Team / Agent</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Priority</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SLA</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tickets as $t)
                    <tr class="hover:bg-gray-50 {{ $t->isOverdue() ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3">
                            <a href="{{ route('support.tickets.show', $t) }}" class="font-medium text-blue-700 hover:underline">{{ $t->subject }}</a>
                            <p class="text-xs text-gray-400">{{ $t->ticket_number }} · {{ $t->category?->name ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-700 text-xs">
                            {{ $t->requester_name }}<br>
                            {{ $t->requester_phone ?? $t->requester_email }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">
                            {{ $t->team?->name ?? '—' }}<br>
                            <span class="text-gray-400">{{ $t->assignedTo?->name ?? 'Unassigned' }}</span>
                        </td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $t->priorityColor() }}">{{ ucfirst($t->priority) }}</span></td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $t->statusColor() }}">{{ ucwords(str_replace('_',' ',$t->status)) }}</span></td>
                        <td class="px-4 py-3 text-xs">
                            @if($t->sla_resolution_due_at)
                                <span class="{{ $t->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                    {{ $t->isOverdue() ? '⚠ ' : '' }}{{ $t->sla_resolution_due_at->format('d M H:i') }}
                                </span>
                            @else—@endif
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-400">{{ $t->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No tickets found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($tickets->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $tickets->links() }}</div>
        @endif
    </div>
</div>
@endsection
