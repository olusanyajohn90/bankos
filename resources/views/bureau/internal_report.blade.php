@extends('layouts.app')

@section('title', $customer->full_name . ' — Internal Credit Report')

@section('content')
@php
    $score       = $internal['score'];
    $grade       = $internal['grade'];
    $gradeColor  = $internal['grade_color'];
    $gradeDesc   = $internal['grade_desc'];
    $factors     = $internal['factors'];
    $rec         = $internal['recommendation'];
    $consolidated= $internal['consolidated'];
    $accounts    = $consolidated['accounts'];
    $subject     = $consolidated['subject'];
    $sources     = $consolidated['bureau_sources'];

    // Score arc (300–850 → 0–283 path length)
    $arcLen = round(($score - 300) / 550 * 283);

    // Deduplicate + sanitise inquiries: skip entries whose institution contains digits
    // (artefact of FC right-to-left PDF column leakage) or are blank
    $rawInquiries = $consolidated['inquiries'];
    $seenInq = [];
    $inquiries = [];
    foreach ($rawInquiries as $inq) {
        $inst = trim($inq['institution'] ?? '');
        if (!$inst) continue;
        if (preg_match('/\d/', $inst)) continue; // skip garbage entries containing digits
        $key = strtolower($inst) . '|' . ($inq['date'] ?? '');
        if (isset($seenInq[$key])) continue;
        $seenInq[$key] = true;
        $inquiries[] = $inq;
    }

    // Grade colours (safe static tokens only)
    $gcText = match($gradeColor) {
        'emerald' => 'text-emerald-600', 'teal' => 'text-teal-600',
        'yellow'  => 'text-yellow-500',  'orange' => 'text-orange-600',
        default   => 'text-red-600'
    };
    $gcBg = match($gradeColor) {
        'emerald' => 'bg-emerald-500', 'teal' => 'bg-teal-500',
        'yellow'  => 'bg-yellow-400',  'orange' => 'bg-orange-500',
        default   => 'bg-red-500'
    };
    $gcBorder = match($gradeColor) {
        'emerald' => 'border-emerald-300', 'teal' => 'border-teal-300',
        'yellow'  => 'border-yellow-300',  'orange' => 'border-orange-300',
        default   => 'border-red-300'
    };

    // Decision colours
    $decBg = match($rec['decision']) {
        'APPROVE'       => 'bg-emerald-500',
        'DECLINE'       => 'bg-red-500',
        'MANUAL REVIEW' => 'bg-orange-500',
        default         => 'bg-yellow-500',
    };
    $decBorder = match($rec['decision']) {
        'APPROVE'       => 'border-emerald-200',
        'DECLINE'       => 'border-red-200',
        'MANUAL REVIEW' => 'border-orange-200',
        default         => 'border-yellow-200',
    };

    // Factor metadata
    $factorMeta = [
        'payment_history' => ['label' => 'Payment History',    'weight' => '35%', 'hex' => ''],
        'utilization'     => ['label' => 'Credit Utilization', 'weight' => '30%', 'hex' => ''],
        'history_length'  => ['label' => 'History Length',     'weight' => '15%', 'hex' => ''],
        'credit_mix'      => ['label' => 'Credit Mix',         'weight' => '10%', 'hex' => ''],
        'new_credit'      => ['label' => 'New Credit',         'weight' => '10%', 'hex' => ''],
    ];

    // Helper: score → hex colour
    $scoreHex = fn(int $s) => $s >= 80 ? '#10b981' : ($s >= 60 ? '#14b8a6' : ($s >= 40 ? '#eab308' : ($s >= 25 ? '#f97316' : '#ef4444')));
    $scoreTxt = fn(int $s) => $s >= 80 ? 'text-emerald-600' : ($s >= 60 ? 'text-teal-600' : ($s >= 40 ? 'text-yellow-500' : ($s >= 25 ? 'text-orange-600' : 'text-red-600')));
    $scoreLbl = fn(int $s) => $s >= 80 ? 'Excellent' : ($s >= 60 ? 'Good' : ($s >= 40 ? 'Fair' : ($s >= 25 ? 'Poor' : 'Very Poor')));
