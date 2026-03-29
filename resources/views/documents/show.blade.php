@extends('layouts.app')

@section('title', $document->title)

@section('content')
@php
    $isExpired    = $document->expiry_date && $document->expiry_date->isPast();
    $isExpiringSoon = !$isExpired && $document->expiry_date && $document->expiry_date->diffInDays(now()) <= ($document->alert_days_before ?? 30);

    $statusBadge = match($document->status) {
        'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'expired'  => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
        'archived' => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
        default    => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    };

    $isPdf   = str_contains($document->mime_type ?? '', 'pdf');
    $isImage = str_starts_with($document->mime_type ?? '', 'image/');

    $canReview = auth()->user()->hasAnyRole(['super_admin', 'admin', 'compliance_officer', 'branch_manager'])
                 && $document->status === 'pending';
    $notes              = $notes             ?? collect();
    $signatures         = $signatures        ?? collect();
    $workflowInstances  = $workflowInstances ?? collect();
    $availableWorkflows = $availableWorkflows ?? collect();
    $myPendingAction    = $myPendingAction   ?? null;
@endphp

<div class="max-w-7xl mx-auto">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-bankos-text-sec mb-4">
        <a href="{{ route('documents.index', ['documentable_type' => $document->documentable_type, 'documentable_id' => $document->documentable_id]) }}"
           class="hover:text-bankos-primary flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Documents
        </a>
        <span>/</span>
        <span class="text-bankos-text font-medium truncate max-w-xs">{{ $document->title }}</span>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ===== LEFT COLUMN (2/3) ===== --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Document Info Card --}}
            <div class="card p-6">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h1 class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ $document->title }}</h1>
                        <p class="text-sm text-bankos-text-sec mt-0.5">{{ str_replace('_', ' ', $document->document_type) }}</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold shrink-0 {{ $statusBadge }}">
                        {{ ucfirst($document->status) }}
                    </span>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-bankos-text-sec uppercase tracking-wide">Category</dt>
                        <dd class="mt-0.5 text-bankos-text dark:text-bankos-dark-text font-medium">{{ ucfirst($document->document_category) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-bankos-text-sec uppercase tracking-wide">Version</dt>
                        <dd class="mt-0.5 font-mono font-semibold text-bankos-primary">v{{ $document->version ?? 1 }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-bankos-text-sec uppercase tracking-wide">File Name</dt>
                        <dd class="mt-0.5 text-bankos-text dark:text-bankos-dark-text truncate">{{ $document->file_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-bankos-text-sec uppercase tracking-wide">File Size</dt>
                        <dd class="mt-0.5 text-bankos-text dark:text-bankos-dark-text">{{ $document->file_size_kb ? number_format($document->file_size_kb) . ' KB' : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-bankos-text-sec uppercase tracking-wide">Uploaded By</dt>
                        <dd class="mt-0.5 text-bankos-text dark:text-bankos-dark-text">{{ $document->uploadedBy?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-bankos-text-sec uppercase tracking-wide">Upload Date</dt>
                        <dd class="mt-0.5 text-bankos-text dark:text-bankos-dark-text">{{ $document->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-bankos-text-sec uppercase tracking-wide">Expiry Date</dt>
                        <dd class="mt-0.5 {{ $isExpired ? 'text-red-600 font-semibold' : ($isExpiringSoon ? 'text-amber-600 font-semibold' : 'text-bankos-text dark:text-bankos-dark-text') }}">
                            @if ($document->expiry_date)
                                {{ $document->expiry_date->format('d M Y') }}
                                @if ($isExpired)
                                    <span class="text-red-500 font-normal text-xs">(Expired)</span>
                                @elseif ($isExpiringSoon)
                                    <span class="text-amber-500 font-normal text-xs">(Expiring soon)</span>
                                @endif
                            @else
                                <span class="text-bankos-muted">No expiry</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-bankos-text-sec uppercase tracking-wide">Required</dt>
                        <dd class="mt-0.5">
                            @if ($document->is_required)
                                <span class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded-full font-medium">Yes — Required</span>
                            @else
                                <span class="text-xs text-bankos-muted">Optional</span>
                            @endif
                        </dd>
                    </div>
                    @if ($document->description)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-bankos-text-sec uppercase tracking-wide">Description</dt>
                        <dd class="mt-0.5 text-bankos-text dark:text-bankos-dark-text">{{ $document->description }}</dd>
                    </div>
                    @endif
                    @if ($document->reviewedByUser)
                    <div>
                        <dt class="text-xs font-medium text-bankos-text-sec uppercase tracking-wide">Reviewed By</dt>
                        <dd class="mt-0.5 text-bankos-text dark:text-bankos-dark-text">{{ $document->reviewedByUser->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-bankos-text-sec uppercase tracking-wide">Reviewed At</dt>
                        <dd class="mt-0.5 text-bankos-text dark:text-bankos-dark-text">{{ $document->reviewed_at?->format('d M Y, H:i') ?? '—' }}</dd>
                    </div>
                    @if ($document->review_notes)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-bankos-text-sec uppercase tracking-wide">Review Notes</dt>
                        <dd class="mt-0.5 text-bankos-text dark:text-bankos-dark-text italic">{{ $document->review_notes }}</dd>
                    </div>
                    @endif
                    @endif
                </dl>

                <div class="flex gap-2 mt-5 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                    <a href="{{ route('documents.download', $document) }}"
                       class="btn btn-primary text-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Download
                    </a>
                </div>
            </div>

            {{-- Document Preview --}}
            <div class="card p-4">
                <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    Document Preview
                </h3>
                @if ($isPdf)
                    <iframe src="{{ route('documents.preview', $document) }}"
                            class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border"
                            style="height: 500px;" title="{{ $document->title }}"></iframe>
                @elseif ($isImage)
                    <img src="{{ route('documents.preview', $document) }}"
                         alt="{{ $document->title }}"
                         class="max-w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border mx-auto block">
                @elseif (in_array($document->mime_type, ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword']))
                    {{-- Word Document Preview via Mammoth.js --}}
                    <div id="docx-preview" class="prose prose-sm max-w-none p-6 bg-white dark:bg-bankos-dark-card rounded-lg border border-bankos-border dark:border-bankos-dark-border overflow-y-auto" style="height: 500px;">
                        <div class="flex items-center justify-center py-12 text-bankos-muted">
                            <svg class="animate-spin h-6 w-6 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Loading document preview...
                        </div>
                    </div>
                    <script src="https://cdn.jsdelivr.net/npm/mammoth@1.6.0/mammoth.browser.min.js"></script>
                    <script>
                        fetch("{{ route('documents.preview', $document) }}")
                            .then(r => r.arrayBuffer())
                            .then(buffer => mammoth.convertToHtml({ arrayBuffer: buffer }))
                            .then(result => {
                                document.getElementById('docx-preview').innerHTML = result.value;
                            })
                            .catch(err => {
                                document.getElementById('docx-preview').innerHTML = '<div class="text-center py-12 text-bankos-muted"><p class="font-medium">Could not render Word document</p><p class="text-xs mt-1">' + err.message + '</p><a href="{{ route('documents.download', $document) }}" class="mt-3 inline-block btn btn-secondary text-sm">Download to View</a></div>';
                            });
                    </script>
                @elseif (in_array($document->mime_type, ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv']))
                    {{-- Excel/CSV Preview via SheetJS --}}
                    <div id="xlsx-preview" class="overflow-auto rounded-lg border border-bankos-border dark:border-bankos-dark-border" style="height: 500px;">
                        <div class="flex items-center justify-center py-12 text-bankos-muted">
                            <svg class="animate-spin h-6 w-6 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Loading spreadsheet preview...
                        </div>
                    </div>
                    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
                    <script>
                        fetch("{{ route('documents.preview', $document) }}")
                            .then(r => r.arrayBuffer())
                            .then(buffer => {
                                const wb = XLSX.read(buffer, { type: 'array' });
                                const ws = wb.Sheets[wb.SheetNames[0]];
                                const html = XLSX.utils.sheet_to_html(ws, { editable: false });
                                const container = document.getElementById('xlsx-preview');
                                container.innerHTML = html;
                                // Style the table
                                const table = container.querySelector('table');
                                if (table) {
                                    table.className = 'w-full text-sm';
                                    table.querySelectorAll('th, td').forEach(cell => {
                                        cell.style.padding = '8px 12px';
                                        cell.style.borderBottom = '1px solid #e2e8f0';
                                        cell.style.textAlign = 'left';
                                        cell.style.whiteSpace = 'nowrap';
                                    });
                                    table.querySelectorAll('th').forEach(th => {
                                        th.style.background = '#f8fafc';
                                        th.style.fontWeight = '600';
                                        th.style.fontSize = '12px';
                                        th.style.textTransform = 'uppercase';
                                        th.style.letterSpacing = '0.5px';
                                        th.style.color = '#64748b';
                                    });
                                }
                            })
                            .catch(err => {
                                document.getElementById('xlsx-preview').innerHTML = '<div class="text-center py-12 text-bankos-muted"><p class="font-medium">Could not render spreadsheet</p><p class="text-xs mt-1">' + err.message + '</p><a href="{{ route('documents.download', $document) }}" class="mt-3 inline-block btn btn-secondary text-sm">Download to View</a></div>';
                            });
                    </script>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-bankos-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mb-3 text-bankos-border"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        <p class="text-sm font-medium text-bankos-text-sec">Preview not available for this file type</p>
                        <p class="text-xs mt-1">{{ $document->mime_type }}</p>
                        <a href="{{ route('documents.download', $document) }}" class="mt-3 btn btn-secondary text-sm">Download to View</a>
                    </div>
                @endif
            </div>

            {{-- Review Form --}}
            @if ($canReview)
            <div class="card p-6 border-l-4 border-bankos-warning">
                <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                    Review This Document
                </h3>
                <form method="POST" action="{{ route('documents.review', $document) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Decision <span class="text-red-500">*</span></label>
                        <select name="status" required class="form-select w-full sm:w-48">
                            <option value="">-- Select --</option>
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Review Notes</label>
                        <textarea name="review_notes" rows="3" placeholder="Add notes about this decision..."
                                  class="form-input w-full resize-none"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary text-sm">Submit Review</button>
                </form>
            </div>
            @endif

        </div>

        {{-- ===== RIGHT COLUMN (1/3) ===== --}}
        <div class="space-y-6">

            {{-- Version History --}}
            <div class="card p-5">
                <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="12 8 12 12 14 14"></polyline><path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5"></path></svg>
                    Version History
                </h3>
                <ol class="relative border-l border-bankos-border dark:border-bankos-dark-border ml-2 space-y-4">
                    @forelse ($versions as $ver)
                    @php
                        $isCurrent = $ver->id === $document->id;
                        $verStatus = match($ver->status) {
                            'approved' => 'bg-green-100 text-green-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            'archived' => 'bg-gray-100 text-gray-500',
                            default    => 'bg-yellow-100 text-yellow-700',
                        };
                    @endphp
                    <li class="ml-4 {{ $isCurrent ? 'ring-2 ring-bankos-primary ring-offset-2 rounded-lg p-2 bg-bankos-light/30 dark:bg-bankos-primary/5' : '' }}">
                        <div class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border border-white dark:border-bankos-dark-surface {{ $isCurrent ? 'bg-bankos-primary' : 'bg-bankos-border' }}"></div>
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-xs font-mono font-bold {{ $isCurrent ? 'text-bankos-primary' : 'text-bankos-text-sec' }}">
                                    v{{ $ver->version ?? 1 }}
                                    @if ($isCurrent) <span class="font-sans font-normal text-bankos-primary">(current)</span> @endif
                                </p>
                                <p class="text-xs text-bankos-muted">{{ $ver->created_at->format('d M Y') }}</p>
                                <p class="text-xs text-bankos-text-sec">{{ $ver->uploadedBy?->name ?? '—' }}</p>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-xs px-1.5 py-0.5 rounded font-medium {{ $verStatus }}">{{ ucfirst($ver->status) }}</span>
                                <a href="{{ route('documents.download', $ver) }}"
                                   class="text-xs text-bankos-primary hover:underline">Download</a>
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="ml-4 text-xs text-bankos-muted py-2">No version history.</li>
                    @endforelse
                </ol>
            </div>

            {{-- Access Log --}}
            <div class="card p-5">
                <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    Access Log
                </h3>
                @forelse ($accessLogs as $log)
                @php
                    $actionBadge = match($log->action) {
                        'downloaded' => 'bg-green-100 text-green-700',
                        'printed'    => 'bg-purple-100 text-purple-700',
                        default      => 'bg-blue-100 text-blue-700',
                    };
                @endphp
                <div class="flex items-start justify-between gap-2 py-2 border-b border-bankos-border/50 dark:border-bankos-dark-border/50 last:border-0">
                    <div>
                        <p class="text-xs font-medium text-bankos-text dark:text-bankos-dark-text">
                            {{ $log->accessedByUser?->name ?? 'Unknown User' }}
                        </p>
                        <p class="text-xs text-bankos-muted">{{ $log->accessed_at?->diffForHumans() ?? '—' }}</p>
                        @if ($log->ip_address)
                            <p class="text-xs text-bankos-muted font-mono">{{ $log->ip_address }}</p>
                        @endif
                    </div>
                    <span class="text-xs px-1.5 py-0.5 rounded font-medium shrink-0 {{ $actionBadge }}">
                        {{ ucfirst($log->action) }}
                    </span>
                </div>
                @empty
                <p class="text-xs text-bankos-muted py-2">No access records yet.</p>
                @endforelse

                @if ($accessLogs->hasPages())
                    <div class="mt-3 pt-3 border-t border-bankos-border dark:border-bankos-dark-border text-xs">
                        {{ $accessLogs->links() }}
                    </div>
                @endif
            </div>

            {{-- Signatures --}}
            <div class="card p-5" x-data="{ showSign: false, sigType: 'typed', typed: '' }">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Digital Signatures</h3>
                @if($signatures->isNotEmpty())
                <div class="space-y-2 mb-3">
                    @foreach($signatures as $sig)
                    <div class="flex items-center justify-between text-xs">
                        <div>
                            <span class="font-medium text-gray-800">{{ $sig->signer->name }}</span>
                            <span class="text-gray-400 ml-1">· {{ ucfirst($sig->signature_type) }}</span>
                        </div>
                        <span class="text-gray-500">{{ $sig->signed_at->format('d M Y H:i') }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
                <button @click="showSign = !showSign" class="w-full btn text-sm bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg">
                    Sign This Document
                </button>
                <div x-show="showSign" x-transition class="mt-3">
                    <form action="{{ route('documents.sign', $document) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="flex gap-3">
                            <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer">
                                <input type="radio" x-model="sigType" value="typed"> Type name
                            </label>
                            <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer">
                                <input type="radio" x-model="sigType" value="drawn"> Draw
                            </label>
                        </div>
                        <div x-show="sigType === 'typed'">
                            <input type="text" x-model="typed" placeholder="Type your full name" class="form-input w-full text-sm">
                            <input type="hidden" name="signature_data" :value="typed">
                        </div>
                        <div x-show="sigType === 'drawn'" class="border rounded-lg overflow-hidden">
                            <canvas id="sigCanvas" width="300" height="100" class="bg-white w-full cursor-crosshair"></canvas>
                            <input type="hidden" name="signature_data" id="sigData">
                            <div class="flex justify-between px-2 py-1 bg-gray-50">
                                <span class="text-xs text-gray-400">Draw your signature above</span>
                                <button type="button" onclick="clearSig()" class="text-xs text-red-500">Clear</button>
                            </div>
                        </div>
                        <input type="hidden" name="signature_type" :value="sigType">
                        <button type="submit" class="w-full btn text-sm bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg">Confirm Signature</button>
                    </form>
                </div>
            </div>

            {{-- Workflow --}}
            <div class="card p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Approval Workflow</h3>

                @if($myPendingAction)
                <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <p class="text-xs font-semibold text-amber-800 mb-2">Action Required: {{ $myPendingAction->step->name }}</p>
                    <form action="{{ route('documents.workflow.act', $myPendingAction) }}" method="POST" class="space-y-2">
                        @csrf
                        <select name="decision" required class="form-input w-full text-sm">
                            <option value="">— Your decision —</option>
                            <option value="approved">Approve</option>
                            <option value="signed">Sign</option>
                            <option value="acknowledged">Acknowledge</option>
                            <option value="rejected">Reject</option>
                        </select>
                        <textarea name="notes" rows="2" class="form-input w-full text-sm resize-none" placeholder="Notes (optional)"></textarea>
                        <button type="submit" class="w-full btn text-sm bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg">Submit</button>
                    </form>
                </div>
                @endif

                @foreach($workflowInstances as $wfi)
                <div class="mb-3 text-sm">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-medium text-gray-800">{{ $wfi->workflow->name }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $wfi->statusColor() }}">{{ ucfirst($wfi->status) }}</span>
                    </div>
                    <div class="space-y-1">
                        @foreach($wfi->actions as $a)
                        <div class="flex items-center gap-2 text-xs">
                            <span class="w-2 h-2 rounded-full {{ $a->status === 'pending' ? 'bg-amber-400' : ($a->status === 'approved' || $a->status === 'signed' ? 'bg-green-500' : ($a->status === 'rejected' ? 'bg-red-500' : 'bg-gray-300')) }} flex-none"></span>
                            <span class="text-gray-700">{{ $a->step->name ?? 'Step' }}</span>
                            <span class="text-gray-400">→ {{ $a->assignee->name }}</span>
                            @if($a->acted_at)<span class="text-gray-400 ml-auto">{{ $a->acted_at->format('d M') }}</span>@endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                @if($availableWorkflows->isNotEmpty())
                <form action="{{ route('documents.workflow.initiate', $document) }}" method="POST" class="flex gap-2 mt-2">
                    @csrf
                    <select name="workflow_id" required class="form-input flex-1 text-sm">
                        <option value="">— Start workflow —</option>
                        @foreach($availableWorkflows as $wf)
                            <option value="{{ $wf->id }}">{{ $wf->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn text-sm bg-gray-700 hover:bg-gray-800 text-white px-3 py-1.5 rounded-lg">Start</button>
                </form>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- Signature canvas JS --}}
<script>
const canvas = document.getElementById('sigCanvas');
if (canvas) {
    const ctx = canvas.getContext('2d');
    let drawing = false;
    canvas.addEventListener('mousedown', e => { drawing = true; ctx.beginPath(); ctx.moveTo(e.offsetX, e.offsetY); });
    canvas.addEventListener('mousemove', e => { if (!drawing) return; ctx.lineTo(e.offsetX, e.offsetY); ctx.stroke(); });
    canvas.addEventListener('mouseup', () => { drawing = false; document.getElementById('sigData').value = canvas.toDataURL(); });
    canvas.addEventListener('touchstart', e => { e.preventDefault(); const t = e.touches[0]; const r = canvas.getBoundingClientRect(); drawing = true; ctx.beginPath(); ctx.moveTo(t.clientX - r.left, t.clientY - r.top); });
    canvas.addEventListener('touchmove', e => { e.preventDefault(); if (!drawing) return; const t = e.touches[0]; const r = canvas.getBoundingClientRect(); ctx.lineTo(t.clientX - r.left, t.clientY - r.top); ctx.stroke(); document.getElementById('sigData').value = canvas.toDataURL(); });
    canvas.addEventListener('touchend', () => { drawing = false; });
}
function clearSig() { if (canvas) { canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height); document.getElementById('sigData').value = ''; } }
</script>

{{-- Notes section below main grid --}}
<div class="max-w-7xl mx-auto mt-6">
    <div class="card p-5" x-data="{ showForm: false }">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-700">Notes & Comments ({{ $notes->count() }})</h3>
            <button @click="showForm = !showForm" class="text-xs text-blue-600 hover:text-blue-800 font-medium">+ Add Note</button>
        </div>

        <div x-show="showForm" x-transition class="mb-4">
            <form action="{{ route('documents.notes.store', $document) }}" method="POST" class="space-y-2">
                @csrf
                <textarea name="body" rows="3" required class="form-input w-full text-sm resize-none" placeholder="Write a note or comment…"></textarea>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300"> Internal note (staff only)
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg">Post</button>
                        <button type="button" @click="showForm = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1.5 rounded-lg">Cancel</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="space-y-3">
            @forelse($notes as $note)
            <div class="p-3 rounded-lg {{ $note->is_internal ? 'bg-yellow-50 border border-yellow-100' : 'bg-gray-50' }}">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-800">{{ $note->author->name }}</span>
                        @if($note->is_internal)<span class="px-1.5 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded">Internal</span>@endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">{{ $note->created_at->diffForHumans() }}</span>
                        @if($note->author_id === auth()->id())
                        <form action="{{ route('documents.notes.destroy', $note) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-600">Delete</button>
                        </form>
                        @endif
                    </div>
                </div>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $note->body }}</p>
            </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">No notes yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
