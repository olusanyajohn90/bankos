@extends('layouts.app')

@section('title', $declaration->title)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('cooperative.dividends.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Dividends
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $declaration->title }}</h1>
            <p class="text-sm text-gray-500 mt-1">Financial Year {{ $declaration->financial_year }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($declaration->status === 'draft')
                <form action="{{ route('cooperative.dividends.approve', $declaration->id) }}" method="POST" class="inline"
                      onsubmit="return confirm('Approve this declaration?')">
                    @csrf
                    <button type="submit" class="btn btn-primary">Approve</button>
                </form>
                <form action="{{ route('cooperative.dividends.cancel', $declaration->id) }}" method="POST" class="inline"
                      onsubmit="return confirm('Cancel this declaration?')">
                    @csrf
                    <button type="submit" class="btn btn-secondary text-red-600">Cancel</button>
                </form>
            @elseif($declaration->status === 'approved')
                <form action="{{ route('cooperative.dividends.process', $declaration->id) }}" method="POST" class="inline"
                      onsubmit="return confirm('Process all payouts? This will credit member accounts and cannot be undone.')">
                    @csrf
                    <button type="submit" class="btn btn-primary bg-green-600 hover:bg-green-700">Process Payouts</button>
                </form>
                <form action="{{ route('cooperative.dividends.cancel', $declaration->id) }}" method="POST" class="inline"
                      onsubmit="return confirm('Cancel this declaration?')">
                    @csrf
                    <button type="submit" class="btn btn-secondary text-red-600">Cancel</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
    @endif

    {{-- Declaration Details --}}
    @php
        $statusColors = [
            'draft'      => 'bg-gray-100 text-gray-800',
            'approved'   => 'bg-blue-100 text-blue-800',
            'processing' => 'bg-yellow-100 text-yellow-800',
            'completed'  => 'bg-green-100 text-green-800',
            'cancelled'  => 'bg-red-100 text-red-800',
        ];
        $color = $statusColors[$declaration->status] ?? 'bg-gray-100 text-gray-800';
    @endphp

    <div class="card p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <p class="text-xs text-gray-500 uppercase font-medium">Status</p>
                <span class="inline-block mt-1 text-xs px-3 py-1 rounded font-medium {{ $color }}">{{ ucfirst($declaration->status) }}</span>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-medium">Total Surplus</p>
                <p class="text-lg font-bold text-gray-900 mt-1">{{ number_format($declaration->total_surplus, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-medium">Dividend Rate</p>
                <p class="text-lg font-bold text-gray-900 mt-1">{{ number_format($declaration->dividend_rate, 2) }}%</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-medium">Total Distributed</p>
                <p class="text-lg font-bold text-green-600 mt-1">{{ number_format($declaration->total_distributed, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-medium">Eligible Members</p>
                <p class="text-lg font-bold text-gray-900 mt-1">{{ number_format($declaration->eligible_members) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-medium">Declaration Date</p>
                <p class="text-sm text-gray-900 mt-1">{{ \Carbon\Carbon::parse($declaration->declaration_date)->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-medium">Payment Date</p>
                <p class="text-sm text-gray-900 mt-1">{{ $declaration->payment_date ? \Carbon\Carbon::parse($declaration->payment_date)->format('d M Y') : '---' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-medium">Notes</p>
                <p class="text-sm text-gray-600 mt-1">{{ $declaration->notes ?? '---' }}</p>
            </div>
        </div>
    </div>

    {{-- Preview (for draft/approved) --}}
    @if($preview->isNotEmpty())
    <div class="card overflow-hidden">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-100">
            <h2 class="font-semibold text-blue-800">Payout Preview</h2>
            <p class="text-xs text-blue-600 mt-0.5">Estimated distribution based on current active shareholdings ({{ $preview->count() }} member(s), total: {{ number_format($preview->sum('amount'), 2) }})</p>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer ID</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Shares Held</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Par Value</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dividend Amount</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($preview as $p)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-mono text-xs text-gray-600">{{ Str::limit($p->customer_id, 12) }}</td>
                    <td class="px-6 py-3 text-right font-mono text-sm">{{ number_format($p->total_shares) }}</td>
                    <td class="px-6 py-3 text-right font-mono text-sm">{{ number_format($p->par_value, 2) }}</td>
                    <td class="px-6 py-3 text-right font-mono text-sm font-semibold text-green-600">{{ number_format($p->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td class="px-6 py-3 font-semibold text-sm" colspan="3">Total</td>
                    <td class="px-6 py-3 text-right font-mono text-sm font-bold text-green-700">{{ number_format($preview->sum('amount'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- Actual Payouts (after processing) --}}
    @if($payouts->total() > 0)
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h2 class="font-semibold text-gray-900">Member Payouts</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ $payouts->total() }} payout record(s)</p>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer No.</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Shares</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid At</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($payouts as $payout)
                @php
                    $payoutColor = match($payout->status) {
                        'paid'   => 'bg-green-100 text-green-800',
                        'failed' => 'bg-red-100 text-red-800',
                        default  => 'bg-gray-100 text-gray-800',
                    };
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $payout->first_name }} {{ $payout->last_name }}</td>
                    <td class="px-6 py-3 font-mono text-xs text-gray-600">{{ $payout->customer_number }}</td>
                    <td class="px-6 py-3 text-right font-mono text-sm">{{ number_format($payout->shares_held) }}</td>
                    <td class="px-6 py-3 text-right font-mono text-sm font-semibold text-green-600">{{ number_format($payout->amount, 2) }}</td>
                    <td class="px-6 py-3">
                        <span class="text-xs px-2 py-1 rounded font-medium {{ $payoutColor }}">{{ ucfirst($payout->status) }}</span>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $payout->paid_at ? \Carbon\Carbon::parse($payout->paid_at)->format('d M Y H:i') : '---' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($payouts->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $payouts->links() }}
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
