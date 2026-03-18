@extends('layouts.app')

@section('title', 'Edit Agent')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('agents.show', $agent) }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Agent — {{ $agent->full_name }}</h1>
    </div>

    <form action="{{ route('agents.update', $agent) }}" method="POST" class="card p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">First Name <span class="text-red-500">*</span></label>
                <input type="text" name="first_name" value="{{ old('first_name', $agent->first_name) }}" class="form-input w-full" required>
                @error('first_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Last Name <span class="text-red-500">*</span></label>
                <input type="text" name="last_name" value="{{ old('last_name', $agent->last_name) }}" class="form-input w-full" required>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Phone <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone', $agent->phone) }}" class="form-input w-full" required>
                @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $agent->email) }}" class="form-input w-full">
            </div>
        </div>

        <div>
            <label class="form-label">Address</label>
            <textarea name="address" class="form-input w-full" rows="2">{{ old('address', $agent->address) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Branch</label>
                <select name="branch_id" class="form-input w-full">
                    <option value="">— No Branch —</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(old('branch_id', $agent->branch_id) === $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-input w-full">
                    <option value="active" @selected(old('status', $agent->status) === 'active')>Active</option>
                    <option value="suspended" @selected(old('status', $agent->status) === 'suspended')>Suspended</option>
                    <option value="inactive" @selected(old('status', $agent->status) === 'inactive')>Inactive</option>
                </select>
            </div>
        </div>

        <fieldset class="border rounded-lg p-4">
            <legend class="text-sm font-semibold text-gray-700 px-2">Transaction Limits</legend>
            <div class="grid grid-cols-3 gap-4 mt-2">
                <div>
                    <label class="form-label">Daily Cash-In (₦)</label>
                    <input type="number" name="daily_cash_in_limit" value="{{ old('daily_cash_in_limit', $agent->daily_cash_in_limit) }}" class="form-input w-full" step="1000">
                </div>
                <div>
                    <label class="form-label">Daily Cash-Out (₦)</label>
                    <input type="number" name="daily_cash_out_limit" value="{{ old('daily_cash_out_limit', $agent->daily_cash_out_limit) }}" class="form-input w-full" step="1000">
                </div>
                <div>
                    <label class="form-label">Daily Transfer (₦)</label>
                    <input type="number" name="daily_transfer_limit" value="{{ old('daily_transfer_limit', $agent->daily_transfer_limit) }}" class="form-input w-full" step="1000">
                </div>
            </div>
        </fieldset>

        <div>
            <label class="form-label">Commission Rate</label>
            <input type="number" name="commission_rate" value="{{ old('commission_rate', $agent->commission_rate) }}" class="form-input w-full" step="0.0001" min="0" max="1">
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('agents.show', $agent) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
