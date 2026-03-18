@extends('layouts.app')
@section('title', 'Visitor Meetings')
@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('visitor.dashboard') }}" class="text-xs text-gray-400 hover:text-gray-600">← Visitor Management</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Meetings</h1>
            <p class="text-sm text-gray-500 mt-0.5">Scheduled and completed visitor meetings.</p>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Schedule Meeting</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    {{-- New Meeting Form --}}
    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Schedule Meeting</h2>
        <form action="{{ route('visitor.meeting-store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div class="md:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Meeting Title *</label>
                <input type="text" name="title" required class="form-input w-full text-sm" placeholder="e.g. Loan Negotiation, Board Briefing">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Date & Time *</label>
                <input type="datetime-local" name="scheduled_at" required class="form-input w-full text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Duration (minutes)</label>
                <input type="number" name="duration_minutes" value="60" min="15" max="480" class="form-input w-full text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Meeting Room</label>
                <select name="room_id" class="form-input w-full text-sm">
                    <option value="">— No room —</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}">{{ $room->name }} (Cap: {{ $room->capacity }}){{ !$room->is_available ? ' — Unavailable' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Visitor (optional — link later)</label>
                <select name="visitor_id" class="form-input w-full text-sm">
                    <option value="">— Not assigned yet —</option>
                    @foreach($recentVisitors as $v)
                        <option value="{{ $v->id }}">{{ $v->full_name }}{{ $v->company ? ' ('.$v->company.')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Agenda</label>
                <textarea name="agenda" rows="3" class="form-input w-full text-sm resize-none" placeholder="Meeting agenda or brief description"></textarea>
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Schedule</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Filter --}}
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="date" name="date" value="{{ request('date') }}" class="form-input text-sm">
            <select name="status" class="form-input text-sm">
                <option value="">All Statuses</option>
                @foreach(['scheduled','in_progress','completed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn text-sm bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg">Filter</button>
            @if(request()->hasAny(['date','status']))
                <a href="{{ route('visitor.meetings') }}" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Clear</a>
            @endif
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Meeting</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Organiser</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Room</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Scheduled</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Duration</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($meetings as $meeting)
                @php
                    $colors = $meeting->statusColor();
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $meeting->title }}</p>
                        @if($meeting->agenda)<p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($meeting->agenda, 60) }}</p>@endif
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $meeting->organiser?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $meeting->room?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600">{{ $meeting->scheduled_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $meeting->duration_minutes }} min</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $colors }}">{{ ucwords(str_replace('_',' ',$meeting->status)) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            @if($meeting->status === 'scheduled')
                                <form action="{{ route('visitor.meeting-status', $meeting) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="in_progress">
                                    <button class="text-xs text-blue-600 hover:text-blue-800 font-medium">Start</button>
                                </form>
                                <form action="{{ route('visitor.meeting-status', $meeting) }}" method="POST"
                                      onsubmit="return confirm('Cancel this meeting?')">
                                    @csrf
                                    <input type="hidden" name="status" value="cancelled">
                                    <button class="text-xs text-red-400 hover:text-red-600 font-medium">Cancel</button>
                                </form>
                            @elseif($meeting->status === 'in_progress')
                                <form action="{{ route('visitor.meeting-status', $meeting) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="completed">
                                    <button class="text-xs text-green-600 hover:text-green-800 font-medium">End</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No meetings found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($meetings->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $meetings->links() }}</div>
        @endif
    </div>

</div>
@endsection
