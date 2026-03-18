@extends('layouts.app')

@section('title', 'KPI Setup — Staff Profiles')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">KPI Setup</h1>
        <p class="text-sm text-gray-500 mt-0.5">Assign staff to teams, set line managers, and manage referral codes</p>
    </div>

    @include('kpi.setup._tabs', ['active' => 'staff'])

    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    {{-- Add staff profile --}}
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Add Staff Profile</h2>
        <form method="POST" action="{{ route('kpi.staff.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">User <span class="text-red-500">*</span></label>
                <select name="user_id" class="form-input w-full" required>
                    <option value="">— Select User —</option>
                    @foreach($managers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Department</label>
                <input type="text" name="department" class="form-input w-full" placeholder="credit, operations…">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Job Title</label>
                <input type="text" name="job_title" class="form-input w-full">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Branch</label>
                <select name="branch_id" class="form-input w-full">
                    <option value="">— Branch —</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Line Manager</label>
                <select name="manager_id" class="form-input w-full">
                    <option value="">— Manager —</option>
                    @foreach($managers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Team</label>
                <select name="team_id" class="form-input w-full">
                    <option value="">— Team —</option>
                    @foreach($teams as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Employment Type</label>
                <select name="employment_type" class="form-input w-full">
                    <option value="full_time">Full Time</option>
                    <option value="contract">Contract</option>
                    <option value="intern">Intern</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary">Add Profile</button>
            </div>
        </form>
    </div>

    {{-- Staff profiles table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200 text-xs">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500">Staff</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500">Department</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500">Branch</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500">Team</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500">Manager</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-500">Referral Code</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-500">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($profiles as $profile)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $profile->user?->name ?? '—' }}</div>
                            <div class="text-xs text-gray-400">{{ $profile->job_title }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $profile->department ?: '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $profile->branch?->name ?: '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $profile->team?->name ?: '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $profile->manager?->name ?: '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <code class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded">{{ $profile->referral_code ?: '—' }}</code>
                                @if($profile->referral_code)
                                    <form method="POST" action="{{ route('kpi.staff.regen-code', $profile) }}" class="inline">
                                        @csrf
                                        <button title="Regenerate code" class="text-xs text-gray-400 hover:text-indigo-600">↻</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $profile->status === 'active' ? 'bg-green-100 text-green-700' : ($profile->status === 'suspended' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ ucfirst($profile->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('kpi.me') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View KPIs</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                            No staff profiles configured. Add profiles above to enable individual KPI tracking.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $profiles->links() }}</div>
</div>
@endsection
