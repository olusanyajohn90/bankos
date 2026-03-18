@extends('layouts.app')

@section('title', 'Credit Bureau — Customers')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Credit Bureau</h1>
            <p class="text-sm text-gray-500 mt-1">Customers with bureau enquiries on file</p>
        </div>
        <a href="{{ route('bureau.upload') }}" class="btn btn-primary flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Upload PDF Report
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    {{-- Search --}}
    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by name, BVN, or phone…"
               class="form-input flex-1 max-w-sm">
        <button type="submit" class="btn btn-secondary">Search</button>
        @if(request('search'))
            <a href="{{ route('bureau.index') }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>

    {{-- Customer table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Customer</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">BVN</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Reports</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Bureaus</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Latest Score</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Outstanding</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Last Updated</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($query as $customer)
                @php
                    $reports    = $customer->bureauReports;
                    $latest     = $reports->first();
                    $bureauList = $reports->pluck('bureau')->unique()->filter()->values();
                    $latestScore= $reports->whereNotNull('credit_score')->first()?->credit_score;
                    $totalOut   = $reports->sum('total_outstanding');
                    $lastDate   = $reports->max('created_at');
                    $parsedCount= $reports->whereIn('status', ['parsed','retrieved'])->count();
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $customer->full_name }}</div>
                                <div class="text-xs text-gray-400">{{ $customer->phone }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $customer->bvn ?: '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-700">
                            {{ $reports->count() }}
                            @if($parsedCount > 0)
                                <span class="text-xs text-green-600">({{ $parsedCount }} parsed)</span>
                            @endif
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1">
                            @foreach($bureauList as $bureau)
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    {{ $bureau === 'firstcentral' ? 'bg-blue-100 text-blue-700' : ($bureau === 'crc' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ strtoupper($bureau) }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($latestScore)
                            @php
                                $scoreColor = $latestScore >= 700 ? 'text-emerald-600' : ($latestScore >= 600 ? 'text-yellow-600' : 'text-red-600');
                            @endphp
                            <span class="font-bold {{ $scoreColor }}">{{ $latestScore }}</span>
                        @else
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-gray-700">
                        @if($totalOut > 0)
                            ₦{{ number_format($totalOut, 0) }}
                        @else
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-400">
                        {{ $lastDate ? \Carbon\Carbon::parse($lastDate)->format('d M Y') : '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('bureau.customer.reports', $customer) }}"
                               class="text-xs text-indigo-600 hover:text-indigo-800 font-medium whitespace-nowrap">
                                View Reports
                            </a>
                            @if($parsedCount > 0)
                                <a href="{{ route('bureau.customer.internal', $customer) }}"
                                   class="text-xs text-emerald-600 hover:text-emerald-800 font-medium whitespace-nowrap">
                                    Internal Score
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="font-medium">No bureau reports on file</p>
                        <p class="text-sm mt-1">
                            <a href="{{ route('bureau.upload') }}" class="text-indigo-600 hover:underline">Upload a PDF report</a> to get started.
                        </p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($query->hasPages())
        <div>{{ $query->links() }}</div>
    @endif

</div>
@endsection
