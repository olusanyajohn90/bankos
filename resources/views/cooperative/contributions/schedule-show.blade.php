@extends('layouts.app')

@section('title', $schedule->name)

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('cooperative.contributions.index') }}" class="text-gray-400 hover:text-blue-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $schedule->name }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    &#8358;{{ number_format($schedule->amount, 2) }} &middot; {{ ucfirst(str_replace('_', ' ', $schedule->frequency)) }}
                    &middot; Period: <span class="font-semibold">{{ $currentPeriod }}</span>
                </p>
            </div>
        </div>
        <a href="{{ route('cooperative.contributions.collect') }}" class="btn btn-primary flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            Record Payment
        </a>
    </div>

    {{-- Summary --}}
    @php
        $paidCount = $payments->count();
        $unpaidCount = $members->count() - $paidCount;
        $totalCollected = $payments->sum('amount');
        $expected = $members->count() * $schedule->amount;
        $rate = $members->count() > 0 ? round(($paidCount / $members->count()) * 100, 1) : 0;
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Paid</p>
            <p class="text-2xl font-bold text-green-600 mt-2">{{ $paidCount }}</p>
            <p class="text-xs text-gray-400 mt-1">members</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unpaid</p>
            <p class="text-2xl font-bold text-red-600 mt-2">{{ $unpaidCount }}</p>
            <p class="text-xs text-gray-400 mt-1">members</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Collected</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">&#8358;{{ number_format($totalCollected, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">of &#8358;{{ number_format($expected, 2) }} expected</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Compliance</p>
            <p class="text-2xl font-bold {{ $rate >= 80 ? 'text-green-600' : ($rate >= 50 ? 'text-amber-600' : 'text-red-600') }} mt-2">{{ $rate }}%</p>
            <p class="text-xs text-gray-400 mt-1">this period</p>
        </div>
    </div>

    {{-- Member Payment Status --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Member Payment Status &mdash; {{ $currentPeriod }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($members as $member)
                    @php $payment = $payments->get($member->id); @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-6 py-4">
                            <a href="{{ route('cooperative.contributions.member-history', $member->id) }}" class="font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                {{ $member->first_name }} {{ $member->last_name }}
                            </a>
                            <p class="text-xs text-gray-400 font-mono">{{ $member->customer_number ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($payment && $payment->status === 'paid')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Paid</span>
                            @elseif($payment && $payment->status === 'waived')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Waived</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Unpaid</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-sm">
                            @if($payment)
                                &#8358;{{ number_format($payment->amount, 2) }}
                            @else
                                <span class="text-gray-400">&mdash;</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ $payment ? ucfirst($payment->payment_method) : '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ $payment ? \Carbon\Carbon::parse($payment->created_at)->format('d M Y') : '—' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('cooperative.contributions.member-history', $member->id) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm font-medium">History</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
