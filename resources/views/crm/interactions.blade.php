@extends('layouts.app')
@section('title', 'Interactions')
@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <h1 class="text-2xl font-bold text-gray-900">Interactions</h1>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Log Interaction</button>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Log New Interaction</h2>
        <form action="{{ route('crm.interactions.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div><label class="block text-xs text-gray-500 mb-1">Type</label>
                <select name="interaction_type" class="form-input w-full text-sm">
                    @foreach(['call','meeting','email','whatsapp','visit','sms','note'] as $t)
                        <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Direction</label>
                <select name="direction" class="form-input w-full text-sm">
                    <option value="outbound">Outbound</option><option value="inbound">Inbound</option>
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Date & Time *</label>
                <input type="datetime-local" name="interacted_at" value="{{ now()->format('Y-m-d\TH:i') }}" required class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Subject Type</label>
                <select name="subject_type" class="form-input w-full text-sm">
                    <option value="lead">Lead</option><option value="account">Account</option>
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Subject ID (Lead/Account ID)</label>
                <input type="text" name="subject_id" class="form-input w-full text-sm" placeholder="UUID"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Duration (mins)</label>
                <input type="number" name="duration_mins" class="form-input w-full text-sm" min="0"></div>
            <div class="md:col-span-3"><label class="block text-xs text-gray-500 mb-1">Summary *</label>
                <textarea name="summary" rows="2" required class="form-input w-full text-sm resize-none"></textarea></div>
            <div class="md:col-span-3"><label class="block text-xs text-gray-500 mb-1">Outcome</label>
                <input type="text" name="outcome" class="form-input w-full text-sm"></div>
            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Log</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search summary…" class="form-input text-sm flex-1 min-w-[180px]">
        <select name="type" class="form-input text-sm">
            <option value="">All Types</option>
            @foreach(['call','meeting','email','whatsapp','visit','sms','note'] as $t)
                <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn text-sm bg-gray-700 text-white px-4 py-2 rounded-lg">Filter</button>
        @if(request()->hasAny(['search','type']))
            <a href="{{ route('crm.interactions') }}" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Clear</a>
        @endif
    </form>

    <div class="space-y-3">
        @php $icons = ['call'=>'📞','meeting'=>'🤝','email'=>'📧','whatsapp'=>'💬','visit'=>'🏢','sms'=>'📱','note'=>'📝']; @endphp
        @forelse($interactions as $i)
            <div class="card p-4 flex gap-4">
                <span class="text-2xl">{{ $icons[$i->interaction_type] ?? '📝' }}</span>
                <div class="flex-1">
                    <div class="flex items-start justify-between gap-2 flex-wrap">
                        <div>
                            <span class="text-xs font-bold text-gray-600 uppercase">{{ $i->interaction_type }} · {{ ucfirst($i->direction) }}</span>
                            @if($i->lead)<span class="ml-2 text-xs text-blue-600">Lead: {{ $i->lead->title }}</span>@endif
                        </div>
                        <span class="text-xs text-gray-400">{{ $i->interacted_at->format('d M Y, H:i') }}</span>
                    </div>
                    <p class="text-sm text-gray-800 mt-1">{{ $i->summary }}</p>
                    @if($i->outcome)<p class="text-xs text-gray-500 mt-0.5 italic">{{ $i->outcome }}</p>@endif
                    <p class="text-xs text-gray-400 mt-1">Logged by {{ $i->createdBy?->name }}
                        @if($i->duration_mins) · {{ $i->duration_mins }}m @endif</p>
                </div>
            </div>
        @empty
            <div class="card p-12 text-center text-gray-400 text-sm">No interactions found.</div>
        @endforelse
    </div>

    @if($interactions->hasPages())
        <div>{{ $interactions->links() }}</div>
    @endif

</div>
@endsection
