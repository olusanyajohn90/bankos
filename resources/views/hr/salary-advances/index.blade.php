@extends('layouts.app')
@section('title', 'Salary Advances')
@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ showNew: false, approveId: null, approveOpen: false, rejectId: null, rejectOpen: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Salary Advances</h1>
            <p class="text-sm text-gray-500 mt-0.5">Request and manage salary advance payments</p>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Request Advance</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>@endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 text-center"><p class="text-xl font-bold text-amber-500">{{ $stats['pending'] }}</p><p class="text-xs text-gray-500">Pending</p></div>
        <div class="card p-4 text-center"><p class="text-xl font-bold text-green-600">{{ $stats['approved'] }}</p><p class="text-xs text-gray-500">Approved</p></div>
        <div class="card p-4 text-center"><p class="text-xl font-bold text-blue-600">{{ $stats['disbursed'] }}</p><p class="text-xs text-gray-500">Disbursed</p></div>
        <div class="card p-4 text-center"><p class="text-xl font-bold text-red-600">₦{{ number_format($stats['total_outstanding']) }}</p><p class="text-xs text-gray-500">Outstanding Balance</p></div>
    </div>

    {{-- Request Form --}}
    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">New Salary Advance Request</h2>
        <form action="{{ route('hr.salary-advances.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div><label class="block text-xs text-gray-500 mb-1">Amount Requested (₦) *</label>
                <input type="number" name="amount_requested" required min="1000" step="1000" class="form-input w-full text-sm" placeholder="e.g. 50000"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Repayment Period *</label>
                <select name="repayment_months" required class="form-input w-full text-sm">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}">{{ $m }} month{{ $m > 1 ? 's' : '' }}</option>
                    @endforeach
                </select></div>
            <div class="md:col-span-3"><label class="block text-xs text-gray-500 mb-1">Reason *</label>
                <textarea name="reason" rows="2" required class="form-input w-full text-sm resize-none" placeholder="Briefly explain why you need this advance…"></textarea></div>
            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Submit Request</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Staff</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Requested</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Approved</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Repayment</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Outstanding</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($advances as $adv)
                    @php
                        $sc = match($adv->status) {
                            'pending'   => 'bg-amber-100 text-amber-700',
                            'approved'  => 'bg-green-100 text-green-700',
                            'rejected'  => 'bg-red-100 text-red-600',
                            'disbursed' => 'bg-blue-100 text-blue-700',
                            'repaid'    => 'bg-gray-100 text-gray-500',
                            default     => 'bg-gray-100 text-gray-400',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $adv->user?->name }}</p>
                            <p class="text-xs text-gray-400 truncate max-w-xs">{{ $adv->reason }}</p>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">₦{{ number_format($adv->amount_requested, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ $adv->amount_approved ? '₦' . number_format($adv->amount_approved, 2) : '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-600 text-xs">
                            {{ $adv->repayment_months }} mo.<br>
                            @if($adv->monthly_deduction)<span class="font-medium">₦{{ number_format($adv->monthly_deduction, 0) }}/mo</span>@endif
                        </td>
                        <td class="px-4 py-3 text-right {{ $adv->balance_remaining > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                            {{ $adv->balance_remaining > 0 ? '₦' . number_format($adv->balance_remaining, 2) : '—' }}
                        </td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $sc }}">{{ ucfirst($adv->status) }}</span></td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($adv->status === 'pending' && $isAdmin)
                                    <button @click="approveId='{{ $adv->id }}'; approveOpen=true"
                                            class="text-xs text-green-600 hover:text-green-800 font-medium">Approve</button>
                                    <button @click="rejectId='{{ $adv->id }}'; rejectOpen=true"
                                            class="text-xs text-red-500 hover:text-red-700 font-medium">Reject</button>
                                @endif
                                @if($adv->status === 'approved' && $isAdmin)
                                    <form action="{{ route('hr.salary-advances.disburse', $adv) }}" method="POST" onsubmit="return confirm('Mark as disbursed?')">
                                        @csrf
                                        <button class="text-xs text-blue-600 hover:text-blue-800 font-medium">Disburse</button>
                                    </form>
                                @endif
                                @if($adv->rejection_reason)
                                    <span class="text-xs text-gray-400 italic" title="{{ $adv->rejection_reason }}">Reason ⓘ</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No salary advance requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($advances->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $advances->links() }}</div>
        @endif
    </div>

    {{-- Approve Modal --}}
    <div x-show="approveOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm">
            <h3 class="font-semibold text-gray-800 mb-4">Approve Salary Advance</h3>
            <form :action="'/hr/salary-advances/' + approveId + '/approve'" method="POST">
                @csrf
                <label class="block text-xs text-gray-500 mb-1">Amount to Approve (₦) *</label>
                <input type="number" name="amount_approved" required min="1" step="1000" class="form-input w-full text-sm mb-4">
                <div class="flex gap-2 justify-end">
                    <button type="button" @click="approveOpen=false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
                    <button type="submit" class="btn text-sm bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">Approve</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div x-show="rejectOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm">
            <h3 class="font-semibold text-gray-800 mb-4">Reject Advance Request</h3>
            <form :action="'/hr/salary-advances/' + rejectId + '/reject'" method="POST">
                @csrf
                <textarea name="reason" rows="3" required class="form-input w-full text-sm resize-none mb-4" placeholder="Reason for rejection…"></textarea>
                <div class="flex gap-2 justify-end">
                    <button type="button" @click="rejectOpen=false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
                    <button type="submit" class="btn text-sm bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">Reject</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
