@extends('layouts.app')

@section('title', 'Collection — ' . $loan->loan_account_number)

@section('content')
<div class="space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <a href="{{ route('collections.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Collections
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $loan->customer?->full_name ?? '—' }}</h1>
            <p class="text-sm text-gray-500 font-mono">{{ $loan->loan_account_number }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 bg-red-50 border border-red-200">
            <p class="text-xs text-red-600 uppercase font-medium">Days Past Due</p>
            <p class="text-3xl font-extrabold text-red-700 mt-1">{{ $loan->days_past_due }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Outstanding</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">₦{{ number_format($loan->outstanding_balance, 2) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Risk Score</p>
            <p class="text-2xl font-bold mt-1 {{ $loan->overdue_score >= 70 ? 'text-red-600' : ($loan->overdue_score >= 40 ? 'text-yellow-600' : 'text-green-600') }}">
                {{ $loan->overdue_score }} / 100
            </p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Status</p>
            <p class="mt-1">
                <span class="text-xs px-2 py-1 rounded font-medium bg-red-100 text-red-800">{{ ucfirst($loan->status) }}</span>
            </p>
        </div>
    </div>

    {{-- Log action --}}
    <div class="card p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Log Collection Action</h3>
        <form action="{{ route('collections.log', $loan) }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Action <span class="text-red-500">*</span></label>
                    <select name="action" class="form-input w-full" required>
                        <option value="call">Phone Call</option>
                        <option value="sms">SMS</option>
                        <option value="visit">Field Visit</option>
                        <option value="demand_letter">Demand Letter</option>
                        <option value="legal">Legal Action</option>
                        <option value="restructure">Restructure</option>
                        <option value="write_off">Write Off</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Outcome <span class="text-red-500">*</span></label>
                    <select name="outcome" class="form-input w-full" required>
                        <option value="contacted">Contacted</option>
                        <option value="promised_to_pay">Promised to Pay</option>
                        <option value="paid">Paid</option>
                        <option value="unreachable">Unreachable</option>
                        <option value="disputed">Disputed</option>
                        <option value="escalated">Escalated</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Promise Amount (₦)</label>
                    <input type="number" name="promise_amount" class="form-input w-full" step="100" min="0">
                </div>
                <div>
                    <label class="form-label">Promise Date</label>
                    <input type="date" name="promise_date" class="form-input w-full">
                </div>
            </div>
            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-input w-full" rows="2" placeholder="What happened?"></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">Log Action</button>
            </div>
        </form>
    </div>

    {{-- Action log history --}}
    <div class="card">
        <div class="px-6 py-4 border-b">
            <h3 class="font-semibold text-gray-900">Collection History</h3>
        </div>
        <div class="divide-y">
            @forelse($logs as $log)
            <div class="px-6 py-4">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs px-2 py-1 rounded font-medium bg-gray-200 hover:bg-gray-300 text-gray-800 mr-2">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                        <span class="text-xs px-2 py-1 rounded font-medium
                            {{ $log->outcome === 'paid' ? 'bg-green-100 text-green-700' : ($log->outcome === 'unreachable' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                            {{ ucfirst(str_replace('_', ' ', $log->outcome)) }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $log->actioned_at->format('d M Y H:i') }} — {{ $log->officer?->name ?? 'System' }}
                    </div>
                </div>
                @if($log->notes)
                <p class="text-sm text-gray-600 mt-2">{{ $log->notes }}</p>
                @endif
                @if($log->promise_amount)
                <p class="text-xs text-gray-500 mt-1">Promise: ₦{{ number_format($log->promise_amount, 0) }} by {{ $log->promise_date?->format('d M Y') }}</p>
                @endif
            </div>
            @empty
            <div class="px-6 py-8 text-center text-gray-400">No collection actions logged yet.</div>
            @endforelse
        </div>
        <div class="px-6 py-4 border-t">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
