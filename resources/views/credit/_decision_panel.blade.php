@php
    $decision = $loan->creditDecision ?? \App\Models\CreditDecision::where('loan_id', $loan->id)->first();
@endphp

@if($decision)
@php
    $score    = $decision->final_score;
    $minScore = 300;
    $maxScore = 850;
    $pct      = $score ? round((($score - $minScore) / ($maxScore - $minScore)) * 100) : 0;

    $scoreColor = match(true) {
        $score >= 750 => 'bg-emerald-500',
        $score >= 700 => 'bg-teal-500',
        $score >= 650 => 'bg-yellow-400',
        $score >= 580 => 'bg-orange-400',
        default       => 'bg-red-500',
    };

    $recBadge = match($decision->recommendation) {
        'approve'     => 'bg-green-100 text-green-800 border-green-200',
        'conditional' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'refer'       => 'bg-amber-100 text-amber-800 border-amber-200',
        'decline'     => 'bg-red-100 text-red-800 border-red-200',
        default       => 'bg-gray-100 text-gray-600 border-gray-200',
    };

    $recLabel = match($decision->recommendation) {
        'approve'     => 'Approve',
        'conditional' => 'Conditional Approval',
        'refer'       => 'Refer for Review',
        'decline'     => 'Decline',
        default       => ucfirst($decision->recommendation),
    };

    $passedCount = count($decision->rules_passed ?? []);
    $failedCount = count($decision->rules_failed ?? []);
@endphp

