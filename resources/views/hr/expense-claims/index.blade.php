@extends('layouts.app')
@section('title', 'Expense Claims')
@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ showNew: false, rejectId: null, rejectOpen: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Expense Claims</h1>
            <p class="text-sm text-gray-500 mt-0.5">Submit and track reimbursement requests</p>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ New Claim</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>@endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 text-center"><p class="text-xl font-bold text-amber-500">{{ $stats['pending'] }}</p><p class="text-xs text-gray-500">Pending Review</p></div>
        <div class="card p-4 text-center"><p class="text-xl font-bold text-green-600">{{ $stats['approved'] }}</p><p class="text-xs text-gray-500">Approved</p></div>
        <div class="card p-4 text-center"><p class="text-xl font-bold text-purple-600">{{ $stats['paid'] }}</p><p class="text-xs text-gray-500">Paid</p></div>
        <div class="card p-4 text-center"><p class="text-xl font-bold text-blue-600">₦{{ number_format($stats['total_pending_amount']) }}</p><p class="text-xs text-gray-500">Outstanding Amount</p></div>
    </div>

    {{-- New Claim Form --}}
    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">New Expense Claim</h2>
        <form action="{{ route('hr.expense-claims.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div class="md:col-span-2"><label class="block text-xs text-gray-500 mb-1">Title *</label>
                <input type="text" name="title" required class="form-input w-full text-sm" placeholder="e.g. Lagos client visit – transport"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Category *</label>
                <select name="category" required class="form-input w-full text-sm">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ ucwords(str_replace('_',' ',$cat)) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Expense Date *</label>
                <input type="date" name="expense_date" required class="form-input w-full text-sm" value="{{ now()->toDateString() }}"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Amount (₦) *</label>
                <input type="number" name="amount" required min="1" step="100" class="form-input w-full text-sm"></div>
            <div class="md:col-span-3"><label class="block text-xs text-gray-500 mb-1">Description</label>
                <textarea name="description" rows="2" class="form-input w-full text-sm resize-none" placeholder="Add details or purpose…"></textarea></div>
            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Save Draft</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3">
        <select name="status" class="form-input text-sm">
            <option value="">All Statuses</option>
            @foreach(['draft','submitted','approved','rejected','paid'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="category" class="form-input text-sm">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$cat)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn text-sm bg-gray-700 text-white px-4 py-2 rounded-lg">Filter</button>
        @if(request()->hasAny(['status','category']))
            <a href="{{ route('hr.expense-claims.index') }}" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Claim</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Submitted By</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($claims as $claim)
                    @php
                        $sc = match($claim->status) {
                            'draft'     => 'bg-gray-100 text-gray-500',
                            'submitted' => 'bg-amber-100 text-amber-700',
                            'approved'  => 'bg-green-100 text-green-700',
                            'rejected'  => 'bg-red-100 text-red-600',
                            'paid'      => 'bg-purple-100 text-purple-700',
                            default     => 'bg-gray-100 text-gray-400',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $claim->title }}</p>
                            @if($claim->description)<p class="text-xs text-gray-400 truncate max-w-xs">{{ $claim->description }}</p>@endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ ucwords(str_replace('_',' ',$claim->category)) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $claim->expense_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">₦{{ number_format($claim->amount, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $claim->submittedBy?->name }}<br>{{ $claim->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $sc }}">{{ ucfirst($claim->status) }}</span></td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($claim->status === 'draft' && $claim->submitted_by === auth()->id())
                                    <form action="{{ route('hr.expense-claims.submit', $claim) }}" method="POST" onsubmit="return confirm('Submit this claim for approval?')">
                                        @csrf
                                        <button class="text-xs text-blue-600 hover:text-blue-800 font-medium">Submit</button>
                                    </form>
                                @endif
                                @if($claim->status === 'submitted' && auth()->user()->hasRole(['admin','hr_manager','finance_manager']))
                                    <form action="{{ route('hr.expense-claims.approve', $claim) }}" method="POST" onsubmit="return confirm('Approve this claim?')">
                                        @csrf
                                        <button class="text-xs text-green-600 hover:text-green-800 font-medium">Approve</button>
                                    </form>
                                    <button @click="rejectId='{{ $claim->id }}'; rejectOpen=true" class="text-xs text-red-600 hover:text-red-800 font-medium">Reject</button>
                                @endif
                                @if($claim->status === 'approved' && auth()->user()->hasRole(['admin','finance_manager']))
                                    <form action="{{ route('hr.expense-claims.paid', $claim) }}" method="POST" onsubmit="return confirm('Mark as paid?')">
                                        @csrf
                                        <button class="text-xs text-purple-600 hover:text-purple-800 font-medium">Mark Paid</button>
                                    </form>
                                @endif
                                @if($claim->rejection_reason)
                                    <span class="text-xs text-gray-400 italic" title="{{ $claim->rejection_reason }}">Reason ⓘ</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No expense claims found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($claims->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $claims->links() }}</div>
        @endif
    </div>

    {{-- Reject Modal --}}
    <div x-show="rejectOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md">
            <h3 class="font-semibold text-gray-800 mb-4">Reject Claim</h3>
            <form :action="'/hr/expense-claims/' + rejectId + '/reject'" method="POST">
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
