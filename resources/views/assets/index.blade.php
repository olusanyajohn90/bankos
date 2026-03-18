@extends('layouts.app')
@section('title', 'Asset Register')
@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Asset Register</h1>
            <p class="text-sm text-gray-500 mt-0.5">Track all organisational assets</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('assets.categories') }}" class="btn text-sm bg-gray-100 hover:bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Categories</a>
            <a href="{{ route('assets.procurement') }}" class="btn text-sm bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg">Procurement</a>
            <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Register Asset</button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="card p-4 text-center"><p class="text-xl font-bold text-gray-800">{{ $total }}</p><p class="text-xs text-gray-500">Total</p></div>
        <div class="card p-4 text-center"><p class="text-xl font-bold text-green-600">{{ $available }}</p><p class="text-xs text-gray-500">Available</p></div>
        <div class="card p-4 text-center"><p class="text-xl font-bold text-blue-600">{{ $assigned }}</p><p class="text-xs text-gray-500">Assigned</p></div>
        <div class="card p-4 text-center"><p class="text-xl font-bold text-amber-500">{{ $maint }}</p><p class="text-xs text-gray-500">Maintenance</p></div>
        <div class="card p-4 text-center"><p class="text-xl font-bold text-purple-600">₦{{ number_format($totalValue) }}</p><p class="text-xs text-gray-500">Book Value</p></div>
    </div>

    {{-- New Asset Form --}}
    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Register New Asset</h2>
        <form action="{{ route('assets.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div><label class="block text-xs text-gray-500 mb-1">Asset Name *</label>
                <input type="text" name="name" required class="form-input w-full text-sm" placeholder="e.g. HP EliteBook 840 G10"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Category *</label>
                <select name="category_id" required class="form-input w-full text-sm">
                    <option value="">—</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Serial Number</label>
                <input type="text" name="serial_number" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Model</label>
                <input type="text" name="model" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Manufacturer</label>
                <input type="text" name="manufacturer" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Vendor</label>
                <input type="text" name="vendor" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Purchase Date</label>
                <input type="date" name="purchase_date" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Purchase Price (₦)</label>
                <input type="number" name="purchase_price" class="form-input w-full text-sm" min="0" step="100"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Warranty Expiry</label>
                <input type="date" name="warranty_expiry" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Condition</label>
                <select name="condition" class="form-input w-full text-sm">
                    @foreach(['new','good','fair','poor'] as $c)
                        <option value="{{ $c }}" {{ $c === 'new' ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Branch / Location</label>
                <select name="branch_id" class="form-input w-full text-sm">
                    <option value="">—</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Notes</label>
                <input type="text" name="notes" class="form-input w-full text-sm"></div>
            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Register</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, tag, serial…" class="form-input text-sm flex-1 min-w-[180px]">
        <select name="status" class="form-input text-sm">
            <option value="">All Statuses</option>
            @foreach(['available','assigned','under_maintenance','disposed','lost'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="category_id" class="form-input text-sm">
            <option value="">All Categories</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ request('category_id') === $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn text-sm bg-gray-700 text-white px-4 py-2 rounded-lg">Filter</button>
        @if(request()->hasAny(['search','status','category_id']))
            <a href="{{ route('assets.index') }}" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Asset</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tag</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Assigned To</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Value</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($assets as $asset)
                    @php
                        $sb = match($asset->status) {
                            'available'         => 'bg-green-100 text-green-700',
                            'assigned'          => 'bg-blue-100 text-blue-700',
                            'under_maintenance' => 'bg-amber-100 text-amber-700',
                            'disposed'          => 'bg-gray-100 text-gray-400',
                            'lost'              => 'bg-red-100 text-red-700',
                            default             => 'bg-gray-100 text-gray-500',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('assets.show', $asset) }}" class="font-medium text-blue-700 hover:underline">{{ $asset->name }}</a>
                            <p class="text-xs text-gray-400">{{ $asset->manufacturer }} {{ $asset->model }}</p>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $asset->asset_tag }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $asset->category?->name }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $asset->currentAssignment?->staffProfile?->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $asset->current_value ? '₦' . number_format($asset->current_value) : '—' }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $sb }}">{{ ucwords(str_replace('_',' ',$asset->status)) }}</span></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('assets.show', $asset) }}" class="text-xs text-blue-600 hover:underline">Manage →</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No assets found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($assets->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $assets->links() }}</div>
        @endif
    </div>

</div>
@endsection
