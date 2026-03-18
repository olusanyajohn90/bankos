@extends('layouts.app')

@section('title', $message->subject)

@section('content')
@php
    $isAcked      = ! is_null($recipient->ack_at);
    $isSender     = auth()->id() === $message->sender_id;
    $isAdmin      = auth()->user()->hasAnyRole(['super_admin', 'admin', 'hr_manager']);
    $canSeeStats  = $isSender || $isAdmin;

    $priorityBadge = match($message->priority) {
        'critical' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'urgent'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        default    => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
    };
    $typeBadge = match($message->type) {
        'circular'     => 'bg-indigo-100 text-indigo-700',
        'announcement' => 'bg-purple-100 text-purple-700',
        default        => 'bg-blue-100 text-blue-700',
    };
    $scopeLabel = match($message->scope_type) {
        'all'        => 'All Staff',
        'branch'     => 'Branch',
        'department' => 'Department',
        'team'       => 'Team',
        'role'       => 'Role',
        'individual' => 'Individual',
        default      => ucfirst($message->scope_type ?? '—'),
    };
@endphp

<div class="max-w-3xl mx-auto">

    {{-- Back Link --}}
    <div class="mb-4">
        <a href="{{ route('comms.inbox.index') }}" class="text-sm text-bankos-text-sec hover:text-bankos-primary flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Back to Inbox
        </a>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Header Card --}}
    <div class="card p-6 mb-4">
        <div class="flex flex-wrap items-start gap-2 mb-4">
            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $typeBadge }}">{{ ucfirst($message->type) }}</span>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $priorityBadge }}">{{ ucfirst($message->priority) }}</span>
            @if ($message->requires_ack)
                <span class="text-xs bg-orange-100 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400 px-2 py-0.5 rounded-full font-medium">
                    Requires Acknowledgement
                </span>
            @endif
        </div>

        <h1 class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text mb-3">{{ $message->subject }}</h1>

        <dl class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
            <div>
                <dt class="text-xs text-bankos-text-sec uppercase tracking-wide font-medium">From</dt>
                <dd class="mt-0.5 text-bankos-text dark:text-bankos-dark-text font-medium">
                    {{ $message->sender?->name ?? 'System' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-bankos-text-sec uppercase tracking-wide font-medium">Published</dt>
                <dd class="mt-0.5 text-bankos-text dark:text-bankos-dark-text">
                    {{ $message->published_at?->format('d M Y, H:i') ?? '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-bankos-text-sec uppercase tracking-wide font-medium">Scope</dt>
                <dd class="mt-0.5 text-bankos-text dark:text-bankos-dark-text">{{ $scopeLabel }}</dd>
            </div>
            @if ($message->ack_deadline)
            <div>
                <dt class="text-xs text-bankos-text-sec uppercase tracking-wide font-medium">Ack Deadline</dt>
                <dd class="mt-0.5 text-bankos-text dark:text-bankos-dark-text {{ $message->ack_deadline->isPast() && !$isAcked ? 'text-red-600 font-semibold' : '' }}">
                    {{ $message->ack_deadline->format('d M Y') }}
                    @if ($message->ack_deadline->isPast() && !$isAcked)
                        <span class="text-red-500 text-xs">(Overdue)</span>
                    @endif
                </dd>
            </div>
            @endif
        </dl>
    </div>

    {{-- Body Card --}}
    <div class="card p-6 mb-4">
        <div class="prose prose-sm max-w-none dark:prose-invert text-bankos-text dark:text-bankos-dark-text
                    prose-headings:text-bankos-text prose-a:text-bankos-primary">
            {!! $message->body !!}
        </div>
    </div>

    {{-- Attachments --}}
    @if ($message->attachments->isNotEmpty())
    <div class="card p-5 mb-4">
        <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-3 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
            Attachments ({{ $message->attachments->count() }})
        </h3>
        <div class="space-y-2">
            @foreach ($message->attachments as $att)
            <a href="{{ route('comms.attachments.download', $att) }}"
               class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-bankos-dark-bg/40 rounded-lg border border-bankos-border dark:border-bankos-dark-border hover:bg-bankos-light dark:hover:bg-bankos-primary/10 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-bankos-light flex items-center justify-center shrink-0">
                    @php
                        $ext = pathinfo($att->file_name, PATHINFO_EXTENSION);
                        $iconColor = match($ext) {
                            'pdf'  => 'text-red-500',
                            'doc','docx' => 'text-blue-500',
                            'xls','xlsx' => 'text-green-500',
                            default => 'text-gray-400',
                        };
                    @endphp
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $iconColor }}"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text truncate group-hover:text-bankos-primary transition-colors">
                        {{ $att->file_name }}
                    </p>
                    <p class="text-xs text-bankos-muted">{{ number_format($att->file_size_kb) }} KB</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-muted group-hover:text-bankos-primary transition-colors shrink-0"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Acknowledgement Section --}}
    @if ($message->requires_ack)
        @if ($isAcked)
        <div class="p-5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl mb-4 flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-green-600"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-green-800 dark:text-green-300">Acknowledged</p>
                <p class="text-xs text-green-700 dark:text-green-400 mt-0.5">
                    You acknowledged this message on {{ $recipient->ack_at?->format('d M Y \a\t H:i') }}.
                </p>
                @if ($recipient->ack_note)
                    <p class="text-xs text-green-600 dark:text-green-500 mt-1 italic">"{{ $recipient->ack_note }}"</p>
                @endif
            </div>
        </div>
        @else
        <div class="p-5 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl mb-4">
            <div class="flex items-start gap-3 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600 dark:text-blue-400 shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <div>
                    <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">This message requires your acknowledgement</p>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">
                        Please confirm that you have read and understood this message.
                        @if ($message->ack_deadline)
                            Deadline: <strong>{{ $message->ack_deadline->format('d M Y') }}</strong>.
                        @endif
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('comms.inbox.acknowledge', $message) }}">
                @csrf
                <div class="mb-3">
                    <label for="ack_note" class="block text-xs font-medium text-blue-700 dark:text-blue-400 mb-1">
                        Note (optional)
                    </label>
                    <textarea id="ack_note" name="ack_note" rows="2"
                              placeholder="Add a note to your acknowledgement..."
                              class="w-full text-sm border border-blue-200 dark:border-blue-700 rounded-lg px-3 py-2 bg-white dark:bg-bankos-dark-surface text-bankos-text dark:text-bankos-dark-text focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('ack_note') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary text-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    I Acknowledge
                </button>
            </form>
        </div>
        @endif
    @endif

    {{-- Recipient Status (Sender / Admin only) --}}
    @if ($canSeeStats)
    <div class="card p-5"
         x-data="{
             open: false,
             loading: false,
             recipients: [],
             total: 0,
             load() {
                 this.loading = true;
                 fetch('{{ route('comms.messages.recipients', $message) }}')
                     .then(r => r.json())
                     .then(data => {
                         this.recipients = data.data;
                         this.total      = data.total;
                         this.loading    = false;
                     })
                     .catch(() => { this.loading = false; });
             }
         }">
        <button @click="open = !open; if (open && recipients.length === 0) load()"
                class="flex items-center justify-between w-full text-left">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                Recipient Status
                <span x-show="total > 0" class="text-xs text-bankos-muted font-normal" x-text="`(${total} recipients)`"></span>
            </h3>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 class="text-bankos-muted transition-transform"
                 :class="open ? 'rotate-180' : ''">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </button>

        <div x-show="open" x-cloak class="mt-4">
            <div x-show="loading" class="text-xs text-bankos-muted py-4 text-center">Loading...</div>
            <div x-show="!loading && recipients.length === 0" class="text-xs text-bankos-muted py-4 text-center">No recipients found.</div>
            <div x-show="!loading && recipients.length > 0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                                <th class="pb-2 font-semibold text-left">Name</th>
                                <th class="pb-2 font-semibold text-center">Read</th>
                                <th class="pb-2 font-semibold text-center">Acknowledged</th>
                                <th class="pb-2 font-semibold text-left">Ack Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-bankos-border/50 dark:divide-bankos-dark-border/50">
                            <template x-for="r in recipients" :key="r.id">
                                <tr class="text-xs">
                                    <td class="py-2 font-medium text-bankos-text dark:text-bankos-dark-text" x-text="r.name"></td>
                                    <td class="py-2 text-center">
                                        <span x-show="r.read_at" class="text-green-600 font-semibold">&#10003;</span>
                                        <span x-show="!r.read_at" class="text-bankos-muted">&mdash;</span>
                                    </td>
                                    <td class="py-2 text-center">
                                        <span x-show="r.ack_at" class="text-green-600 font-semibold">&#10003;</span>
                                        <span x-show="!r.ack_at" class="text-bankos-muted">&mdash;</span>
                                    </td>
                                    <td class="py-2 text-bankos-text-sec italic" x-text="r.ack_note || '—'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
