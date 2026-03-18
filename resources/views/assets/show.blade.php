@extends('layouts.app')
@section('title', $asset->name)
@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ showAssign: false, showMaint: false }">

    <div>
        <a href="{{ route('assets.index') }}" class="text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1 mb-2">← Back to Assets</a>
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $asset->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $asset->asset_tag }} · {{ $asset->category?->name }} · {{ $asset->branch?->name }}</p>
            </div>
            @php
                $sb = match($asset->status) {
                    'available' => 'bg-green-100 text-green-700', 'assigned' => 'bg-blue-100 text-blue-700',
                    'under_maintenance' => 'bg-amber-100 text-amber-700', default => 'bg-gray-100 text-gray-500',
                };
            @endphp
            <span class="px-3 py-1.5 rounded-full text-sm font-bold {{ $sb }}">{{ ucwords(str_replace('_',' ',$asset->status)) }}</span>
        </div>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>@endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Info --}}
        <div class="space-y-4">
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Asset Details</h2>
                <dl class="space-y-2 text-sm">
                    @foreach([
                        'Tag' => $asset->asset_tag, 'Serial No.' => $asset->serial_number ?? '—',
                        'Model' => $asset->model ?? '—', 'Manufacturer' => $asset->manufacturer ?? '—',
                        'Vendor' => $asset->vendor ?? '—', 'Condition' => ucfirst($asset->condition),
                        'Purchase Date' => $asset->purchase_date?->format('d M Y') ?? '—',
                        'Purchase Price' => $asset->purchase_price ? '₦'.number_format($asset->purchase_price) : '—',
                        'Current Value' => $asset->current_value ? '₦'.number_format($asset->current_value) : '—',
                        'Warranty Expires' => $asset->warranty_expiry ? ($asset->isWarrantyValid() ? $asset->warranty_expiry->format('d M Y') . ' ✓' : $asset->warranty_expiry->format('d M Y') . ' (expired)') : '—',
                    ] as $label => $value)
                        <div><dt class="text-xs text-gray-400">{{ $label }}</dt><dd class="font-medium text-gray-800 text-sm">{{ $value }}</dd></div>
                    @endforeach
                </dl>
            </div>

            {{-- Current Assignment --}}
            @if($asset->currentAssignment)
                <div class="card p-5 bg-blue-50 border border-blue-100">
                    <h2 class="text-sm font-semibold text-blue-700 mb-3">Currently Assigned</h2>
                    <p class="font-semibold text-gray-900">{{ $asset->currentAssignment->staffProfile?->user?->name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Since {{ $asset->currentAssignment->assigned_date->format('d M Y') }}</p>
                    <form action="{{ route('assets.return', $asset) }}" method="POST" class="mt-3 space-y-2">
                        @csrf
                        <select name="condition_at_return" required class="form-input w-full text-sm">
                            @foreach(['good','fair','poor','damaged'] as $c)
                                <option value="{{ $c }}">Returned in {{ ucfirst($c) }} condition</option>
                            @endforeach
                        </select>
                        <input type="text" name="notes" class="form-input w-full text-sm" placeholder="Return notes">
                        <button type="submit" onclick="return confirm('Confirm asset return?')" class="w-full btn text-sm bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg">Return Asset</button>
                    </form>
                </div>
            @elseif($asset->status === 'available')
                <div class="card p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-gray-700">Assign Asset</h2>
                        <button @click="showAssign = !showAssign" class="text-xs text-blue-600 hover:underline">+ Assign</button>
                    </div>
                    <div x-show="showAssign" x-transition>
                        <form action="{{ route('assets.assign', $asset) }}" method="POST" class="space-y-3">
                            @csrf
                            <select name="staff_profile_id" required class="form-input w-full text-sm">
                                <option value="">— Select Staff —</option>
                                @foreach($staff as $sp)
                                    <option value="{{ $sp->id }}">{{ $sp->user?->name }}</option>
                                @endforeach
                            </select>
                            <select name="condition_at_assignment" required class="form-input w-full text-sm">
                                @foreach(['new','good','fair','poor'] as $c)
                                    <option value="{{ $c }}" {{ $c === $asset->condition ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="notes" class="form-input w-full text-sm" placeholder="Notes">
                            <button type="submit" class="w-full btn text-sm bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg">Assign</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right: History + Maintenance --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Log Maintenance --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-700">Log Maintenance</h2>
                    <button @click="showMaint = !showMaint" class="text-xs text-blue-600 hover:underline">+ Log</button>
                </div>
                <div x-show="showMaint" x-transition>
                    <form action="{{ route('assets.maintenance', $asset) }}" method="POST" class="grid grid-cols-2 gap-3">
                        @csrf
                        <div><label class="block text-xs text-gray-500 mb-1">Type</label>
                            <select name="maintenance_type" class="form-input w-full text-sm">
                                @foreach(['routine','repair','upgrade','inspection'] as $t)
                                    <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                                @endforeach
                            </select></div>
                        <div><label class="block text-xs text-gray-500 mb-1">Scheduled Date</label>
                            <input type="date" name="scheduled_date" value="{{ now()->toDateString() }}" required class="form-input w-full text-sm"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">Vendor</label>
                            <input type="text" name="vendor" class="form-input w-full text-sm"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">Estimated Cost (₦)</label>
                            <input type="number" name="cost" class="form-input w-full text-sm" min="0" step="100"></div>
                        <div class="col-span-2"><label class="block text-xs text-gray-500 mb-1">Description *</label>
                            <textarea name="description" rows="2" required class="form-input w-full text-sm resize-none"></textarea></div>
                        <div class="col-span-2"><button type="submit" class="btn text-sm bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg">Log Maintenance</button></div>
                    </form>
                </div>
            </div>

            {{-- Maintenance history --}}
            @if($asset->maintenanceLogs->isNotEmpty())
                <div class="card p-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Maintenance History</h2>
                    <div class="space-y-3">
                        @foreach($asset->maintenanceLogs->sortByDesc('scheduled_date') as $log)
                            @php
                                $lc = match($log->status) {
                                    'completed' => 'bg-green-50 border-green-100', 'in_progress' => 'bg-amber-50 border-amber-100',
                                    default => 'bg-gray-50 border-gray-100'
                                };
                            @endphp
                            <div class="rounded-lg border p-3 {{ $lc }}">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-600 uppercase">{{ ucfirst($log->maintenance_type) }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400">{{ $log->scheduled_date->format('d M Y') }}</span>
                                        @if($log->status !== 'completed')
                                            <form action="{{ route('assets.maintenance.complete', $log) }}" method="POST">
                                                @csrf
                                                <button class="text-xs text-green-600 hover:text-green-800 font-medium">Mark Complete</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">{{ $log->description }}</p>
                                @if($log->cost)<p class="text-xs text-gray-500 mt-0.5">Cost: ₦{{ number_format($log->cost) }} · {{ $log->vendor }}</p>@endif
                                @if($log->findings)<p class="text-xs text-gray-500 mt-0.5 italic">{{ $log->findings }}</p>@endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Assignment history --}}
            @if($asset->assignments->isNotEmpty())
                <div class="card p-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Assignment History</h2>
                    <table class="w-full text-sm">
                        <thead class="text-xs text-gray-500">
                            <tr>
                                <th class="text-left pb-2">Staff</th>
                                <th class="text-left pb-2">Assigned</th>
                                <th class="text-left pb-2">Returned</th>
                                <th class="text-left pb-2">Condition</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($asset->assignments->sortByDesc('assigned_date') as $a)
                                <tr>
                                    <td class="py-2 font-medium text-gray-800">{{ $a->staffProfile?->user?->name }}</td>
                                    <td class="py-2 text-gray-600">{{ $a->assigned_date->format('d M Y') }}</td>
                                    <td class="py-2 text-gray-600">{{ $a->returned_date?->format('d M Y') ?? '(Active)' }}</td>
                                    <td class="py-2 text-gray-600">{{ ucfirst($a->condition_at_return ?? $a->condition_at_assignment) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
