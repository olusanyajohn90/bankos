@extends('layouts.app')
@section('title', 'CRM Dashboard')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">CRM Dashboard</h1>
            <p class="text-sm text-gray-500 mt-0.5">Pipeline, interactions & follow-ups</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('crm.leads') }}" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">All Leads</a>
            <a href="{{ route('crm.interactions') }}" class="btn text-sm bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg">Interactions</a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $totalLeads }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Leads</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-amber-500">{{ $activeLeads }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Active</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $converted }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Converted</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-purple-600">{{ $myFollowUps->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">My Follow-ups Due</p>
        </div>
    </div>

    {{-- Pipeline Kanban summary --}}
    <div class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Sales Pipeline</h2>
        <div class="flex gap-3 overflow-x-auto pb-2">
            @foreach($pipelineData as $col)
                <div class="flex-shrink-0 w-44">
                    <div class="rounded-xl border-2 p-3" style="border-color: {{ $col['stage']->color }}20; background: {{ $col['stage']->color }}10">
                        <div class="flex items-center gap-1.5 mb-2">
                            <div class="w-2.5 h-2.5 rounded-full" style="background: {{ $col['stage']->color }}"></div>
                            <span class="text-xs font-bold text-gray-700 truncate">{{ $col['stage']->name }}</span>
                        </div>
                        <p class="text-xl font-bold text-gray-900">{{ $col['count'] }}</p>
                        <p class="text-xs text-gray-500">₦{{ number_format($col['total_value']) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Interactions --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-700">Recent Interactions</h2>
                <a href="{{ route('crm.interactions') }}" class="text-xs text-blue-600 hover:underline">View all</a>
            </div>
            <div class="space-y-3">
                @forelse($recentInt as $i)
                    @php
                        $typeIcon = match($i->interaction_type) {
                            'call' => '📞', 'meeting' => '🤝', 'email' => '📧',
                            'whatsapp' => '💬', 'visit' => '🏢', default => '📝'
                        };
                    @endphp
                    <div class="flex items-start gap-3">
                        <span class="text-lg">{{ $typeIcon }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800 font-medium truncate">{{ $i->summary }}</p>
                            <p class="text-xs text-gray-400">{{ $i->createdBy?->name }} · {{ $i->interacted_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No interactions yet.</p>
                @endforelse
            </div>
        </div>

        {{-- My Follow-ups --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-700">My Follow-ups</h2>
            </div>
            <div class="space-y-2">
                @forelse($myFollowUps as $f)
                    @php $overdue = $f->due_at->isPast(); @endphp
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $overdue ? 'bg-red-50 border border-red-100' : 'bg-gray-50' }}">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $f->title }}</p>
                            <p class="text-xs {{ $overdue ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                                {{ $overdue ? 'Overdue: ' : '' }}{{ $f->due_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                        <form action="{{ route('crm.follow-ups.complete', $f) }}" method="POST">
                            @csrf
                            <button class="text-xs text-green-600 hover:text-green-800 font-medium">Done</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No pending follow-ups.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
