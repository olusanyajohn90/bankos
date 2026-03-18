@extends('layouts.app')

@section('title', 'Staff ID Cards')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Staff ID Cards</h1>
            <p class="text-sm text-gray-500 mt-0.5">Issue, manage and track physical ID cards</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('hr.id-cards.templates') }}" class="btn text-sm bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg">Card Templates</a>
            {{-- Bulk generate --}}
            <form action="{{ route('hr.id-cards.bulk') }}" method="POST">
                @csrf
                @if($templates->isNotEmpty())
                    <input type="hidden" name="template_id" value="{{ $templates->firstWhere('is_default', true)?->id ?? $templates->first()->id }}">
                @endif
                <button type="submit"
                    onclick="return confirm('Generate cards for {{ $staffWithoutCard->count() }} staff without active cards?')"
                    class="btn text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
                    @if($staffWithoutCard->isEmpty()) disabled @endif>
                    Bulk Issue ({{ $staffWithoutCard->count() }})
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $active }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Active</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-amber-500">{{ $expired }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Expired</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-red-500">{{ $lost }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Lost</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-gray-600">{{ $staffWithoutCard->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Without Card</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Issue single card --}}
        <div class="card p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Issue Single Card</h2>
            <form action="{{ route('hr.id-cards.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Staff Member</label>
                    <select name="staff_profile_id" required class="form-input w-full text-sm">
                        <option value="">— Select staff —</option>
                        @foreach($staffWithoutCard as $sp)
                            <option value="{{ $sp->id }}">{{ $sp->user?->name }} ({{ $sp->staff_code }})</option>
                        @endforeach
                    </select>
                </div>
                @if($templates->isNotEmpty())
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Template</label>
                    <select name="template_id" class="form-input w-full text-sm">
                        <option value="">Default</option>
                        @foreach($templates as $t)
                            <option value="{{ $t->id }}" {{ $t->is_default ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Notes (optional)</label>
                    <input type="text" name="notes" class="form-input w-full text-sm" placeholder="e.g. Joining card">
                </div>
                <button type="submit" class="w-full btn text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg">
                    Generate Card
                </button>
            </form>
        </div>

        {{-- Filters --}}
        <div class="lg:col-span-2 card p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Filter Cards</h2>
            <form method="GET" class="flex flex-wrap gap-3">
                <div class="flex-1 min-w-[180px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search name or card number…"
                        class="form-input w-full text-sm">
                </div>
                <div>
                    <select name="status" class="form-input text-sm">
                        <option value="">All Statuses</option>
                        @foreach(['active','expired','lost','replaced','cancelled'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn text-sm bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg">Filter</button>
                @if(request()->hasAny(['search','status']))
                    <a href="{{ route('hr.id-cards.index') }}" class="btn text-sm bg-gray-100 hover:bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Clear</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Cards table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Staff</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Card No.</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Issued</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Expiry</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($cards as $card)
                    @php
                        $statusBadge = match($card->status) {
                            'active'    => $card->isExpired() ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700',
                            'expired'   => 'bg-amber-100 text-amber-700',
                            'lost'      => 'bg-red-100 text-red-700',
                            'replaced'  => 'bg-gray-100 text-gray-500',
                            'cancelled' => 'bg-gray-100 text-gray-400',
                            default     => 'bg-gray-100 text-gray-500',
                        };
                        $statusLabel = $card->status === 'active' && $card->isExpired() ? 'Expired (active)' : ucfirst($card->status);
                        $expiryClass = $card->expiry_date && $card->expiry_date->isPast() ? 'text-red-500' : ($card->expiry_date && $card->expiry_date->diffInDays(now()) < 90 ? 'text-amber-500' : 'text-gray-700');
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $card->staffProfile?->user?->name ?? '—' }}</p>
                            <p class="text-xs text-gray-400">{{ $card->staffProfile?->staff_code }} · {{ $card->staffProfile?->branch?->name }}</p>
                        </td>
                        <td class="px-4 py-3 font-mono text-gray-800">{{ $card->card_number }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $card->issued_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 {{ $expiryClass }} font-medium">{{ $card->expiry_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $statusBadge }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('hr.id-cards.download', $card) }}"
                                   class="text-xs text-blue-600 hover:text-blue-800 font-medium">Download</a>

                                @if($card->status === 'active' && !$card->isExpired())
                                    <form action="{{ route('hr.id-cards.report-lost', $card) }}" method="POST"
                                          onsubmit="return confirm('Mark card {{ $card->card_number }} as lost?')">
                                        @csrf
                                        <button class="text-xs text-amber-600 hover:text-amber-800 font-medium">Lost</button>
                                    </form>
                                @endif

                                @if(in_array($card->status, ['lost','expired']) || $card->isExpired())
                                    <form action="{{ route('hr.id-cards.replace', $card) }}" method="POST"
                                          onsubmit="return confirm('Issue replacement for card {{ $card->card_number }}?')">
                                        @csrf
                                        <button class="text-xs text-green-600 hover:text-green-800 font-medium">Replace</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">No ID cards found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($cards->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $cards->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
