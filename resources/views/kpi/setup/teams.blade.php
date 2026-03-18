@extends('layouts.app')

@section('title', 'KPI Setup — Teams')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">KPI Setup</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage teams and team membership</p>
    </div>

    @include('kpi.setup._tabs', ['active' => 'teams'])

    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    {{-- Create team --}}
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Create Team</h2>
        <form method="POST" action="{{ route('kpi.teams.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Team Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" class="form-input w-full" required>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Department <span class="text-red-500">*</span></label>
                <input type="text" name="department" class="form-input w-full" placeholder="credit, operations…" required>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Branch</label>
                <select name="branch_id" class="form-input w-full">
                    <option value="">— All Branches —</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Team Lead</label>
                <select name="team_lead_id" class="form-input w-full">
                    <option value="">— No Lead —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Description</label>
                <input type="text" name="description" class="form-input w-full">
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary">Create Team</button>
            </div>
        </form>
    </div>

    {{-- Teams list --}}
    <div class="space-y-4">
        @forelse($teams as $team)
            <div class="card p-5">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-black text-sm shrink-0">
                            {{ strtoupper(substr($team->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $team->name }}</div>
                            <div class="text-xs text-gray-500">
                                {{ ucfirst($team->department) }}
                                @if($team->branch) · {{ $team->branch->name }} @endif
                                · Lead: {{ $team->teamLead?->name ?? 'None' }}
                                · {{ $team->members->count() }} member(s)
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('kpi.team', $team) }}" class="btn btn-secondary text-xs">View Dashboard</a>
                        <form method="POST" action="{{ route('kpi.teams.destroy', $team) }}"
                              onsubmit="return confirm('Delete this team?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-600">Delete</button>
                        </form>
                    </div>
                </div>

                {{-- Members --}}
                @if($team->members->isNotEmpty())
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($team->members as $member)
                            <div class="flex items-center gap-1.5 bg-gray-100 rounded-full px-3 py-1 text-xs">
                                <span class="font-medium text-gray-800">{{ $member->user?->name ?? '—' }}</span>
                                <form method="POST" action="{{ route('kpi.teams.members.remove', [$team, $member->user_id]) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-gray-400 hover:text-red-500 ml-1 leading-none">×</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Add member --}}
                <form method="POST" action="{{ route('kpi.teams.members.add', $team) }}" class="mt-3 flex gap-2">
                    @csrf
                    <select name="user_id" class="form-input text-sm py-1.5 flex-1 max-w-xs">
                        <option value="">+ Add member…</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-secondary text-xs">Add</button>
                </form>
            </div>
        @empty
            <div class="card p-12 text-center text-gray-400">
                <p class="font-medium">No teams yet.</p>
                <p class="text-sm mt-1">Create a team above to start tracking group performance.</p>
            </div>
        @endforelse
    </div>
    <div>{{ $teams->links() }}</div>
</div>
@endsection
