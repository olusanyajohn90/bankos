@extends('layouts.app')

@section('title', 'Dividend Declarations')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dividend Declarations</h1>
            <p class="text-sm text-gray-500 mt-1">Distribute surplus/profits to cooperative members based on shareholding</p>
        </div>
        <a href="{{ route('cooperative.dividends.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Declare Dividend
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Declarations</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats->total_declarations) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Distributed</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($stats->total_distributed, 2) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Completed</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats->completed_count) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Drafts</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ number_format($stats->draft_count) }}</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
    @endif

    {{-- Declarations Table --}}
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Financial Year</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Surplus</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Rate (%)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Distributed</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Members</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Declared</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($declarations as $d)
                @php
                    $statusColors = [
                        'draft'      => 'bg-gray-100 text-gray-800',
                        'approved'   => 'bg-blue-100 text-blue-800',
                        'processing' => 'bg-yellow-100 text-yellow-800',
                        'completed'  => 'bg-green-100 text-green-800',
                        'cancelled'  => 'bg-red-100 text-red-800',
                    ];
                    $color = $statusColors[$d->status] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $d->title }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $d->financial_year }}</td>
                    <td class="px-6 py-4 text-right font-mono text-sm">{{ number_format($d->total_surplus, 2) }}</td>
                    <td class="px-6 py-4 text-right font-mono text-sm">{{ number_format($d->dividend_rate, 2) }}%</td>
                    <td class="px-6 py-4 text-right font-mono text-sm font-semibold text-green-600">{{ number_format($d->total_distributed, 2) }}</td>
                    <td class="px-6 py-4 text-center text-sm">{{ $d->eligible_members }}</td>
                    <td class="px-6 py-4">
                        <span class="text-xs px-2 py-1 rounded font-medium {{ $color }}">{{ ucfirst($d->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ \Carbon\Carbon::parse($d->declaration_date)->format('d M Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('cooperative.dividends.show', $d->id) }}" class="btn btn-secondary text-xs">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-gray-400">No dividend declarations yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($declarations->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $declarations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
