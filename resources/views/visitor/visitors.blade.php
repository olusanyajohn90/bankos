@extends('layouts.app')
@section('title', 'Visitor Registry')
@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('visitor.dashboard') }}" class="text-xs text-gray-400 hover:text-gray-600">← Visitor Management</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Visitor Registry</h1>
            <p class="text-sm text-gray-500 mt-0.5">All known visitors — profiles reused across multiple visits.</p>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Add Visitor</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    {{-- New Visitor Form --}}
    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">New Visitor Profile</h2>
        <form action="{{ route('visitor.visitor-store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-1">Full Name *</label>
                <input type="text" name="full_name" required class="form-input w-full text-sm" placeholder="John Doe">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Phone</label>
                <input type="text" name="phone" class="form-input w-full text-sm" placeholder="+234 800 000 0000">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Email</label>
                <input type="email" name="email" class="form-input w-full text-sm" placeholder="visitor@example.com">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Company / Organisation</label>
                <input type="text" name="company" class="form-input w-full text-sm" placeholder="Company name">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">ID Type</label>
                <select name="id_type" class="form-input w-full text-sm">
                    <option value="">— None —</option>
                    <option value="national_id">National ID</option>
                    <option value="passport">Passport</option>
                    <option value="drivers_license">Driver's License</option>
                    <option value="voters_card">Voter's Card</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">ID Number</label>
                <input type="text" name="id_number" class="form-input w-full text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Notes</label>
                <input type="text" name="notes" class="form-input w-full text-sm" placeholder="Any additional notes">
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Save Visitor</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Search --}}
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, company…" class="form-input flex-1 min-w-[200px] text-sm">
            <select name="filter" class="form-input text-sm">
                <option value="">All Visitors</option>
                <option value="blacklisted" {{ request('filter') === 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                <option value="vip" {{ request('filter') === 'vip' ? 'selected' : '' }}>VIP</option>
            </select>
            <button type="submit" class="btn text-sm bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg">Search</button>
            @if(request()->hasAny(['search','filter']))
                <a href="{{ route('visitor.visitors') }}" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Clear</a>
            @endif
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Visitor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contact</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Visits</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Last Visit</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($visitors as $visitor)
                <tr class="hover:bg-gray-50 {{ $visitor->is_blacklisted ? 'bg-red-50' : '' }}">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $visitor->full_name }}</p>
                        <p class="text-xs text-gray-400">{{ $visitor->company }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        @if($visitor->id_type)
                            {{ ucwords(str_replace('_',' ',$visitor->id_type)) }}<br>
                            <span class="font-mono">{{ $visitor->id_number }}</span>
                        @else —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        {{ $visitor->phone }}<br>{{ $visitor->email }}
                    </td>
                    <td class="px-4 py-3 text-center font-semibold text-gray-700">{{ $visitor->visits_count ?? $visitor->visits()->count() }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        {{ $visitor->lastVisit()?->checked_in_at?->format('d M Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($visitor->is_blacklisted)
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Blacklisted</span>
                        @elseif($visitor->isVip())
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">VIP</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Normal</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            @if(!$visitor->is_blacklisted)
                                <form action="{{ route('visitor.visitor-blacklist', $visitor) }}" method="POST"
                                      onsubmit="return confirm('Blacklist {{ $visitor->full_name }}?')">
                                    @csrf
                                    <input type="text" name="reason" placeholder="Reason (required)" required
                                           class="form-input text-xs w-32 mr-1" onclick="event.stopPropagation()">
                                    <button class="text-xs text-red-500 hover:text-red-700 font-medium">Blacklist</button>
                                </form>
                            @else
                                <span class="text-xs text-red-400 italic" title="{{ $visitor->blacklist_reason }}">{{ Str::limit($visitor->blacklist_reason, 30) }}</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No visitors found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($visitors->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $visitors->links() }}</div>
        @endif
    </div>

</div>
@endsection
