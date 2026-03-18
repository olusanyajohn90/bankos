@extends('layouts.app')

@section('title', $reviewCycle->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <a href="{{ route('hr.performance.cycles.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Review Cycles
        </a>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $reviewCycle->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ \Carbon\Carbon::parse($reviewCycle->start_date)->format('d M Y') }} —
                    {{ \Carbon\Carbon::parse($reviewCycle->end_date)->format('d M Y') }}
                </p>
            </div>
            @php
                $statusBadge = match($reviewCycle->status) {
                    'draft'  => 'bg-gray-100 text-gray-600',
                    'active' => 'bg-green-100 text-green-800',
                    'closed' => 'bg-blue-100 text-blue-700',
                    default  => 'bg-gray-100 text-gray-600',
                };
            @endphp
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold {{ $statusBadge }}">
                {{ ucfirst($reviewCycle->status) }}
            </span>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Summary Strip --}}
    @php
        $allReviews       = $reviews->getCollection();
        $totalCount       = $reviews->total();
        $pendingCount     = $reviews->getCollection()->where('status', 'pending')->count();
        $selfAssessed     = $reviews->getCollection()->where('status', 'self_assessed')->count();
        $mgrReviewed      = $reviews->getCollection()->where('status', 'manager_reviewed')->count();
        $hrApproved       = $reviews->getCollection()->where('status', 'hr_approved')->count();
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        @foreach ([
            ['label' => 'Total',            'value' => $totalCount,    'color' => 'text-gray-800'],
            ['label' => 'Pending',          'value' => $pendingCount,  'color' => 'text-yellow-600'],
            ['label' => 'Self-Assessed',    'value' => $selfAssessed,  'color' => 'text-blue-600'],
            ['label' => 'Mgr Reviewed',     'value' => $mgrReviewed,   'color' => 'text-orange-600'],
            ['label' => 'HR Approved',      'value' => $hrApproved,    'color' => 'text-green-600'],
        ] as $stat)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold {{ $stat['color'] }}">{{ $stat['value'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $stat['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Reviews Table --}}
    <div class="card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Reviews ({{ $reviews->total() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Staff Name</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Reviewer</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($reviews as $review)
                        @php
                            $statusBadge = match($review->status) {
                                'pending'          => 'bg-yellow-100 text-yellow-800',
                                'self_assessed'    => 'bg-blue-100 text-blue-800',
                                'manager_reviewed' => 'bg-orange-100 text-orange-800',
                                'hr_approved'      => 'bg-green-100 text-green-800',
                                default            => 'bg-gray-100 text-gray-600',
                            };
                            $ratingBadge = match($review->rating ?? '') {
                                'exceptional'           => 'bg-purple-100 text-purple-800',
                                'exceeds_expectations'  => 'bg-blue-100 text-blue-800',
                                'meets_expectations'    => 'bg-green-100 text-green-800',
                                'below_expectations'    => 'bg-yellow-100 text-yellow-800',
                                'unsatisfactory'        => 'bg-red-100 text-red-700',
                                default                 => '',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ optional($review->staffProfile->user)->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-gray-600 text-xs">{{ optional($review->staffProfile->department)->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ optional($review->reviewer)->name ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge }}">
                                    {{ ucwords(str_replace('_', ' ', $review->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ $review->overall_score ? number_format($review->overall_score, 2) . ' / 5' : '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($review->rating && $ratingBadge)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ratingBadge }}">
                                        {{ ucwords(str_replace('_', ' ', $review->rating)) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('hr.performance.reviews.show', $review) }}"
                                   class="text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400 text-sm">No reviews found for this cycle.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reviews->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $reviews->links() }}</div>
        @endif
    </div>

</div>
@endsection
