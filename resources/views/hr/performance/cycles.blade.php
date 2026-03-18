@extends('layouts.app')

@section('title', 'Performance Review Cycles')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Performance Review Cycles</h1>
        <p class="text-sm text-gray-500 mt-1">Manage appraisal cycles and initiate reviews for all active staff.</p>
    </div>

    @include('hr.org._tabs')

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Cycles Table --}}
    <div class="card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Review Cycles</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Period Type</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Reviews</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($cycles as $cycle)
                        @php
                            $statusBadge = match($cycle->status) {
                                'draft'  => 'bg-gray-100 text-gray-600',
                                'active' => 'bg-green-100 text-green-800',
                                'closed' => 'bg-blue-100 text-blue-700',
                                default  => 'bg-gray-100 text-gray-600',
                            };
                            $periodLabel = match($cycle->period_type) {
                                'annual'      => 'Annual',
                                'semi_annual' => 'Semi-Annual',
                                'quarterly'   => 'Quarterly',
                                default       => ucfirst($cycle->period_type),
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $cycle->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $periodLabel }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($cycle->start_date)->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($cycle->end_date)->format('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge }}">
                                    {{ ucfirst($cycle->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-700 font-medium">{{ $cycle->reviews_count }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('hr.performance.cycles.show', $cycle) }}"
                                       class="text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded">View</a>

                                    @if ($cycle->status === 'draft')
                                        <form action="{{ route('hr.performance.cycles.activate', $cycle) }}" method="POST"
                                              onsubmit="return confirm('Activate this cycle and create reviews for all active staff?')">
                                            @csrf
                                            <button type="submit" class="text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 px-2 py-1 rounded">Activate</button>
                                        </form>
                                    @endif

                                    @if ($cycle->status === 'active')
                                        <form action="{{ route('hr.performance.cycles.close', $cycle) }}" method="POST"
                                              onsubmit="return confirm('Close this review cycle?')">
                                            @csrf
                                            <button type="submit" class="text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded">Close</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400 text-sm">No review cycles found. Create the first one below.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($cycles->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $cycles->links() }}</div>
        @endif
    </div>

    {{-- Create Cycle Form --}}
    <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Create Review Cycle</h3>
        <form action="{{ route('hr.performance.cycles.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cycle Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. 2025 Annual Performance Review"
                           class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Period Type <span class="text-red-500">*</span></label>
                    <select name="period_type" required class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Select...</option>
                        <option value="annual">Annual</option>
                        <option value="semi_annual">Semi-Annual</option>
                        <option value="quarterly">Quarterly</option>
                    </select>
                </div>
                <div>
                    {{-- spacer --}}
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" required
                           class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date <span class="text-red-500">*</span></label>
                    <input type="date" name="end_date" required
                           class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn-primary px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Create Cycle</button>
            </div>
        </form>
    </div>

</div>
@endsection
