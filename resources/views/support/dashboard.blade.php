@extends('layouts.app')
@section('title', 'Support Dashboard')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Support Centre</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
        </div>
        <a href="{{ route('support.tickets.create') }}" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ New Ticket</a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        <div class="card p-4 text-center"><p class="text-2xl font-bold text-blue-600">{{ $stats['open'] }}</p><p class="text-xs text-gray-500">Open</p></div>
        <div class="card p-4 text-center"><p class="text-2xl font-bold text-amber-500">{{ $stats['in_progress'] }}</p><p class="text-xs text-gray-500">In Progress</p></div>
        <div class="card p-4 text-center"><p class="text-2xl font-bold text-purple-600">{{ $stats['pending'] }}</p><p class="text-xs text-gray-500">Pending</p></div>
        <div class="card p-4 text-center"><p class="text-2xl font-bold text-green-600">{{ $stats['resolved_today'] }}</p><p class="text-xs text-gray-500">Resolved Today</p></div>
        <div class="card p-4 text-center"><p class="text-2xl font-bold text-red-600">{{ $stats['breached'] }}</p><p class="text-xs text-gray-500">SLA Breached</p></div>
        <div class="card p-4 text-center"><p class="text-2xl font-bold text-gray-800">{{ $stats['my_open'] }}</p><p class="text-xs text-gray-500">My Tickets</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: by priority + by team --}}
        <div class="space-y-4">
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Open by Priority</h2>
                @foreach(['critical','high','medium','low'] as $p)
                    @php $cnt = $byPriority[$p] ?? 0; $colors = ['critical'=>'bg-red-500','high'=>'bg-orange-500','medium'=>'bg-amber-400','low'=>'bg-gray-300']; @endphp
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-16 text-xs font-medium text-gray-600 capitalize">{{ $p }}</span>
                        <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                            @php $total = array_sum($byPriority->toArray() ?: [1]); @endphp
                            <div class="h-full {{ $colors[$p] }} rounded-full" style="width:{{ $total > 0 ? round($cnt/$total*100) : 0 }}%"></div>
                        </div>
                        <span class="w-6 text-xs font-bold text-gray-800 text-right">{{ $cnt }}</span>
                    </div>
                @endforeach
            </div>

            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-700">Teams</h2>
                    <a href="{{ route('support.teams.index') }}" class="text-xs text-blue-600 hover:underline">Manage</a>
                </div>
                <div class="space-y-2">
                    @forelse($byTeam as $team)
                        <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 hover:bg-gray-100">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $team->name }}</p>
                                <p class="text-xs text-gray-400">{{ $team->members_count }} agents</p>
                            </div>
                            <span class="text-sm font-bold {{ $team->open_tickets_count > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                {{ $team->open_tickets_count }} open
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-3">No teams set up yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Middle: My tickets --}}
        <div class="space-y-4">
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-700">My Open Tickets</h2>
                    <a href="{{ route('support.tickets.index', ['assigned_to' => 'me']) }}" class="text-xs text-blue-600 hover:underline">View all</a>
                </div>
                <div class="space-y-2">
                    @forelse($myTickets as $t)
                        <a href="{{ route('support.tickets.show', $t) }}" class="block p-3 rounded-lg bg-gray-50 hover:bg-blue-50 transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $t->subject }}</p>
                                    <p class="text-xs text-gray-400">{{ $t->ticket_number }} · {{ $t->category?->name }}</p>
                                </div>
                                <span class="px-1.5 py-0.5 rounded text-xs font-bold flex-shrink-0 {{ $t->priorityColor() }}">{{ ucfirst($t->priority) }}</span>
                            </div>
                            @if($t->sla_resolution_due_at)
                                <p class="text-xs mt-1 {{ $t->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                                    Due {{ $t->sla_resolution_due_at->diffForHumans() }}
                                </p>
                            @endif
                        </a>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">No tickets assigned to you.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right: Recent tickets --}}
        <div class="space-y-4">
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-700">Recent Tickets</h2>
                    <a href="{{ route('support.tickets.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
                </div>
                <div class="space-y-2">
                    @foreach($recentTickets as $t)
                        <a href="{{ route('support.tickets.show', $t) }}" class="block p-3 rounded-lg bg-gray-50 hover:bg-blue-50 transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $t->subject }}</p>
                                    <p class="text-xs text-gray-400">{{ $t->ticket_number }} · {{ $t->team?->name ?? 'Unassigned' }}</p>
                                </div>
                                <span class="px-1.5 py-0.5 rounded text-xs font-bold flex-shrink-0 {{ $t->statusColor() }}">{{ ucfirst(str_replace('_',' ',$t->status)) }}</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ $t->created_at->diffForHumans() }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