@endphp

<div class="space-y-6">

    {{-- ── Breadcrumb + header ──────────────────────────────────────────────── --}}
    <div>
        <nav class="text-xs text-gray-400 mb-2 flex items-center gap-1">
            <a href="{{ route('bureau.index') }}" class="hover:text-indigo-600">Credit Bureau</a>
            <span>/</span>
            <a href="{{ route('bureau.customer.reports', $customer) }}" class="hover:text-indigo-600">{{ $customer->full_name }}</a>
            <span>/</span>
            <span class="text-gray-600">Internal Credit Report</span>
        </nav>
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Internal Credit Report</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    Generated {{ now()->format('d M Y, H:i') }}
                    &middot; Sources: {{ implode(', ', array_map('strtoupper', $sources)) }}
                    &middot; {{ $reports->count() }} report(s)
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('bureau.customer.reports', $customer) }}" class="btn btn-secondary text-sm">← All Reports</a>
                <button onclick="window.print()" class="btn btn-secondary text-sm">Print</button>
            </div>
        </div>
    </div>

    {{-- ── Section 1: Score · Subject · Decision ──────────────────────────── --}}
    {{-- Row A: score (compact left) + subject (right) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Score gauge --}}
        <div class="card p-6 flex flex-col items-center justify-center text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Internal Credit Score</p>

            <div class="relative w-48 h-28 mx-auto">
                <svg viewBox="0 0 200 115" class="w-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="scoreGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%"   stop-color="#ef4444"/>
                            <stop offset="35%"  stop-color="#f97316"/>
                            <stop offset="60%"  stop-color="#eab308"/>
                            <stop offset="80%"  stop-color="#14b8a6"/>
                            <stop offset="100%" stop-color="#10b981"/>
                        </linearGradient>
                    </defs>
                    <path d="M 12 102 A 88 88 0 0 1 188 102" fill="none" stroke="#e5e7eb" stroke-width="16" stroke-linecap="round"/>
                    <path d="M 12 102 A 88 88 0 0 1 188 102" fill="none" stroke="url(#scoreGrad)" stroke-width="16"
                          stroke-linecap="round" stroke-dasharray="{{ $arcLen }} 276"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-end pb-0.5">
                    <span class="text-4xl font-black leading-none {{ $gcText }}">{{ $score }}</span>
                    <span class="text-xs text-gray-400 mt-0.5">300 — 850</span>
                </div>
            </div>

            <div class="mt-3 flex items-center gap-3">
                <span class="px-4 py-1 rounded-full text-sm font-bold {{ $gcBg }} text-white">{{ $grade }}</span>
                <span class="text-xs text-gray-500">{{ $gradeDesc }}</span>
            </div>

            {{-- Score band strip --}}
            <div class="mt-4 w-full flex rounded-full overflow-hidden h-2">
                @foreach([['#ef4444',52],['#f97316',18],['#eab308',18],['#14b8a6',18],['#10b981',18]] as [$c,$w])
                    <div style="width:{{ $w }}%;background:{{ $c }}"></div>
                @endforeach
            </div>
            <div class="mt-2 w-full flex justify-between text-xs text-gray-400 px-0.5">
                <span>300</span><span>Poor</span><span>Fair</span><span>Good</span><span>850</span>
            </div>

            {{-- Current band label --}}
            <div class="mt-3 text-xs font-semibold" style="color:{{ match($grade) {'Excellent'=>'#10b981','Good'=>'#14b8a6','Fair'=>'#eab308','Poor'=>'#f97316',default=>'#ef4444'} }}">
                Your score falls in the <strong>{{ $grade }}</strong> band ({{ match($grade) {'Excellent'=>'750–850','Good'=>'700–749','Fair'=>'650–699','Poor'=>'580–649',default=>'300–579'} }})
            </div>
        </div>

        {{-- Subject --}}
        <div class="card p-6">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Subject</p>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-black text-lg shrink-0">
                    {{ strtoupper(substr($customer->first_name,0,1).substr($customer->last_name,0,1)) }}
                </div>
                <div class="min-w-0">
                    <div class="font-bold text-gray-900 text-base leading-tight truncate">{{ $customer->full_name }}</div>
                    <div class="text-xs text-gray-400">{{ $customer->customer_number }}</div>
                </div>
            </div>
            <dl class="divide-y divide-gray-100 text-sm">
                @if($customer->bvn)
                    <div class="flex justify-between py-2 gap-4">
                        <dt class="text-gray-400 shrink-0">BVN</dt>
                        <dd class="font-mono text-gray-700 text-right">{{ $customer->bvn }}</dd>
                    </div>
                @endif
                @if($customer->phone)
                    <div class="flex justify-between py-2 gap-4">
                        <dt class="text-gray-400 shrink-0">Phone</dt>
                        <dd class="text-gray-700 text-right">{{ $customer->phone }}</dd>
                    </div>
                @endif
                @if($customer->date_of_birth)
                    <div class="flex justify-between py-2 gap-4">
                        <dt class="text-gray-400 shrink-0">Date of Birth</dt>
                        <dd class="text-gray-700 text-right">{{ $customer->date_of_birth->format('d M Y') }} <span class="text-gray-400">({{ $customer->date_of_birth->age }} yrs)</span></dd>
                    </div>
                @endif
                @if($customer->gender)
                    <div class="flex justify-between py-2 gap-4">
                        <dt class="text-gray-400 shrink-0">Gender</dt>
                        <dd class="text-gray-700 text-right">{{ ucfirst($customer->gender) }}</dd>
                    </div>
                @endif
                @if(!empty($subject['employer']))
                    <div class="flex justify-between py-2 gap-4">
                        <dt class="text-gray-400 shrink-0">Employer</dt>
                        <dd class="text-gray-700 text-right">{{ $subject['employer'] }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- Row B: Lending Decision — full width --}}
    <div class="card p-6 border-l-4 {{ $decBorder }}">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Lending Decision</p>
        <div class="flex items-start gap-6 flex-wrap">

            {{-- Decision badge --}}
            <div class="shrink-0 text-center" style="min-width:140px">
                <span class="inline-flex items-center px-5 py-2.5 rounded-xl font-black text-white text-lg tracking-wide {{ $decBg }}">
                    {{ $rec['decision'] }}
                </span>
                <div class="mt-2">
                    <span class="text-2xl font-black {{ $gcText }}">{{ $score }}</span>
                    <span class="text-xs text-gray-400 block">internal score</span>
                </div>
            </div>

            {{-- Divider --}}
            <div class="hidden md:block w-px self-stretch bg-gray-200 shrink-0"></div>

            {{-- Reason + actions --}}
            <div class="flex-1" style="min-width:220px">
                <div class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-3 mb-4">
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $rec['reason'] }}</p>
                </div>

                @if(!empty($rec['actions']))
                    @php
                        $iconPath  = $rec['decision'] === 'APPROVE' ? 'M5 13l4 4L19 7' : 'M9 5l7 7-7 7';
                        $iconColor = $rec['decision'] === 'APPROVE' ? '#10b981' : ($rec['decision'] === 'DECLINE' ? '#ef4444' : '#f97316');
                    @endphp
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Recommended Actions</p>
                    <ul class="space-y-2">
                        @foreach($rec['actions'] as $action)
                            <li class="flex items-start gap-2.5 text-sm text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                     fill="none" stroke="{{ $iconColor }}" stroke-width="2.5"
                                     stroke-linecap="round" stroke-linejoin="round"
                                     style="margin-top:3px;flex-shrink:0">
                                    <path d="{{ $iconPath }}"/>
                                </svg>
                                <span>{{ $action }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Section 2: Score Factor Breakdown (2 per row) ───────────────────── --}}
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-5">Score Factor Breakdown</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($factorMeta as $key => $meta)
                @php
                    $f       = $factors[$key] ?? ['score' => 0, 'label' => 'N/A', 'detail' => ''];
                    $fScore  = $f['score'];
                    $fHex    = $scoreHex($fScore);
                    $fTxt    = $scoreTxt($fScore);
                    $fLbl    = $f['label'] ?? $scoreLbl($fScore);
                    $fPct    = round($fScore * 2.51); // 100 → 251 circumference (r=40)
                @endphp
                <div class="flex items-start gap-5 rounded-xl border border-gray-200 bg-gray-50/60 p-4">

                    {{-- Circular dial --}}
                    <div class="relative w-20 h-20 shrink-0">
                        <svg viewBox="0 0 44 44" class="w-full h-full -rotate-90" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="22" cy="22" r="18" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                            <circle cx="22" cy="22" r="18" fill="none"
                                    stroke="{{ $fHex }}"
                                    stroke-width="4"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ $fPct }} 113"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-lg font-black leading-none" style="color:{{ $fHex }}">{{ $fScore }}</span>
                            <span class="text-xs text-gray-400">/100</span>
                        </div>
                    </div>

                    {{-- Text content --}}
                    <div class="flex-1 min-w-0 pt-1">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="font-semibold text-gray-900 text-sm">{{ $meta['label'] }}</span>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-200 text-gray-600">{{ $meta['weight'] }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-xs font-semibold {{ $fTxt }}">{{ $fLbl }}</span>
                        </div>
                        {{-- Progress bar --}}
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mb-2">
                            <div class="h-1.5 rounded-full" style="width:{{ $fScore }}%;background:{{ $fHex }}"></div>
                        </div>
                        <p class="text-xs text-gray-500 leading-relaxed">{{ $f['detail'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Weighted contribution bar --}}
        <div class="mt-5 pt-5 border-t border-gray-100">
            <p class="text-xs font-semibold text-gray-500 mb-3">Score Contribution by Factor</p>
            <div class="flex h-3 rounded-full overflow-hidden gap-0.5">
                @php
                    $weights  = [0.35, 0.30, 0.15, 0.10, 0.10];
                    $fKeys    = array_keys($factorMeta);
                    $fHexMap  = ['#10b981','#14b8a6','#6366f1','#f59e0b','#8b5cf6'];
                @endphp
                @foreach($fKeys as $i => $k)
                    @php $contrib = round(($factors[$k]['raw'] ?? 0) * $weights[$i] * 100, 1); @endphp
                    <div title="{{ $factorMeta[$k]['label'] }}: {{ $contrib }}%"
                         style="width:{{ $weights[$i]*100 }}%;background:{{ $fHexMap[$i] }};opacity:{{ 0.4 + ($factors[$k]['raw']??0)*0.6 }}"
                         class="rounded-sm"></div>
                @endforeach
            </div>
            <div class="flex mt-1.5 gap-4 flex-wrap">
                @foreach($fKeys as $i => $k)
                    <span class="text-xs text-gray-500 flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full inline-block shrink-0" style="background:{{ $fHexMap[$i] }}"></span>
                        {{ $factorMeta[$k]['label'] }} ({{ $factorMeta[$k]['weight'] }})
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Section 3: Consolidated accounts ───────────────────────────────── --}}
    <div class="card p-6">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h2 class="text-lg font-semibold text-gray-900">Consolidated Credit Facilities</h2>
            @php
                $perfCount = count(array_filter($accounts, fn($a) => ($a['status']??'') === 'performing'));
                $npCount   = count(array_filter($accounts, fn($a) => ($a['status']??'') === 'non_performing'));
                $clCount   = count(array_filter($accounts, fn($a) => ($a['status']??'') === 'closed'));
                $totalBal  = array_sum(array_column($accounts, 'outstanding_balance'));
                $totalLim  = array_sum(array_column($accounts, 'credit_limit'));
            @endphp
            <div class="flex gap-2 text-xs flex-wrap">
                @if($perfCount > 0)<span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full font-medium">{{ $perfCount }} Performing</span>@endif
                @if($npCount > 0)<span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-full font-medium">{{ $npCount }} Non-Performing</span>@endif
                @if($clCount > 0)<span class="px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full font-medium">{{ $clCount }} Closed</span>@endif
            </div>
        </div>

        @if(empty($accounts))
            <p class="text-sm text-gray-400 py-6 text-center">No accounts found across the parsed reports.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs">
                        <tr>
                            <th class="text-left px-3 py-2.5 font-semibold text-gray-500">Institution</th>
                            <th class="text-left px-3 py-2.5 font-semibold text-gray-500">Account No.</th>
                            <th class="text-left px-3 py-2.5 font-semibold text-gray-500">Type</th>
                            <th class="text-right px-3 py-2.5 font-semibold text-gray-500">Balance</th>
                            <th class="text-right px-3 py-2.5 font-semibold text-gray-500">Limit</th>
                            <th class="text-right px-3 py-2.5 font-semibold text-gray-500">DPD</th>
                            <th class="text-center px-3 py-2.5 font-semibold text-gray-500">Classification</th>
                            <th class="text-center px-3 py-2.5 font-semibold text-gray-500">Status</th>
                            <th class="text-left px-3 py-2.5 font-semibold text-gray-500">Opened</th>
                            <th class="text-left px-3 py-2.5 font-semibold text-gray-500">Last Payment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($accounts as $acct)
                            @php
                                $st  = $acct['status'] ?? 'performing';
                                $cls = strtoupper($acct['classification'] ?? '');
                                $rowBg = $st === 'non_performing' ? 'bg-red-50/50' : ($st === 'closed' ? 'bg-gray-50/70' : '');
                                $clsBadge = match(true) {
                                    in_array($cls,['LOSS','LOST']) => 'bg-red-100 text-red-800',
                                    $cls === 'DOUBTFUL'           => 'bg-orange-100 text-orange-800',
                                    $cls === 'SUBSTANDARD'        => 'bg-yellow-100 text-yellow-800',
                                    $cls === 'WATCHLIST'          => 'bg-blue-100 text-blue-800',
                                    $cls === 'PERFORMING'         => 'bg-green-100 text-green-800',
                                    default                       => 'bg-gray-100 text-gray-600',
                                };
                                $stBadge = match($st) {
                                    'non_performing' => 'bg-red-100 text-red-700',
                                    'closed'         => 'bg-gray-100 text-gray-600',
                                    default          => 'bg-green-100 text-green-700',
                                };
                            @endphp
                            <tr class="{{ $rowBg }} hover:bg-indigo-50/30 transition-colors">
                                <td class="px-3 py-3 font-medium text-gray-900">{{ $acct['institution'] ?? '—' }}</td>
                                <td class="px-3 py-3 font-mono text-xs text-gray-500">{{ $acct['account_number'] ?? '—' }}</td>
                                <td class="px-3 py-3 text-xs text-gray-500">{{ $acct['account_type'] ?? '—' }}</td>
                                <td class="px-3 py-3 text-right font-medium text-gray-800">
                                    {{ ($acct['outstanding_balance']??0) > 0 ? '₦'.number_format($acct['outstanding_balance'],2) : '—' }}
                                </td>
                                <td class="px-3 py-3 text-right text-gray-600">
                                    {{ ($acct['credit_limit']??0) > 0 ? '₦'.number_format($acct['credit_limit'],2) : '—' }}
                                </td>
                                <td class="px-3 py-3 text-right font-bold {{ ($acct['dpd']??0) > 0 ? 'text-red-600' : 'text-gray-300' }}">
                                    {{ ($acct['dpd']??0) > 0 ? $acct['dpd'].'d' : '—' }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($cls)<span class="px-2 py-0.5 rounded text-xs font-medium {{ $clsBadge }}">{{ $cls }}</span>@else<span class="text-gray-300">—</span>@endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $stBadge }}">
                                        {{ ucwords(str_replace('_',' ',$st)) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-500">{{ $acct['date_opened'] ?? '—' }}</td>
                                <td class="px-3 py-3 text-xs text-gray-500">{{ $acct['last_payment_date'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    @if($totalBal > 0 || $totalLim > 0)
                        <tfoot class="bg-gray-50 border-t-2 border-gray-200 text-sm font-semibold">
                            <tr>
                                <td colspan="3" class="px-3 py-2.5 text-gray-600">Totals — {{ count($accounts) }} account(s)</td>
                                <td class="px-3 py-2.5 text-right text-gray-900">₦{{ number_format($totalBal,2) }}</td>
                                <td class="px-3 py-2.5 text-right text-gray-600">₦{{ number_format($totalLim,2) }}</td>
                                <td colspan="5"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @endif
    </div>

    {{-- ── Section 4: Enquiry history ───────────────────────────────────────── --}}
    @if(!empty($inquiries))
    <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Enquiry History</h2>
            <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full font-medium">{{ count($inquiries) }} enquiry(ies)</span>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200 text-xs">
                <tr>
                    <th class="text-left px-3 py-2.5 font-semibold text-gray-500">Date</th>
                    <th class="text-left px-3 py-2.5 font-semibold text-gray-500">Institution</th>
                    <th class="text-left px-3 py-2.5 font-semibold text-gray-500">Reason</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($inquiries as $inq)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2.5 text-gray-500 text-xs whitespace-nowrap">{{ $inq['date'] ?: '—' }}</td>
                        <td class="px-3 py-2.5 font-medium text-gray-800">{{ $inq['institution'] }}</td>
                        <td class="px-3 py-2.5 text-gray-500 text-xs">{{ $inq['reason'] ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ── Section 5: CBN Risk checklist + Source reports ─────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- CBN checklist --}}
        <div class="card p-6 lg:col-span-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">CBN Risk & Compliance Checklist</h2>
            @php
                $allAccts = $consolidated['accounts'];
                $maxDpd   = empty($allAccts) ? 0 : max(array_column($allAccts,'dpd'));
                $lossAccts= count(array_filter($allAccts, fn($a) => in_array(strtoupper($a['classification']??''),['LOSS','LOST'])));
                $util2    = $totalLim > 0 ? round($totalBal/$totalLim*100,1) : 0;
                $inqCnt   = count($inquiries);
                $checks   = [
                    ['No active LOSS/LOST accounts',        $lossAccts === 0],
                    ['No accounts 90–179 DPD (Substandard)',        $maxDpd < 90],
                    ['No accounts 180+ days past due',              $maxDpd < 180],
                    ['Credit utilization below 50%',        $util2 < 50],
                    ['Credit utilization below 80%',        $util2 < 80],
                    ['Fewer than 3 enquiries (12 months)',  $inqCnt < 3],
                    ['Internal score ≥ 650 (Fair or above)',$score >= 650],
                    ['Internal score ≥ 700 (Good or above)',$score >= 700],
                ];
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($checks as $chk)
                    @if(count($chk) === 2)
                        @php [$clabel, $cpass] = $chk; @endphp
                        <div class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 {{ $cpass ? 'bg-green-50' : 'bg-red-50' }}">
                            @if($cpass)
                                <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-sm text-green-800">{{ $clabel }}</span>
                            @else
                                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span class="text-sm text-red-800">{{ $clabel }}</span>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Source reports --}}
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Source Reports</h2>
            <div class="space-y-2">
                @foreach($reports as $report)
                    @php
                        $bHex = $report->bureau === 'firstcentral' ? '#3b82f6' : ($report->bureau === 'crc' ? '#8b5cf6' : '#6b7280');
                        $date = $report->uploaded_at ?? $report->retrieved_at ?? $report->created_at;
                    @endphp
                    <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 text-white"
                             style="background:{{ $bHex }}">
                            {{ strtoupper(substr($report->bureau,0,3)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-medium text-gray-800 truncate">{{ $report->reference }}</div>
                            <div class="text-xs text-gray-400">{{ $date ? \Carbon\Carbon::parse($date)->format('d M Y') : '' }}</div>
                        </div>
                        <a href="{{ route('bureau.analytics', $report) }}" class="text-xs text-indigo-600 hover:text-indigo-800 shrink-0 font-medium">View →</a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Disclaimer --}}
    <p class="text-xs text-gray-400 border-t border-gray-200 pt-4">
        <strong>Disclaimer:</strong> This internal credit report is generated by {{ config('app.name') }} using data from
        {{ implode(' and ', array_map('strtoupper', $sources)) }} bureau(s). It is for internal credit decision support only
        and does not constitute a regulatory bureau report. All decisions remain the responsibility of the authorised credit officer.
        Score computed using FICO-standard factor weights adapted for the Nigerian lending environment.
    </p>

</div>
@endsection
