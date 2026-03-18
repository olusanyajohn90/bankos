@extends('layouts.app')
@section('title', 'Ticket Categories')
@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ticket Categories</h1>
            <p class="text-sm text-gray-500 mt-0.5">Organise tickets into categories and assign default routing teams.</p>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ New Category</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    {{-- New Category Form --}}
    <div x-show="showNew" x-transition class="card p-5">
        <form action="{{ route('support.categories.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div><label class="block text-xs text-gray-500 mb-1">Category Name *</label>
                <input type="text" name="name" required class="form-input w-full text-sm" placeholder="e.g. Card Dispute"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Icon (emoji)</label>
                <input type="text" name="icon" class="form-input w-full text-sm" placeholder="💳"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Default Routing Team</label>
                <select name="team_id" class="form-input w-full text-sm">
                    <option value="">— None —</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select></div>
            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create Category</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Default Team</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Tickets</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $cat)
                <tr class="hover:bg-gray-50 {{ ! $cat->is_active ? 'opacity-60' : '' }}">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            @if($cat->icon)<span class="text-lg">{{ $cat->icon }}</span>@endif
                            <span class="font-medium text-gray-900">{{ $cat->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $cat->team?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center font-semibold text-gray-700">{{ $cat->tickets_count }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $cat->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $cat->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <form action="{{ route('support.categories.toggle', $cat) }}" method="POST" class="inline">
                            @csrf
                            <button class="text-xs {{ $cat->is_active ? 'text-red-500 hover:text-red-700' : 'text-green-600 hover:text-green-800' }} font-medium">
                                {{ $cat->is_active ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
