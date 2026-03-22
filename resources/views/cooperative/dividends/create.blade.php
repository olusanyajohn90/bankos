@extends('layouts.app')

@section('title', 'Declare Dividend')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('cooperative.dividends.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Dividends
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Declare Dividend</h1>
        <p class="text-sm text-gray-500 mt-1">Create a new dividend distribution from cooperative surplus</p>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('cooperative.dividends.store') }}" method="POST" class="card p-6 space-y-6">
        @csrf

        <div>
            <label class="form-label">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" class="form-input w-full" placeholder="e.g. 2025 Annual Dividend" required>
            @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Financial Year <span class="text-red-500">*</span></label>
                <input type="text" name="financial_year" value="{{ old('financial_year', date('Y') - 1) }}" class="form-input w-full" placeholder="e.g. 2025" required>
                @error('financial_year')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Declaration Date <span class="text-red-500">*</span></label>
                <input type="date" name="declaration_date" value="{{ old('declaration_date', date('Y-m-d')) }}" class="form-input w-full" required>
                @error('declaration_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">Total Surplus to Distribute <span class="text-red-500">*</span></label>
                <input type="number" name="total_surplus" value="{{ old('total_surplus') }}" class="form-input w-full" step="0.01" min="0.01" placeholder="0.00" required>
                <span class="text-xs text-gray-400 mt-1 block">The total profit/surplus amount available for distribution</span>
                @error('total_surplus')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Dividend Rate (%) <span class="text-red-500">*</span></label>
                <input type="number" name="dividend_rate" value="{{ old('dividend_rate') }}" class="form-input w-full" step="0.01" min="0.01" max="100" placeholder="e.g. 5.00" required>
                <span class="text-xs text-gray-400 mt-1 block">Percentage per share (applied to par value)</span>
                @error('dividend_rate')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="form-label">Notes</label>
            <textarea name="notes" rows="3" class="form-input w-full" placeholder="Optional notes about this dividend declaration...">{{ old('notes') }}</textarea>
        </div>

        <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
            <a href="{{ route('cooperative.dividends.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Declaration</button>
        </div>
    </form>
</div>
@endsection
