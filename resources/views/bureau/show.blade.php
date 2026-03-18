@extends('layouts.app')

@section('title', 'Bureau Report')

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <a href="{{ route('bureau.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Bureau Report — {{ $bureauReport->reference }}</h1>
    </div>

    <div class="card p-6 grid grid-cols-2 gap-6">
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
                <dt class="text-gray-500">Customer</dt>
                <dd class="font-medium">{{ $bureauReport->customer?->full_name ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Bureau</dt>
                <dd class="font-semibold uppercase">{{ $bureauReport->bureau }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Status</dt>
                <dd>
                    <span class="text-xs px-2 py-1 rounded font-medium
                        {{ $bureauReport->status === 'retrieved' ? 'bg-green-100 text-green-800' : ($bureauReport->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ ucfirst($bureauReport->status) }}
                    </span>
                </dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Linked Loan</dt>
                <dd>{{ $bureauReport->loan?->loan_account_number ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Retrieved At</dt>
                <dd>{{ $bureauReport->retrieved_at?->format('d M Y H:i') ?? '—' }}</dd>
            </div>
        </dl>

        <div class="flex flex-col items-center justify-center border rounded-lg p-6 text-center">
            @if($bureauReport->credit_score)
            <p class="text-xs text-gray-500 uppercase font-medium mb-2">Credit Score</p>
            <p class="text-5xl font-extrabold {{ $bureauReport->credit_score >= 700 ? 'text-green-600' : ($bureauReport->credit_score >= 600 ? 'text-yellow-600' : 'text-red-600') }}">
                {{ $bureauReport->credit_score }}
            </p>
            <p class="text-xs text-gray-500 mt-1">
                {{ $bureauReport->credit_score >= 700 ? 'Good' : ($bureauReport->credit_score >= 600 ? 'Fair' : 'Poor') }}
            </p>
            @else
            <p class="text-gray-400">Score unavailable</p>
            @endif
        </div>
    </div>

    @if($bureauReport->status === 'retrieved')
    <div class="card p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Summary</h3>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div class="border rounded-lg p-4">
                <p class="text-2xl font-bold text-gray-900">{{ $bureauReport->active_loans_count }}</p>
                <p class="text-xs text-gray-500 mt-1">Active Loans</p>
            </div>
            <div class="border rounded-lg p-4">
                <p class="text-2xl font-bold text-gray-900">₦{{ number_format($bureauReport->total_outstanding, 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Outstanding</p>
            </div>
            <div class="border rounded-lg p-4">
                <p class="text-2xl font-bold {{ $bureauReport->delinquency_count > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $bureauReport->delinquency_count }}
                </p>
                <p class="text-xs text-gray-500 mt-1">Delinquencies</p>
            </div>
        </div>
    </div>
    @endif

    @if($bureauReport->raw_response)
    <div class="card p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Raw Response</h3>
        <pre class="bg-gray-50 rounded p-4 text-xs overflow-auto max-h-64">{{ json_encode($bureauReport->raw_response, JSON_PRETTY_PRINT) }}</pre>
    </div>
    @endif
</div>
@endsection
