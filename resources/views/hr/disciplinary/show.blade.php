@extends('layouts.app')

@section('title', 'Case #' . $disciplinaryCase->case_number)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('hr.disciplinary.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Cases
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Case #{{ $disciplinaryCase->case_number }}</h1>
        </div>
        @php
            $statusBadge = match($disciplinaryCase->status) {
                'open'      => 'bg-blue-100 text-blue-800',
                'responded' => 'bg-yellow-100 text-yellow-800',
                'closed'    => 'bg-green-100 text-green-800',
                'appealed'  => 'bg-purple-100 text-purple-800',
                default     => 'bg-gray-100 text-gray-600',
            };
        @endphp
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold {{ $statusBadge }}">
            {{ ucfirst($disciplinaryCase->status) }}
        </span>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Case Header Card --}}
    <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Case Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 text-sm">
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Staff Member</p>
                <p class="font-semibold text-gray-900">{{ optional($disciplinaryCase->staffProfile->user)->name ?? '—' }}</p>
                @if ($disciplinaryCase->staffProfile->employee_number)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $disciplinaryCase->staffProfile->employee_number }}</p>
                @endif
            </div>
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Case Type</p>
                @php
                    $typeBadge = match($disciplinaryCase->type) {
                        'query'       => 'bg-yellow-100 text-yellow-800',
                        'warning'     => 'bg-orange-100 text-orange-800',
                        'suspension'  => 'bg-red-100 text-red-700',
                        'demotion'    => 'bg-purple-100 text-purple-800',
                        'termination' => 'bg-red-200 text-red-900',
                        default       => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeBadge }}">
                    {{ ucfirst($disciplinaryCase->type) }}
                </span>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Incident Date</p>
                <p class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($disciplinaryCase->incident_date)->format('d F Y') }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Raised By</p>
                <p class="font-medium text-gray-800">{{ optional($disciplinaryCase->raisedBy)->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Date Opened</p>
                <p class="font-medium text-gray-800">{{ $disciplinaryCase->created_at->format('d F Y') }}</p>
            </div>
        </div>

        <div class="mt-5 pt-5 border-t border-gray-100">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Description / Allegation</p>
            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $disciplinaryCase->description }}</p>
        </div>
    </div>

    {{-- Response Timeline --}}
    @if ($disciplinaryCase->responses->isNotEmpty())
    <div class="mb-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Response Timeline</h2>
        <div class="space-y-4">
            @foreach ($disciplinaryCase->responses as $response)
                <div class="relative pl-8">
                    <div class="absolute left-0 top-1.5 w-3 h-3 rounded-full bg-blue-500 ring-2 ring-white ring-offset-1"></div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-medium text-gray-400">
                                Staff Response — {{ \Carbon\Carbon::parse($response->responded_at)->format('d M Y, H:i') }}
                            </p>
                            @if ($response->outcome)
                                @php
                                    $outcomeBadge = match($response->outcome) {
                                        'no_action', 'cleared'  => 'bg-green-100 text-green-700',
                                        'warning_issued'        => 'bg-yellow-100 text-yellow-700',
                                        'suspended'             => 'bg-orange-100 text-orange-700',
                                        'dismissed'             => 'bg-red-100 text-red-700',
                                        default                 => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $outcomeBadge }}">
                                    {{ ucwords(str_replace('_', ' ', $response->outcome)) }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $response->staff_response }}</p>

                        @if ($response->decided_by)
                        <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-4 text-xs text-gray-500">
                            <span>Decision by: <strong class="text-gray-700">{{ optional($response->decidedBy)->name }}</strong></span>
                            @if ($response->decided_at)
                                <span>on {{ \Carbon\Carbon::parse($response->decided_at)->format('d M Y') }}</span>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Action Forms based on Status --}}
    @if (in_array($disciplinaryCase->status, ['open', 'awaiting_response']))
    <div class="card bg-white rounded-xl shadow-sm border border-blue-200 bg-blue-50/30 p-6 mb-6">
        <h3 class="text-base font-semibold text-gray-800 mb-3">Submit Staff Response</h3>
        <form action="{{ route('hr.disciplinary.respond', $disciplinaryCase) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Staff Response <span class="text-red-500">*</span></label>
                <textarea name="staff_response" rows="5" required
                          placeholder="Enter the staff member's response to the allegation..."
                          class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
            </div>
            <button type="submit" class="btn-primary px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                Record Response
            </button>
        </form>
    </div>
    @endif

    @if ($disciplinaryCase->status === 'responded')
    <div class="card bg-white rounded-xl shadow-sm border border-green-200 bg-green-50/30 p-6 mb-6">
        <h3 class="text-base font-semibold text-gray-800 mb-3">Close Case — Record Outcome</h3>
        <form action="{{ route('hr.disciplinary.close', $disciplinaryCase) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Outcome <span class="text-red-500">*</span></label>
                <select name="outcome" required class="form-input w-full sm:w-64 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">Select outcome...</option>
                    <option value="no_action">No Action Taken</option>
                    <option value="warning_issued">Warning Issued</option>
                    <option value="suspended">Suspension</option>
                    <option value="dismissed">Dismissal</option>
                    <option value="cleared">Cleared / Acquitted</option>
                </select>
            </div>
            <button type="submit" class="btn-primary px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700"
                    onclick="return confirm('Confirm case closure with selected outcome?')">
                Close Case
            </button>
        </form>
    </div>
    @endif

    @if ($disciplinaryCase->status === 'closed')
    <div class="card bg-white rounded-xl shadow-sm border border-purple-200 bg-purple-50/30 p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-2">Appeal</h3>
        <p class="text-sm text-gray-500 mb-3">If the staff member wishes to appeal the decision, record the appeal here.</p>
        <form action="{{ route('hr.disciplinary.appeal', $disciplinaryCase) }}" method="POST">
            @csrf
            <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700"
                    onclick="return confirm('Mark this case as appealed?')">
                Record Appeal
            </button>
        </form>
    </div>
    @endif

</div>
@endsection
