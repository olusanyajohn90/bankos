@extends('layouts.app')
@section('title', 'Credit Report Analytics')

@section('content')
@php
    use Illuminate\Support\Str;

    $bureauLabel = match($bureauReport->bureau) {
        'firstcentral' => 'FirstCentral Credit Bureau',
        'crc'          => 'CRC / CreditRegistry',
        'xds'          => 'XDS / CreditInfo',
        default        => strtoupper($bureauReport->bureau),
    };

    $riskLevel  = $summary['risk_level'] ?? 'low';
    $maxDpd     = (int)($summary['max_dpd'] ?? 0);
    $worstStatus= $summary['worst_status'] ?? 'PERFORMING';
    $derogCount = (int)($summary['derogatory_count'] ?? 0);
    $totalAccts = (int)($summary['total_accounts'] ?? count($accounts));
    $activeAccts= (int)($summary['active_accounts'] ?? 0);
    $closedAccts= (int)($summary['closed_accounts'] ?? 0);
    $totalBal   = (float)($summary['total_balance'] ?? 0);
    $totalLimit = (float)($summary['total_credit_limit'] ?? 0);
    $utilization= (float)($summary['credit_utilization'] ?? 0);
    $totalOverdue=(float)($summary['total_overdue'] ?? 0);
    $inq12m     = (int)($summary['inquiries_12m'] ?? count($inquiries));
    $inq3m      = (int)($summary['inquiries_3m'] ?? 0);
    $creditScore= $summary['credit_score'] ?? null;

    $riskConfig = match($riskLevel) {
        'high'    => ['label'=>'HIGH RISK',     'bg'=>'bg-red-600',    'text'=>'text-white',       'ring'=>'ring-red-500',   'light'=>'bg-red-50',    'ltext'=>'text-red-700'],
        'medium'  => ['label'=>'MEDIUM RISK',   'bg'=>'bg-orange-500', 'text'=>'text-white',       'ring'=>'ring-orange-400','light'=>'bg-orange-50', 'ltext'=>'text-orange-700'],
        'caution' => ['label'=>'USE CAUTION',   'bg'=>'bg-yellow-400', 'text'=>'text-yellow-900',  'ring'=>'ring-yellow-400','light'=>'bg-yellow-50', 'ltext'=>'text-yellow-800'],
        default   => ['label'=>'LOW RISK',      'bg'=>'bg-green-500',  'text'=>'text-white',       'ring'=>'ring-green-500', 'light'=>'bg-green-50',  'ltext'=>'text-green-700'],
    };

    // CBN classification color helper
    $clsColor = function(string $cls) {
        return match(true) {
            in_array(strtoupper($cls), ['LOSS','LOST'])        => 'bg-red-100 text-red-800 border-red-200',
            strtoupper($cls) === 'DOUBTFUL'                    => 'bg-orange-100 text-orange-800 border-orange-200',
            in_array(strtoupper($cls), ['SUBSTANDARD','SUB-STANDARD']) => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            in_array(strtoupper($cls), ['WATCHLIST','WATCH LIST'])     => 'bg-blue-100 text-blue-800 border-blue-200',
            in_array(strtoupper($cls), ['CLOSED','PAID OFF (CLOSED)']) => 'bg-gray-100 text-gray-600 border-gray-200',
            default => 'bg-green-100 text-green-800 border-green-200',
        };
    };

    // Utilization color
    $utilColor = $utilization >= 80 ? 'text-red-600' : ($utilization >= 60 ? 'text-yellow-600' : 'text-green-600');
    $utilBarColor = $utilization >= 80 ? 'bg-red-500' : ($utilization >= 60 ? 'bg-yellow-400' : 'bg-green-500');

    // Chart data
    $chartPortfolio = [
        count($performing),
        count($nonPerforming),
        count($closed),
    ];
    $chartPerf = [
        'labels'  => ['Performing', 'Non-Performing', 'Closed'],
        'data'    => $chartPortfolio,
        'colors'  => ['#10b981', '#ef4444', '#9ca3af'],
    ];
@endphp

<div class="space-y-5">

