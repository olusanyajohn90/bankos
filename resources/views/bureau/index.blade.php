@extends('layouts.app')

@section('title', 'Credit Bureau Reports')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Credit Bureau</h1>
            <p class="text-sm text-gray-500 mt-1">CRC, XDS, FirstCentral bureau enquiries</p>
        </div>
        <a href="{{ route('bureau.upload') }}" class="btn btn-primary flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Upload PDF Report
        </a>
    </div>

    {{-- New Query Form --}}
    <div class="card p-6" x-data="{}">
        <h3 class="font-semibold text-gray-900 mb-4">New Bureau Query</h3>
        <form action="{{ route('bureau.query') }}" method="POST" class="flex flex-wrap gap-4 items-end">
            @csrf
            <div class="flex-1 min-w-[200px]">
                <label class="form-label">Customer <span class="text-red-500">*</span></label>
                <select name="customer_id" class="form-input w-full" required>
                    <option value="">— Select Customer —</option>
                    @foreach(\App\Models\Customer::orderBy('first_name')->get() as $c)
                        <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->phone }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Bureau <span class="text-red-500">*</span></label>
                <select name="bureau" class="form-input" required>
                    <option value="crc">CRC</option>
                    <option value="xds">XDS</option>
                    <option value="firstcentral">FirstCentral</option>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="form-label">Link to Loan (optional)</label>
                <select name="loan_id" class="form-input w-full">
                    <option value="">— None —</option>
                    @foreach(\App\Models\Loan::where('status', 'pending')->with('customer')->get() as $l)
                        <option value="{{ $l->id }}">{{ $l->loan_account_number }} — {{ $l->customer?->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Run Query</button>
        </form>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card p-4 flex flex-wrap gap-4">
        <div>
            <select name="bureau" class="form-input">
                <option value="">All Bureaus</option>
                <option value="crc" @selected(request('bureau') === 'crc')>CRC</option>
                <option value="xds" @selected(request('bureau') === 'xds')>XDS</option>
                <option value="firstcentral" @selected(request('bureau') === 'firstcentral')>FirstCentral</option>
            </select>
        </div>
        <div>
            <select name="status" class="form-input">
                <option value="">All Statuses</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="retrieved" @selected(request('status') === 'retrieved')>Retrieved</option>
                <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                <option value="uploaded" @selected(request('status') === 'uploaded')>Uploaded</option>
                <option value="parsed" @selected(request('status') === 'parsed')>Parsed</option>
            </select>
        </div>
        <button class="btn btn-secondary">Filter</button>
        <a href="{{ route('bureau.index') }}" class="btn btn-secondary">Clear</a>
    </form>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bureau</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Score</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Active Loans</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($reports as $report)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-mono text-xs text-gray-600">{{ $report->reference }}</td>
                    <td class="px-6 py-4 text-sm font-medium">{{ $report->customer?->full_name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm uppercase font-semibold">{{ $report->bureau }}</td>
                    <td class="px-6 py-4 text-right text-sm font-bold {{ $report->credit_score >= 650 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $report->credit_score ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">{{ $report->active_loans_count }}</td>
                    <td class="px-6 py-4 text-right font-mono text-sm">₦{{ number_format($report->total_outstanding, 0) }}</td>
                    <td class="px-6 py-4">
                        <span class="text-xs px-2 py-1 rounded font-medium
                            {{ $report->status === 'retrieved' ? 'bg-green-100 text-green-800' : ($report->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($report->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $report->created_at->format('d M Y') }}</td>
                    <td class="px-6 py-4 text-right space-x-3">
                        @if($report->status === 'parsed')
                            <a href="{{ route('bureau.analytics', $report) }}" class="text-blue-600 hover:underline text-sm font-semibold">Analytics</a>
                        @endif
                        <a href="{{ route('bureau.show', $report) }}" class="text-gray-500 hover:underline text-sm">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-6 py-12 text-center text-gray-400">No bureau reports yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">{{ $reports->links() }}</div>
    </div>
</div>
@endsection
