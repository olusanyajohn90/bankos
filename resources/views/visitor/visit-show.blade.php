@extends('layouts.app')
@section('title', 'Visit Detail')
@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ showActivity: false }">

    <div>
        <a href="{{ route('visitor.visits') }}" class="text-xs text-gray-400 hover:text-gray-600">← Visit Log</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-1">Visit: {{ $visit->visitor?->full_name }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ $visit->checked_in_at?->format('d M Y H:i') ?? 'Not yet checked in' }}
            @if($visit->badge_number) · Badge #{{ $visit->badge_number }}@endif
        </p>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Visit Info --}}
        <div class="lg:col-span-2 space-y-5">

            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Visit Details</h2>
                <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-400">Visitor</p>
                        <p class="font-medium text-gray-900">{{ $visit->visitor?->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Company</p>
                        <p class="text-gray-700">{{ $visit->visitor?->company ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Host</p>
                        <p class="text-gray-700">{{ $visit->host?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Purpose</p>
                        <p class="text-gray-700">{{ ucwords(str_replace('_',' ',$visit->purpose)) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Check In</p>
                        <p class="text-gray-700">{{ $visit->checked_in_at?->format('d M Y H:i') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Check Out</p>
                        <p class="text-gray-700">
                            {{ $visit->checked_out_at?->format('d M Y H:i') ?? '—' }}
                            @if($visit->checked_out_at) <span class="text-gray-400 text-xs">({{ $visit->duration() }})</span>@endif
                        </p>
                    </div>
                    @if($visit->vehicle_plate)
                    <div>
                        <p class="text-xs text-gray-400">Vehicle</p>
                        <p class="font-mono text-gray-700">{{ $visit->vehicle_plate }}</p>
                    </div>
                    @endif
                    @if($visit->items_brought)
                    <div>
                        <p class="text-xs text-gray-400">Items Brought In</p>
                        <p class="text-gray-700">{{ $visit->items_brought }}</p>
                    </div>
                    @endif
                    @if($visit->items_left)
                    <div>
                        <p class="text-xs text-gray-400">Items Left Behind</p>
                        <p class="text-gray-700">{{ $visit->items_left }}</p>
                    </div>
                    @endif
                    @if($visit->notes)
                    <div class="col-span-2">
                        <p class="text-xs text-gray-400">Notes</p>
                        <p class="text-gray-700">{{ $visit->notes }}</p>
                    </div>
                    @endif
                    @if($visit->denial_reason)
                    <div class="col-span-2">
                        <p class="text-xs text-red-400">Denial Reason</p>
                        <p class="text-red-700">{{ $visit->denial_reason }}</p>
                    </div>
                    @endif
                </div>
                @php
                    $colors = [
                        'checked_in'  => 'bg-blue-100 text-blue-700',
                        'checked_out' => 'bg-green-100 text-green-700',
                        'expected'    => 'bg-amber-100 text-amber-700',
                        'denied'      => 'bg-red-100 text-red-700',
                        'no_show'     => 'bg-gray-100 text-gray-500',
                    ];
                    $badge = $colors[$visit->status] ?? 'bg-gray-100 text-gray-500';
                @endphp
                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-3">
                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $badge }}">{{ ucwords(str_replace('_',' ',$visit->status)) }}</span>
                    @if($visit->status === 'checked_in')
                        <form action="{{ route('visitor.check-out', $visit) }}" method="POST"
                              onsubmit="return confirm('Check out this visitor now?')">
                            @csrf
                            <button class="btn text-sm bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg">Check Out Now</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Activity Log --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-700">Activity Log</h2>
                    <button @click="showActivity = !showActivity"
                            class="btn text-xs bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1.5 rounded-lg">+ Log Activity</button>
                </div>

                <div x-show="showActivity" x-transition class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <form action="{{ route('visitor.log-activity', $visit) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Activity Type *</label>
                                <select name="activity_type" required class="form-input w-full text-sm">
                                    <option value="meeting">Meeting</option>
                                    <option value="delivery">Delivery</option>
                                    <option value="document_signed">Document Signed</option>
                                    <option value="area_access">Area Access</option>
                                    <option value="transaction">Transaction</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Area / Location</label>
                                <input type="text" name="area_accessed" class="form-input w-full text-sm" placeholder="e.g. Boardroom, Vault">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Description *</label>
                            <input type="text" name="description" required class="form-input w-full text-sm" placeholder="Brief description of activity">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Time</label>
                            <input type="datetime-local" name="occurred_at" value="{{ now()->format('Y-m-d\TH:i') }}" class="form-input w-full text-sm">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg">Log</button>
                            <button type="button" @click="showActivity = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1.5 rounded-lg">Cancel</button>
                        </div>
                    </form>
                </div>

                @if($visit->activities->isEmpty())
                    <p class="text-sm text-gray-400 py-4 text-center">No activities logged for this visit.</p>
                @else
                    <div class="space-y-3">
                        @foreach($visit->activities->sortByDesc('occurred_at') as $activity)
                        <div class="flex gap-3">
                            <div class="mt-1 w-2 h-2 rounded-full bg-blue-400 flex-shrink-0 mt-2"></div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-semibold text-gray-700 capitalize">{{ str_replace('_',' ',$activity->activity_type) }}</span>
                                    @if($activity->area_accessed)<span class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">{{ $activity->area_accessed }}</span>@endif
                                    <span class="text-xs text-gray-400 ml-auto">{{ $activity->occurred_at->format('H:i') }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-0.5">{{ $activity->description }}</p>
                                <p class="text-xs text-gray-400">Logged by {{ $activity->loggedBy?->name }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        {{-- Visitor Profile Sidebar --}}
        <div class="space-y-5">
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Visitor Profile</h2>
                @if($visit->visitor)
                    @php $v = $visit->visitor; @endphp
                    <div class="space-y-2 text-sm">
                        <div><p class="text-xs text-gray-400">Name</p><p class="font-medium text-gray-900">{{ $v->full_name }}</p></div>
                        @if($v->phone)<div><p class="text-xs text-gray-400">Phone</p><p class="text-gray-700">{{ $v->phone }}</p></div>@endif
                        @if($v->email)<div><p class="text-xs text-gray-400">Email</p><p class="text-gray-700">{{ $v->email }}</p></div>@endif
                        @if($v->company)<div><p class="text-xs text-gray-400">Company</p><p class="text-gray-700">{{ $v->company }}</p></div>@endif
                        @if($v->id_type)<div><p class="text-xs text-gray-400">ID</p><p class="text-gray-700">{{ ucwords(str_replace('_',' ',$v->id_type)) }}: {{ $v->id_number }}</p></div>@endif
                    </div>
                    @if($v->is_blacklisted)
                        <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-700">
                            <strong>Blacklisted:</strong> {{ $v->blacklist_reason }}
                        </div>
                    @endif
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-400">Total visits: <strong class="text-gray-700">{{ $v->visits()->count() }}</strong></p>
                    </div>
                @endif
            </div>

            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Check-in Details</h2>
                <div class="space-y-2 text-sm">
                    <div><p class="text-xs text-gray-400">Checked in by</p><p class="text-gray-700">{{ $visit->checkedInBy?->name ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-400">Checked out by</p><p class="text-gray-700">{{ $visit->checkedOutBy?->name ?? '—' }}</p></div>
                    @if($visit->expected_at)<div><p class="text-xs text-gray-400">Expected at</p><p class="text-gray-700">{{ $visit->expected_at->format('d M Y H:i') }}</p></div>@endif
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
