@extends('layouts.app')

@section('title', 'Inbox')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Page Header & Tab Bar --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-primary"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>
                Inbox
            </h1>
            <p class="text-sm text-bankos-text-sec mt-1">Messages addressed to you from management and colleagues.</p>
        </div>
        <a href="{{ route('comms.messages.create') }}" class="btn btn-primary flex items-center gap-2 text-sm shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            Compose
        </a>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- ===== UNREAD SECTION ===== --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <h2 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text uppercase tracking-wide">
                Unread
            </h2>
            @if ($unread->isNotEmpty())
                <span class="text-xs bg-bankos-primary text-white px-2 py-0.5 rounded-full font-semibold">
                    {{ $unread->count() }}
                </span>
            @endif
        </div>

        @forelse ($unread as $recipient)
        @php
            $msg          = $recipient->message;
            $priorityBadge = match($msg->priority) {
                'critical' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                'urgent'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                default    => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
            };
            $typeBadge = match($msg->type) {
                'circular'     => 'bg-indigo-100 text-indigo-700',
                'announcement' => 'bg-purple-100 text-purple-700',
                default        => 'bg-blue-100 text-blue-700',
            };
        @endphp
        <a href="{{ route('comms.inbox.show', $msg) }}"
           class="block card p-4 mb-2 border-l-4 hover:shadow-md transition-shadow
               {{ $msg->priority === 'critical' ? 'border-l-red-500' : ($msg->priority === 'urgent' ? 'border-l-amber-500' : 'border-l-bankos-primary') }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs px-1.5 py-0.5 rounded font-medium {{ $priorityBadge }}">
                            {{ ucfirst($msg->priority) }}
                        </span>
                        <span class="text-xs px-1.5 py-0.5 rounded font-medium {{ $typeBadge }}">
                            {{ ucfirst($msg->type) }}
                        </span>
                    </div>
                    <p class="font-bold text-bankos-text dark:text-bankos-dark-text truncate text-sm">
                        {{ $msg->subject }}
                    </p>
                    <p class="text-xs text-bankos-muted mt-0.5">
                        From: {{ $msg->sender?->name ?? 'System' }}
                        &bull; {{ $msg->published_at?->diffForHumans() ?? '—' }}
                    </p>
                </div>
                <div class="shrink-0 flex flex-col items-end gap-1.5">
                    @if ($msg->requires_ack)
                        <span class="text-xs bg-orange-100 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400 px-2 py-0.5 rounded-full font-medium flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                            Ack Required
                        </span>
                    @endif
                    <span class="w-2 h-2 rounded-full bg-bankos-primary inline-block"></span>
                </div>
            </div>
        </a>
        @empty
        <div class="card p-6 text-center text-bankos-muted">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2 text-bankos-border"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>
            <p class="text-sm">No unread messages — you're all caught up!</p>
        </div>
        @endforelse
    </div>

    {{-- ===== READ SECTION ===== --}}
    <div>
        <div class="flex items-center gap-2 mb-3">
            <h2 class="text-sm font-semibold text-bankos-text-sec dark:text-bankos-dark-text-sec uppercase tracking-wide">
                Read
            </h2>
            @if ($read->isNotEmpty())
                <span class="text-xs bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-full font-medium">
                    {{ $read->count() }}
                </span>
            @endif
        </div>

        @forelse ($read as $recipient)
        @php
            $msg          = $recipient->message;
            $priorityBadge = match($msg->priority) {
                'critical' => 'bg-red-100 text-red-700',
                'urgent'   => 'bg-amber-100 text-amber-700',
                default    => 'bg-gray-100 text-gray-500',
            };
            $typeBadge = match($msg->type) {
                'circular'     => 'bg-indigo-50 text-indigo-600',
                'announcement' => 'bg-purple-50 text-purple-600',
                default        => 'bg-blue-50 text-blue-600',
            };
            $isAcked = ! is_null($recipient->ack_at);
        @endphp
        <a href="{{ route('comms.inbox.show', $msg) }}"
           class="block bg-white dark:bg-bankos-dark-surface border border-bankos-border dark:border-bankos-dark-border rounded-lg p-4 mb-2 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors opacity-80 hover:opacity-100">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs px-1.5 py-0.5 rounded font-medium {{ $typeBadge }}">
                            {{ ucfirst($msg->type) }}
                        </span>
                        @if ($msg->priority !== 'normal')
                        <span class="text-xs px-1.5 py-0.5 rounded font-medium {{ $priorityBadge }}">
                            {{ ucfirst($msg->priority) }}
                        </span>
                        @endif
                    </div>
                    <p class="text-bankos-text dark:text-bankos-dark-text text-sm truncate">{{ $msg->subject }}</p>
                    <p class="text-xs text-bankos-muted mt-0.5">
                        From: {{ $msg->sender?->name ?? 'System' }}
                        &bull; {{ $msg->published_at?->format('d M Y') ?? '—' }}
                    </p>
                </div>
                <div class="shrink-0 flex flex-col items-end gap-1.5">
                    @if ($msg->requires_ack)
                        @if ($isAcked)
                            <span class="text-xs text-green-600 flex items-center gap-1 font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Acknowledged
                            </span>
                        @else
                            <span class="text-xs text-orange-600 font-medium">Pending Ack</span>
                        @endif
                    @endif
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-muted"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </div>
            </div>
        </a>
        @empty
        @if ($unread->isNotEmpty())
        <div class="card p-4 text-center text-xs text-bankos-muted">No previously read messages.</div>
        @endif
        @endforelse
    </div>

</div>
@endsection
