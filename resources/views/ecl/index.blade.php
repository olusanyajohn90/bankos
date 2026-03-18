@extends('layouts.app')

@section('title', 'IFRS 9 ECL Provisions')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">IFRS 9 — ECL Provisions</h1>
            <p class="text-sm text-gray-500 mt-1">Expected Credit Loss computation by stage (IFRS 9 compliant)</p>
        </div>
        @can('run ecl')
        <form action="{{ route('ecl.run') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary" onclick="return confirm('Run ECL computation for all active loans as at today?')">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Run ECL
            </button>
        </form>
        @endcan
    </div>

    {{-- Date filter --}}
    <form method="GET" class="card p-4 flex gap-4 items-center">
        <label class="form-label mb-0">Reporting Date</label>
        <input type="date" name="date" value="{{ $reportingDate }}" class="form-input">
        <button class="btn btn-secondary">Load</button>
    </form>

    {{-- Summary by Stage --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach([1 => ['Stage 1 — Performing', 'bg-green-50 border-green-200 text-green-700'], 2 => ['Stage 2 — Underperforming', 'bg-yellow-50 border-yellow-200 text-yellow-700'], 3 => ['Stage 3 — Non-Performing', 'bg-red-50 border-red-200 text-red-700']] as $stage => [$label, $classes])
        <div class="card p-5 border {{ $classes }}">
            <p class="text-xs font-semibold uppercase">{{ $label }}</p>
            <p class="text-2xl font-bold mt-1">{{ $summary[$stage]?->loan_count ?? 0 }} loans</p>
            <p class="text-sm mt-1">ECL: ₦{{ number_format($summary[$stage]?->total_ecl ?? 0, 2) }}</p>
            <p class="text-xs mt-1 opacity-75">Exposure: ₦{{ number_format($summary[$stage]?->total_exposure ?? 0, 0) }}</p>
        </div>
        @endforeach
    </div>

    <div class="card p-4 bg-blue-50 border border-blue-200 flex items-center justify-between">
        <span class="font-semibold text-blue-900">Total ECL Provision as at {{ \Carbon\Carbon::parse($reportingDate)->format('d M Y') }}</span>
        <span class="text-2xl font-bold text-blue-700">₦{{ number_format($totalEcl, 2) }}</span>
    </div>

    {{-- Detail table --}}
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan Account</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stage</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">DPD</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Outstanding (EAD)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">PD</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">LGD</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ECL</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($provisions as $prov)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $prov->customer?->full_name ?? '—' }}</td>
                    <td class="px-6 py-4 font-mono text-xs text-gray-600">{{ $prov->loan?->loan_account_number ?? '—' }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-xs px-2 py-1 rounded font-medium
                            {{ $prov->stage === 1 ? 'bg-green-100 text-green-800' : ($prov->stage === 2 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            Stage {{ $prov->stage }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm {{ $prov->days_past_due > 0 ? 'text-red-600 font-semibold' : '' }}">
                        {{ $prov->days_past_due }}
                    </td>
                    <td class="px-6 py-4 text-right font-mono text-sm">₦{{ number_format($prov->outstanding_balance, 2) }}</td>
                    <td class="px-6 py-4 text-right text-sm">{{ number_format($prov->probability_of_default * 100, 1) }}%</td>
                    <td class="px-6 py-4 text-right text-sm">{{ number_format($prov->loss_given_default * 100, 1) }}%</td>
                    <td class="px-6 py-4 text-right font-mono text-sm font-semibold text-red-700">₦{{ number_format($prov->ecl_amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                        No ECL provisions for this date.
                        <form action="{{ route('ecl.run') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-blue-600 hover:underline bg-transparent border-0 p-0 cursor-pointer"
                                onclick="return confirm('Run ECL computation for all active loans as at today?')">Run ECL now</button>
                        </form>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">{{ $provisions->links() }}</div>
    </div>
</div>
@endsection
