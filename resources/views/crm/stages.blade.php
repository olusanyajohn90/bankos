@extends('layouts.app')
@section('title', 'Pipeline Stages')
@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('crm.dashboard') }}" class="text-xs text-gray-400 hover:text-gray-600">← Back to CRM</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Pipeline Stages</h1>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Add Stage</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>@endif

    <div x-show="showNew" x-transition class="card p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">New Stage</h2>
        <form action="{{ route('crm.pipeline.stages.store') }}" method="POST" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs text-gray-500 mb-1">Stage Name *</label>
                <input type="text" name="name" required class="form-input w-full text-sm" placeholder="e.g. Proposal Sent">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Color</label>
                <input type="color" name="color" value="#3b82f6" class="form-input w-14 h-9 cursor-pointer p-1">
            </div>
            <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Add</button>
            <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-10">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stage Name</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Color</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Leads</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($stages as $stage)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center text-gray-400 font-mono">{{ $stage->position }}</td>
                        <td class="px-4 py-3">
                            <span class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full" style="background: {{ $stage->color }}"></span>
                                <span class="font-medium text-gray-900">{{ $stage->name }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs font-mono" style="background: {{ $stage->color }}20; color: {{ $stage->color }}">{{ $stage->color }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500">
                            @if($stage->is_closed_won) <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">Won</span>
                            @elseif($stage->is_closed_lost) <span class="px-2 py-0.5 bg-red-100 text-red-600 rounded-full font-medium">Lost</span>
                            @else <span class="text-gray-400">Active</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-700">{{ $stage->leads_count ?? 0 }}</td>
                        <td class="px-4 py-3 text-right">
                            @if(!$stage->is_closed_won && !$stage->is_closed_lost)
                                <form action="{{ route('crm.pipeline.stages.destroy', $stage) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete stage {{ $stage->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-500 hover:text-red-700">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No stages configured.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
