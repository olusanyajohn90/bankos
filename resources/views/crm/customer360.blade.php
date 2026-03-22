@extends('layouts.app')
@section('title', 'Customer 360 — ' . $account->owner?->name)
@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ showLog: false, showFollowUp: false }">

    {{-- Header --}}
    <div>
        <a href="{{ route('accounts.index') }}" class="text-xs text-gray-400 hover:text-gray-600">← Back to Accounts</a>
        <div class="flex items-start justify-between gap-4 flex-wrap mt-1">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $account->owner?->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">Customer 360 View · {{ $account->account_number }} · {{ $account->branch?->name }}</p>
            </div>
            @php
                $sb = $account->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500';
            @endphp
            <span class="px-3 py-1.5 rounded-full text-sm font-bold {{ $sb }}">{{ ucfirst($account->status ?? 'active') }}</span>
        </div>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Account & Customer Info --}}
        <div class="space-y-4">
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Account Details</h2>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-xs text-gray-400">Account No.</dt><dd class="font-mono font-semibold text-gray-800">{{ $account->account_number }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Balance</dt><dd class="font-bold text-lg text-gray-900">₦{{ number_format($account->available_balance ?? 0, 2) }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Product</dt><dd class="font-medium text-gray-800">{{ $account->product?->name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Branch</dt><dd class="font-medium text-gray-800">{{ $account->branch?->name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Opened</dt><dd class="text-gray-600">{{ $account->created_at?->format('d M Y') }}</dd></div>
                </dl>
            </div>

            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Customer Info</h2>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-xs text-gray-400">Full Name</dt><dd class="font-semibold text-gray-800">{{ $account->owner?->name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Phone</dt><dd class="text-gray-700">{{ $account->owner?->phone ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Email</dt><dd class="text-gray-700 truncate">{{ $account->owner?->email ?? '—' }}</dd></div>
                </dl>
                <div class="mt-3 pt-3 border-t border-gray-100 flex gap-2">
                    <a href="{{ route('accounts.show', $account) }}" class="text-xs text-blue-600 hover:underline">View Account</a>
                </div>
            </div>
        </div>

        {{-- Right: Interactions + Follow-ups --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Quick log --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-700">Log Interaction</h2>
                    <button @click="showLog = !showLog" class="text-xs text-blue-600 hover:underline">+ Add</button>
                </div>
                <div x-show="showLog" x-transition>
                    <form action="{{ route('crm.interactions.store') }}" method="POST" class="grid grid-cols-2 gap-3">
                        @csrf
                        <input type="hidden" name="subject_type" value="account">
                        <input type="hidden" name="subject_id" value="{{ $account->id }}">
                        <input type="hidden" name="account_id" value="{{ $account->id }}">
                        <div><label class="block text-xs text-gray-500 mb-1">Type</label>
                            <select name="interaction_type" class="form-input w-full text-sm">
                                @foreach(['call','meeting','email','whatsapp','visit','note'] as $t)
                                    <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                                @endforeach
                            </select></div>
                        <div><label class="block text-xs text-gray-500 mb-1">Direction</label>
                            <select name="direction" class="form-input w-full text-sm">
                                <option value="outbound">Outbound</option>
                                <option value="inbound">Inbound</option>
                            </select></div>
                        <div><label class="block text-xs text-gray-500 mb-1">Date & Time</label>
                            <input type="datetime-local" name="interacted_at" value="{{ now()->format('Y-m-d\TH:i') }}" class="form-input w-full text-sm"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">Duration (min)</label>
                            <input type="number" name="duration_mins" class="form-input w-full text-sm" min="0"></div>
                        <div class="col-span-2"><label class="block text-xs text-gray-500 mb-1">Summary *</label>
                            <textarea name="summary" rows="2" required class="form-input w-full text-sm resize-none"></textarea></div>
                        <div class="col-span-2"><button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Log</button></div>
                    </form>
                </div>
            </div>

            {{-- Interaction History --}}
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Interaction History ({{ $interactions->count() }})</h2>
                @php $icons = ['call'=>'📞','meeting'=>'🤝','email'=>'📧','whatsapp'=>'💬','visit'=>'🏢','note'=>'📝']; @endphp
                <div class="space-y-3">
                    @forelse($interactions as $i)
                        <div class="flex gap-3 p-3 rounded-lg bg-gray-50">
                            <span class="text-xl">{{ $icons[$i->interaction_type] ?? '📝' }}</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-600 uppercase">{{ $i->interaction_type }} · {{ ucfirst($i->direction) }}</span>
                                    <span class="text-xs text-gray-400">{{ $i->interacted_at->format('d M Y, H:i') }}</span>
                                </div>
                                <p class="text-sm text-gray-800 mt-1">{{ $i->summary }}</p>
                                @if($i->outcome)<p class="text-xs text-gray-500 mt-0.5 italic">{{ $i->outcome }}</p>@endif
                                <p class="text-xs text-gray-400 mt-1">{{ $i->createdBy?->name }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">No interactions logged yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Follow-ups --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-700">Follow-ups ({{ $followUps->count() }} pending)</h2>
                    <button @click="showFollowUp = !showFollowUp" class="text-xs text-blue-600 hover:underline">+ Schedule</button>
                </div>
                <div x-show="showFollowUp" x-transition class="mb-4">
                    <form action="{{ route('crm.follow-ups.store') }}" method="POST" class="grid grid-cols-2 gap-3">
                        @csrf
                        <input type="hidden" name="subject_type" value="account">
                        <input type="hidden" name="subject_id" value="{{ $account->id }}">
                        <div class="col-span-2"><input type="text" name="title" required class="form-input w-full text-sm" placeholder="e.g. Call to offer FD renewal"></div>
                        <div><input type="datetime-local" name="due_at" required class="form-input w-full text-sm"></div>
                        <div><button type="submit" class="w-full btn text-sm bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg">Schedule</button></div>
                    </form>
                </div>
                <div class="space-y-2">
                    @forelse($followUps as $f)
                        @php $overdue = $f->due_at->isPast(); @endphp
                        <div class="flex items-center justify-between p-2.5 rounded-lg {{ $overdue ? 'bg-red-50' : 'bg-gray-50' }}">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $f->title }}</p>
                                <p class="text-xs {{ $overdue ? 'text-red-600 font-semibold' : 'text-gray-400' }}">{{ $f->due_at->format('d M Y, H:i') }}</p>
                            </div>
                            <form action="{{ route('crm.follow-ups.complete', $f) }}" method="POST">
                                @csrf
                                <button class="text-xs text-green-600 font-medium">Done</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-3">No pending follow-ups.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
