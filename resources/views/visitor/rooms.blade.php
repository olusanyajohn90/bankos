@extends('layouts.app')
@section('title', 'Meeting Rooms')
@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('visitor.dashboard') }}" class="text-xs text-gray-400 hover:text-gray-600">← Visitor Management</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Meeting Rooms</h1>
            <p class="text-sm text-gray-500 mt-0.5">Configure rooms available for visitor meetings.</p>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Add Room</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    {{-- New Room Form --}}
    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">New Meeting Room</h2>
        <form action="{{ route('visitor.room-store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-1">Room Name *</label>
                <input type="text" name="name" required class="form-input w-full text-sm" placeholder="e.g. Boardroom A">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Location / Floor</label>
                <input type="text" name="location" class="form-input w-full text-sm" placeholder="e.g. 2nd Floor, East Wing">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Capacity</label>
                <input type="number" name="capacity" value="6" min="1" max="100" class="form-input w-full text-sm">
            </div>
            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Add Room</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($rooms as $room)
        <div class="card p-5 {{ !$room->is_available ? 'opacity-70' : '' }}">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-semibold text-gray-900">{{ $room->name }}</p>
                    @if($room->location)<p class="text-xs text-gray-400 mt-0.5">{{ $room->location }}</p>@endif
                    <p class="text-xs text-gray-500 mt-1">Capacity: {{ $room->capacity }} people</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $room->is_available ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $room->is_available ? 'Available' : 'Unavailable' }}
                </span>
            </div>

            {{-- Today's bookings --}}
            @php
                $todayMeetings = $room->meetings()->whereDate('scheduled_at', today())->orderBy('scheduled_at')->get();
            @endphp
            @if($todayMeetings->isNotEmpty())
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 mb-1">Today's bookings</p>
                    @foreach($todayMeetings as $m)
                        <div class="text-xs text-gray-600 py-0.5">
                            {{ $m->scheduled_at->format('H:i') }} — {{ $m->title }}
                            <span class="text-gray-400">({{ $m->duration_minutes }}min)</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-4 flex gap-2">
                <form action="{{ route('visitor.room-toggle', $room) }}" method="POST">
                    @csrf
                    <button class="text-xs font-medium {{ $room->is_available ? 'text-amber-600 hover:text-amber-800' : 'text-green-600 hover:text-green-800' }}">
                        {{ $room->is_available ? 'Mark Unavailable' : 'Mark Available' }}
                    </button>
                </form>
            </div>
        </div>
        @empty
            <div class="md:col-span-3 card p-12 text-center text-gray-400">No meeting rooms configured yet.</div>
        @endforelse
    </div>

</div>
@endsection
