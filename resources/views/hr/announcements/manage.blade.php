@extends('layouts.app')
@section('title', 'Manage Announcements')
@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('hr.announcements.index') }}" class="text-xs text-gray-400 hover:text-gray-600">← Notice Board</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Manage Announcements</h1>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ New Announcement</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    {{-- New Announcement Form --}}
    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">New Announcement</h2>
        <form action="{{ route('hr.announcements.store') }}" method="POST" class="space-y-4">
            @csrf
            <div><label class="block text-xs text-gray-500 mb-1">Title *</label>
                <input type="text" name="title" required class="form-input w-full text-sm" placeholder="Announcement headline"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Body *</label>
                <textarea name="body" rows="5" required class="form-input w-full text-sm resize-none" placeholder="Full announcement text…"></textarea></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div><label class="block text-xs text-gray-500 mb-1">Priority</label>
                    <select name="priority" class="form-input w-full text-sm">
                        <option value="low">Low</option>
                        <option value="normal" selected>Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select></div>
                <div><label class="block text-xs text-gray-500 mb-1">Audience</label>
                    <select name="audience" class="form-input w-full text-sm">
                        <option value="all">All Staff</option>
                        <option value="branch">By Branch</option>
                        <option value="department">By Department</option>
                    </select></div>
                <div class="flex flex-col gap-2 pt-5">
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="is_pinned" value="1" class="rounded border-gray-300"> Pin to top
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="publish_now" value="1" checked class="rounded border-gray-300"> Publish immediately
                    </label>
                </div>
                <div><label class="block text-xs text-gray-500 mb-1">Publish At (optional)</label>
                    <input type="datetime-local" name="publish_at" class="form-input w-full text-sm"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Expires At (optional)</label>
                    <input type="datetime-local" name="expires_at" class="form-input w-full text-sm"></div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Save</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Priority</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Published</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Expires</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($announcements as $a)
                    @php
                        $pb = match($a->priority) {
                            'urgent' => 'bg-red-100 text-red-700',
                            'high'   => 'bg-amber-100 text-amber-700',
                            'low'    => 'bg-gray-100 text-gray-500',
                            default  => 'bg-blue-100 text-blue-700',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                @if($a->is_pinned)<span class="text-amber-500 text-xs">📌</span>@endif
                                <span class="font-medium text-gray-900">{{ $a->title }}</span>
                            </div>
                            <p class="text-xs text-gray-400">{{ $a->createdBy?->name }} · {{ $a->created_at->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $pb }}">{{ ucfirst($a->priority) }}</span></td>
                        <td class="px-4 py-3">
                            @if($a->is_published)
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">Published {{ $a->publish_at?->format('d M') }}</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $a->expires_at?->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if(! $a->is_published)
                                    <form action="{{ route('hr.announcements.publish', $a) }}" method="POST">
                                        @csrf
                                        <button class="text-xs text-blue-600 hover:text-blue-800 font-medium">Publish</button>
                                    </form>
                                @endif
                                <form action="{{ route('hr.announcements.pin', $a) }}" method="POST">
                                    @csrf
                                    <button class="text-xs text-amber-600 hover:text-amber-800 font-medium">{{ $a->is_pinned ? 'Unpin' : 'Pin' }}</button>
                                </form>
                                <form action="{{ route('hr.announcements.destroy', $a) }}" method="POST" onsubmit="return confirm('Delete this announcement?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-500 hover:text-red-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">No announcements yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($announcements->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $announcements->links() }}</div>
        @endif
    </div>

</div>
@endsection