<div class="card p-5" id="credit-decision-panel">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="font-bold text-sm uppercase tracking-wider text-bankos-text-sec">Credit Decision</h3>
            @if($decision->auto_decided)
                <p class="text-xs text-bankos-muted mt-0.5">Automated decision &middot; {{ $decision->created_at->format('d M Y H:i') }}</p>
            @else
                <p class="text-xs text-bankos-muted mt-0.5">Decision recorded &middot; {{ $decision->created_at->format('d M Y H:i') }}</p>
            @endif
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $recBadge }}">
            {{ $recLabel }}
        </span>
    </div>

    {{-- Score Gauge --}}
    @if($score)
    <div class="mb-4">
        <div class="flex items-end justify-between mb-1.5">
            <span class="text-xs font-medium text-bankos-text-sec">Credit Score</span>
            <span class="text-2xl font-extrabold tracking-tight {{ str_replace('bg-', 'text-', $scoreColor) }}">{{ $score }}</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
            <div class="{{ $scoreColor }} h-3 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
        </div>
        <div class="flex justify-between text-[10px] text-bankos-muted mt-1">
            <span>300</span>
            <span>500</span>
            <span>650</span>
            <span>750</span>
            <span>850</span>
        </div>
    </div>
    @endif

    {{-- Score Breakdown --}}
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="text-center rounded-lg bg-gray-50 dark:bg-bankos-dark-bg/30 p-2">
            <p class="text-[10px] uppercase font-bold text-bankos-text-sec mb-0.5">Internal</p>
            <p class="font-bold text-sm">{{ $decision->internal_score ?? '—' }}</p>
        </div>
        <div class="text-center rounded-lg bg-gray-50 dark:bg-bankos-dark-bg/30 p-2">
            <p class="text-[10px] uppercase font-bold text-bankos-text-sec mb-0.5">Bureau</p>
            <p class="font-bold text-sm">{{ $decision->bureau_score ?? '—' }}</p>
        </div>
        <div class="text-center rounded-lg bg-gray-50 dark:bg-bankos-dark-bg/30 p-2">
            <p class="text-[10px] uppercase font-bold text-bankos-text-sec mb-0.5">Final</p>
            <p class="font-bold text-sm {{ $score ? str_replace('bg-', 'text-', $scoreColor) : '' }}">{{ $score ?? '—' }}</p>
        </div>
    </div>

    {{-- Rules Summary --}}
    <div class="flex gap-3 mb-4">
        <div class="flex-1 flex items-center gap-2 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 px-3 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="text-green-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <div>
                <p class="text-xs font-bold text-green-700 dark:text-green-400">{{ $passedCount }} passed</p>
                <p class="text-[10px] text-green-600 dark:text-green-500">rules</p>
            </div>
        </div>
        <div class="flex-1 flex items-center gap-2 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 px-3 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="text-red-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            <div>
                <p class="text-xs font-bold text-red-700 dark:text-red-400">{{ $failedCount }} failed</p>
                <p class="text-[10px] text-red-600 dark:text-red-500">rules</p>
            </div>
        </div>
    </div>

    {{-- Failed Rules Detail --}}
    @if($failedCount > 0)
    <div class="mb-4 space-y-1.5">
        <p class="text-xs font-semibold text-bankos-text-sec">Failed Rules</p>
        @foreach(array_slice($decision->rules_failed ?? [], 0, 4) as $failed)
        <div class="flex items-start gap-2 text-xs rounded bg-red-50 dark:bg-red-900/10 px-3 py-1.5 border border-red-100 dark:border-red-800">
            <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $failed['severity'] === 'hard' ? 'bg-red-600 text-white' : 'bg-gray-200 hover:bg-gray-300 text-gray-800' }} flex-shrink-0 mt-0.5">
                {{ $failed['severity'] }}
            </span>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-red-800 dark:text-red-300">{{ str_replace('_', ' ', ucwords($failed['rule_type'], '_')) }}</p>
                <p class="text-red-600 dark:text-red-400 text-[10px] truncate">{{ $failed['detail'] }}</p>
            </div>
        </div>
        @endforeach
        @if($failedCount > 4)
            <p class="text-[10px] text-bankos-muted pl-1">+ {{ $failedCount - 4 }} more failed rule(s)</p>
        @endif
    </div>
    @endif

    {{-- Conditions --}}
    @if(!empty($decision->conditions))
    <div class="mb-4 space-y-1.5">
        <p class="text-xs font-semibold text-bankos-text-sec">Conditions</p>
        @foreach($decision->conditions as $condition)
        <div class="flex items-center gap-2 text-xs rounded bg-yellow-50 dark:bg-yellow-900/10 px-3 py-1.5 border border-yellow-200 dark:border-yellow-700 text-yellow-800 dark:text-yellow-300">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ $condition['description'] }}
        </div>
        @endforeach
    </div>
    @endif

    {{-- Notes --}}
    @if($decision->notes)
    <p class="text-xs text-bankos-muted mb-4 italic leading-relaxed">{{ $decision->notes }}</p>
    @endif

    {{-- Re-evaluate Button --}}
    @if(\Illuminate\Support\Facades\Route::has('credit.evaluate'))
    <form action="{{ route('credit.evaluate', $loan) }}" method="POST" x-data="{ loading: false }">
        @csrf
        <button type="submit" @click="loading = true" :disabled="loading"
            class="w-full btn btn-secondary text-xs py-2 flex items-center justify-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="loading ? 'animate-spin' : ''"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            <span x-text="loading ? 'Evaluating...' : 'Re-evaluate Credit'"></span>
        </button>
    </form>
    @endif
</div>
@else
{{-- No decision yet --}}
<div class="card p-5">
    <h3 class="font-bold text-sm uppercase tracking-wider text-bankos-text-sec mb-3">Credit Decision</h3>
    <p class="text-sm text-bankos-muted mb-4">No credit decision has been recorded for this loan yet.</p>
    @if(\Illuminate\Support\Facades\Route::has('credit.evaluate'))
    <form action="{{ route('credit.evaluate', $loan) }}" method="POST" x-data="{ loading: false }">
        @csrf
        <button type="submit" @click="loading = true" :disabled="loading"
            class="w-full btn btn-primary text-xs py-2 flex items-center justify-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="loading ? 'animate-spin' : ''"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <span x-text="loading ? 'Evaluating...' : 'Run Credit Evaluation'"></span>
        </button>
    </form>
    @endif
</div>
@endif
