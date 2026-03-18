@extends('layouts.app')
@section('title', 'Leads')
@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ showNew: false }">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <h1 class="text-2xl font-bold text-gray-900">Leads</h1>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ New Lead</button>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- New Lead Form --}}
    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">New Lead</h2>
        <form action="{{ route('crm.leads.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div><label class="block text-xs text-gray-500 mb-1">Title *</label>
                <input type="text" name="title" required class="form-input w-full text-sm" placeholder="e.g. SME Loan Inquiry — Mama Put"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Contact Name *</label>
                <input type="text" name="contact_name" required class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Phone</label>
                <input type="text" name="contact_phone" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Email</label>
                <input type="email" name="contact_email" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Company</label>
                <input type="text" name="company" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Product Interest</label>
                <select name="product_interest" class="form-input w-full text-sm">
                    <option value="">—</option>
                    @foreach(['Savings Account','Current Account','Fixed Deposit','Personal Loan','Business Loan','SME Loan','Agric Loan','Mortgage','Investment','Other'] as $p)
                        <option>{{ $p }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Source</label>
                <select name="source" class="form-input w-full text-sm">
                    <option value="">—</option>
                    @foreach(['Walk-in','Referral','Social Media','Cold Call','Agency','Online','Event','Staff'] as $s)
                        <option>{{ $s }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Est. Value (₦)</label>
                <input type="number" name="estimated_value" class="form-input w-full text-sm" min="0" step="1000"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Stage</label>
                <select name="stage_id" class="form-input w-full text-sm">
                    <option value="">Default (first)</option>
                    @foreach($stages as $st)
                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Assign To</label>
                <select name="assigned_to" class="form-input w-full text-sm">
                    <option value="">—</option>
                    @foreach($agents as $a)
                        <option value="{{ $a->id }}" {{ $a->id == auth()->id() ? 'selected' : '' }}>{{ $a->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Expected Close</label>
                <input type="date" name="expected_close_date" class="form-input w-full text-sm"></div>
            <div class="md:col-span-3">
                <label class="block text-xs text-gray-500 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="form-input w-full text-sm resize-none"></textarea>
            </div>
            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create Lead</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-100 hover:bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, company, phone…" class="form-input text-sm flex-1 min-w-[200px]">
        <select name="status" class="form-input text-sm">
            <option value="">All Statuses</option>
            @foreach(['new','in_progress','converted','lost','on_hold'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="stage_id" class="form-input text-sm">
            <option value="">All Stages</option>
            @foreach($stages as $st)
                <option value="{{ $st->id }}" {{ request('stage_id') === $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn text-sm bg-gray-700 text-white px-4 py-2 rounded-lg">Filter</button>
        @if(request()->hasAny(['search','status','stage_id']))
            <a href="{{ route('crm.leads') }}" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Clear</a>
        @endif
    </form>

    {{-- Leads table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Lead</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stage</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Value</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Assigned</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($leads as $lead)
                    @php
                        $statusColor = match($lead->status) {
                            'new'         => 'bg-blue-100 text-blue-700',
                            'in_progress' => 'bg-amber-100 text-amber-700',
                            'converted'   => 'bg-green-100 text-green-700',
                            'lost'        => 'bg-red-100 text-red-600',
                            'on_hold'     => 'bg-gray-100 text-gray-500',
                            default       => 'bg-gray-100 text-gray-400',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('crm.leads.show', $lead) }}" class="font-medium text-blue-700 hover:underline">{{ $lead->title }}</a>
                            <p class="text-xs text-gray-400">{{ $lead->contact_name }} {{ $lead->company ? '· ' . $lead->company : '' }}</p>
                            @if($lead->contact_phone)<p class="text-xs text-gray-400">{{ $lead->contact_phone }}</p>@endif
                        </td>
                        <td class="px-4 py-3">
                            @if($lead->stage)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium" style="background: {{ $lead->stage->color }}20; color: {{ $lead->stage->color }}">{{ $lead->stage->name }}</span>
                            @else <span class="text-gray-400">—</span>@endif
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $lead->estimated_value ? '₦' . number_format($lead->estimated_value) : '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $lead->assignedTo?->name ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $statusColor }}">{{ ucwords(str_replace('_',' ',$lead->status)) }}</span></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('crm.leads.show', $lead) }}" class="text-xs text-blue-600 hover:underline">View →</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">No leads found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($leads->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $leads->links() }}</div>
        @endif
    </div>

</div>
@endsection
