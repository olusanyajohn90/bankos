@extends('layouts.app')

@section('title', 'Smart Collections')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Smart Collections</h1>
        <p class="text-sm text-gray-500 mt-1">Overdue loans ranked by risk score — prioritise collection effort</p>
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan Account</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">DPD</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Risk Score</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($overdueLoans as $loan)
                @php
                    $score = $loan->overdue_score;
                    $scoreClass = $score >= 70 ? 'bg-red-100 text-red-800' : ($score >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800');
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $loan->customer?->full_name ?? '—' }}</td>
                    <td class="px-6 py-4 font-mono text-xs text-gray-600">{{ $loan->loan_account_number }}</td>
                    <td class="px-6 py-4 text-right font-mono text-sm font-semibold text-red-600">₦{{ number_format($loan->outstanding_balance, 2) }}</td>
                    <td class="px-6 py-4 text-right text-sm font-bold text-red-600">{{ $loan->days_past_due }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-xs px-3 py-1 rounded-full font-bold {{ $scoreClass }}">{{ $score }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs px-2 py-1 rounded font-medium
                            {{ $loan->status === 'overdue' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($loan->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('collections.show', $loan) }}" class="btn btn-secondary text-xs">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">No overdue loans. Great job!</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t text-sm text-gray-500">
            Showing {{ ($page - 1) * $perPage + 1 }}–{{ min($page * $perPage, $total) }} of {{ $total }} loans
        </div>
    </div>
</div>
@endsection
