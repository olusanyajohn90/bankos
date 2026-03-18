@extends('layouts.app')

@section('title', 'KPI Setup — Definitions')

@section('content')
<div class="space-y-6">

    {{-- Header + tabs --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">KPI Setup</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage KPI definitions, targets, staff profiles and teams</p>
        </div>
    </div>

    {{-- Tab bar --}}
    @include('kpi.setup._tabs', ['active' => 'definitions'])

    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    {{-- Add KPI form --}}
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Add Custom KPI Definition</h2>
        <form method="POST" action="{{ route('kpi.definitions.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" class="form-input w-full uppercase" placeholder="MY_CUSTOM_KPI" required>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" class="form-input w-full" required>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Category <span class="text-red-500">*</span></label>
                <select name="category" class="form-input w-full" required>
                    <option value="business_development">Business Development</option>
                    <option value="credit_lending">Credit & Lending</option>
                    <option value="operations">Operations</option>
                    <option value="customer_service">Customer Service</option>
                    <option value="branch">Branch</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Unit <span class="text-red-500">*</span></label>
                <select name="unit" class="form-input w-full">
                    <option value="count">Count</option>
                    <option value="ngn">₦ (NGN)</option>
                    <option value="percent">Percent (%)</option>
                    <option value="days">Days</option>
                    <option value="score">Score</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Direction <span class="text-red-500">*</span></label>
                <select name="direction" class="form-input w-full">
                    <option value="higher_better">Higher is Better</option>
                    <option value="lower_better">Lower is Better</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Weight</label>
                <input type="number" name="weight" step="0.5" min="0" max="10" value="1.0" class="form-input w-full">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Computation Type</label>
                <select name="computation_type" class="form-input w-full">
                    <option value="manual">Manual Entry</option>
                    <option value="auto">Auto-computed</option>
                </select>
            </div>
            <div class="md:col-span-2 flex items-end">
                <button type="submit" class="btn btn-primary">Add KPI Definition</button>
            </div>
        </form>
    </div>

    {{-- Definitions table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200 text-xs">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500">Name</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500">Code</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500">Category</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-500">Unit</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-500">Direction</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-500">Type</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-500">Weight</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-500">Active</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($kpis as $kpi)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $kpi->name }}</div>
                            @if($kpi->description)
                                <div class="text-xs text-gray-400 truncate max-w-xs" title="{{ $kpi->description }}">{{ Str::limit($kpi->description, 60) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $kpi->code }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $kpi->category_label }}</td>
                        <td class="px-4 py-3 text-center text-xs">{{ $kpi->unit }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs {{ $kpi->direction === 'lower_better' ? 'text-blue-600' : 'text-emerald-600' }}">
                                {{ $kpi->direction === 'lower_better' ? '↓ Lower' : '↑ Higher' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs {{ $kpi->computation_type === 'auto' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $kpi->computation_type === 'auto' ? 'Auto' : 'Manual' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-gray-600">{{ $kpi->weight }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="w-2 h-2 rounded-full inline-block {{ $kpi->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($kpi->is_system)
                                <span class="text-xs text-gray-300">System</span>
                            @else
                                <form method="POST" action="{{ route('kpi.definitions.destroy', $kpi) }}" class="inline"
                                      onsubmit="return confirm('Delete this KPI definition?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-400 hover:text-red-600">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                            No KPI definitions found. Run <code>php artisan db:seed --class=KpiDefinitionSeeder</code> to load system defaults.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $kpis->links() }}</div>
</div>
@endsection
