@extends('layouts.app')

@section('title', 'Communications — Outbox')

@section('content')
<div class="max-w-7xl mx-auto"
     x-data="{ activeTab: 'all' }">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Communications</h1>
            <p class="text-sm text-bankos-text-sec mt-1">Compose and manage memos, circulars, and announcements.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('comms.inbox.index') }}" class="btn btn-secondary flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>
                Inbox
            </a>
            <a href="{{ route('comms.messages.create') }}" class="btn btn-primary flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                Compose
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Summary Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-600"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ number_format($draftCount) }}</p>
                <p class="text-xs text-bankos-text-sec">Drafts</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ number_format($publishedCount) }}</p>
                <p class="text-xs text-bankos-text-sec">Published</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-800/50 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ number_format($archivedCount) }}</p>
                <p class="text-xs text-bankos-text-sec">Archived</p>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card p-4 mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Type</label>
                <select name="type" class="form-select text-sm" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="memo" {{ request('type') === 'memo' ? 'selected' : '' }}>Memo</option>
                    <option value="circular" {{ request('type') === 'circular' ? 'selected' : '' }}>Circular</option>
                    <option value="announcement" {{ request('type') === 'announcement' ? 'selected' : '' }}>Announcement</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Priority</label>
                <select name="priority" class="form-select text-sm" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Status</label>
                <select name="status" class="form-select text-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            @if (request()->hasAny(['type', 'priority', 'status']))
                <a href="{{ route('comms.messages.index') }}" class="text-sm text-bankos-primary hover:underline self-end pb-1">Clear</a>
            @endif
        </form>
    </div>

    {{-- Tab Bar --}}
    <div class="border-b border-bankos-border dark:border-bankos-dark-border mb-0 flex overflow-x-auto hide-scrollbar">
        @foreach (['all' => 'All', 'memo' => 'Memos', 'circular' => 'Circulars', 'announcement' => 'Announcements'] as $tab => $label)
        <button @click="activeTab = '{{ $tab }}'"
                :class="activeTab === '{{ $tab }}'
                    ? 'border-bankos-primary text-bankos-primary'
                    : 'border-transparent text-bankos-text-sec hover:text-bankos-text dark:hover:text-gray-300'"
                class="py-3 px-5 font-medium text-sm border-b-2 whitespace-nowrap outline-none transition-colors">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Messages Table --}}
    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-4 py-3 font-semibold">Message</th>
                        <th class="px-4 py-3 font-semibold">Priority</th>
                        <th class="px-4 py-3 font-semibold">Scope</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold">Read Rate</th>
                        <th class="px-4 py-3 font-semibold">Ack Rate</th>
                        <th class="px-4 py-3 font-semibold">Published</th>
                        <th class="px-4 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse ($messages as $msg)
                    @php
                        $typeHide  = "activeTab !== 'all' && activeTab !== '{$msg->type}'";

                        $typeBadge = match($msg->type) {
                            'circular'     => 'bg-indigo-100 text-indigo-700',
                            'announcement' => 'bg-purple-100 text-purple-700',
                            default        => 'bg-blue-100 text-blue-700',
                        };
                        $priorityBadge = match($msg->priority) {
                            'critical' => 'bg-red-100 text-red-700',
                            'urgent'   => 'bg-amber-100 text-amber-700',
                            default    => 'bg-gray-100 text-gray-600',
                        };
                        $statusBadge = match($msg->status) {
                            'published' => 'bg-green-100 text-green-700',
                            'archived'  => 'bg-gray-100 text-gray-500',
                            default     => 'bg-yellow-100 text-yellow-700',
                        };

                        $readRate = $msg->readRate();
                        $ackRate  = $msg->ackRate();

                        $scopeLabel = match($msg->scope_type) {
                            'all'        => 'All Staff',
                            'branch'     => 'Branch',
                            'department' => 'Department',
                            'team'       => 'Team',
                            'role'       => 'Role',
                            'individual' => 'Individual',
                            default      => ucfirst($msg->scope_type ?? '—'),
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                        x-show="{{ $typeHide }} ? false : true">
                        <td class="px-4 py-3 max-w-xs">
                            <div class="flex items-start gap-2">
                                <span class="text-xs px-1.5 py-0.5 rounded font-medium shrink-0 {{ $typeBadge }}">
                                    {{ ucfirst($msg->type) }}
                                </span>
                                <div>
                                    <p class="font-semibold text-bankos-text dark:text-bankos-dark-text truncate max-w-[200px]" title="{{ $msg->subject }}">
                                        {{ $msg->subject }}
                                    </p>
                                    <p class="text-xs text-bankos-muted mt-0.5">{{ $msg->sender?->name ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $priorityBadge }}">
                                {{ ucfirst($msg->priority) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-bankos-text-sec">{{ $scopeLabel }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $statusBadge }}">
                                {{ ucfirst($msg->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($msg->status === 'published')
                                <div class="flex items-center gap-2 min-w-[80px]">
                                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $readRate >= 80 ? 'bg-green-500' : ($readRate >= 40 ? 'bg-yellow-500' : 'bg-red-400') }}"
                                             style="width: {{ $readRate }}%"></div>
                                    </div>
                                    <span class="text-xs text-bankos-text-sec font-mono">{{ $readRate }}%</span>
                                </div>
                            @else
                                <span class="text-xs text-bankos-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($msg->requires_ack && $msg->status === 'published')
                                <div class="flex items-center gap-2 min-w-[80px]">
                                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $ackRate >= 80 ? 'bg-green-500' : ($ackRate >= 40 ? 'bg-yellow-500' : 'bg-red-400') }}"
                                             style="width: {{ $ackRate }}%"></div>
                                    </div>
                                    <span class="text-xs text-bankos-text-sec font-mono">{{ $ackRate }}%</span>
                                </div>
                            @else
                                <span class="text-xs text-bankos-muted">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-bankos-text-sec">
                            {{ $msg->published_at ? $msg->published_at->format('d M Y') : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Edit (draft only) --}}
                                @if ($msg->status === 'draft')
                                <a href="{{ route('comms.messages.edit', $msg) }}"
                                   class="text-xs text-bankos-primary hover:underline font-medium">Edit</a>

                                <form method="POST" action="{{ route('comms.messages.publish', $msg) }}" class="inline"
                                      onsubmit="return confirm('Publish this message now?')">
                                    @csrf
                                    <button type="submit" class="text-xs text-green-600 hover:text-green-700 font-medium">Publish</button>
                                </form>
                                @endif

                                {{-- Archive (published only) --}}
                                @if ($msg->status === 'published')
                                <form method="POST" action="{{ route('comms.messages.archive', $msg) }}" class="inline"
                                      onsubmit="return confirm('Archive this message?')">
                                    @csrf
                                    <button type="submit" class="text-xs text-gray-500 hover:text-gray-700 font-medium">Archive</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-bankos-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-bankos-border"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            <p class="font-medium text-bankos-text-sec">No messages found</p>
                            <p class="text-xs mt-1">Compose your first message to get started.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($messages->hasPages())
            <div class="px-4 py-3 border-t border-bankos-border dark:border-bankos-dark-border">
                {{ $messages->withQueryString()->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
