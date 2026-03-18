@extends('layouts.app')
@section('title', 'Procurement')
@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Procurement Requests</h1>
            <p class="text-sm text-gray-500 mt-0.5">Asset purchases with approval workflow</p>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ New Request</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>@endif

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="card p-4 text-center"><p class="text-xl font-bold text-amber-500">{{ $pending }}</p><p class="text-xs text-gray-500">Pending Approval</p></div>
        <div class="card p-4 text-center"><p class="text-xl font-bold text-green-600">{{ $approved }}</p><p class="text-xs text-gray-500">Approved</p></div>
        <div class="card p-4 text-center"><p class="text-xl font-bold text-purple-600">₦{{ number_format($totalSpend) }}</p><p class="text-xs text-gray-500">Total Received</p></div>
    </div>

    {{-- New request form --}}
    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">New Procurement Request</h2>
        <form action="{{ route('assets.procurement.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div class="md:col-span-2"><label class="block text-xs text-gray-500 mb-1">Item Name *</label>
                <input type="text" name="item_name" required class="form-input w-full text-sm" placeholder="e.g. HP LaserJet Printer"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Category</label>
                <select name="category_id" class="form-input w-full text-sm">
                    <option value="">—</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Quantity *</label>
                <input type="number" name="quantity" required value="1" min="1" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Unit Price (₦)</label>
                <input type="number" name="unit_price" class="form-input w-full text-sm" min="0" step="1000"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Vendor</label>
                <input type="text" name="vendor_name" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Urgency</label>
                <select name="urgency" class="form-input w-full text-sm">
                    <option value="normal">Normal</option>
                    <option value="urgent">Urgent</option>
                    <option value="critical">Critical</option>
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Required By</label>
                <input type="date" name="required_by_date" class="form-input w-full text-sm"></div>
            <div class="md:col-span-3"><label class="block text-xs text-gray-500 mb-1">Justification *</label>
                <textarea name="justification" rows="2" required class="form-input w-full text-sm resize-none" placeholder="Why is this needed?"></textarea></div>
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
            @foreach(['draft','pending','approved','rejected','ordered','received','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="urgency" class="form-input text-sm">
            <option value="">All Urgencies</option>
            @foreach(['normal','urgent','critical'] as $u)
                <option value="{{ $u }}" {{ request('urgency') === $u ? 'selected' : '' }}>{{ ucfirst($u) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn text-sm bg-gray-700 text-white px-4 py-2 rounded-lg">Filter</button>
        @if(request()->hasAny(['status','urgency']))
            <a href="{{ route('assets.procurement') }}" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Item</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Qty × Price</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Urgency</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requested By</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($requests as $req)
                    @php
                        $sc = match($req->status) {
                            'draft'    => 'bg-gray-100 text-gray-500',
                            'pending'  => 'bg-amber-100 text-amber-700',
                            'approved' => 'bg-green-100 text-green-700',
                            'rejected' => 'bg-red-100 text-red-600',
                            'ordered'  => 'bg-blue-100 text-blue-700',
                            'received' => 'bg-purple-100 text-purple-700',
                            default    => 'bg-gray-100 text-gray-400',
                        };
                        $uc = match($req->urgency) {
                            'urgent'   => 'bg-amber-100 text-amber-700',
                            'critical' => 'bg-red-100 text-red-700',
                            default    => 'bg-gray-100 text-gray-500',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $req->item_name }}</p>
                            <p class="text-xs text-gray-400">{{ $req->vendor_name }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $req->quantity }}×
                            {{ $req->unit_price ? '₦' . number_format($req->unit_price) : '—' }}
                            @if($req->total_amount)<span class="font-semibold"> = ₦{{ number_format($req->total_amount) }}</span>@endif
                        </td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $uc }}">{{ ucfirst($req->urgency) }}</span></td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $req->requestedBy?->name }}<br>{{ $req->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $sc }}">{{ ucfirst($req->status) }}</span></td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($req->status === 'draft' && $req->requested_by === auth()->id())
                                    <form action="{{ route('assets.procurement.submit', $req) }}" method="POST" onsubmit="return confirm('Submit for approval?')">
                                        @csrf
                                        <button class="text-xs text-blue-600 hover:text-blue-800 font-medium">Submit</button>
                                    </form>
                                @endif
                                @if($req->status === 'approved')
                                    <form action="{{ route('assets.procurement.received', $req) }}" method="POST" onsubmit="return confirm('Mark as received?')">
                                        @csrf
                                        <button class="text-xs text-green-600 hover:text-green-800 font-medium">Mark Received</button>
                                    </form>
                                @endif
                                @if($req->approval_request_id)
                                    <a href="{{ route('hr.approvals.requests.show', $req->approval_request_id) }}"
                                       class="text-xs text-purple-600 hover:underline">Approval →</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($requests->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $requests->links() }}</div>
        @endif
    </div>

</div>
@endsection