{{-- ══════════════════════════════════════════════════════════════
     HEADER BAR
══════════════════════════════════════════════════════════════ --}}
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
            <a href="{{ route('bureau.index') }}" class="hover:text-bankos-primary transition-colors">Bureau Reports</a>
            <span>/</span>
            <span class="text-gray-600 font-medium">Credit Analytics</span>
        </div>
        <h1 class="text-xl font-bold text-gray-900 leading-tight">
            {{ !empty($subject['name']) ? $subject['name'] : ($bureauReport->customer?->full_name ?? 'Unknown Subject') }}
        </h1>
        <div class="flex flex-wrap items-center gap-3 mt-1 text-xs text-gray-400">
            @if(!empty($subject['bvn']))
            <span class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"/></svg>
                BVN: <span class="font-mono font-semibold text-gray-600">{{ $subject['bvn'] }}</span>
            </span>
            @endif
            @if(!empty($subject['date_of_birth']))
            <span>DOB: {{ $subject['date_of_birth'] }}{{ isset($subject['age']) ? ' (Age '.$subject['age'].')' : '' }}</span>
            @endif
            @if(!empty($subject['phone']))
            <span>{{ $subject['phone'] }}</span>
            @endif
            <span class="text-gray-300">|</span>
            <span>{{ $bureauLabel }}</span>
            <span>&middot; Ref: {{ $reportReference }}</span>
            <span>&middot; {{ $reportDate }}</span>
        </div>
    </div>
    <div class="flex items-center gap-3">
        @if($bureauReport->customer)
        <a href="{{ route('customers.show', $bureauReport->customer) }}"
           class="text-xs text-bankos-primary border border-bankos-primary/30 rounded-lg px-3 py-1.5 hover:bg-blue-50 transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            {{ $bureauReport->customer->full_name }}
        </a>
        @endif
        <span class="px-4 py-2 rounded-lg text-sm font-bold uppercase tracking-wider ring-2 {{ $riskConfig['bg'] }} {{ $riskConfig['text'] }} {{ $riskConfig['ring'] }}">
            {{ $riskConfig['label'] }}
        </span>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     CREDIT HEALTH OVERVIEW — 3 panels
