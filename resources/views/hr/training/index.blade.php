@extends('layouts.app')

@section('title', 'Training & Development')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Training & Development</h1>
        <p class="text-sm text-gray-500 mt-1">Manage training programs and track staff attendance.</p>
    </div>

    @include('hr.org._tabs')

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Alpine Two-Tab Layout --}}
    <div x-data="{ tab: 'programs' }">

        <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg w-fit mb-6">
            <button @click="tab = 'programs'"
                    :class="tab === 'programs' ? 'bg-white shadow text-blue-700' : 'text-gray-600 hover:text-gray-800'"
                    class="px-5 py-2 text-sm font-medium rounded-md transition-all">
                Programs
            </button>
            <button @click="tab = 'attendance'"
                    :class="tab === 'attendance' ? 'bg-white shadow text-blue-700' : 'text-gray-600 hover:text-gray-800'"
                    class="px-5 py-2 text-sm font-medium rounded-md transition-all">
                Attendance
            </button>
        </div>

        {{-- ══════════════════════ PROGRAMS TAB ══════════════════════ --}}
        <div x-show="tab === 'programs'" x-transition>

            {{-- Programs Table --}}
            <div class="card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-800">Training Programs ({{ $programs->count() }})</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Mandatory</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Enrolled</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($programs as $program)
                                @php
                                    $catColors = [
                                        'technical'    => 'bg-blue-100 text-blue-700',
                                        'compliance'   => 'bg-red-100 text-red-700',
                                        'leadership'   => 'bg-purple-100 text-purple-700',
                                        'soft_skills'  => 'bg-green-100 text-green-700',
                                        'safety'       => 'bg-orange-100 text-orange-700',
                                        'other'        => 'bg-gray-100 text-gray-600',
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50" x-data="{ editProgram: false, enrollModal: false }">
                                    <td class="px-4 py-4 font-medium text-gray-900 max-w-xs">
                                        <p>{{ $program->title }}</p>
                                        @if ($program->description)
                                            <p class="text-xs text-gray-400 mt-0.5 truncate" title="{{ $program->description }}">{{ Str::limit($program->description, 50) }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $catColors[$program->category] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ ucwords(str_replace('_', ' ', $program->category)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-gray-600">{{ $program->provider ?? '—' }}</td>
                                    <td class="px-4 py-4 text-gray-700">{{ $program->duration_hours }}h</td>
                                    <td class="px-4 py-4">
                                        @if ($program->is_mandatory)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Mandatory</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Optional</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $program->status === 'active' ? 'bg-green-100 text-green-700' :
                                               ($program->status === 'archived' ? 'bg-gray-200 text-gray-500' : 'bg-yellow-100 text-yellow-700') }}">
                                            {{ ucfirst($program->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-gray-700 font-medium">{{ $program->attendances_count }}</td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <button @click="editProgram = true" class="text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded">Edit</button>
                                            <button @click="enrollModal = true" class="text-xs font-medium text-green-700 hover:text-green-900 bg-green-50 hover:bg-green-100 px-2 py-1 rounded">Enroll</button>
                                        </div>
                                    </td>

                                    {{-- Edit Program Modal --}}
                                    <td x-show="editProgram" x-cloak>
                                        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="editProgram = false">
                                            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 max-h-screen overflow-y-auto">
                                                <h3 class="text-base font-semibold text-gray-900 mb-4">Edit Program</h3>
                                                <form action="{{ route('hr.training.update', $program) }}" method="POST" class="space-y-4">
                                                    @csrf @method('PATCH')
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                                        <input type="text" name="title" value="{{ $program->title }}" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                                            <select name="category" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                                @foreach (['technical','compliance','leadership','soft_skills','safety','other'] as $cat)
                                                                    <option value="{{ $cat }}" {{ $program->category === $cat ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $cat)) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                                                            <input type="text" name="provider" value="{{ $program->provider }}" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Duration (hours)</label>
                                                            <input type="number" name="duration_hours" value="{{ $program->duration_hours }}" step="0.5" min="0.5" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                                            <select name="status" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                                <option value="active" {{ $program->status === 'active' ? 'selected' : '' }}>Active</option>
                                                                <option value="inactive" {{ $program->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                                <option value="archived" {{ $program->status === 'archived' ? 'selected' : '' }}>Archived</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                                        <input type="hidden" name="is_mandatory" value="0">
                                                        <input type="checkbox" name="is_mandatory" value="1" {{ $program->is_mandatory ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                                                        Mandatory Training
                                                    </label>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                                        <textarea name="description" rows="2" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $program->description }}</textarea>
                                                    </div>
                                                    <div class="flex justify-end gap-3">
                                                        <button type="button" @click="editProgram = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                                                        <button type="submit" class="btn-primary px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Enroll Staff Modal --}}
                                    <td x-show="enrollModal" x-cloak>
                                        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="enrollModal = false">
                                            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                                                <h3 class="text-base font-semibold text-gray-900 mb-1">Enroll Staff</h3>
                                                <p class="text-sm text-gray-500 mb-4">{{ $program->title }}</p>
                                                <form action="{{ route('hr.training.enroll', $program) }}" method="POST">
                                                    @csrf
                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Staff <span class="text-red-500">*</span></label>
                                                        <select name="staff_profile_ids[]" multiple required
                                                                class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm" size="8">
                                                            @foreach ($staff as $s)
                                                                <option value="{{ $s->id }}">{{ optional($s->user)->name }} ({{ $s->employee_number }})</option>
                                                            @endforeach
                                                        </select>
                                                        <p class="text-xs text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple.</p>
                                                    </div>
                                                    <div class="flex justify-end gap-3">
                                                        <button type="button" @click="enrollModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                                                        <button type="submit" class="btn-primary px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">Enroll Selected</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center text-gray-400 text-sm">No training programs found. Add the first program below.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Add Program Form --}}
            <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">Add Training Program</h3>
                <form action="{{ route('hr.training.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" required placeholder="Program title..." class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                            <select name="category" required class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Select...</option>
                                @foreach (['technical','compliance','leadership','soft_skills','safety','other'] as $cat)
                                    <option value="{{ $cat }}">{{ ucwords(str_replace('_', ' ', $cat)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                            <input type="text" name="provider" class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Training provider">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Duration (hours) <span class="text-red-500">*</span></label>
                            <input type="number" name="duration_hours" step="0.5" min="0.5" required class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input type="hidden" name="is_mandatory" value="0">
                                <input type="checkbox" name="is_mandatory" value="1" class="rounded border-gray-300 text-blue-600">
                                Mandatory Training
                            </label>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="2" class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Brief description of the training program..."></textarea>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn-primary px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Add Program</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══════════════════════ ATTENDANCE TAB ══════════════════════ --}}
        <div x-show="tab === 'attendance'" x-transition>

            <div class="card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-800">Training Attendance</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Program</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Enrolled</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Certificate</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Update</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($attendances as $att)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ optional($att->staffProfile->user)->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-700 text-xs max-w-xs truncate">{{ optional($att->program)->title ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $att->enrolled_at ? \Carbon\Carbon::parse($att->enrolled_at)->format('d M Y') : '—' }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $attColors = [
                                                'enrolled'   => 'bg-blue-100 text-blue-700',
                                                'attending'  => 'bg-yellow-100 text-yellow-700',
                                                'completed'  => 'bg-green-100 text-green-700',
                                                'absent'     => 'bg-red-100 text-red-700',
                                                'withdrawn'  => 'bg-gray-100 text-gray-500',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $attColors[$att->status] ?? 'bg-gray-100 text-gray-500' }}">
                                            {{ ucfirst($att->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $att->score ? $att->score . '%' : '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($att->certificate_issued)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Issued</span>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3" x-data="{ updateModal: false }">
                                        <button @click="updateModal = true" class="text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded">Update</button>

                                        <div x-show="updateModal" x-cloak
                                             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                                            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6" @click.stop>
                                                <h3 class="text-base font-semibold text-gray-900 mb-4">Update Attendance</h3>
                                                <form action="{{ route('hr.training.attendance.update', $att) }}" method="POST" class="space-y-4">
                                                    @csrf @method('PATCH')
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                                        <select name="status" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                            @foreach (['enrolled','attending','completed','absent','withdrawn'] as $s)
                                                                <option value="{{ $s }}" {{ $att->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Score (%)</label>
                                                        <input type="number" name="score" value="{{ $att->score }}" min="0" max="100" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Completion Date</label>
                                                        <input type="date" name="completed_at" value="{{ $att->completed_at ? \Carbon\Carbon::parse($att->completed_at)->format('Y-m-d') : '' }}" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                    </div>
                                                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                                        <input type="hidden" name="certificate_issued" value="0">
                                                        <input type="checkbox" name="certificate_issued" value="1" {{ $att->certificate_issued ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                                                        Certificate Issued
                                                    </label>
                                                    <div class="flex justify-end gap-3">
                                                        <button type="button" @click="updateModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                                                        <button type="submit" class="btn-primary px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-400 text-sm">No attendance records found. Enroll staff in a training program to get started.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($attendances->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">{{ $attendances->links() }}</div>
                @endif
            </div>
        </div>

    </div>{{-- end Alpine --}}
</div>
@endsection
