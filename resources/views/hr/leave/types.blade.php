@extends('layouts.app')

@section('title', 'Leave Types')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Leave Types</h1>
        <p class="text-sm text-gray-500 mt-1">Configure leave categories and initialise annual balances.</p>
    </div>

    @include('hr.org._tabs')

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm flex items-start gap-2">
            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm flex items-start gap-2">
            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9v4a1 1 0 002 0V9a1 1 0 00-2 0zm0-4a1 1 0 112 0 1 1 0 01-2 0z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Initialise Balances Card --}}
    <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Initialise Leave Balances</h3>
        <p class="text-xs text-gray-500 mb-3">Creates balance records for all active staff for the selected year. Existing records are not overwritten.</p>
        <form action="{{ route('hr.leave.types.init-balances') }}" method="POST" class="flex items-end gap-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Year <span class="text-red-500">*</span></label>
                <input type="number" name="year" value="{{ date('Y') }}" min="2020" max="2030" required
                       class="form-input w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <button type="submit" class="btn-primary px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                Initialise Balances
            </button>
        </form>
    </div>

    {{-- Leave Types Table --}}
    <div class="card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">Leave Types ({{ $leaveTypes->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Days Entitled</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Carry Over</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Paid</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Approval</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Active</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($leaveTypes as $type)
                        <tr class="hover:bg-gray-50" x-data="{ editType: false }">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $type->name }}</td>
                            <td class="px-6 py-4 text-gray-600 font-mono">{{ $type->code }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $type->days_entitled }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $type->carry_over_days }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $type->gender_restriction === 'all' ? 'bg-gray-100 text-gray-600' :
                                       ($type->gender_restriction === 'female' ? 'bg-pink-100 text-pink-700' : 'bg-blue-100 text-blue-700') }}">
                                    {{ ucfirst($type->gender_restriction) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($type->is_paid)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Paid</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Unpaid</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($type->requires_approval)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Required</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Auto</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($type->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Yes</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 flex items-center gap-2">
                                <button @click="editType = true" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</button>
                                <form action="{{ route('hr.leave.types.destroy', $type) }}" method="POST"
                                      onsubmit="return confirm('Delete {{ $type->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                                </form>
                            </td>

                            {{-- Edit Leave Type Modal --}}
                            <td x-show="editType" x-cloak class="hidden">
                                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="editType = false">
                                    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 max-h-screen overflow-y-auto">
                                        <h3 class="text-base font-semibold text-gray-900 mb-4">Edit Leave Type</h3>
                                        <form action="{{ route('hr.leave.types.update', $type) }}" method="POST" class="space-y-4">
                                            @csrf @method('PUT')
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                                    <input type="text" name="name" value="{{ $type->name }}" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                                                    <input type="text" name="code" value="{{ $type->code }}" maxlength="20" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm uppercase">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Days Entitled</label>
                                                    <input type="number" name="days_entitled" value="{{ $type->days_entitled }}" step="0.5" min="0.5" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Carry Over Days</label>
                                                    <input type="number" name="carry_over_days" value="{{ $type->carry_over_days }}" step="0.5" min="0" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender Restriction</label>
                                                    <select name="gender_restriction" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                        <option value="all" {{ $type->gender_restriction === 'all' ? 'selected' : '' }}>All</option>
                                                        <option value="male" {{ $type->gender_restriction === 'male' ? 'selected' : '' }}>Male</option>
                                                        <option value="female" {{ $type->gender_restriction === 'female' ? 'selected' : '' }}>Female</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-3 gap-4">
                                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                                    <input type="hidden" name="requires_approval" value="0">
                                                    <input type="checkbox" name="requires_approval" value="1" {{ $type->requires_approval ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                                                    Requires Approval
                                                </label>
                                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                                    <input type="hidden" name="is_paid" value="0">
                                                    <input type="checkbox" name="is_paid" value="1" {{ $type->is_paid ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                                                    Is Paid
                                                </label>
                                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                                    <input type="hidden" name="is_active" value="0">
                                                    <input type="checkbox" name="is_active" value="1" {{ $type->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                                                    Is Active
                                                </label>
                                            </div>
                                            <div class="flex justify-end gap-3 pt-2">
                                                <button type="button" @click="editType = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                                                <button type="submit" class="btn-primary px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-gray-400 text-sm">No leave types configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Leave Type Form --}}
    <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Add Leave Type</h3>
        <form action="{{ route('hr.leave.types.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Annual Leave" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" maxlength="20" required placeholder="e.g. AL" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Days Entitled <span class="text-red-500">*</span></label>
                    <input type="number" name="days_entitled" step="0.5" min="0.5" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Carry Over Days</label>
                    <input type="number" name="carry_over_days" value="0" step="0.5" min="0" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender Restriction</label>
                    <select name="gender_restriction" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="all">All</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="flex items-end gap-6 pb-1">
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="hidden" name="requires_approval" value="0">
                        <input type="checkbox" name="requires_approval" value="1" checked class="rounded border-gray-300 text-blue-600">
                        Requires Approval
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="hidden" name="is_paid" value="0">
                        <input type="checkbox" name="is_paid" value="1" checked class="rounded border-gray-300 text-blue-600">
                        Paid
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600">
                        Active
                    </label>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn-primary px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Add Leave Type</button>
            </div>
        </form>
    </div>

</div>
@endsection
