@extends('layouts.app')

@section('title', 'Disciplinary Cases')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Disciplinary Cases</h1>
        <p class="text-sm text-gray-500 mt-1">Manage staff disciplinary proceedings.</p>
    </div>

    @include('hr.org._tabs')

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Filter Bar --}}
    <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="form-input rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    @foreach (['open','responded','closed','appealed'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="form-input rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Types</option>
                    @foreach (['query','warning','suspension','demotion','termination'] as $t)
                        <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Filter</button>
            @if (request()->hasAny(['status', 'type']))
                <a href="{{ route('hr.disciplinary.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">Clear</a>
            @endif
        </form>
    </div>

    {{-- Cases Table --}}
    <div class="card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Case No.</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Incident Date</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Raised By</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($cases as $case)
                        @php
                            $typeBadge = match($case->type) {
                                'query'       => 'bg-yellow-100 text-yellow-800',
                                'warning'     => 'bg-orange-100 text-orange-800',
                                'suspension'  => 'bg-red-100 text-red-700',
                                'demotion'    => 'bg-purple-100 text-purple-800',
                                'termination' => 'bg-red-200 text-red-900',
                                default       => 'bg-gray-100 text-gray-600',
                            };
                            $statusBadge = match($case->status) {
                                'open'      => 'bg-blue-100 text-blue-800',
                                'responded' => 'bg-yellow-100 text-yellow-800',
                                'closed'    => 'bg-green-100 text-green-800',
                                'appealed'  => 'bg-purple-100 text-purple-800',
                                default     => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs font-medium text-gray-800">{{ $case->case_number }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ optional($case->staffProfile->user)->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeBadge }}">
                                    {{ ucfirst($case->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($case->incident_date)->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ optional($case->raisedBy)->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge }}">
                                    {{ ucfirst($case->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('hr.disciplinary.show', $case) }}"
                                   class="text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400 text-sm">No disciplinary cases found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($cases->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $cases->links() }}</div>
        @endif
    </div>

    {{-- Open New Case --}}
    <div x-data="{ open: false }">
        <button @click="open = !open"
                class="flex items-center gap-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg px-4 py-2.5 hover:bg-gray-50 shadow-sm mb-3">
            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            Open New Disciplinary Case
        </button>

        <div x-show="open" x-transition class="card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">New Disciplinary Case</h3>
            <form action="{{ route('hr.disciplinary.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Staff Member <span class="text-red-500">*</span></label>
                        <select name="staff_profile_id" required class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Select staff...</option>
                            @foreach ($staff as $s)
                                <option value="{{ $s->id }}">{{ optional($s->user)->name }} ({{ $s->employee_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Case Type <span class="text-red-500">*</span></label>
                        <select name="type" required class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Select type...</option>
                            <option value="query">Query</option>
                            <option value="warning">Warning</option>
                            <option value="suspension">Suspension</option>
                            <option value="demotion">Demotion</option>
                            <option value="termination">Termination</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Incident Date <span class="text-red-500">*</span></label>
                        <input type="date" name="incident_date" required class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                        <textarea name="description" rows="4" required placeholder="Describe the incident and alleged misconduct in detail..."
                                  class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn-primary px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Open Case</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
