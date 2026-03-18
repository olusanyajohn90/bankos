@extends('layouts.app')
@section('title', 'SLA Policies')
@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">SLA Policies</h1>
            <p class="text-sm text-gray-500 mt-0.5">Define response and resolution time targets per ticket priority.</p>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ New Policy</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    {{-- New Policy Form --}}
    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">New SLA Policy</h2>
        <form action="{{ route('support.sla.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="lg:col-span-2"><label class="block text-xs text-gray-500 mb-1">Policy Name *</label>
                    <input type="text" name="name" required class="form-input w-full text-sm" placeholder="e.g. Critical Priority SLA"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Priority *</label>
                    <select name="priority" required class="form-input w-full text-sm">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select></div>
                <div><label class="block text-xs text-gray-500 mb-1">Response (minutes) *</label>
                    <input type="number" name="response_minutes" required min="5" class="form-input w-full text-sm" placeholder="60"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Resolution (minutes) *</label>
                    <input type="number" name="resolution_minutes" required min="15" class="form-input w-full text-sm" placeholder="480"></div>
            </div>
            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="business_hours_only" value="1" checked class="rounded border-gray-300"> Business hours only
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" class="rounded border-gray-300"> Set as default
                </label>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create Policy</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Policies Table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Policy</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Priority</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">First Response</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Resolution</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Scope</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($policies as $p)
                @php
                    $pc = match($p->priority) {
                        'critical' => 'bg-red-100 text-red-700',
                        'high'     => 'bg-orange-100 text-orange-700',
                        'medium'   => 'bg-yellow-100 text-yellow-700',
                        default    => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <tr class="hover:bg-gray-50" x-data="{ editMode: false }">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-900">{{ $p->name }}</span>
                            @if($p->is_default)<span class="px-1.5 py-0.5 rounded text-xs bg-blue-100 text-blue-700 font-medium">Default</span>@endif
                        </div>
                    </td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $pc }}">{{ ucfirst($p->priority) }}</span></td>
                    <td class="px-4 py-3 text-gray-700">{{ $p->response_label }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $p->resolution_label }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $p->business_hours_only ? 'Business hours' : '24/7' }}</td>
                    <td class="px-4 py-3 text-right">
                        <button @click="editMode = !editMode" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                    </td>
                </tr>
                <tr x-show="editMode" x-transition x-data="{ editMode: false }" class="bg-blue-50">
                    <td colspan="6" class="px-4 py-4">
                        <form action="{{ route('support.sla.update', $p) }}" method="POST" class="grid grid-cols-2 md:grid-cols-5 gap-3">
                            @csrf @method('PATCH')
                            <input type="text" name="name" value="{{ $p->name }}" required class="form-input text-sm col-span-2 md:col-span-1" placeholder="Name">
                            <input type="number" name="response_minutes" value="{{ $p->response_minutes }}" required class="form-input text-sm" placeholder="Response min">
                            <input type="number" name="resolution_minutes" value="{{ $p->resolution_minutes }}" required class="form-input text-sm" placeholder="Resolution min">
                            <div class="flex items-center gap-3 text-sm text-gray-600">
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="checkbox" name="business_hours_only" value="1" {{ $p->business_hours_only ? 'checked' : '' }} class="rounded"> Biz hrs
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="checkbox" name="is_default" value="1" {{ $p->is_default ? 'checked' : '' }} class="rounded"> Default
                                </label>
                            </div>
                            <button type="submit" class="btn text-sm bg-blue-600 text-white px-3 py-1.5 rounded-lg">Save</button>
                        </form>
                    </td>
                </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No SLA policies defined yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Guide --}}
    <div class="card p-5 bg-blue-50 border border-blue-100">
        <h3 class="text-sm font-semibold text-blue-800 mb-2">How SLA Policies Work</h3>
        <ul class="text-xs text-blue-700 space-y-1 list-disc list-inside">
            <li>When a ticket is created, the SLA policy matching its priority is automatically applied.</li>
            <li><strong>First Response</strong> — time allowed before the first agent reply; breached if no reply within this window.</li>
            <li><strong>Resolution</strong> — total time from ticket creation to resolution.</li>
            <li>Business hours only excludes nights and weekends from countdown.</li>
            <li>The <strong>Default</strong> policy is applied if no priority-matched policy exists.</li>
        </ul>
    </div>

</div>
@endsection
