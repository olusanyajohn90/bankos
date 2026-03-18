@extends('layouts.app')
@section('title', 'Document Management')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Document Management</h1>
            <p class="text-sm text-gray-500 mt-0.5">Central repository for all bank documents — internal, inbound and outbound.</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('documents.my-actions') }}" class="btn text-sm bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg">
                My Actions @if($pendingMyAction->count())<span class="ml-1 bg-white text-amber-600 text-xs font-bold px-1.5 rounded-full">{{ $pendingMyAction->count() }}</span>@endif
            </a>
            <a href="{{ route('documents.workflows.index') }}" class="btn text-sm bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg">Workflows</a>
            <a href="{{ route('documents.create') }}" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Upload Document</a>
        </div>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Total Documents</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-amber-600">{{ $stats['pending_review'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Pending Review</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Approved</div>
        </div>
        <div class="card p-4 text-center border-l-4 border-orange-400">
            <div class="text-2xl font-bold text-orange-500">{{ $stats['expiring_soon'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Expiring ≤30 days</div>
        </div>
        <div class="card p-4 text-center border-l-4 border-red-400">
            <div class="text-2xl font-bold text-red-500">{{ $stats['expired'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Expired</div>
        </div>
    </div>

    {{-- Pending My Actions --}}
    @if($pendingMyAction->count())
    <div class="card p-5 border-l-4 border-amber-400">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Awaiting My Action</h2>
        <div class="space-y-2">
            @foreach($pendingMyAction as $action)
            <div class="flex items-center justify-between gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-900">{{ $action->instance->document->title ?? 'Unknown' }}</span>
                    <span class="ml-2 text-xs text-gray-500">{{ $action->step->name ?? '' }} · {{ $action->instance->workflow->name ?? '' }}</span>
                </div>
                <div class="flex items-center gap-3">
                    @if($action->deadline_at)
                        <span class="text-xs {{ $action->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                            Due {{ $action->deadline_at->diffForHumans() }}
                        </span>
                    @endif
                    <a href="{{ route('documents.show', $action->instance->document_id) }}" class="text-xs bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700">Review</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="card p-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-xs text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-input w-full text-sm" placeholder="Title or document type…">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Status</label>
            <select name="status" class="form-input text-sm">
                <option value="">All statuses</option>
                @foreach(['pending','pending_review','approved','rejected','archived'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Category</label>
            <select name="category" class="form-input text-sm">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Filter</button>
        @if(request()->hasAny(['search','status','category','type']))
            <a href="{{ route('documents.index') }}" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Clear</a>
        @endif
    </form>

    {{-- Documents Table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Document</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Category / Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Direction</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Expiry</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Uploaded</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($documents as $doc)
                @php
                    $sc = match($doc->status) {
                        'approved'       => 'bg-green-100 text-green-700',
                        'pending_review' => 'bg-amber-100 text-amber-700',
                        'rejected'       => 'bg-red-100 text-red-700',
                        'archived'       => 'bg-gray-100 text-gray-500',
                        default          => 'bg-blue-100 text-blue-700',
                    };
                    $dc = match($doc->direction ?? 'internal') {
                        'inbound'  => 'bg-purple-100 text-purple-700',
                        'outbound' => 'bg-teal-100 text-teal-700',
                        default    => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <tr class="hover:bg-gray-50 {{ $doc->isExpired() ? 'bg-red-50' : ($doc->isExpiringSoon() ? 'bg-orange-50' : '') }}">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            @if($doc->is_confidential ?? false)<span class="text-xs text-red-500" title="Confidential">🔒</span>@endif
                            <div>
                                <a href="{{ route('documents.show', $doc) }}" class="font-medium text-blue-700 hover:text-blue-900">{{ $doc->title }}</a>
                                @if($doc->ref_number ?? false)<div class="text-xs text-gray-400">Ref: {{ $doc->ref_number }}</div>@endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <div class="text-gray-700">{{ $doc->document_category }}</div>
                        <div class="text-xs text-gray-400">{{ $doc->document_type }}</div>
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell">
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $dc }}">{{ ucfirst($doc->direction ?? 'Internal') }}</span>
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell text-xs">
                        @if($doc->expiry_date)
                            <span class="{{ $doc->isExpired() ? 'text-red-600 font-semibold' : ($doc->isExpiringSoon() ? 'text-orange-600 font-semibold' : 'text-gray-600') }}">
                                {{ $doc->expiry_date->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">{{ ucwords(str_replace('_',' ',$doc->status)) }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500 hidden md:table-cell">
                        {{ $doc->uploadedBy?->name }}<br>{{ $doc->created_at->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('documents.show', $doc) }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">View</a>
                            <a href="{{ route('documents.download', $doc) }}" class="text-xs text-gray-600 hover:text-gray-800">Download</a>
                        </div>
                    </td>
                </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No documents found. <a href="{{ route('documents.create') }}" class="text-blue-600 hover:underline">Upload the first document</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($documents->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $documents->links() }}</div>
        @endif
    </div>

</div>
@endsection
