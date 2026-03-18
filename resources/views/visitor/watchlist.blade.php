@extends('layouts.app')
@section('title', 'Visitor Watchlist')
@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('visitor.dashboard') }}" class="text-xs text-gray-400 hover:text-gray-600">← Visitor Management</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Watchlist</h1>
            <p class="text-sm text-gray-500 mt-0.5">Blacklisted, VIP, and pre-approved visitors.</p>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Add to Watchlist</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $watchlist->where('status','blacklisted')->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Blacklisted</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-purple-600">{{ $watchlist->where('status','vip')->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">VIP</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $watchlist->where('status','pre_approved')->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Pre-Approved</p>
        </div>
    </div>

    {{-- Add to Watchlist Form --}}
    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Add to Watchlist</h2>
        <form action="{{ route('visitor.watchlist-store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-1">Visitor *</label>
                <select name="visitor_id" required class="form-input w-full text-sm">
                    <option value="">— Select visitor —</option>
                    @foreach($visitors as $v)
                        <option value="{{ $v->id }}">{{ $v->full_name }}{{ $v->company ? ' ('.$v->company.')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status *</label>
                <select name="status" required class="form-input w-full text-sm">
                    <option value="blacklisted">Blacklisted</option>
                    <option value="vip">VIP</option>
                    <option value="pre_approved">Pre-Approved</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Reason</label>
                <input type="text" name="reason" class="form-input w-full text-sm" placeholder="Reason for this classification">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Expires At (optional)</label>
                <input type="date" name="expires_at" class="form-input w-full text-sm">
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Add to Watchlist</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Visitor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Classification</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Added By</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Expires</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Added</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($watchlist as $entry)
                @php
                    $colors = [
                        'blacklisted'  => 'bg-red-100 text-red-700',
                        'vip'          => 'bg-purple-100 text-purple-700',
                        'pre_approved' => 'bg-green-100 text-green-700',
                    ];
                    $badge = $colors[$entry->status] ?? 'bg-gray-100 text-gray-500';
                    $expired = $entry->expires_at && $entry->expires_at->isPast();
                @endphp
                <tr class="hover:bg-gray-50 {{ $expired ? 'opacity-50' : '' }}">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $entry->visitor?->full_name }}</p>
                        <p class="text-xs text-gray-400">{{ $entry->visitor?->company }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">{{ ucwords(str_replace('_',' ',$entry->status)) }}</span>
                        @if($expired)<span class="ml-1 text-xs text-gray-400 italic">Expired</span>@endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $entry->reason ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $entry->addedBy?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs {{ $expired ? 'text-red-500' : 'text-gray-500' }}">
                        {{ $entry->expires_at ? $entry->expires_at->format('d M Y') : 'Never' }}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400">{{ $entry->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No watchlist entries.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
