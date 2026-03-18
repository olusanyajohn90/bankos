@extends('layouts.app')
@section('title', 'Visitor Management')
@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ showCheckIn: false, showPreReg: false, showNewVisitor: false, showMeeting: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Visitor Management</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button @click="showNewVisitor = !showNewVisitor" class="btn text-sm bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg">+ Register Visitor</button>
            <button @click="showPreReg = !showPreReg" class="btn text-sm bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg">Pre-Register</button>
            <button @click="showCheckIn = !showCheckIn" class="btn text-sm bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">Check In Visitor</button>
            <button @click="showMeeting = !showMeeting" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Schedule Meeting</button>
        </div>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm font-semibold">{{ session('error') }}</div>@endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="card p-4 text-center border-l-4 border-green-400">
            <div class="text-2xl font-bold text-green-600">{{ $stats['checked_in_now'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">On Premises Now</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $stats['today_total'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Total Today</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-amber-500">{{ $stats['expected_today'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Expected Today</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-purple-600">{{ $stats['meetings_today'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Meetings Today</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-gray-600">{{ $stats['this_month'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">This Month</div>
        </div>
        <div class="card p-4 text-center border-l-4 border-red-400">
            <div class="text-2xl font-bold text-red-600">{{ $stats['blacklisted'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Blacklisted</div>
        </div>
    </div>

    {{-- Register New Visitor --}}
    <div x-show="showNewVisitor" x-transition class="card p-5 border-l-4 border-gray-500">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Register New Visitor</h2>
        <form action="{{ route('visitor.visitor-store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div><label class="block text-xs text-gray-500 mb-1">Full Name *</label>
                <input type="text" name="full_name" required class="form-input w-full text-sm" placeholder="Visitor's full name"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Phone</label>
                <input type="text" name="phone" class="form-input w-full text-sm" placeholder="+234..."></div>
            <div><label class="block text-xs text-gray-500 mb-1">Company / Organisation</label>
                <input type="text" name="company" class="form-input w-full text-sm" placeholder="Company name"></div>
            <div><label class="block text-xs text-gray-500 mb-1">ID Type</label>
                <select name="id_type" class="form-input w-full text-sm">
                    <option value="">— Select —</option>
                    <option value="national_id">National ID</option>
                    <option value="passport">Passport</option>
                    <option value="drivers_license">Driver's License</option>
                    <option value="voters_card">Voter's Card</option>
                    <option value="nin">NIN</option>
                    <option value="bvn">BVN</option>
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">ID Number</label>
                <input type="text" name="id_number" class="form-input w-full text-sm" placeholder="ID number"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Email</label>
                <input type="email" name="email" class="form-input w-full text-sm" placeholder="visitor@example.com"></div>
            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="btn text-sm bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg">Register Visitor</button>
                <button type="button" @click="showNewVisitor=false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Check In --}}
    <div x-show="showCheckIn" x-transition class="card p-5 border-l-4 border-green-500">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Check In Visitor</h2>
        <form action="{{ route('visitor.check-in') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @csrf
            <div><label class="block text-xs text-gray-500 mb-1">Visitor *</label>
                <select name="visitor_id" required class="form-input w-full text-sm">
                    <option value="">— Select registered visitor —</option>
                    @foreach(\App\Models\Visitor\Visitor::where('tenant_id', session('tenant_id'))->orderBy('full_name')->get() as $v)
                        <option value="{{ $v->id }}" {{ $v->is_blacklisted ? 'disabled class="text-red-600"' : '' }}>
                            {{ $v->full_name }}{{ $v->company ? ' — '.$v->company : '' }}{{ $v->is_blacklisted ? ' [BLACKLISTED]' : '' }}
                        </option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Host (Staff being visited) *</label>
                <select name="host_user_id" required class="form-input w-full text-sm">
                    <option value="">— Select staff —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Purpose of Visit *</label>
                <select name="purpose" required class="form-input w-full text-sm">
                    <option value="">— Select —</option>
                    @foreach(['Meeting','Delivery','Interview','Banking Transaction','Maintenance/Repair','Document Collection','Vendor Visit','Regulatory Visit','Personal','Other'] as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Badge Number</label>
                <input type="text" name="badge_number" class="form-input w-full text-sm" placeholder="B-001"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Vehicle Plate (if any)</label>
                <input type="text" name="vehicle_plate" class="form-input w-full text-sm" placeholder="ABC-123-XY"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Items Brought</label>
                <input type="text" name="items_brought" class="form-input w-full text-sm" placeholder="e.g. Laptop, documents"></div>
            <div class="lg:col-span-3 flex gap-2">
                <button type="submit" class="btn text-sm bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">Check In</button>
                <button type="button" @click="showCheckIn=false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Pre-Register --}}
    <div x-show="showPreReg" x-transition class="card p-5 border-l-4 border-amber-400">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Pre-Register Expected Visitor</h2>
        <form action="{{ route('visitor.pre-register') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @csrf
            <div><label class="block text-xs text-gray-500 mb-1">Visitor *</label>
                <select name="visitor_id" required class="form-input w-full text-sm">
                    <option value="">— Select visitor —</option>
                    @foreach(\App\Models\Visitor\Visitor::where('tenant_id', session('tenant_id'))->orderBy('full_name')->get() as $v)
                        <option value="{{ $v->id }}">{{ $v->full_name }}{{ $v->company ? ' — '.$v->company : '' }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Host *</label>
                <select name="host_user_id" required class="form-input w-full text-sm">
                    <option value="">— Select staff —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Expected Date & Time *</label>
                <input type="datetime-local" name="expected_at" required class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Purpose *</label>
                <input type="text" name="purpose" required class="form-input w-full text-sm" placeholder="Purpose of visit"></div>
            <div class="md:col-span-2 flex gap-2 items-end">
                <button type="submit" class="btn text-sm bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg">Pre-Register</button>
                <button type="button" @click="showPreReg=false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Schedule Meeting --}}
    <div x-show="showMeeting" x-transition class="card p-5 border-l-4 border-blue-400">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Schedule Meeting</h2>
        <form action="{{ route('visitor.meeting-store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @csrf
            <div class="lg:col-span-2"><label class="block text-xs text-gray-500 mb-1">Meeting Title *</label>
                <input type="text" name="title" required class="form-input w-full text-sm" placeholder="e.g. Vendor Presentation, Board Meeting"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Room</label>
                <select name="room_id" class="form-input w-full text-sm">
                    <option value="">— No room —</option>
                    @foreach($rooms as $r)
                        <option value="{{ $r->id }}">{{ $r->name }}{{ $r->location ? ' ('.$r->location.')' : '' }} · {{ $r->capacity }} pax</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Date & Time *</label>
                <input type="datetime-local" name="scheduled_at" required class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Duration (minutes) *</label>
                <input type="number" name="duration_minutes" value="60" min="15" class="form-input w-full text-sm"></div>
            <div class="lg:col-span-3"><label class="block text-xs text-gray-500 mb-1">Agenda</label>
                <textarea name="agenda" rows="2" class="form-input w-full text-sm resize-none" placeholder="Meeting agenda…"></textarea></div>
            <div class="lg:col-span-3 flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Schedule Meeting</button>
                <button type="button" @click="showMeeting=false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Currently On Premises --}}
        <div class="lg:col-span-2 card overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Currently On Premises ({{ $currentVisitors->count() }})</h2>
                <a href="{{ route('visitor.visits') }}" class="text-xs text-blue-600 hover:text-blue-800">All Visits →</a>
            </div>
            @if($currentVisitors->isEmpty())
                <div class="px-4 py-8 text-center text-sm text-gray-400">No visitors currently on premises.</div>
            @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Visitor</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 hidden md:table-cell">Host</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 hidden md:table-cell">Purpose</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">In</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Badge</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($currentVisitors as $v)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <div class="font-medium text-gray-900">{{ $v->visitor->full_name }}</div>
                            @if($v->visitor->company)<div class="text-xs text-gray-400">{{ $v->visitor->company }}</div>@endif
                        </td>
                        <td class="px-4 py-2 text-gray-600 hidden md:table-cell">{{ $v->host?->name }}</td>
                        <td class="px-4 py-2 text-gray-600 hidden md:table-cell text-xs">{{ $v->purpose }}</td>
                        <td class="px-4 py-2 text-xs text-gray-500">{{ $v->checked_in_at->format('H:i') }}</td>
                        <td class="px-4 py-2 text-xs font-mono text-gray-600">{{ $v->badge_number ?? '—' }}</td>
                        <td class="px-4 py-2 text-right">
                            <form action="{{ route('visitor.check-out', $v) }}" method="POST" class="inline">
                                @csrf
                                <button class="btn text-xs bg-gray-700 hover:bg-gray-800 text-white px-2 py-1 rounded">Check Out</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        {{-- Right column --}}
        <div class="space-y-4">

            {{-- Expected Today --}}
            @if($expectedToday->isNotEmpty())
            <div class="card p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Expected Today ({{ $expectedToday->count() }})</h3>
                <div class="space-y-2">
                    @foreach($expectedToday as $e)
                    <div class="flex items-center justify-between text-sm">
                        <div>
                            <div class="font-medium text-gray-800">{{ $e->visitor->full_name }}</div>
                            <div class="text-xs text-gray-400">{{ $e->expected_at->format('H:i') }} · {{ $e->host?->name }}</div>
                        </div>
                        <form action="{{ route('visitor.check-in') }}" method="POST">
                            @csrf
                            <input type="hidden" name="visitor_id" value="{{ $e->visitor_id }}">
                            <input type="hidden" name="host_user_id" value="{{ $e->host_user_id }}">
                            <input type="hidden" name="purpose" value="{{ $e->purpose }}">
                            <button class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200">Check In</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Today's Meetings --}}
            <div class="card p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700">Meetings Today ({{ $meetingsToday->count() }})</h3>
                    <a href="{{ route('visitor.meetings') }}" class="text-xs text-blue-600 hover:text-blue-800">All →</a>
                </div>
                @forelse($meetingsToday as $m)
                <div class="mb-2 pb-2 border-b border-gray-100 last:border-0 last:mb-0 last:pb-0">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-800">{{ $m->title }}</span>
                        <span class="px-1.5 py-0.5 rounded text-xs {{ $m->statusColor() }}">{{ ucfirst($m->status) }}</span>
                    </div>
                    <div class="text-xs text-gray-400">{{ $m->scheduled_at->format('H:i') }} · {{ $m->duration_minutes }}min {{ $m->room ? '· '.$m->room->name : '' }}</div>
                    <div class="text-xs text-gray-500">{{ $m->organiser->name }}</div>
                </div>
                @empty
                    <p class="text-xs text-gray-400">No meetings scheduled today.</p>
                @endforelse
            </div>

        </div>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('visitor.visits') }}" class="card p-4 text-center hover:shadow-md transition-shadow">
            <div class="text-2xl mb-1">📋</div><div class="text-sm font-semibold text-gray-700">Visit Log</div>
        </a>
        <a href="{{ route('visitor.visitors') }}" class="card p-4 text-center hover:shadow-md transition-shadow">
            <div class="text-2xl mb-1">👤</div><div class="text-sm font-semibold text-gray-700">Visitor Registry</div>
        </a>
        <a href="{{ route('visitor.meetings') }}" class="card p-4 text-center hover:shadow-md transition-shadow">
            <div class="text-2xl mb-1">📅</div><div class="text-sm font-semibold text-gray-700">Meetings</div>
        </a>
        <a href="{{ route('visitor.watchlist') }}" class="card p-4 text-center hover:shadow-md transition-shadow border-l-4 border-red-300">
            <div class="text-2xl mb-1">🚫</div><div class="text-sm font-semibold text-gray-700">Watchlist</div>
        </a>
    </div>

</div>
@endsection
