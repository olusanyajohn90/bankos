@extends('layouts.app')

@section('title', 'Documents')

@section('content')
@php
    $entityName = '';
    if ($entity) {
        $entityName = match(true) {
            method_exists($entity, 'getFullNameAttribute') => $entity->full_name,
            isset($entity->name)                           => $entity->name,
            isset($entity->first_name)                     => $entity->first_name . ' ' . ($entity->last_name ?? ''),
            default                                        => class_basename($documentableType) . ' #' . $documentableId,
        };
    }
    $baseTypeLabel = class_basename($documentableType);
    $categories = ['identity','financial','legal','compliance','operational','other'];
    $statuses   = ['pending','approved','rejected','expired','archived'];
@endphp

<div class="max-w-7xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-bankos-text-sec mb-1">
                <a href="javascript:history.back()" class="hover:text-bankos-primary flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    Back to {{ $baseTypeLabel }}
                </a>
                <span>/</span>
                <span class="text-bankos-text font-medium">Documents</span>
            </div>
            <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">
                Documents &mdash; {{ $entityName ?: ($baseTypeLabel . ' #' . $documentableId) }}
            </h1>
            <p class="text-sm text-bankos-text-sec mt-1">Manage compliance documents, track versions, and review status.</p>
        </div>
        <a href="{{ route('documents.create', ['documentable_type' => $documentableType, 'documentable_id' => $documentableId]) }}"
           class="btn btn-primary flex items-center gap-2 shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Upload Document
        </a>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 rounded-lg text-sm flex items-center gap-2">
            <svg class="h-4 w-4 shrink-0 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-lg text-sm flex items-center gap-2">
            <svg class="h-4 w-4 shrink-0 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- CBN Checklist Status --}}
    @if (! empty($checklistStatus))
        <div class="card p-4 mb-6">
            <div class="flex items-center gap-2 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-primary"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">CBN Compliance Checklist</h3>
            </div>
            @include('documents._checklist_status', ['checklistStatus' => $checklistStatus])
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="card p-4 mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="documentable_type" value="{{ $documentableType }}">
            <input type="hidden" name="documentable_id" value="{{ $documentableId }}">
            <div>
                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Category</label>
                <select name="category" class="form-select text-sm" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                            {{ ucfirst($cat) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Status</label>
                <select name="status" class="form-select text-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>
                            {{ ucfirst($st) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 self-end pb-1">
                <input type="checkbox" id="show_all" name="show_all" value="1" {{ request('show_all') ? 'checked' : '' }}
                       class="h-4 w-4 text-bankos-primary border-bankos-border rounded" onchange="this.form.submit()">
                <label for="show_all" class="text-sm text-bankos-text-sec cursor-pointer">Show all versions</label>
            </div>
            @if (request()->hasAny(['category', 'status', 'show_all']))
                <a href="{{ route('documents.index', ['documentable_type' => $documentableType, 'documentable_id' => $documentableId]) }}"
                   class="text-sm text-bankos-primary hover:underline self-end pb-1">Clear filters</a>
            @endif
        </form>
    </div>

    {{-- Documents Table --}}
    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-4 py-3 font-semibold">Title & Type</th>
                        <th class="px-4 py-3 font-semibold">Category</th>
                        <th class="px-4 py-3 font-semibold">Version</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold">Expiry</th>
                        <th class="px-4 py-3 font-semibold">Uploaded By</th>
                        <th class="px-4 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse ($documents as $doc)
                    @php
                        $isExpired    = $doc->expiry_date && $doc->expiry_date->isPast();
                        $isExpiringSoon = !$isExpired && $doc->expiry_date && $doc->expiry_date->diffInDays(now()) <= ($doc->alert_days_before ?? 30);
                        $expiryClass  = $isExpired ? 'text-red-600 font-semibold' : ($isExpiringSoon ? 'text-amber-600 font-semibold' : 'text-bankos-text-sec');
                        $statusBadge  = match($doc->status) {
                            'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                            'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            'expired'  => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                            'archived' => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
                            default    => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-bankos-text dark:text-white">{{ $doc->title }}</p>
                            <p class="text-xs text-bankos-muted mt-0.5 uppercase tracking-wide">{{ str_replace('_', ' ', $doc->document_type) }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 px-2 py-0.5 rounded-full font-medium">
                                {{ ucfirst($doc->document_category) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs bg-bankos-light text-bankos-primary px-2 py-0.5 rounded-full font-mono font-semibold">
                                v{{ $doc->version ?? 1 }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $statusBadge }}">
                                {{ ucfirst($doc->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs {{ $expiryClass }}">
                            @if ($doc->expiry_date)
                                {{ $doc->expiry_date->format('d M Y') }}
                                @if ($isExpired)
                                    <span class="block text-red-500 font-normal">(Expired)</span>
                                @elseif ($isExpiringSoon)
                                    <span class="block text-amber-500 font-normal">(Expiring soon)</span>
                                @endif
                            @else
                                <span class="text-bankos-muted">No expiry</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-bankos-text-sec">
                            <p>{{ $doc->uploadedBy?->name ?? '—' }}</p>
                            <p class="text-bankos-muted">{{ $doc->created_at->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('documents.show', $doc) }}"
                                   class="text-xs text-bankos-primary hover:underline font-medium">View</a>

                                <a href="{{ route('documents.download', $doc) }}"
                                   class="text-xs text-bankos-text-sec hover:text-bankos-primary font-medium">Download</a>

                                <button type="button"
                                        x-data
                                        @click="$dispatch('open-version-modal', { id: {{ $doc->id }} })"
                                        class="text-xs text-bankos-text-sec hover:text-bankos-primary font-medium">
                                    New Version
                                </button>

                                <form method="POST" action="{{ route('documents.destroy', $doc) }}"
                                      onsubmit="return confirm('Archive this document?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Archive</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-bankos-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-bankos-border"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            <p class="font-medium text-bankos-text-sec">No documents found</p>
                            <p class="text-xs mt-1">Upload the first document to get started.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($documents->hasPages())
            <div class="px-4 py-3 border-t border-bankos-border dark:border-bankos-dark-border">
                {{ $documents->withQueryString()->links() }}
            </div>
        @endif
    </div>

</div>

{{-- New Version Upload Modals --}}
@foreach ($documents as $doc)
<div x-data="{ open: false }"
     @open-version-modal.window="open = ($event.detail.id === {{ $doc->id }})"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
     x-transition>
    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl shadow-xl w-full max-w-md mx-4 p-6" @click.outside="open = false">
        <h3 class="text-base font-semibold text-bankos-text dark:text-bankos-dark-text mb-1">Upload New Version</h3>
        <p class="text-xs text-bankos-text-sec mb-4">{{ $doc->title }}</p>
        <form method="POST" action="{{ route('documents.version', $doc) }}" enctype="multipart/form-data">
            @csrf
            <input type="file" name="file" required class="form-input w-full text-sm mb-4">
            <div class="flex gap-2 justify-end">
                <button type="button" @click="open = false" class="btn btn-secondary text-sm">Cancel</button>
                <button type="submit" class="btn btn-primary text-sm">Upload Version</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
