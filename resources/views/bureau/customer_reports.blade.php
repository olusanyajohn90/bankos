@extends('layouts.app')

@section('title', $customer->full_name . ' — Bureau Reports')

@section('content')
<div class="space-y-6">

    {{-- Breadcrumb + header --}}
    <div>
        <nav class="text-xs text-gray-400 mb-2">
            <a href="{{ route('bureau.index') }}" class="hover:text-indigo-600">Credit Bureau</a>
            <span class="mx-2">/</span>
            <span class="text-gray-600">{{ $customer->full_name }}</span>
        </nav>
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-lg shrink-0">
                    {{ strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $customer->full_name }}</h1>
                    <div class="flex items-center gap-4 text-sm text-gray-500 mt-0.5">
                        @if($customer->bvn)<span>BVN: <span class="font-mono">{{ $customer->bvn }}</span></span>@endif
                        @if($customer->phone)<span>{{ $customer->phone }}</span>@endif
                        @if($customer->customer_number)<span>{{ $customer->customer_number }}</span>@endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @if($reports->whereIn('status', ['parsed','retrieved'])->count() > 0)
                    <a href="{{ route('bureau.customer.internal', $customer) }}"
                       class="btn btn-primary flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Generate Internal Score
                    </a>
                @endif
                <a href="{{ route('bureau.upload') }}" class="btn btn-secondary flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Report
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    {{-- Summary stats --}}
    @php
        $parsed   = $reports->whereIn('status', ['parsed','retrieved']);
        $bureaus  = $reports->pluck('bureau')->unique()->filter();
        $topScore = $parsed->whereNotNull('credit_score')->sortByDesc('credit_score')->first()?->credit_score;
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="text-2xl font-bold text-gray-900">{{ $reports->count() }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Total Reports</div>
        </div>
        <div class="card p-4">
            <div class="text-2xl font-bold text-gray-900">{{ $parsed->count() }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Parsed / Retrieved</div>
        </div>
        <div class="card p-4">
            <div class="text-2xl font-bold text-gray-900">{{ $bureaus->count() }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Bureau Sources</div>
        </div>
        <div class="card p-4">
            @if($topScore)
                @php $sc = $topScore >= 700 ? 'text-emerald-600' : ($topScore >= 580 ? 'text-yellow-600' : 'text-red-600'); @endphp
                <div class="text-2xl font-bold {{ $sc }}">{{ $topScore }}</div>
                <div class="text-xs text-gray-500 mt-0.5">Best Bureau Score</div>
            @else
                <div class="text-2xl font-bold text-gray-400">—</div>
                <div class="text-xs text-gray-500 mt-0.5">Best Bureau Score</div>
            @endif
        </div>
    </div>

    {{-- Reports list --}}
    @if($reports->isEmpty())
        <div class="card p-12 text-center text-gray-400">
            <p class="font-medium">No bureau reports found for this customer.</p>
            <a href="{{ route('bureau.upload') }}" class="text-indigo-600 text-sm hover:underline mt-1 inline-block">Upload a PDF report</a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($reports as $report)
                @php
                    $bureauLabel = strtoupper($report->bureau);
                    $bureauColor = $report->bureau === 'firstcentral' ? 'blue' : ($report->bureau === 'crc' ? 'purple' : 'gray');
                    $statusColor = match($report->status) {
                        'parsed', 'retrieved' => 'green',
                        'uploaded'            => 'yellow',
                        default               => 'gray',
                    };
                    $date = $report->uploaded_at ?? $report->retrieved_at ?? $report->created_at;
                @endphp
                <div class="card p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            {{-- Bureau badge --}}
                            <div class="w-12 h-12 rounded-xl bg-{{ $bureauColor }}-100 text-{{ $bureauColor }}-700 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ $bureauLabel }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-gray-900">{{ $report->reference }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700">
                                        {{ ucfirst($report->status) }}
                                    </span>
                                    @if($report->original_filename)
                                        <span class="text-xs text-gray-400 truncate">{{ $report->original_filename }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-4 mt-1 text-sm text-gray-500">
                                    @if($report->credit_score)
                                        <span>Score: <strong class="text-gray-800">{{ $report->credit_score }}</strong></span>
                                    @endif
                                    @if($report->total_outstanding > 0)
                                        <span>Outstanding: <strong class="text-gray-800">₦{{ number_format($report->total_outstanding, 0) }}</strong></span>
                                    @endif
                                    @if($report->delinquency_count > 0)
                                        <span class="text-red-600">{{ $report->delinquency_count }} delinquency(ies)</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2 shrink-0">
                            <span class="text-xs text-gray-400">{{ $date ? \Carbon\Carbon::parse($date)->format('d M Y, H:i') : '' }}</span>
                            <div class="flex gap-2">
                                @if(in_array($report->status, ['parsed','retrieved']))
                                    <a href="{{ route('bureau.analytics', $report) }}"
                                       class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                        View Analytics
                                    </a>
                                @endif
                                <a href="{{ route('bureau.show', $report) }}"
                                   class="text-xs text-gray-500 hover:text-gray-700">
                                    Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
