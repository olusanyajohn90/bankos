@extends('layouts.app')

@section('title', 'Organisation Structure')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Organisation Structure</h1>
        <p class="text-sm text-gray-500 mt-1">Manage regions, divisions, and departments.</p>
    </div>

    @include('hr.org._tabs')

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Alpine Tabs --}}
    <div x-data="{ tab: 'regions' }">

        {{-- Tab Switcher --}}
        <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg w-fit mb-6">
            <button @click="tab = 'regions'"
                    :class="tab === 'regions' ? 'bg-white shadow text-blue-700' : 'text-gray-600 hover:text-gray-800'"
                    class="px-5 py-2 text-sm font-medium rounded-md transition-all">
                Regions
            </button>
            <button @click="tab = 'divisions'"
                    :class="tab === 'divisions' ? 'bg-white shadow text-blue-700' : 'text-gray-600 hover:text-gray-800'"
                    class="px-5 py-2 text-sm font-medium rounded-md transition-all">
                Divisions
            </button>
            <button @click="tab = 'departments'"
                    :class="tab === 'departments' ? 'bg-white shadow text-blue-700' : 'text-gray-600 hover:text-gray-800'"
                    class="px-5 py-2 text-sm font-medium rounded-md transition-all">
                Departments
            </button>
        </div>

        {{-- ══════════════════════════════════ REGIONS TAB ══════════════════════════════════ --}}
        <div x-show="tab === 'regions'" x-transition>

            <div class="card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Regions ({{ $regions->count() }})</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Manager</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Branches</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($regions as $region)
                                <tr class="hover:bg-gray-50" x-data="{ editRegion: false }">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $region->name }}</td>
                                    <td class="px-6 py-4 text-gray-600 font-mono">{{ $region->code }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ optional($region->manager)->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $region->branches_count }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $region->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ ucfirst($region->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 flex items-center gap-2">
                                        <button @click="editRegion = true" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</button>
                                        <form action="{{ route('hr.org.regions.destroy', $region) }}" method="POST"
                                              onsubmit="return confirm('Delete region {{ $region->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                                        </form>
                                    </td>

                                    {{-- Edit Region Modal --}}
                                    <td x-show="editRegion" x-cloak class="hidden">
                                        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="editRegion = false">
                                            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                                                <h3 class="text-base font-semibold text-gray-900 mb-4">Edit Region</h3>
                                                <form action="{{ route('hr.org.regions.update', $region) }}" method="POST" class="space-y-4">
                                                    @csrf @method('PUT')
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                                        <input type="text" name="name" value="{{ $region->name }}" required class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                                                        <input type="text" name="code" value="{{ $region->code }}" maxlength="10" required class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm uppercase">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Manager</label>
                                                        <select name="manager_id" class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                                            <option value="">— None —</option>
                                                            @foreach ($users as $user)
                                                                <option value="{{ $user->id }}" {{ $region->manager_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                                        <select name="status" class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                                            <option value="active" {{ $region->status === 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="inactive" {{ $region->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                        </select>
                                                    </div>
                                                    <div class="flex justify-end gap-3 pt-2">
                                                        <button type="button" @click="editRegion = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                                                        <button type="submit" class="btn-primary px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-400 text-sm">No regions found. Add the first region below.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Add Region Form --}}
            <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">Add Region</h3>
                <form action="{{ route('hr.org.regions.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
                            <input type="text" name="code" value="{{ old('code') }}" maxlength="10" required class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm uppercase" placeholder="e.g. NORTH">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Manager</label>
                            <select name="manager_id" class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">— None —</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ old('manager_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn-primary px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Add Region</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══════════════════════════════════ DIVISIONS TAB ══════════════════════════════════ --}}
        <div x-show="tab === 'divisions'" x-transition>

            <div class="card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-800">Divisions ({{ $divisions->count() }})</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Departments</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($divisions as $division)
                                <tr class="hover:bg-gray-50" x-data="{ editDivision: false }">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $division->name }}</td>
                                    <td class="px-6 py-4 text-gray-600 font-mono">{{ $division->code }}</td>
                                    <td class="px-6 py-4 text-gray-500 max-w-xs truncate">{{ $division->description ?? '—' }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $division->departments_count }}</td>
                                    <td class="px-6 py-4 flex items-center gap-2">
                                        <button @click="editDivision = true" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</button>
                                        <form action="{{ route('hr.org.divisions.destroy', $division) }}" method="POST"
                                              onsubmit="return confirm('Delete division {{ $division->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                                        </form>
                                    </td>

                                    <td x-show="editDivision" x-cloak class="hidden">
                                        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="editDivision = false">
                                            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                                                <h3 class="text-base font-semibold text-gray-900 mb-4">Edit Division</h3>
                                                <form action="{{ route('hr.org.divisions.update', $division) }}" method="POST" class="space-y-4">
                                                    @csrf @method('PUT')
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                                        <input type="text" name="name" value="{{ $division->name }}" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                                                        <input type="text" name="code" value="{{ $division->code }}" maxlength="20" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm uppercase">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                                        <textarea name="description" rows="2" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $division->description }}</textarea>
                                                    </div>
                                                    <div class="flex justify-end gap-3 pt-2">
                                                        <button type="button" @click="editDivision = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                                                        <button type="submit" class="btn-primary px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-400 text-sm">No divisions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">Add Division</h3>
                <form action="{{ route('hr.org.divisions.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
                            <input type="text" name="code" maxlength="20" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm uppercase">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input type="text" name="description" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn-primary px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Add Division</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══════════════════════════════════ DEPARTMENTS TAB ══════════════════════════════════ --}}
        <div x-show="tab === 'departments'" x-transition>

            <div class="card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-800">Departments ({{ $departments->count() }})</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Division</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Head</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Cost Centre</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($departments as $dept)
                                <tr class="hover:bg-gray-50" x-data="{ editDept: false }">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $dept->name }}</td>
                                    <td class="px-6 py-4 text-gray-600 font-mono">{{ $dept->code }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ optional($dept->division)->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ optional($dept->head)->name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-gray-500 font-mono text-xs">{{ $dept->cost_centre_code ?? '—' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $dept->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ ucfirst($dept->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">{{ $dept->staff_profiles_count }}</td>
                                    <td class="px-6 py-4 flex items-center gap-2">
                                        <button @click="editDept = true" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</button>
                                        <form action="{{ route('hr.org.departments.destroy', $dept) }}" method="POST"
                                              onsubmit="return confirm('Delete department {{ $dept->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                                        </form>
                                    </td>

                                    <td x-show="editDept" x-cloak class="hidden">
                                        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="editDept = false">
                                            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                                                <h3 class="text-base font-semibold text-gray-900 mb-4">Edit Department</h3>
                                                <form action="{{ route('hr.org.departments.update', $dept) }}" method="POST" class="space-y-4">
                                                    @csrf @method('PUT')
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                                            <input type="text" name="name" value="{{ $dept->name }}" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                                                            <input type="text" name="code" value="{{ $dept->code }}" maxlength="20" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm uppercase">
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Division</label>
                                                            <select name="division_id" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                                <option value="">— None —</option>
                                                                @foreach ($divisions as $div)
                                                                    <option value="{{ $div->id }}" {{ $dept->division_id == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Head</label>
                                                            <select name="head_id" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                                <option value="">— None —</option>
                                                                @foreach ($users as $user)
                                                                    <option value="{{ $user->id }}" {{ $dept->head_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Cost Centre Code</label>
                                                            <input type="text" name="cost_centre_code" value="{{ $dept->cost_centre_code }}" maxlength="20" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                                            <select name="status" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                                <option value="active" {{ $dept->status === 'active' ? 'selected' : '' }}>Active</option>
                                                                <option value="inactive" {{ $dept->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="flex justify-end gap-3 pt-2">
                                                        <button type="button" @click="editDept = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                                                        <button type="submit" class="btn-primary px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center text-gray-400 text-sm">No departments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Add Department Form --}}
            <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">Add Department</h3>
                <form action="{{ route('hr.org.departments.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
                            <input type="text" name="code" maxlength="20" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm uppercase">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Division</label>
                            <select name="division_id" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">— None —</option>
                                @foreach ($divisions as $div)
                                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Head</label>
                            <select name="head_id" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">— None —</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cost Centre Code</label>
                            <input type="text" name="cost_centre_code" maxlength="20" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn-primary px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Add Department</button>
                    </div>
                </form>
            </div>
        </div>

    </div>{{-- end Alpine --}}
</div>
@endsection
