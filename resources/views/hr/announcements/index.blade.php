@extends('layouts.app')
@section('title', 'Notice Board')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notice Board</h1>
            <p class="text-sm text-gray-500 mt-0.5">Company announcements and updates</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasRole(['admin','hr_manager','super_admin']))
                <a href="{{ route('hr.announcements.manage') }}" class="btn text-sm bg-gray-100 hover:bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Manage</a>
            @endif
            @if($unread > 0)
                <span class="inline-flex items-center px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-sm font-medium">{{ $unread }} unread</span>
            @endif
        </div>
    </div>

    @if($announcements->isEmpty())
        <div class="card p-12 text-center text-gray-400">
            <svg class="mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <p>No announcements at the moment.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($announcements as $a)
                @php
                    $pc = match($a->priority) {
                        'urgent' => 'border-l-4 border-red-500 bg-red-50',
                        'high'   => 'border-l-4 border-amber-500 bg-amber-50',
                        'low'    => 'bg-gray-50',
                        default  => 'bg-white',
                    };
                    $pb = match($a->priority) {
                        'urgent' => 'bg-red-100 text-red-700',
                        'high'   => 'bg-amber-100 text-amber-700',
                        'low'    => 'bg-gray-100 text-gray-500',
                        default  => 'bg-blue-100 text-blue-700',
                    };
                @endphp
                <div class="card p-5 {{ $pc }}">
                    <div class="flex items-start justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($a->is_pinned)<span class="text-amber-500 text-sm">📌</span>@endif
                            <h3 class="font-semibold text-gray-900">{{ $a->title }}</h3>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $pb }}">{{ ucfirst($a->priority) }}</span>
                        </div>
                        <div class="text-xs text-gray-400 text-right">
                            <span>{{ $a->createdBy?->name }}</span><br>
                            <span>{{ $a->created_at->format('d M Y') }}</span>
                            @if($a->expires_at)<br><span class="text-amber-600">Expires {{ $a->expires_at->format('d M Y') }}</span>@endif
                        </div>
                    </div>
                    <div class="mt-3 text-sm text-gray-700 leading-relaxed prose prose-sm max-w-none">
                        {!! nl2br(e($a->body)) !!}
                    </div>
                </div>
            @endforeach
        </div>
        @if($announcements->hasPages())
            <div>{{ $announcements->links() }}</div>
        @endif
    @endif

</div>
@endsection
