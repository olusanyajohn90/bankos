@extends('layouts.app')
@section('title', 'Support Teams')
@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{ showNew: false, editId: null }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <h1 class="text-2xl font-bold text-gray-900">Support Teams</h1>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ New Team</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    {{-- New Team Form --}}
    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">New Support Team</h2>
        <form action="{{ route('support.teams.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div><label class="block text-xs text-gray-500 mb-1">Team Name *</label>
                    <input type="text" name="name" required class="form-input w-full text-sm" placeholder="e.g. Card Operations"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Code</label>
                    <input type="text" name="code" class="form-input w-full text-sm" placeholder="e.g. CARD"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Division</label>
                    <input type="text" name="division" class="form-input w-full text-sm" placeholder="e.g. Operations"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Team Email</label>
                    <input type="email" name="email" class="form-input w-full text-sm" placeholder="team@bank.com"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Team Lead</label>
                    <select name="team_lead_id" class="form-input w-full text-sm">
                        <option value="">— Select —</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select></div>
            </div>
            <div><label class="block text-xs text-gray-500 mb-1">Description</label>
                <textarea name="description" rows="2" class="form-input w-full text-sm resize-none" placeholder="Team responsibilities…"></textarea></div>
            <div class="flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create Team</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Teams Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($teams as $team)
        <div class="card p-5 {{ ! $team->is_active ? 'opacity-60' : '' }}" x-data="{ showEdit: false, showMembers: false }">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <div class="flex items-center gap-2">
                        @if($team->code)<span class="px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-700">{{ $team->code }}</span>@endif
                        <span class="font-semibold text-gray-900">{{ $team->name }}</span>
                    </div>
                    @if($team->division)<p class="text-xs text-gray-400 mt-0.5">{{ $team->division }}</p>@endif
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $team->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $team->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            @if($team->description)<p class="text-xs text-gray-500 mb-3">{{ $team->description }}</p>@endif

            <div class="grid grid-cols-3 gap-2 text-center mb-3">
                <div class="bg-gray-50 rounded p-2">
                    <div class="text-lg font-bold text-gray-800">{{ $team->members_count }}</div>
                    <div class="text-xs text-gray-400">Agents</div>
                </div>
                <div class="bg-gray-50 rounded p-2">
                    <div class="text-lg font-bold text-blue-600">{{ $team->open_tickets_count }}</div>
                    <div class="text-xs text-gray-400">Open</div>
                </div>
                <div class="bg-gray-50 rounded p-2">
                    <div class="text-lg font-bold text-gray-600">{{ $team->tickets_count }}</div>
                    <div class="text-xs text-gray-400">Total</div>
                </div>
            </div>

            @if($team->teamLead)
                <p class="text-xs text-gray-500 mb-3">Lead: <span class="font-medium text-gray-700">{{ $team->teamLead->name }}</span></p>
            @endif

            <div class="flex gap-2 flex-wrap">
                <button @click="showMembers = !showMembers" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Members</button>
                <button @click="showEdit = !showEdit" class="text-xs text-gray-500 hover:text-gray-700 font-medium">Edit</button>
                <form action="{{ route('support.teams.toggle', $team) }}" method="POST" class="inline">
                    @csrf
                    <button class="text-xs {{ $team->is_active ? 'text-red-500 hover:text-red-700' : 'text-green-600 hover:text-green-800' }} font-medium">
                        {{ $team->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
            </div>

            {{-- Edit Form --}}
            <div x-show="showEdit" x-transition class="mt-4 pt-4 border-t border-gray-100">
                <form action="{{ route('support.teams.update', $team) }}" method="POST" class="space-y-3">
                    @csrf @method('PATCH')
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" name="name" value="{{ $team->name }}" required class="form-input col-span-2 text-sm" placeholder="Name">
                        <input type="text" name="code" value="{{ $team->code }}" class="form-input text-sm" placeholder="Code">
                        <input type="text" name="division" value="{{ $team->division }}" class="form-input text-sm" placeholder="Division">
                        <input type="email" name="email" value="{{ $team->email }}" class="form-input col-span-2 text-sm" placeholder="Team email">
                        <select name="team_lead_id" class="form-input col-span-2 text-sm">
                            <option value="">— No Lead —</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ $team->team_lead_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full btn text-sm bg-gray-700 text-white py-1.5 rounded-lg">Save Changes</button>
                </form>
            </div>

            {{-- Members Panel --}}
            <div x-show="showMembers" x-transition class="mt-4 pt-4 border-t border-gray-100 space-y-3">
                @if($team->members->isNotEmpty())
                <div class="space-y-1">
                    @foreach($team->members as $m)
                    <div class="flex items-center justify-between text-sm">
                        <div>
                            <span class="font-medium text-gray-800">{{ $m->name }}</span>
                            <span class="ml-2 px-1.5 py-0.5 rounded text-xs bg-gray-100 text-gray-500">{{ $m->pivot->role }}</span>
                        </div>
                        <form action="{{ route('support.teams.remove-member', [$team, $m]) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-600">Remove</button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @else
                    <p class="text-xs text-gray-400">No members yet.</p>
                @endif
                <form action="{{ route('support.teams.add-member', $team) }}" method="POST" class="flex gap-2 mt-2">
                    @csrf
                    <select name="user_id" required class="form-input flex-1 text-sm">
                        <option value="">— Add agent —</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <select name="role" class="form-input text-sm">
                        <option value="agent">Agent</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="team_lead">Team Lead</option>
                    </select>
                    <button type="submit" class="btn text-sm bg-blue-600 text-white px-3 py-1.5 rounded-lg">Add</button>
                </form>
            </div>
        </div>
        @empty
            <div class="md:col-span-2 xl:col-span-3 card p-12 text-center text-gray-400">No support teams yet. Create your first team.</div>
        @endforelse
    </div>

</div>
@endsection