══════════════════════════════════════════════════════════════ --}}
<div class="grid lg:grid-cols-3 gap-4">

    {{-- Panel 1: Credit Utilization --}}
    <div class="card p-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Credit Utilization</p>
        <div class="flex items-end justify-between mb-3">
            <div>
                <p class="text-4xl font-black {{ $utilColor }}">{{ $totalLimit > 0 ? $utilization.'%' : '—' }}</p>
                <p class="text-xs text-gray-400 mt-1">of total credit limit used</p>
            </div>
            <div class="text-right text-sm">
                <p class="font-bold text-gray-700">₦{{ number_format($totalBal, 0) }}</p>
                <p class="text-gray-400 text-xs">Balance</p>
                <p class="font-semibold text-gray-500 mt-1">₦{{ number_format($totalLimit, 0) }}</p>
                <p class="text-gray-400 text-xs">Total Limit</p>
            </div>
        </div>
        @if($totalLimit > 0)
        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
            <div class="{{ $utilBarColor }} h-3 rounded-full transition-all" style="width: {{ min($utilization, 100) }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-400 mt-1.5">
            <span>0%</span>
            <span class="{{ $utilization < 30 ? 'text-green-500 font-medium' : '' }}">30% ideal</span>
            <span class="{{ $utilization >= 80 ? 'text-red-500 font-medium' : '' }}">80% alert</span>
            <span>100%</span>
        </div>
        @else
        <p class="text-xs text-gray-400 italic">No credit limit data available</p>
        @endif
    </div>

    {{-- Panel 2: Portfolio Breakdown (Chart.js donut) --}}
    <div class="card p-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Portfolio Breakdown</p>
        @if($totalAccts > 0)
        <div class="flex items-center gap-4">
            <div class="relative w-28 h-28 flex-shrink-0">
                <canvas id="portfolioChart" width="112" height="112"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <p class="text-2xl font-black text-gray-800">{{ $totalAccts }}</p>
                    <p class="text-xs text-gray-400">accounts</p>
                </div>
            </div>
            <div class="space-y-2 text-sm flex-1">
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span><span class="text-gray-600">Performing</span></span>
                    <span class="font-bold text-gray-800">{{ count($performing) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span><span class="text-gray-600">Non-Performing</span></span>
                    <span class="font-bold text-gray-800">{{ count($nonPerforming) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span><span class="text-gray-600">Closed/Settled</span></span>
                    <span class="font-bold text-gray-800">{{ count($closed) }}</span>
                </div>
                @if(!empty($aggregateSummary))
                <div class="pt-2 border-t border-gray-100 text-xs text-gray-400 space-y-1">
                    @foreach($aggregateSummary as $agg)
                    @if(is_array($agg))
                    <div class="flex justify-between"><span>{{ $agg['type'] }}</span><span class="font-mono">{{ $agg['count'] }} acct</span></div>
                    @endif
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @else
        <p class="text-sm text-gray-400 italic mt-4">No account data extracted.</p>
        @endif
    </div>

    {{-- Panel 3: Credit Health Indicators --}}
    <div class="card p-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Credit Health Factors</p>
        @php
        $factors = [
            [
                'label'   => 'Payment History',
                'pass'    => $derogCount === 0 && $maxDpd === 0,
                'partial' => $maxDpd > 0 && $maxDpd < 90,
                'note'    => $derogCount === 0 ? ($maxDpd === 0 ? 'No delinquencies' : 'Max DPD: '.$maxDpd.' days') : $derogCount.' derogatory account(s)',
                'weight'  => 'Most Important',
            ],
            [
                'label'   => 'Credit Utilization',
                'pass'    => $utilization > 0 && $utilization <= 30,
                'partial' => $utilization > 30 && $utilization <= 60,
                'note'    => $totalLimit > 0 ? $utilization.'% utilization' : 'No limit data',
                'weight'  => 'High Impact',
            ],
            [
                'label'   => 'Account History',
                'pass'    => $totalAccts >= 2,
                'partial' => $totalAccts === 1,
                'note'    => $totalAccts.' total account(s) on file',
                'weight'  => 'Moderate',
            ],
            [
                'label'   => 'Credit Mix',
                'pass'    => count($aggregateSummary) >= 2,
                'partial' => count($aggregateSummary) === 1,
                'note'    => count($aggregateSummary).' credit type(s)',
                'weight'  => 'Low Impact',
            ],
            [
                'label'   => 'Recent Inquiries',
                'pass'    => $inq12m <= 2,
                'partial' => $inq12m > 2 && $inq12m <= 5,
                'note'    => $inq12m.' inquiry/inquiries (12m)',
                'weight'  => 'Low Impact',
            ],
        ];
        @endphp
        <div class="space-y-2.5">
        @foreach($factors as $f)
        @php
            $icon  = $f['pass'] ? 'text-green-500' : ($f['partial'] ? 'text-yellow-500' : 'text-red-400');
            $path  = $f['pass']
                ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                : ($f['partial']
                    ? 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
                    : 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z');
        @endphp
        <div class="flex items-start gap-2.5">
            <svg class="w-4 h-4 {{ $icon }} flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
            </svg>
            <div class="flex-1 min-w-0">
                <div class="flex justify-between items-center">
                    <p class="text-xs font-semibold text-gray-700">{{ $f['label'] }}</p>
                    <span class="text-[10px] text-gray-400">{{ $f['weight'] }}</span>
                </div>
                <p class="text-xs text-gray-400">{{ $f['note'] }}</p>
            </div>
        </div>
        @endforeach
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     PERFORMANCE SUMMARY (CBN classification table)
══════════════════════════════════════════════════════════════ --}}
@if(!empty($performanceSummary))
<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold text-gray-700 text-sm">Performance Summary <span class="text-gray-400 font-normal">(Last 84 months)</span></h2>
        <span class="text-xs text-gray-400">Source: {{ $bureauLabel }}</span>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-y divide-gray-100">
        @php
        $perfItems = [
            ['Open Accounts',        $performanceSummary['open_accounts']    ?? 0,  'blue'],
            ['Closed / Paid Off',    $performanceSummary['closed_accounts']  ?? 0,  'gray'],
            ['Performing',           $performanceSummary['performing']        ?? 0,  'green'],
            ['Delinquent < 30 days', $performanceSummary['delinquent_lt30']  ?? 0,  'yellow'],
            ['Delinquent 30-60 days',$performanceSummary['delinquent_30_60'] ?? 0,  'yellow'],
            ['Substandard (90 DPD)', $performanceSummary['derogatory_90']    ?? 0,  'orange'],
            ['Doubtful (180 DPD)',   $performanceSummary['derogatory_180']   ?? 0,  'red'],
            ['Loss / Charged-off',   ($performanceSummary['derogatory_360'] ?? 0) + ($performanceSummary['written_off'] ?? 0), 'red'],
            ['In Collection',        $performanceSummary['collection']       ?? 0,  'red'],
            ['In Litigation',        $performanceSummary['litigation']       ?? 0,  'red'],
            ['In Judgment',          $performanceSummary['judgment']         ?? 0,  'red'],
            ['Inquiries (12 months)',$performanceSummary['inquiries_12m']    ?? $inq12m, 'gray'],
        ];
        @endphp
        @foreach($perfItems as [$label, $val, $col])
        @php $alert = $val > 0 && in_array($col, ['red','orange','yellow']); @endphp
        <div class="px-4 py-3 flex items-center justify-between gap-2">
            <p class="text-xs text-gray-500">{{ $label }}</p>
            <p class="text-base font-bold {{ $alert ? 'text-'.$col.'-600' : 'text-gray-700' }}">{{ $val }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     TRADELINE CARDS
══════════════════════════════════════════════════════════════ --}}
@if(count($accounts) > 0)
<div>
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold text-gray-700">
            Credit Facilities
            <span class="text-gray-400 font-normal text-sm">({{ count($accounts) }} tradeline{{ count($accounts) !== 1 ? 's' : '' }})</span>
        </h2>
        <div class="flex gap-2 text-xs">
            @if(count($performing))   <span class="px-2.5 py-1 rounded-full bg-green-100 text-green-700 font-medium">{{ count($performing) }} Performing</span>@endif
            @if(count($nonPerforming))<span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700 font-medium">{{ count($nonPerforming) }} Non-Performing</span>@endif
            @if(count($closed))       <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 font-medium">{{ count($closed) }} Closed</span>@endif
        </div>
    </div>

    <div class="space-y-3">
    @foreach($accounts as $idx => $acct)
    @php
        $st       = $acct['status'] ?? 'performing';
        $cls      = strtoupper($acct['classification'] ?? ($st === 'closed' ? 'CLOSED' : 'PERFORMING'));
        $dpd      = (int)($acct['dpd'] ?? 0);
        $balance  = (float)($acct['outstanding_balance'] ?? 0);
        $limit    = (float)($acct['credit_limit'] ?? 0);
        $acctUtil = ($limit > 0) ? round($balance / $limit * 100, 1) : 0;
        $borderL  = $st === 'non_performing' ? 'border-l-4 border-l-red-400' : ($st === 'closed' ? 'border-l-4 border-l-gray-300' : 'border-l-4 border-l-green-400');
    @endphp
    <div class="card p-5 {{ $borderL }}">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
            <div class="flex items-start gap-3">
                {{-- Institution avatar --}}
                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white font-bold text-sm flex-shrink-0
                    {{ $st === 'non_performing' ? 'bg-red-500' : ($st === 'closed' ? 'bg-gray-400' : 'bg-bankos-primary') }}">
                    {{ strtoupper(substr($acct['institution'] ?? '?', 0, 1)) }}
                </div>
                <div>
                    <p class="font-bold text-gray-800">{{ $acct['institution'] ?? 'Unknown Lender' }}</p>
                    <div class="flex flex-wrap items-center gap-2 mt-0.5 text-xs text-gray-400">
                        @if(!empty($acct['account_number']))
                        <span class="font-mono">{{ $acct['account_number'] }}</span>
                        <span class="text-gray-300">·</span>
                        @endif
                        @if(!empty($acct['account_type']) && $acct['account_type'] !== 'NA')
                        <span>{{ $acct['account_type'] }}</span>
                        <span class="text-gray-300">·</span>
                        @endif
                        @if(!empty($acct['payment_cycle']) && $acct['payment_cycle'] !== 'NA')
                        <span>{{ ucfirst($acct['payment_cycle']) }} payments</span>
                        @endif
                        @if(!empty($acct['term']) && is_numeric($acct['term']))
                        <span class="text-gray-300">·</span>
                        <span>{{ $acct['term'] }}-month term</span>
                        @endif
                    </div>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded border text-xs font-semibold {{ $clsColor($cls) }}">
                {{ $cls }}
            </span>
        </div>

        {{-- Key metrics row --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
            <div>
                <p class="text-[10px] uppercase tracking-wider text-gray-400">Outstanding Balance</p>
                <p class="font-bold text-gray-800 text-sm mt-0.5">{{ $balance > 0 ? '₦'.number_format($balance, 2) : '—' }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wider text-gray-400">Credit Limit</p>
                <p class="font-semibold text-gray-700 text-sm mt-0.5">{{ $limit > 0 ? '₦'.number_format($limit, 0) : '—' }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wider text-gray-400">Last Payment</p>
                <p class="font-semibold text-gray-700 text-sm mt-0.5">
                    @if(($acct['last_payment'] ?? 0) > 0)
                        ₦{{ number_format($acct['last_payment'], 0) }}
                    @elseif(!empty($acct['last_payment_date']))
                        {{ $acct['last_payment_date'] }}
                    @else
                        —
                    @endif
                </p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wider text-gray-400">DPD (Days Past Due)</p>
                <p class="font-bold text-sm mt-0.5 {{ $dpd >= 90 ? 'text-red-600' : ($dpd >= 30 ? 'text-yellow-600' : 'text-green-600') }}">
                    {{ $dpd > 0 ? $dpd.' days' : '0 (Current)' }}
                </p>
            </div>
        </div>

        {{-- Utilization bar for open accounts --}}
        @if($limit > 0 && $st !== 'closed')
        <div class="mb-3">
            <div class="flex justify-between text-[10px] text-gray-400 mb-1">
                <span>Utilization: {{ $acctUtil }}%</span>
                <span>₦{{ number_format($balance, 0) }} / ₦{{ number_format($limit, 0) }}</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5">
                <div class="h-1.5 rounded-full {{ $acctUtil >= 80 ? 'bg-red-500' : ($acctUtil >= 60 ? 'bg-yellow-400' : 'bg-green-500') }}" style="width:{{ min($acctUtil,100) }}%"></div>
            </div>
        </div>
        @endif

        {{-- Timeline row --}}
        <div class="flex flex-wrap gap-x-5 gap-y-1 text-xs text-gray-400 border-t border-gray-100 pt-3">
            @if(!empty($acct['date_opened']))
            <span><span class="font-medium text-gray-500">Opened:</span> {{ $acct['date_opened'] }}</span>
            @endif
            @if(!empty($acct['date_last_updated']))
            <span><span class="font-medium text-gray-500">Last Updated:</span> {{ $acct['date_last_updated'] }}</span>
            @endif
            @if(!empty($acct['last_payment_date']))
            <span><span class="font-medium text-gray-500">Last Payment:</span> {{ $acct['last_payment_date'] }}</span>
            @endif
            @if(!empty($acct['account_status_date']))
            <span><span class="font-medium text-gray-500">Status Date:</span> {{ $acct['account_status_date'] }}</span>
            @endif
            @if(!empty($acct['legal_status']) && strtolower($acct['legal_status']) !== 'none')
            <span class="text-red-500 font-medium"><span class="font-medium">Legal Status:</span> {{ $acct['legal_status'] }}</span>
            @endif
        </div>
    </div>
    @endforeach
    </div>
</div>
@else
<div class="card p-10 text-center">
    <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    <p class="font-medium text-gray-500 mb-1">No tradelines could be extracted from this report.</p>
    <p class="text-sm text-gray-400">The PDF may be scanned/image-based. <a href="#raw-text" class="text-bankos-primary hover:underline">View raw text below.</a></p>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     INQUIRY HISTORY
══════════════════════════════════════════════════════════════ --}}
<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-gray-700 text-sm">Inquiry History</h2>
            <div class="flex gap-3 text-xs">
                <span class="px-2.5 py-1 rounded-full {{ $inq3m > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500' }} font-medium">{{ $inq3m }} last 3 months</span>
                <span class="px-2.5 py-1 rounded-full {{ $inq12m > 3 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500' }} font-medium">{{ $inq12m }} last 12 months</span>
                <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-500 font-medium">{{ $summary['inquiries_36m'] ?? count($inquiries) }} last 36 months</span>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-1">Multiple recent inquiries may indicate financial stress or active credit-seeking behaviour.</p>
    </div>
    @if(count($inquiries) > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold">Date</th>
                    <th class="px-5 py-3 text-left font-semibold">Reason</th>
                    <th class="px-5 py-3 text-left font-semibold">Institution</th>
                    <th class="px-5 py-3 text-left font-semibold">Contact</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($inquiries as $inq)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $inq['date'] ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">{{ $inq['reason'] ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-3 font-medium text-gray-700">{{ $inq['institution'] ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-400 font-mono text-xs">{{ $inq['phone'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="px-5 py-6 text-center text-sm text-gray-400">No inquiries recorded in this report.</div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════
     LENDING DECISION SUPPORT
══════════════════════════════════════════════════════════════ --}}
<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-700 text-sm">Lending Decision Support</h2>
    </div>
    <div class="p-5">
        <div class="grid md:grid-cols-2 gap-5">

            {{-- Decision panel --}}
            <div class="rounded-xl {{ $riskConfig['light'] }} {{ $riskConfig['ltext'] }} p-4 flex gap-3">
                @php
                $decisionIcon = match($riskLevel) {
                    'high'    => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                    'medium'  => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                    'caution' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                    default   => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                };
                $decision = match($riskLevel) {
                    'high'    => ['title'=>'Decline / Escalate to Board', 'body'=>'Subject carries significant credit risk. '.($derogCount > 0 ? $derogCount.' non-performing account(s) detected.' : '').' '.($maxDpd >= 90 ? 'Maximum DPD of '.$maxDpd.' days.' : '').' '.($worstStatus && !in_array($worstStatus,['PERFORMING','CLOSED']) ? 'Worst CBN classification: '.$worstStatus.'.' : '').' Do not approve without senior credit committee review and enhanced due diligence.'],
                    'medium'  => ['title'=>'Approve with Conditions', 'body'=>'Some risk indicators present. '.($maxDpd > 0 ? 'Max DPD: '.$maxDpd.' days. ' : '').'Consider requiring collateral, co-guarantor, or reduced exposure. Escalate to L2 approver. Monitor closely post-disbursement.'],
                    'caution' => ['title'=>'Proceed with Enhanced Monitoring', 'body'=>'Moderate signals detected. '.($utilization >= 60 ? 'Credit utilization at '.$utilization.'% — subject is relatively leveraged. ' : '').'Standard approval applies with a post-disbursement review at 90 days.'],
                    default   => ['title'=>'Eligible — Standard Processing', 'body'=>'Subject has a clean credit profile: '.$totalAccts.' account(s) on file, all performing or settled. No derogatory records. '.(count($inquiries) > 0 ? count($inquiries).' prior inquiry/inquiries. ' : 'No recent inquiries. ').'Proceed through normal credit approval workflow.'],
                };
                @endphp
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $decisionIcon }}"/>
                </svg>
                <div>
                    <p class="font-bold text-base">{{ $decision['title'] }}</p>
                    <p class="text-sm mt-1 opacity-90 leading-relaxed">{{ $decision['body'] }}</p>
                </div>
            </div>

            {{-- Risk factor checklist --}}
            <div class="space-y-2">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Risk Factor Checklist</p>
                @php
                $checks = [
                    ['label'=>'No loss/doubtful classification',  'pass'=> !in_array($worstStatus, ['LOSS','LOST','DOUBTFUL'])],
                    ['label'=>'No substandard accounts',          'pass'=> !in_array($worstStatus, ['SUBSTANDARD','SUB-STANDARD'])],
                    ['label'=>'No delinquency in 90+ DPD bucket', 'pass'=> ($performanceSummary['derogatory_90'] ?? 0) + ($performanceSummary['derogatory_120'] ?? 0) + ($performanceSummary['derogatory_150'] ?? 0) === 0],
                    ['label'=>'Utilization below 80%',            'pass'=> $totalLimit === 0 || $utilization < 80],
                    ['label'=>'≤ 3 credit inquiries (12 months)', 'pass'=> $inq12m <= 3],
                    ['label'=>'No accounts in collection/judgment','pass'=> ($performanceSummary['collection'] ?? 0) + ($performanceSummary['judgment'] ?? 0) + ($performanceSummary['litigation'] ?? 0) === 0],
                    ['label'=>'No accounts written off/charged-off','pass'=> ($performanceSummary['written_off'] ?? 0) + ($performanceSummary['derogatory_360'] ?? 0) === 0],
                ];
                @endphp
                @foreach($checks as $chk)
                <div class="flex items-center gap-2.5 text-sm">
                    @if($chk['pass'])
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-gray-600">{{ $chk['label'] }}</span>
                    @else
                    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span class="text-red-600 font-medium">{{ $chk['label'] }}</span>
                    @endif
                </div>
                @endforeach
            </div>

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     CROSS-BUREAU COMPARISON
══════════════════════════════════════════════════════════════ --}}
@if($relatedReports->count() > 0)
<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-700 text-sm">Cross-Bureau Comparison</h2>
        <p class="text-xs text-gray-400 mt-0.5">Multiple reports found for this customer. Discrepancies between bureaus may warrant further investigation.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold">Bureau</th>
                    <th class="px-5 py-3 text-center">Score</th>
                    <th class="px-5 py-3 text-center">Accounts</th>
                    <th class="px-5 py-3 text-right">Total Balance</th>
                    <th class="px-5 py-3 text-right">Total Limit</th>
                    <th class="px-5 py-3 text-center">Max DPD</th>
                    <th class="px-5 py-3 text-center">Worst Class.</th>
                    <th class="px-5 py-3 text-center">Risk</th>
                    <th class="px-5 py-3 text-center">Report Date</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                {{-- Current report highlighted --}}
                <tr class="bg-blue-50/60">
                    <td class="px-5 py-3 font-semibold text-bankos-primary">
                        {{ $bureauLabel }}
                        <span class="text-xs text-gray-400 font-normal ml-1">(this report)</span>
                    </td>
                    <td class="px-5 py-3 text-center font-bold">{{ $creditScore ?? '—' }}</td>
                    <td class="px-5 py-3 text-center">{{ $totalAccts }}</td>
                    <td class="px-5 py-3 text-right font-mono">₦{{ number_format($totalBal, 0) }}</td>
                    <td class="px-5 py-3 text-right font-mono text-gray-500">₦{{ number_format($totalLimit, 0) }}</td>
                    <td class="px-5 py-3 text-center {{ $maxDpd >= 90 ? 'text-red-600 font-bold' : ($maxDpd >= 30 ? 'text-yellow-600' : 'text-green-600') }}">{{ $maxDpd }}</td>
                    <td class="px-5 py-3 text-center"><span class="text-xs px-2 py-0.5 rounded border font-medium {{ $clsColor($worstStatus ?? 'PERFORMING') }}">{{ $worstStatus ?? '—' }}</span></td>
                    <td class="px-5 py-3 text-center"><span class="text-xs px-2 py-0.5 rounded font-bold {{ $riskConfig['bg'] }} {{ $riskConfig['text'] }}">{{ $riskConfig['label'] }}</span></td>
                    <td class="px-5 py-3 text-center text-gray-500 text-xs">{{ $reportDate }}</td>
                    <td></td>
                </tr>
                @foreach($relatedReports as $rel)
                @php
                    $relP = $rel->parsed_data ?? [];
                    $relS = $relP['summary'] ?? [];
                    $relRisk = $relS['risk_level'] ?? 'low';
                    $relRC = match($relRisk) { 'high'=>['bg-red-100 text-red-700'], 'medium'=>['bg-orange-100 text-orange-700'], 'caution'=>['bg-yellow-100 text-yellow-800'], default=>['bg-green-100 text-green-700'] };
                    $relWorst = $relS['worst_status'] ?? '—';
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 font-medium">{{ match($rel->bureau) { 'firstcentral'=>'FirstCentral','crc'=>'CRC/CreditRegistry','xds'=>'XDS/CreditInfo', default=>$rel->bureau } }}</td>
                    <td class="px-5 py-3 text-center">{{ $rel->credit_score ?? '—' }}</td>
                    <td class="px-5 py-3 text-center">{{ $relS['total_accounts'] ?? '—' }}</td>
                    <td class="px-5 py-3 text-right font-mono">₦{{ number_format($rel->total_outstanding, 0) }}</td>
                    <td class="px-5 py-3 text-right font-mono text-gray-500">₦{{ number_format($relS['total_credit_limit'] ?? 0, 0) }}</td>
                    <td class="px-5 py-3 text-center {{ ($relS['max_dpd']??0) >= 90 ? 'text-red-600 font-bold' : '' }}">{{ $relS['max_dpd'] ?? '—' }}</td>
                    <td class="px-5 py-3 text-center"><span class="text-xs px-2 py-0.5 rounded border font-medium {{ $clsColor($relWorst) }}">{{ $relWorst }}</span></td>
                    <td class="px-5 py-3 text-center"><span class="text-xs px-2 py-0.5 rounded font-medium {{ $relRC[0] }}">{{ strtoupper($relRisk) }}</span></td>
                    <td class="px-5 py-3 text-center text-gray-500 text-xs">{{ $rel->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3 text-right"><a href="{{ route('bureau.analytics', $rel) }}" class="text-bankos-primary text-xs hover:underline font-medium">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     RAW EXTRACTED TEXT (collapsible)
══════════════════════════════════════════════════════════════ --}}
@if($bureauReport->raw_text)
<div id="raw-text" x-data="{ open: false }" class="card overflow-hidden">
    <button @click="open = !open"
            class="w-full flex items-center justify-between px-5 py-4 text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors">
        <span class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            Raw Extracted Text
            <span class="text-gray-400 font-normal text-xs">({{ number_format(strlen($bureauReport->raw_text)) }} chars — for audit/verification)</span>
        </span>
        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" x-transition class="border-t border-gray-100">
        <pre class="px-5 py-4 text-xs text-gray-500 bg-gray-50 whitespace-pre-wrap max-h-96 overflow-y-auto font-mono leading-relaxed">{{ $bureauReport->raw_text }}</pre>
    </div>
</div>
@endif

{{-- Actions --}}
<div class="flex items-center justify-between pt-1">
    <a href="{{ route('bureau.index') }}" class="btn btn-secondary">← Back to Bureau Reports</a>
    <a href="{{ route('bureau.upload') }}" class="btn btn-primary">Upload Another Report</a>
</div>

</div>{{-- /space-y-5 --}}

{{-- Chart.js --}}
@if(count($accounts) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const ctx = document.getElementById('portfolioChart');
    if (!ctx) return;
    const data = @json($chartPerf);
    const nonZero = data.data.some(v => v > 0);
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: nonZero ? data.data : [1],
                backgroundColor: nonZero ? data.colors : ['#e5e7eb'],
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 4,
            }]
        },
        options: {
            cutout: '70%',
            plugins: { legend: { display: false }, tooltip: { enabled: nonZero } },
            animation: { duration: 600 },
        }
    });
})();
</script>
@endif
@endsection
