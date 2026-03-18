@extends('layouts.app')
@section('title', 'Lead — ' . $lead->title)
@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ showLog: false, showFollowUp: false, showEdit: false }">

    {{-- Back + Header --}}
    <div>
        <a href="{{ route('crm.leads') }}" class="text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1 mb-2">← Back to Leads</a>
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $lead->title }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $lead->contact_name }}
                    @if($lead->company) · {{ $lead->company }} @endif
                    @if($lead->contact_phone) · {{ $lead->contact_phone }} @endif
                </p>
            </div>
            @php
                $sc = match($lead->status) {
                    'new' => 'bg-blue-100 text-blue-700', 'in_progress' => 'bg-amber-100 text-amber-700',
                    'converted' => 'bg-green-100 text-green-700', 'lost' => 'bg-red-100 text-red-600',
                    default => 'bg-gray-100 text-gray-500',
                };
            @endphp
            <span class="px-3 py-1.5 rounded-full text-sm font-bold {{ $sc }}">{{ ucwords(str_replace('_',' ',$lead->status)) }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Details --}}
        <div class="space-y-4">
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Lead Info</h2>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-xs text-gray-400">Stage</dt>
                        <dd>@if($lead->stage)<span class="px-2 py-0.5 rounded-full text-xs font-medium" style="background:{{ $lead->stage->color }}20;color:{{ $lead->stage->color }}">{{ $lead->stage->name }}</span>@else—@endif</dd></div>
                    <div><dt class="text-xs text-gray-400">Product Interest</dt><dd class="font-medium text-gray-800">{{ $lead->product_interest ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Est. Value</dt><dd class="font-medium text-gray-800">{{ $lead->estimated_value ? '₦' . number_format($lead->estimated_value) : '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Probability</dt><dd class="font-medium text-gray-800">{{ $lead->probability_pct }}%</dd></div>
                    <div><dt class="text-xs text-gray-400">Source</dt><dd class="font-medium text-gray-800">{{ $lead->source ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Assigned To</dt><dd class="font-medium text-gray-800">{{ $lead->assignedTo?->name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Expected Close</dt><dd class="font-medium text-gray-800">{{ $lead->expected_close_date?->format('d M Y') ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Created</dt><dd class="text-gray-500 text-xs">{{ $lead->created_at->format('d M Y') }} by {{ $lead->createdBy?->name }}</dd></div>
                </dl>
            </div>

            {{-- Quick Update --}}
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Quick Update</h2>
                <form action="{{ route('crm.leads.update', $lead) }}" method="POST" class="space-y-3">
                    @csrf @method('PATCH')
                    <div><label class="block text-xs text-gray-500 mb-1">Stage</label>
                        <select name="stage_id" class="form-input w-full text-sm">
                            @foreach($stages as $st)
                                <option value="{{ $st->id }}" {{ $lead->stage_id === $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status" class="form-input w-full text-sm">
                            @foreach(['new','in_progress','converted','lost','on_hold'] as $s)
                                <option value="{{ $s }}" {{ $lead->status === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Probability %</label>
                        <input type="number" name="probability_pct" value="{{ $lead->probability_pct }}" min="0" max="100" class="form-input w-full text-sm"></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Assign To</label>
                        <select name="assigned_to" class="form-input w-full text-sm">
                            @foreach($agents as $a)
                                <option value="{{ $a->id }}" {{ $lead->assigned_to == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                            @endforeach
                        </select></div>
                    <button type="submit" class="w-full btn text-sm bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg">Update</button>
                </form>
            </div>
        </div>

        {{-- Right: Interactions + Follow-ups --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Log interaction --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-700">Log Interaction</h2>
                    <button @click="showLog = !showLog" class="text-xs text-blue-600 hover:underline">+ Add</button>
                </div>
                <div x-show="showLog" x-transition>
                    <form action="{{ route('crm.interactions.store') }}" method="POST" class="grid grid-cols-2 gap-3">
                        @csrf
                        <input type="hidden" name="subject_type" value="lead">
                        <input type="hidden" name="subject_id" value="{{ $lead->id }}">
                        <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                        <div><label class="block text-xs text-gray-500 mb-1">Type</label>
                            <select name="interaction_type" class="form-input w-full text-sm">
                                @foreach(['call','meeting','email','whatsapp','visit','sms','note'] as $t)
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
                        <div><label class="block text-xs text-gray-500 mb-1">Duration (mins)</label>
                            <input type="number" name="duration_mins" class="form-input w-full text-sm" min="0" placeholder="5"></div>
                        <div class="col-span-2"><label class="block text-xs text-gray-500 mb-1">Summary *</label>
                            <textarea name="summary" rows="2" required class="form-input w-full text-sm resize-none"></textarea></div>
                        <div class="col-span-2"><label class="block text-xs text-gray-500 mb-1">Outcome / Next Action</label>
                            <input type="text" name="outcome" class="form-input w-full text-sm"></div>
                        <div class="col-span-2"><button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Log Interaction</button></div>
                    </form>
                </div>
            </div>

            {{-- Interactions list --}}
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Interaction History ({{ $lead->interactions->count() }})</h2>
                <div class="space-y-3">
                    @forelse($lead->interactions->sortByDesc('interacted_at') as $i)
                        @php $icons = ['call'=>'📞','meeting'=>'🤝','email'=>'📧','whatsapp'=>'💬','visit'=>'🏢','sms'=>'📱','note'=>'📝']; @endphp
                        <div class="flex gap-3 p-3 rounded-lg bg-gray-50">
                            <span class="text-xl">{{ $icons[$i->interaction_type] ?? '📝' }}</span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-600 uppercase">{{ $i->interaction_type }} · {{ ucfirst($i->direction) }}</span>
                                    <span class="text-xs text-gray-400">{{ $i->interacted_at->format('d M Y, H:i') }}</span>
                                </div>
                                <p class="text-sm text-gray-800 mt-1">{{ $i->summary }}</p>
                                @if($i->outcome)<p class="text-xs text-gray-500 mt-0.5 italic">{{ $i->outcome }}</p>@endif
                                <p class="text-xs text-gray-400 mt-1">by {{ $i->createdBy?->name }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-4">No interactions logged yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Follow-ups --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-700">Follow-ups</h2>
                    <button @click="showFollowUp = !showFollowUp" class="text-xs text-blue-600 hover:underline">+ Schedule</button>
                </div>
                <div x-show="showFollowUp" x-transition class="mb-4">
                    <form action="{{ route('crm.follow-ups.store') }}" method="POST" class="grid grid-cols-2 gap-3">
                        @csrf
                        <input type="hidden" name="subject_type" value="lead">
                        <input type="hidden" name="subject_id" value="{{ $lead->id }}">
                        <div class="col-span-2"><label class="block text-xs text-gray-500 mb-1">Title *</label>
                            <input type="text" name="title" required class="form-input w-full text-sm" placeholder="e.g. Call to follow up on loan application"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">Due Date & Time *</label>
                            <input type="datetime-local" name="due_at" required class="form-input w-full text-sm"></div>
                        <div><label class="block text-xs text-gray-500 mb-1">Assign To</label>
                            <select name="assigned_to" class="form-input w-full text-sm">
                                @foreach($agents as $a)
                                    <option value="{{ $a->id }}" {{ $a->id == auth()->id() ? 'selected' : '' }}>{{ $a->name }}</option>
                                @endforeach
                            </select></div>
                        <div class="col-span-2"><button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Schedule Follow-up</button></div>
                    </form>
                </div>
                <div class="space-y-2">
                    @forelse($lead->followUps as $f)
                        @php $overdue = $f->due_at->isPast() && $f->status === 'pending'; @endphp
                        <div class="flex items-center justify-between p-2.5 rounded-lg {{ $overdue ? 'bg-red-50' : ($f->status === 'completed' ? 'bg-green-50' : 'bg-gray-50') }}">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $f->title }}</p>
                                <p class="text-xs {{ $overdue ? 'text-red-600 font-semibold' : 'text-gray-400' }}">{{ $f->due_at->format('d M Y, H:i') }} · {{ $f->assignedTo?->name }}</p>
                            </div>
                            @if($f->status === 'pending')
                                <form action="{{ route('crm.follow-ups.complete', $f) }}" method="POST">
                                    @csrf
                                    <button class="text-xs text-green-600 font-medium">Done</button>
                                </form>
                            @else <span class="text-xs text-green-600 font-bold">✓ Done</span>@endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-3">No follow-ups scheduled.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
