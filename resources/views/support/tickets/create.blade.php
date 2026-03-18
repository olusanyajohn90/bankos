@extends('layouts.app')
@section('title', 'New Support Ticket')
@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div>
        <a href="{{ route('support.tickets.index') }}" class="text-xs text-gray-400 hover:text-gray-600">← Tickets</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-1">New Support Ticket</h1>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="card p-6">
        <form action="{{ route('support.tickets.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Subject *</label>
                    <input type="text" name="subject" required value="{{ old('subject') }}" class="form-input w-full text-sm" placeholder="Brief description of the issue">
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Requester Name *</label>
                    <input type="text" name="requester_name" required value="{{ old('requester_name') }}" class="form-input w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Requester Type</label>
                    <select name="requester_type" class="form-input w-full text-sm">
                        <option value="customer">Customer</option>
                        <option value="staff">Staff</option>
                        <option value="walk_in">Walk-in</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Phone</label>
                    <input type="text" name="requester_phone" value="{{ old('requester_phone') }}" class="form-input w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Email</label>
                    <input type="email" name="requester_email" value="{{ old('requester_email') }}" class="form-input w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Account Number</label>
                    <input type="text" name="account_number" value="{{ old('account_number') }}" class="form-input w-full text-sm" placeholder="If applicable">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Channel</label>
                    <select name="channel" class="form-input w-full text-sm">
                        @foreach(['web','phone','email','walk_in','whatsapp','portal'] as $ch)
                            <option value="{{ $ch }}">{{ ucwords(str_replace('_',' ',$ch)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Priority *</label>
                    <select name="priority" required class="form-input w-full text-sm">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Category</label>
                    <select name="category_id" class="form-input w-full text-sm">
                        <option value="">— Select —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Route to Team</label>
                    <select name="team_id" class="form-input w-full text-sm">
                        <option value="">— Unassigned —</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Description *</label>
                    <textarea name="description" rows="5" required class="form-input w-full text-sm resize-none" placeholder="Full details of the issue…">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">Create Ticket</button>
                <a href="{{ route('support.tickets.index') }}" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2 rounded-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
