@extends('layouts.app')
@section('title', 'Public Holidays')
@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Public Holidays</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage bank and public holidays for attendance tracking</p>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Add Holiday</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    {{-- Year switcher --}}
    <div class="flex gap-2 flex-wrap">
        @foreach($years as $y)
            <a href="{{ route('hr.holidays.index', ['year' => $y]) }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $y == $year ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $y }}
            </a>
        @endforeach
    </div>

    {{-- Add form --}}
    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Add Holiday for {{ $year }}</h2>
        <form action="{{ route('hr.holidays.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div class="md:col-span-2"><label class="block text-xs text-gray-500 mb-1">Holiday Name *</label>
                <input type="text" name="name" required class="form-input w-full text-sm" placeholder="e.g. Independence Day"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Date *</label>
                <input type="date" name="date" required class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Type</label>
                <select name="type" class="form-input w-full text-sm">
                    <option value="national">National</option>
                    <option value="religious">Religious</option>
                    <option value="state">State</option>
                    <option value="company">Company</option>
                </select></div>
            <div class="flex items-center gap-2 pt-5">
                <input type="checkbox" name="is_recurring" id="recurring" value="1" checked class="rounded border-gray-300">
                <label for="recurring" class="text-sm text-gray-600">Recurring annually</label>
            </div>
            <div><label class="block text-xs text-gray-500 mb-1">Notes</label>
                <input type="text" name="notes" class="form-input w-full text-sm" placeholder="Optional notes"></div>
            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Add Holiday</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Calendar grid summary --}}
    <div class="card overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">{{ $year }} — {{ $holidays->count() }} holidays</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Day</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Recurring</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Active</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($holidays as $h)
                    @php $isPast = $h->date->isPast(); @endphp
                    <tr class="hover:bg-gray-50 {{ ! $h->is_active ? 'opacity-50' : '' }}">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $h->name }}
                            @if($h->notes)<p class="text-xs text-gray-400">{{ $h->notes }}</p>@endif
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $h->date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $h->date->format('l') }}</td>
                        <td class="px-4 py-3">
                            @php $tc = ['national'=>'bg-blue-100 text-blue-700','religious'=>'bg-purple-100 text-purple-700','state'=>'bg-amber-100 text-amber-700','company'=>'bg-green-100 text-green-700'][$h->type] ?? 'bg-gray-100 text-gray-500'; @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $tc }}">{{ ucfirst($h->type) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $h->is_recurring ? '✓' : '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $h->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                                {{ $h->is_active ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <form action="{{ route('hr.holidays.toggle', $h) }}" method="POST">
                                    @csrf
                                    <button class="text-xs text-gray-500 hover:text-gray-700">{{ $h->is_active ? 'Disable' : 'Enable' }}</button>
                                </form>
                                <form action="{{ route('hr.holidays.destroy', $h) }}" method="POST" onsubmit="return confirm('Delete this holiday?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-500 hover:text-red-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No holidays defined for {{ $year }}. Add some above.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
