@extends('layouts.app')
@section('title', 'Asset Categories')
@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('assets.index') }}" class="text-xs text-gray-400 hover:text-gray-600">← Back to Assets</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Asset Categories</h1>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Add Category</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">New Category</h2>
        <form action="{{ route('assets.categories.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div><label class="block text-xs text-gray-500 mb-1">Name *</label>
                <input type="text" name="name" required class="form-input w-full text-sm" placeholder="e.g. IT Equipment"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Code</label>
                <input type="text" name="code" class="form-input w-full text-sm" placeholder="IT"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Depreciation Years</label>
                <input type="number" name="depreciation_years" value="5" min="0" max="50" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Depreciation Method</label>
                <select name="depreciation_method" class="form-input w-full text-sm">
                    <option value="straight_line">Straight Line</option>
                    <option value="reducing_balance">Reducing Balance</option>
                    <option value="none">None</option>
                </select></div>
            <div class="md:col-span-2"><label class="block text-xs text-gray-500 mb-1">Description</label>
                <input type="text" name="description" class="form-input w-full text-sm"></div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Code</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Dep. Years</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Method</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Assets</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $cat)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $cat->name }}</td>
                        <td class="px-4 py-3 font-mono text-gray-600">{{ $cat->code ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $cat->depreciation_years }}y</td>
                        <td class="px-4 py-3 text-gray-600">{{ ucwords(str_replace('_',' ',$cat->depreciation_method)) }}</td>
                        <td class="px-4 py-3 text-center font-bold text-gray-700">{{ $cat->assets_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
