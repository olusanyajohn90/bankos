@extends('layouts.app')

@section('title', 'New Insurance Policy')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('insurance.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">New Insurance Policy</h1>
    </div>

    <form action="{{ route('insurance.store') }}" method="POST" class="card p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Customer <span class="text-red-500">*</span></label>
                <select name="customer_id" class="form-input w-full" required>
                    <option value="">— Select —</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected(old('customer_id', $preCustomer?->id) === $c->id)>{{ $c->full_name }}</option>
                    @endforeach
                </select>
                @error('customer_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Link to Loan</label>
                <select name="loan_id" class="form-input w-full">
                    <option value="">— None —</option>
                    @foreach($loans as $l)
                        <option value="{{ $l->id }}" @selected(old('loan_id', $preLoan?->id) === $l->id)>{{ $l->loan_account_number }} ({{ $l->customer?->full_name ?? '—' }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Product <span class="text-red-500">*</span></label>
                <select name="product" class="form-input w-full" required>
                    <option value="credit_life" @selected(old('product') === 'credit_life')>Credit Life</option>
                    <option value="health" @selected(old('product') === 'health')>Health</option>
                    <option value="asset" @selected(old('product') === 'asset')>Asset</option>
                </select>
            </div>
            <div>
                <label class="form-label">Provider <span class="text-red-500">*</span></label>
                <input type="text" name="provider" value="{{ old('provider', 'Leadway') }}" class="form-input w-full" required>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Sum Assured (₦) <span class="text-red-500">*</span></label>
                <input type="number" name="sum_assured" value="{{ old('sum_assured') }}" class="form-input w-full" step="1000" required>
                @error('sum_assured')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Premium (₦) <span class="text-red-500">*</span></label>
                <input type="number" name="premium" value="{{ old('premium') }}" class="form-input w-full" step="100" required>
            </div>
        </div>

        <div>
            <label class="form-label">Premium Frequency</label>
            <select name="premium_frequency" class="form-input w-full">
                <option value="monthly" @selected(old('premium_frequency') === 'monthly')>Monthly</option>
                <option value="quarterly" @selected(old('premium_frequency') === 'quarterly')>Quarterly</option>
                <option value="annual" @selected(old('premium_frequency') === 'annual')>Annual</option>
                <option value="single" @selected(old('premium_frequency') === 'single')>Single Premium</option>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Start Date <span class="text-red-500">*</span></label>
                <input type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" class="form-input w-full" required>
            </div>
            <div>
                <label class="form-label">End Date <span class="text-red-500">*</span></label>
                <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-input w-full" required>
            </div>
        </div>

        <div>
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-input w-full" rows="2">{{ old('notes') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('insurance.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Policy</button>
        </div>
    </form>
</div>
@endsection
