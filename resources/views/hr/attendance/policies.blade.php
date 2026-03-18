@extends('layouts.app')
@section('title', 'Attendance Policies')
@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('hr.attendance.index') }}" class="text-xs text-gray-400 hover:text-gray-600">← Back to Attendance</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Attendance Policies</h1>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ New Policy</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Create Attendance Policy</h2>
        <form action="{{ route('hr.attendance.policies.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div class="md:col-span-3"><label class="block text-xs text-gray-500 mb-1">Policy Name *</label>
                <input type="text" name="name" required class="form-input w-full text-sm" placeholder="e.g. Standard Office Hours"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Work Start Time *</label>
                <input type="time" name="work_start_time" value="08:00" required class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Work End Time *</label>
                <input type="time" name="work_end_time" value="17:00" required class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Grace Period (minutes)</label>
                <input type="number" name="grace_minutes" value="15" min="0" max="60" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Daily Work Hours</label>
                <input type="number" name="daily_work_hours" value="8" min="1" max="24" step="0.5" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Half Day Hours</label>
                <input type="number" name="half_day_hours" value="4" min="1" max="12" class="form-input w-full text-sm"></div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="allow_overtime" value="1" checked class="rounded">
                    Allow Overtime
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_default" value="1" class="rounded">
                    Set as Default
                </label>
            </div>
            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create Policy</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Policy</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Hours</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Grace</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Start</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">End</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Default</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($policies as $policy)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $policy->name }}</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $policy->daily_work_hours }}h/day</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $policy->grace_minutes }}min</td>
                        <td class="px-4 py-3 text-center font-mono text-gray-700">{{ substr($policy->work_start_time, 0, 5) }}</td>
                        <td class="px-4 py-3 text-center font-mono text-gray-700">{{ substr($policy->work_end_time, 0, 5) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($policy->is_default)
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-bold">Default</span>
                            @else <span class="text-gray-300">—</span>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No policies configured yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
