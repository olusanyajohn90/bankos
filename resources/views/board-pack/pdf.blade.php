<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; background: white; }
.page { padding: 28px 36px 60px 36px; }

/* Typography */
h1  { font-size: 20px; font-weight: bold; color: #1e3a8a; }
h2  { font-size: 13px; font-weight: bold; color: #1e3a8a; margin-bottom: 10px;
      border-bottom: 2px solid #1e3a8a; padding-bottom: 5px; margin-top: 20px; }
h3  { font-size: 10px; font-weight: bold; color: #374151; margin: 8px 0 4px; }

/* Cover */
.cover-header { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
.cover-title  { font-size: 9px; color: #9ca3af; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 4px; }
.badge        { display: inline-block; background: #1e3a8a; color: white;
                padding: 3px 12px; border-radius: 10px; font-size: 8px; font-weight: bold; letter-spacing: 1px; }
.badge-red    { background: #dc2626; }
.period-box   { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px;
                padding: 8px 14px; display: inline-block; margin-bottom: 16px; }
.period-box p { font-size: 11px; font-weight: bold; color: #1e3a8a; }
.period-box small { font-size: 8px; color: #6b7280; }

/* KPI Cards (table-based) */
.kpi-table  { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
.kpi-table td { border: 1px solid #e5e7eb; padding: 8px 10px; width: 25%; vertical-align: top; }
.kpi-label  { color: #9ca3af; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.kpi-value  { font-size: 15px; font-weight: bold; color: #111827; }
.kpi-sub    { font-size: 8px; color: #6b7280; margin-top: 2px; }
.kpi-green  { color: #059669; }
.kpi-red    { color: #dc2626; }
.kpi-blue   { color: #2563eb; }

/* Data Tables */
.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
.data-table th { background: #1e3a8a; color: white; padding: 5px 8px; text-align: left;
                 font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
.data-table td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; font-size: 9px; vertical-align: middle; }
.data-table tr:nth-child(even) td { background: #f9fafb; }
.tr { text-align: right; }
.tc { text-align: center; }

/* Bar Charts (pure HTML/CSS — dompdf safe) */
.chart-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
.chart-table td { padding: 2px 4px; vertical-align: middle; font-size: 8px; }
.bar-bg    { background: #f3f4f6; height: 12px; width: 100%; }
.bar-fill  { background: #1e3a8a; height: 12px; }
.bar-fill-green  { background: #059669; height: 12px; }
.bar-fill-amber  { background: #d97706; height: 12px; }
.bar-fill-red    { background: #dc2626; height: 12px; }
.bar-fill-purple { background: #7c3aed; height: 12px; }

/* Summary rows */
.total-row td { background: #eff6ff !important; font-weight: bold; }

/* Dividers */
.section { margin-bottom: 16px; page-break-inside: avoid; }
.page-break { page-break-after: always; }
.divider { border: none; border-top: 1px solid #e5e7eb; margin: 14px 0; }

/* Footer */
.footer { position: fixed; bottom: 0; left: 36px; right: 36px;
          border-top: 1px solid #e5e7eb; padding: 6px 0; }
.footer table { width: 100%; }
.footer td    { font-size: 8px; color: #9ca3af; }

/* Trend section */
.trend-section { margin-bottom: 12px; }
.section-num   { display: inline-block; background: #1e3a8a; color: white;
                 font-size: 9px; font-weight: bold; padding: 2px 7px; border-radius: 10px; margin-right: 6px; }
</style>
</head>
<body>

<div class="page">

{{-- ═══════════════════════════════════════════════════
     COVER
═══════════════════════════════════════════════════ --}}
<table class="cover-header">
<tr>
<td style="vertical-align:top">
    <div class="cover-title">Board of Directors Report</div>
    <h1>{{ $tenant->name ?? 'Institution' }}</h1>
    <p style="font-size:11px;color:#374151;margin-top:3px;">{{ $tenant->address ?? '' }}</p>
    <div style="margin-top:10px;">
        <div class="period-box">
            <p>{{ $periodLabel }}</p>
            <small>{{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }}</small>
        </div>
    </div>
</td>
<td style="text-align:right;vertical-align:top">
    <p style="font-size:8px;color:#9ca3af;">Generated: {{ now()->format('d M Y, H:i') }}</p>
    <p style="font-size:8px;color:#9ca3af;margin-top:2px;">Prepared by: Management</p>
    <br>
    <span class="badge">CONFIDENTIAL</span>
</td>
</tr>
</table>

<hr class="divider">

@php $sn = 0; @endphp

{{-- ═══════════════════════════════════════════════════
     1. EXECUTIVE SUMMARY
═══════════════════════════════════════════════════ --}}
@if(in_array('executive_summary', $sections))
@php $sn++ @endphp
<div class="section">
<h2><span class="section-num">{{ $sn }}</span> Executive Summary</h2>

<table class="kpi-table">
<tr>
    <td>
        <div class="kpi-label">Total Deposit Book</div>
        <div class="kpi-value kpi-blue">₦{{ number_format($totalDeposits ?? 0, 0) }}</div>
        <div class="kpi-sub">All account types</div>
    </td>
    <td>
        <div class="kpi-label">Gross Loan Portfolio</div>
        <div class="kpi-value kpi-blue">₦{{ number_format($loanBook ?? 0, 0) }}</div>
        <div class="kpi-sub">Active + Overdue</div>
    </td>
    <td>
        <div class="kpi-label">NPL Ratio</div>
        <div class="kpi-value {{ ($nplRatio ?? 0) > 5 ? 'kpi-red' : 'kpi-green' }}">{{ number_format($nplRatio ?? 0, 2) }}%</div>
        <div class="kpi-sub">{{ ($nplRatio ?? 0) > 5 ? 'Above 5% threshold' : 'Within safe threshold' }}</div>
    </td>
    <td>
        <div class="kpi-label">Total Customers</div>
        <div class="kpi-value">{{ number_format($totalCustomers ?? 0) }}</div>
        <div class="kpi-sub">+{{ number_format($newCustomers ?? 0) }} this period</div>
    </td>
</tr>
<tr>
    <td>
        <div class="kpi-label">Loan Disbursements</div>
        <div class="kpi-value kpi-blue">₦{{ number_format($loanDisbursements ?? 0, 0) }}</div>
        <div class="kpi-sub">{{ number_format($newLoanCount ?? 0) }} loans issued</div>
    </td>
    <td>
        <div class="kpi-label">Repayment Collections</div>
        <div class="kpi-value kpi-green">₦{{ number_format($repaymentCollections ?? 0, 0) }}</div>
        <div class="kpi-sub">Period collections</div>
    </td>
    <td>
        <div class="kpi-label">Fee Revenue</div>
        <div class="kpi-value kpi-green">₦{{ number_format($feeRevenue ?? 0, 0) }}</div>
        <div class="kpi-sub">Period earnings</div>
    </td>
    <td>
        <div class="kpi-label">Net Interest Income</div>
        <div class="kpi-value {{ ($netProfit ?? 0) >= 0 ? 'kpi-green' : 'kpi-red' }}">₦{{ number_format($netProfit ?? 0, 0) }}</div>
        <div class="kpi-sub">Interest + fees</div>
    </td>
</tr>
</table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════
     2. BALANCE SHEET
═══════════════════════════════════════════════════ --}}
@if(in_array('balance_sheet', $sections))
@php $sn++ @endphp
<div class="section">
<h2><span class="section-num">{{ $sn }}</span> Balance Sheet Snapshot</h2>

<table class="kpi-table">
<tr>
    <td>
        <div class="kpi-label">Total Assets</div>
        <div class="kpi-value">₦{{ number_format($totalAssets ?? 0, 0) }}</div>
    </td>
    <td>
        <div class="kpi-label">Savings Deposits</div>
        <div class="kpi-value">₦{{ number_format($totalSavings ?? 0, 0) }}</div>
    </td>
    <td>
        <div class="kpi-label">Current Deposits</div>
        <div class="kpi-value">₦{{ number_format($totalCurrent ?? 0, 0) }}</div>
    </td>
    <td>
        <div class="kpi-label">Fixed Deposits</div>
        <div class="kpi-value">₦{{ number_format($totalFixed ?? 0, 0) }}</div>
    </td>
</tr>
</table>

@php
    $balanceTotal = ($totalSavings ?? 0) + ($totalCurrent ?? 0) + ($totalFixed ?? 0) ?: 1;
    $balanceItems = [
        ['label' => 'Savings', 'value' => $totalSavings ?? 0, 'color' => 'bar-fill'],
        ['label' => 'Current', 'value' => $totalCurrent ?? 0, 'color' => 'bar-fill-green'],
        ['label' => 'Fixed',   'value' => $totalFixed ?? 0,   'color' => 'bar-fill-amber'],
    ];
@endphp
<h3>Deposit Mix</h3>
<table class="chart-table">
@foreach($balanceItems as $item)
@php $pct = $balanceTotal > 0 ? min(100, round(($item['value'] / $balanceTotal) * 100)) : 0; @endphp
<tr>
    <td style="width:18%">{{ $item['label'] }}</td>
    <td style="width:62%"><div class="{{ $item['color'] }}" style="width:{{ $pct }}%">&nbsp;</div></td>
    <td style="width:12%;text-align:right">{{ $pct }}%</td>
    <td style="width:18%;text-align:right;color:#6b7280">₦{{ number_format($item['value'], 0) }}</td>
</tr>
@endforeach
</table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════
     3. LOAN PORTFOLIO
═══════════════════════════════════════════════════ --}}
@if(in_array('loan_portfolio', $sections) && !empty($loansByStatus))
@php $sn++ @endphp
<div class="section">
<h2><span class="section-num">{{ $sn }}</span> Loan Portfolio</h2>

@php
    $loanStatusMax = collect($loansByStatus)->max('total') ?: 1;
    $loanTotal     = collect($loansByStatus)->sum('total');
    $loanColors = ['active' => 'bar-fill-green', 'approved' => 'bar-fill', 'overdue' => 'bar-fill-red', 'closed' => 'bar-fill-amber'];
@endphp

<h3>Portfolio by Status</h3>
<table class="chart-table" style="margin-bottom:6px">
@foreach($loansByStatus as $row)
@php $pct = $loanStatusMax > 0 ? min(100, round(($row->total / $loanStatusMax) * 100)) : 0;
     $color = $loanColors[$row->status] ?? 'bar-fill'; @endphp
<tr>
    <td style="width:18%">{{ ucfirst($row->status) }}</td>
    <td style="width:42%"><div class="{{ $color }}" style="width:{{ $pct }}%">&nbsp;</div></td>
    <td style="width:10%;text-align:right">{{ number_format($row->count) }}</td>
    <td style="width:30%;text-align:right;color:#6b7280">₦{{ number_format($row->total, 0) }}</td>
</tr>
@endforeach
</table>

<table class="data-table">
<thead><tr><th>Status</th><th class="tr">Loans</th><th class="tr">Outstanding (₦)</th><th class="tr">Share</th></tr></thead>
<tbody>
@foreach($loansByStatus as $row)
<tr>
    <td>{{ ucfirst($row->status) }}</td>
    <td class="tr">{{ number_format($row->count) }}</td>
    <td class="tr">{{ number_format($row->total, 2) }}</td>
    <td class="tr">{{ $loanTotal > 0 ? number_format(($row->total / $loanTotal) * 100, 1) : 0 }}%</td>
</tr>
@endforeach
<tr class="total-row">
    <td>Total</td>
    <td class="tr">{{ number_format(collect($loansByStatus)->sum('count')) }}</td>
    <td class="tr">{{ number_format($loanTotal, 2) }}</td>
    <td class="tr">100%</td>
</tr>
</tbody>
</table>

<table class="kpi-table" style="margin-bottom:12px;">
<tr>
    <td>
        <div class="kpi-label">PAR 30+ (Portfolio at Risk)</div>
        <div class="kpi-value kpi-red">₦{{ number_format($par30 ?? 0, 0) }}</div>
        <div class="kpi-sub">Overdue outstanding balance</div>
    </td>
    <td>
        <div class="kpi-label">Loans Disbursed (Period)</div>
        <div class="kpi-value kpi-blue">{{ number_format($newLoanCount ?? 0) }} loans</div>
        <div class="kpi-sub">₦{{ number_format($loanDisbursements ?? 0, 0) }} disbursed</div>
    </td>
    <td colspan="2"></td>
</tr>
</table>

@if(!empty($loansByProduct) && $loansByProduct->isNotEmpty())
<h3>Portfolio by Product</h3>
@php $productMax = $loansByProduct->max('total') ?: 1; @endphp
<table class="chart-table">
@foreach($loansByProduct as $row)
@php $pct = min(100, round(($row->total / $productMax) * 100)); @endphp
<tr>
    <td style="width:30%">{{ $row->product_name }}</td>
    <td style="width:40%"><div class="bar-fill-purple" style="width:{{ $pct }}%">&nbsp;</div></td>
    <td style="width:10%;text-align:right">{{ number_format($row->count) }}</td>
    <td style="width:20%;text-align:right;color:#6b7280">₦{{ number_format($row->total, 0) }}</td>
</tr>
@endforeach
</table>
@endif

</div>
@endif

<div class="page-break"></div>

{{-- ═══════════════════════════════════════════════════
     4. DEPOSIT ANALYSIS
═══════════════════════════════════════════════════ --}}
@if(in_array('deposit_analysis', $sections) && !empty($depositsByType))
@php $sn++ @endphp
<div class="section">
<h2><span class="section-num">{{ $sn }}</span> Deposit Analysis</h2>

@php
    $depMax   = collect($depositsByType)->max('total') ?: 1;
    $depTotal = collect($depositsByType)->sum('total');
    $depColors = ['savings' => 'bar-fill', 'current' => 'bar-fill-green', 'fixed' => 'bar-fill-amber'];
@endphp
<h3>Deposits by Account Type</h3>
<table class="chart-table" style="margin-bottom:6px">
@foreach($depositsByType as $row)
@php $pct = min(100, round(($row->total / $depMax) * 100));
     $color = $depColors[$row->type] ?? 'bar-fill'; @endphp
<tr>
    <td style="width:18%">{{ ucfirst($row->type) }}</td>
    <td style="width:42%"><div class="{{ $color }}" style="width:{{ $pct }}%">&nbsp;</div></td>
    <td style="width:10%;text-align:right">{{ number_format($row->count) }}</td>
    <td style="width:30%;text-align:right;color:#6b7280">₦{{ number_format($row->total, 0) }}</td>
</tr>
@endforeach
</table>

<table class="data-table" style="margin-bottom:12px">
<thead><tr><th>Account Type</th><th class="tr">Accounts</th><th class="tr">Balance (₦)</th><th class="tr">Share</th></tr></thead>
<tbody>
@foreach($depositsByType as $row)
<tr>
    <td>{{ ucfirst($row->type) }}</td>
    <td class="tr">{{ number_format($row->count) }}</td>
    <td class="tr">{{ number_format($row->total, 2) }}</td>
    <td class="tr">{{ $depTotal > 0 ? number_format(($row->total / $depTotal) * 100, 1) : 0 }}%</td>
</tr>
@endforeach
</tbody>
</table>

@if(!empty($top10Depositors) && count($top10Depositors) > 0)
<h3>Top 10 Depositors</h3>
<table class="data-table">
<thead><tr><th>#</th><th>Account Name</th><th>Account No.</th><th>Type</th><th class="tr">Balance (₦)</th></tr></thead>
<tbody>
@foreach($top10Depositors as $i => $acc)
<tr>
    <td style="color:#9ca3af">{{ $i + 1 }}</td>
    <td>{{ $acc->account_name }}</td>
    <td style="font-family:monospace">{{ $acc->account_number }}</td>
    <td>{{ ucfirst($acc->type) }}</td>
    <td class="tr">{{ number_format($acc->ledger_balance, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
@endif

</div>
@endif

{{-- ═══════════════════════════════════════════════════
     5. TRANSACTION ACTIVITY
═══════════════════════════════════════════════════ --}}
@if(in_array('transaction_activity', $sections))
@php $sn++ @endphp
<div class="section">
<h2><span class="section-num">{{ $sn }}</span> Transaction Activity</h2>

<table class="kpi-table" style="margin-bottom:12px">
<tr>
    <td>
        <div class="kpi-label">Total Volume</div>
        <div class="kpi-value kpi-blue">₦{{ number_format($txnVolume ?? 0, 0) }}</div>
    </td>
    <td>
        <div class="kpi-label">Transaction Count</div>
        <div class="kpi-value">{{ number_format($txnCount ?? 0) }}</div>
    </td>
    <td>
        <div class="kpi-label">Daily Average (txns)</div>
        <div class="kpi-value">{{ number_format($dailyAvg ?? 0, 1) }}</div>
    </td>
    <td>
        <div class="kpi-label">Busiest Day</div>
        <div class="kpi-value">{{ $busiestDay ? \Carbon\Carbon::parse($busiestDay->txn_date)->format('d M') : '—' }}</div>
        <div class="kpi-sub">{{ $busiestDay ? number_format($busiestDay->cnt) . ' txns' : '' }}</div>
    </td>
</tr>
</table>

@if(!empty($txnByType) && $txnByType->isNotEmpty())
@php $txnMax = $txnByType->max('total') ?: 1; @endphp
<h3>Volume by Transaction Type</h3>
<table class="chart-table" style="margin-bottom:6px">
@foreach($txnByType as $row)
@php $pct = min(100, round(($row->total / $txnMax) * 100)); @endphp
<tr>
    <td style="width:25%">{{ ucfirst(str_replace('_', ' ', $row->type)) }}</td>
    <td style="width:45%"><div class="bar-fill" style="width:{{ $pct }}%">&nbsp;</div></td>
    <td style="width:10%;text-align:right">{{ number_format($row->count) }}</td>
    <td style="width:20%;text-align:right;color:#6b7280">₦{{ number_format($row->total, 0) }}</td>
</tr>
@endforeach
</table>

<table class="data-table">
<thead><tr><th>Type</th><th class="tr">Count</th><th class="tr">Volume (₦)</th><th class="tr">Avg (₦)</th></tr></thead>
<tbody>
@foreach($txnByType as $row)
<tr>
    <td>{{ ucfirst(str_replace('_', ' ', $row->type)) }}</td>
    <td class="tr">{{ number_format($row->count) }}</td>
    <td class="tr">{{ number_format($row->total, 2) }}</td>
    <td class="tr">{{ $row->count > 0 ? number_format($row->total / $row->count, 0) : '—' }}</td>
</tr>
@endforeach
</tbody>
</table>
@endif

</div>
@endif

<div class="page-break"></div>

{{-- ═══════════════════════════════════════════════════
     6. CUSTOMER GROWTH
═══════════════════════════════════════════════════ --}}
@if(in_array('customer_growth', $sections))
@php $sn++ @endphp
<div class="section">
<h2><span class="section-num">{{ $sn }}</span> Customer Growth</h2>

<table class="kpi-table" style="margin-bottom:12px">
<tr>
    <td>
        <div class="kpi-label">New Registrations</div>
        <div class="kpi-value kpi-green">+{{ number_format($newRegistrations ?? 0) }}</div>
        <div class="kpi-sub">In reporting period</div>
    </td>
    <td>
        <div class="kpi-label">Portal Activations</div>
        <div class="kpi-value">{{ number_format($portalActivations ?? 0) }}</div>
        <div class="kpi-sub">New self-service users</div>
    </td>
    <td>
        <div class="kpi-label">Total Customer Base</div>
        <div class="kpi-value kpi-blue">{{ number_format($totalCustomers ?? 0) }}</div>
        <div class="kpi-sub">All-time total</div>
    </td>
    <td>
        <div class="kpi-label">Digital Onboarding Rate</div>
        <div class="kpi-value">
            @if(($newRegistrations ?? 0) > 0)
                {{ round((($portalActivations ?? 0) / ($newRegistrations ?? 1)) * 100, 1) }}%
            @else —
            @endif
        </div>
        <div class="kpi-sub">Activations / new regs</div>
    </td>
</tr>
</table>

@if(!empty($kycDistribution) && $kycDistribution->isNotEmpty())
@php $kycMax = $kycDistribution->max('count') ?: 1; $kycTotal = $kycDistribution->sum('count'); @endphp
<h3>KYC Tier Distribution</h3>
<table class="chart-table" style="margin-bottom:6px">
@foreach($kycDistribution as $row)
@php $pct = min(100, round(($row->count / $kycMax) * 100)); @endphp
<tr>
    <td style="width:18%">Tier {{ $row->kyc_tier }}</td>
    <td style="width:52%"><div class="bar-fill" style="width:{{ $pct }}%">&nbsp;</div></td>
    <td style="width:10%;text-align:right">{{ number_format($row->count) }}</td>
    <td style="width:20%;text-align:right;color:#6b7280">{{ $kycTotal > 0 ? number_format(($row->count / $kycTotal) * 100, 1) : 0 }}%</td>
</tr>
@endforeach
</table>
<table class="data-table">
<thead><tr><th>KYC Tier</th><th class="tr">Customers</th><th class="tr">Share</th></tr></thead>
<tbody>
@foreach($kycDistribution as $row)
<tr>
    <td>Tier {{ $row->kyc_tier }}</td>
    <td class="tr">{{ number_format($row->count) }}</td>
    <td class="tr">{{ $kycTotal > 0 ? number_format(($row->count / $kycTotal) * 100, 1) : 0 }}%</td>
</tr>
@endforeach
</tbody>
</table>
@endif

</div>
@endif

{{-- ═══════════════════════════════════════════════════
     7. BRANCH PERFORMANCE
═══════════════════════════════════════════════════ --}}
@if(in_array('branch_performance', $sections) && !empty($branchStats) && $branchStats->isNotEmpty())
@php $sn++ @endphp
<div class="section">
<h2><span class="section-num">{{ $sn }}</span> Branch Performance</h2>

@php $branchMax = $branchStats->max('customer_count') ?: 1; @endphp
<h3>Customers by Branch</h3>
<table class="chart-table" style="margin-bottom:6px">
@foreach($branchStats as $branch)
@php $pct = min(100, round(($branch->customer_count / $branchMax) * 100)); @endphp
<tr>
    <td style="width:30%">{{ $branch->name }}</td>
    <td style="width:50%"><div class="bar-fill" style="width:{{ $pct }}%">&nbsp;</div></td>
    <td style="width:20%;text-align:right">{{ number_format($branch->customer_count) }}</td>
</tr>
@endforeach
</table>
<table class="data-table">
<thead><tr><th>Branch</th><th class="tr">Customers</th><th class="tr">Share</th></tr></thead>
<tbody>
@php $branchTotal = $branchStats->sum('customer_count') ?: 1; @endphp
@foreach($branchStats as $branch)
<tr>
    <td>{{ $branch->name }}</td>
    <td class="tr">{{ number_format($branch->customer_count) }}</td>
    <td class="tr">{{ number_format(($branch->customer_count / $branchTotal) * 100, 1) }}%</td>
</tr>
@endforeach
</tbody>
</table>
</div>
@endif

<div class="page-break"></div>

{{-- ═══════════════════════════════════════════════════
     8. COMPLIANCE & RISK
═══════════════════════════════════════════════════ --}}
@if(in_array('compliance_summary', $sections))
@php $sn++ @endphp
<div class="section">
<h2><span class="section-num">{{ $sn }}</span> Compliance &amp; Risk</h2>

<table class="kpi-table" style="margin-bottom:12px">
<tr>
    <td>
        <div class="kpi-label">Open AML Alerts</div>
        <div class="kpi-value {{ ($openAmlAlerts ?? 0) > 0 ? 'kpi-red' : 'kpi-green' }}">{{ $openAmlAlerts ?? 0 }}</div>
        <div class="kpi-sub">Requires attention</div>
    </td>
    <td>
        <div class="kpi-label">AML Alerts (Period)</div>
        <div class="kpi-value">{{ $amlInPeriod ?? 0 }}</div>
        <div class="kpi-sub">Generated this period</div>
    </td>
    <td>
        <div class="kpi-label">STR Reports Filed</div>
        <div class="kpi-value {{ ($strCount ?? 0) > 0 ? 'kpi-red' : '' }}">{{ $strCount ?? 0 }}</div>
        <div class="kpi-sub">Suspicious transactions</div>
    </td>
    <td>
        <div class="kpi-label">KYC Pending Review</div>
        <div class="kpi-value {{ ($kycPending ?? 0) > 0 ? 'kpi-amber' : 'kpi-green' }}">{{ $kycPending ?? 0 }}</div>
        <div class="kpi-sub">Manual review queue</div>
    </td>
</tr>
<tr>
    <td>
        <div class="kpi-label">Open Disputes</div>
        <div class="kpi-value {{ ($disputesOpen ?? 0) > 0 ? 'kpi-red' : 'kpi-green' }}">{{ $disputesOpen ?? 0 }}</div>
        <div class="kpi-sub">Awaiting resolution</div>
    </td>
    <td>
        <div class="kpi-label">Disputes Resolved</div>
        <div class="kpi-value kpi-green">{{ $disputesResolved ?? 0 }}</div>
        <div class="kpi-sub">Closed this period</div>
    </td>
    <td colspan="2"></td>
</tr>
</table>

@if(!empty($amlByType) && $amlByType->isNotEmpty())
@php $amlMax = $amlByType->max('count') ?: 1; @endphp
<h3>AML Alerts by Type (Period)</h3>
<table class="chart-table">
@foreach($amlByType as $row)
@php $pct = min(100, round(($row->count / $amlMax) * 100)); @endphp
<tr>
    <td style="width:35%">{{ ucwords(str_replace('_', ' ', $row->alert_type)) }}</td>
    <td style="width:45%"><div class="bar-fill-red" style="width:{{ $pct }}%">&nbsp;</div></td>
    <td style="width:20%;text-align:right">{{ number_format($row->count) }}</td>
</tr>
@endforeach
</table>
@endif

</div>
@endif

{{-- ═══════════════════════════════════════════════════
     9. MONTHLY TREND ANALYSIS
═══════════════════════════════════════════════════ --}}
@if(in_array('monthly_trends', $sections) && !empty($monthlyTrends) && count($monthlyTrends) > 1)
@php $sn++ @endphp
<div class="section">
<h2><span class="section-num">{{ $sn }}</span> Monthly Trend Analysis</h2>

@php
    $maxCustomers     = max(array_column($monthlyTrends, 'customers')) ?: 1;
    $maxTxnVolume     = max(array_column($monthlyTrends, 'txn_volume')) ?: 1;
    $maxDisbursements = max(array_column($monthlyTrends, 'disbursements')) ?: 1;
    $maxRepayments    = max(array_column($monthlyTrends, 'repayments')) ?: 1;
@endphp

<h3>New Customer Registrations per Month</h3>
<table class="chart-table" style="margin-bottom:10px">
@foreach($monthlyTrends as $t)
@php $pct = min(100, $maxCustomers > 0 ? round(($t['customers'] / $maxCustomers) * 100) : 0); @endphp
<tr>
    <td style="width:18%">{{ $t['label'] }}</td>
    <td style="width:62%"><div class="bar-fill-green" style="width:{{ max(1,$pct) }}%">&nbsp;</div></td>
    <td style="width:20%;text-align:right">{{ number_format($t['customers']) }} new</td>
</tr>
@endforeach
</table>

<h3>Transaction Volume per Month (₦)</h3>
<table class="chart-table" style="margin-bottom:10px">
@foreach($monthlyTrends as $t)
@php $pct = min(100, $maxTxnVolume > 0 ? round(($t['txn_volume'] / $maxTxnVolume) * 100) : 0); @endphp
<tr>
    <td style="width:18%">{{ $t['label'] }}</td>
    <td style="width:62%"><div class="bar-fill" style="width:{{ max(1,$pct) }}%">&nbsp;</div></td>
    <td style="width:20%;text-align:right">₦{{ number_format($t['txn_volume'], 0) }}</td>
</tr>
@endforeach
</table>

<h3>Loan Disbursements per Month (₦)</h3>
<table class="chart-table" style="margin-bottom:10px">
@foreach($monthlyTrends as $t)
@php $pct = min(100, $maxDisbursements > 0 ? round(($t['disbursements'] / $maxDisbursements) * 100) : 0); @endphp
<tr>
    <td style="width:18%">{{ $t['label'] }}</td>
    <td style="width:62%"><div class="bar-fill-purple" style="width:{{ max(1,$pct) }}%">&nbsp;</div></td>
    <td style="width:20%;text-align:right">₦{{ number_format($t['disbursements'], 0) }}</td>
</tr>
@endforeach
</table>

<h3>Repayment Collections per Month (₦)</h3>
<table class="chart-table" style="margin-bottom:10px">
@foreach($monthlyTrends as $t)
@php $pct = min(100, $maxRepayments > 0 ? round(($t['repayments'] / $maxRepayments) * 100) : 0); @endphp
<tr>
    <td style="width:18%">{{ $t['label'] }}</td>
    <td style="width:62%"><div class="bar-fill-amber" style="width:{{ max(1,$pct) }}%">&nbsp;</div></td>
    <td style="width:20%;text-align:right">₦{{ number_format($t['repayments'], 0) }}</td>
</tr>
@endforeach
</table>

{{-- Summary table --}}
<h3>Period Summary Table</h3>
<table class="data-table">
<thead>
<tr>
    <th>Month</th>
    <th class="tr">New Customers</th>
    <th class="tr">Txn Count</th>
    <th class="tr">Txn Volume (₦)</th>
    <th class="tr">Disbursements (₦)</th>
    <th class="tr">Collections (₦)</th>
</tr>
</thead>
<tbody>
@php $totC=0; $totT=0; $totV=0; $totD=0; $totR=0; @endphp
@foreach($monthlyTrends as $t)
@php $totC+=$t['customers']; $totT+=$t['txn_count']; $totV+=$t['txn_volume']; $totD+=$t['disbursements']; $totR+=$t['repayments']; @endphp
<tr>
    <td>{{ $t['label'] }}</td>
    <td class="tr">{{ number_format($t['customers']) }}</td>
    <td class="tr">{{ number_format($t['txn_count']) }}</td>
    <td class="tr">{{ number_format($t['txn_volume'], 0) }}</td>
    <td class="tr">{{ number_format($t['disbursements'], 0) }}</td>
    <td class="tr">{{ number_format($t['repayments'], 0) }}</td>
</tr>
@endforeach
<tr class="total-row">
    <td>Total</td>
    <td class="tr">{{ number_format($totC) }}</td>
    <td class="tr">{{ number_format($totT) }}</td>
    <td class="tr">{{ number_format($totV, 0) }}</td>
    <td class="tr">{{ number_format($totD, 0) }}</td>
    <td class="tr">{{ number_format($totR, 0) }}</td>
</tr>
</tbody>
</table>

</div>
@endif

</div>{{-- end .page --}}

<div class="footer">
<table>
<tr>
    <td>{{ $tenant->name ?? 'Institution' }} · Board Pack · {{ $periodLabel }}</td>
    <td style="text-align:center">CONFIDENTIAL — For Board Use Only</td>
    <td style="text-align:right">Generated {{ now()->format('d M Y') }}</td>
</tr>
</table>
</div>

</body>
</html>
