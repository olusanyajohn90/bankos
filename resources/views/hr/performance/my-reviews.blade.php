@extends('layouts.app')

@section('title', 'My Performance Reviews')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Performance Reviews</h1>
        <p class="text-sm text-gray-500 mt-1">Track your appraisals across all review cycles.</p>
    </div>

    @include('hr.org._tabs')

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    @if ($reviews->isEmpty())
        <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-500 text-sm">No performance reviews found for your profile.</p>
            <p class="text-gray-400 text-xs mt-1">Reviews are created by HR when a review cycle is activated.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($reviews as $review)
                @php
                    $statusBadge = match($review->status) {
                        'pending'          => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Pending'],
                        'self_assessed'    => ['bg' => 'bg-blue-100',   'text' => 'text-blue-800',   'label' => 'Self-Assessed'],
                        'manager_reviewed' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Manager Reviewed'],
                        'hr_approved'      => ['bg' => 'bg-green-100',  'text' => 'text-green-800',  'label' => 'HR Approved'],
                        default            => ['bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'label' => ucfirst($review->status)],
                    };
                    $ratingBadge = match($review->rating ?? '') {
                        'exceptional'           => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Exceptional'],
                        'exceeds_expectations'  => ['bg' => 'bg-blue-100',   'text' => 'text-blue-800',   'label' => 'Exceeds Expectations'],
                        'meets_expectations'    => ['bg' => 'bg-green-100',  'text' => 'text-green-800',  'label' => 'Meets Expectations'],
                        'below_expectations'    => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Below Expectations'],
                        'unsatisfactory'        => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'label' => 'Unsatisfactory'],
                        default                 => null,
                    };
                @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ optional($review->reviewCycle)->name }}</p>
                            @if ($review->reviewCycle)
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($review->reviewCycle->start_date)->format('M Y') }} —
                                    {{ \Carbon\Carbon::parse($review->reviewCycle->end_date)->format('M Y') }}
                                </p>
                            @endif
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }}">
                            {{ $statusBadge['label'] }}
                        </span>
                    </div>

                    @if ($review->overall_score)
                        <div class="mb-3">
                            <p class="text-xs text-gray-400 mb-1">Overall Score</p>
                            <div class="flex items-center gap-3">
                                <p class="text-2xl font-bold text-blue-700">{{ number_format($review->overall_score, 2) }}<span class="text-sm text-gray-400 font-normal"> /5</span></p>
                                <div class="flex-1">
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($review->overall_score / 5) * 100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($ratingBadge)
                        <div class="mb-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ratingBadge['bg'] }} {{ $ratingBadge['text'] }}">
                                {{ $ratingBadge['label'] }}
                            </span>
                        </div>
                    @endif

                    <div class="mt-auto pt-3 border-t border-gray-100 flex items-center justify-between">
                        <a href="{{ route('hr.performance.reviews.show', $review) }}"
                           class="text-xs font-medium text-blue-600 hover:text-blue-800">View Details</a>

                        @if ($review->status === 'pending')
                            <a href="{{ route('hr.performance.reviews.show', $review) }}"
                               class="text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-md">
                                Complete Self-Assessment
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
