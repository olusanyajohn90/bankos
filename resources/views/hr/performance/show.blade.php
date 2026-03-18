@extends('layouts.app')

@section('title', 'Performance Review — ' . optional($performanceReview->staffProfile->user)->name)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Breadcrumb --}}
    <div class="mb-6">
        <a href="{{ route('hr.performance.cycles.show', $performanceReview->reviewCycle) }}"
           class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ optional($performanceReview->reviewCycle)->name }}
        </a>
        <h1 class="text-2xl font-bold text-gray-900">
            Performance Review — {{ optional($performanceReview->staffProfile->user)->name }}
        </h1>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    @php
        $statusBadge = match($performanceReview->status) {
            'pending'          => 'bg-yellow-100 text-yellow-800',
            'self_assessed'    => 'bg-blue-100 text-blue-800',
            'manager_reviewed' => 'bg-orange-100 text-orange-800',
            'hr_approved'      => 'bg-green-100 text-green-800',
            default            => 'bg-gray-100 text-gray-600',
        };
        $ratingBadge = match($performanceReview->rating ?? '') {
            'exceptional'           => 'bg-purple-100 text-purple-800',
            'exceeds_expectations'  => 'bg-blue-100 text-blue-800',
            'meets_expectations'    => 'bg-green-100 text-green-800',
            'below_expectations'    => 'bg-yellow-100 text-yellow-800',
            'unsatisfactory'        => 'bg-red-100 text-red-700',
            default                 => '',
        };
        $isStaff    = optional($performanceReview->staffProfile)->user_id === auth()->id();
        $isReviewer = $performanceReview->reviewer_id === auth()->id();
        $isHr       = auth()->user()->hasRole('hr') || auth()->user()->hasRole('admin');
    @endphp

    {{-- Review Header Card --}}
    <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-5">
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Staff Member</p>
                <p class="font-semibold text-gray-900">{{ optional($performanceReview->staffProfile->user)->name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $performanceReview->staffProfile->employee_number }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Reviewer</p>
                <p class="font-medium text-gray-800">{{ optional($performanceReview->reviewer)->name ?? 'Not assigned' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Cycle</p>
                <p class="font-medium text-gray-800">{{ optional($performanceReview->reviewCycle)->name }}</p>
            </div>
            <div class="flex flex-col gap-2">
                <span class="inline-flex items-center self-start px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge }}">
                    {{ ucwords(str_replace('_', ' ', $performanceReview->status)) }}
                </span>
                @if ($performanceReview->rating && $ratingBadge)
                    <span class="inline-flex items-center self-start px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ratingBadge }}">
                        {{ ucwords(str_replace('_', ' ', $performanceReview->rating)) }}
                    </span>
                @endif
            </div>
        </div>

        @if ($performanceReview->overall_score)
            <div class="mt-5 pt-5 border-t border-gray-100 flex items-center gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Overall Score</p>
                    <p class="text-3xl font-bold text-blue-700">{{ number_format($performanceReview->overall_score, 2) }}<span class="text-sm text-gray-400 font-normal"> / 5.00</span></p>
                </div>
                <div class="flex-1 max-w-xs">
                    <div class="w-full bg-gray-100 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full" style="width: {{ ($performanceReview->overall_score / 5) * 100 }}%"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Criteria Table --}}
    <div class="card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Evaluation Criteria</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Criterion</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Weight (%)</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Self Score (/5)</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Manager Score (/5)</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Target</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Achievement Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($performanceReview->items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $item->criterion }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $item->weight }}%</td>
                            <td class="px-6 py-4">
                                @if (!is_null($item->self_score))
                                    <span class="font-semibold text-blue-700">{{ number_format($item->self_score, 1) }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if (!is_null($item->manager_score))
                                    <span class="font-semibold text-orange-700">{{ number_format($item->manager_score, 1) }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs max-w-xs">{{ $item->target_description ?? '—' }}</td>
                            <td class="px-6 py-4 text-gray-500 text-xs max-w-xs">{{ $item->achievement_notes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Comments Section --}}
    @if ($performanceReview->staff_comments || $performanceReview->manager_comments)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        @if ($performanceReview->staff_comments)
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-2">Staff Comments</p>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $performanceReview->staff_comments }}</p>
            @if ($performanceReview->submitted_at)
                <p class="text-xs text-gray-400 mt-2">Submitted: {{ $performanceReview->submitted_at->format('d M Y H:i') }}</p>
            @endif
        </div>
        @endif
        @if ($performanceReview->manager_comments)
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-5">
            <p class="text-xs font-semibold text-orange-600 uppercase tracking-wider mb-2">Manager Comments</p>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $performanceReview->manager_comments }}</p>
            @if ($performanceReview->reviewed_at)
                <p class="text-xs text-gray-400 mt-2">Reviewed: {{ $performanceReview->reviewed_at->format('d M Y H:i') }}</p>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- ══════ SELF ASSESSMENT FORM ══════ --}}
    @if ($performanceReview->status === 'pending' && $isStaff)
    <div class="card bg-white rounded-xl shadow-sm border border-blue-200 bg-blue-50/20 p-6 mb-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Self Assessment</h3>
        <p class="text-sm text-gray-500 mb-5">Rate yourself on each criterion. Scores are from 1 (poor) to 5 (excellent).</p>
        <form action="{{ route('hr.performance.reviews.self-assess', $performanceReview) }}" method="POST">
            @csrf
            <div class="space-y-4 mb-5">
                @foreach ($performanceReview->items as $item)
                <div class="flex items-center justify-between bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">{{ $item->criterion }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Weight: {{ $item->weight }}%</p>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        <label class="text-xs text-gray-500">Score (1–5):</label>
                        <input type="number" name="scores[{{ $item->id }}]" min="1" max="5" step="0.5" required
                               class="form-input w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-center">
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Staff Comments <span class="text-red-500">*</span></label>
                <textarea name="staff_comments" rows="4" required placeholder="Share your reflections on your performance this period..."
                          class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
            </div>
            <button type="submit" class="btn-primary px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                Submit Self Assessment
            </button>
        </form>
    </div>
    @endif

    {{-- ══════ MANAGER REVIEW FORM ══════ --}}
    @if ($performanceReview->status === 'self_assessed' && $isReviewer)
    <div class="card bg-white rounded-xl shadow-sm border border-orange-200 bg-orange-50/20 p-6 mb-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Manager Review</h3>
        <p class="text-sm text-gray-500 mb-5">Review the self-assessment and provide your scores for each criterion.</p>
        <form action="{{ route('hr.performance.reviews.manager-review', $performanceReview) }}" method="POST">
            @csrf
            <div class="space-y-4 mb-5">
                @foreach ($performanceReview->items as $item)
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-medium text-gray-800">{{ $item->criterion }}</p>
                        <span class="text-xs text-gray-400">Weight: {{ $item->weight }}%</span>
                    </div>
                    @if (!is_null($item->self_score))
                        <p class="text-xs text-blue-600 mb-2">Staff self-score: <strong>{{ number_format($item->self_score, 1) }}</strong></p>
                    @endif
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-500">Manager Score (1–5):</label>
                        <input type="number" name="scores[{{ $item->id }}]" min="1" max="5" step="0.5" required
                               value="{{ $item->self_score }}"
                               class="form-input w-20 rounded-md border-gray-300 shadow-sm focus:border-orange-400 focus:ring-orange-400 text-sm text-center">
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Manager Comments <span class="text-red-500">*</span></label>
                <textarea name="manager_comments" rows="4" required placeholder="Provide your assessment and feedback..."
                          class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-orange-400 focus:ring-orange-400 text-sm"></textarea>
            </div>
            <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-orange-600 rounded-md hover:bg-orange-700">
                Submit Manager Review
            </button>
        </form>
    </div>
    @endif

    {{-- ══════ HR APPROVE ══════ --}}
    @if ($performanceReview->status === 'manager_reviewed' && $isHr)
    <div class="card bg-white rounded-xl shadow-sm border border-green-200 bg-green-50/20 p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-2">HR Approval</h3>
        <p class="text-sm text-gray-500 mb-3">Approve this performance review to finalise the appraisal process.</p>
        <form action="{{ route('hr.performance.reviews.hr-approve', $performanceReview) }}" method="POST">
            @csrf
            <button type="submit" class="btn-primary px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700"
                    onclick="return confirm('Approve and finalise this performance review?')">
                Approve Review
            </button>
        </form>
    </div>
    @endif

</div>
@endsection
