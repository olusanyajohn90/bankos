<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; font-size: 9pt; color: #1a1a1a; }

/* ── Header ── */
.header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; border-bottom: 2px solid #1d4ed8; padding-bottom: 10px; }
.bank-name { font-size: 16pt; font-weight: bold; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.5px; }
.bank-sub  { font-size: 8pt; color: #6b7280; margin-top: 2px; }
.title-block { text-align: right; }
.title-block h2 { font-size: 12pt; font-weight: bold; color: #111827; }
.title-block p  { font-size: 8pt; color: #6b7280; margin-top: 3px; }

/* ── Account Info Grid ── */
.info-grid { display: flex; gap: 0; margin: 10px 0; border: 1px solid #e5e7eb; border-radius: 6px; overflow: hidden; }
.info-cell { flex: 1; padding: 8px 12px; border-right: 1px solid #e5e7eb; background: #f9fafb; }
.info-cell:last-child { border-right: none; }
.info-label { font-size: 7pt; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; font-weight: bold; margin-bottom: 3px; }
.info-value { font-size: 11pt; font-weight: bold; color: #111827; }
.info-value.primary { color: #1d4ed8; font-family: 'Courier New', monospace; }
.info-value.green   { color: #059669; }
.info-sub   { font-size: 7.5pt; color: #9ca3af; margin-top: 2px; }

/* ── Table ── */
table { width: 100%; border-collapse: collapse; margin-top: 8px; }
thead tr { background: #1e3a5f; color: #ffffff; }
thead th { padding: 6px 8px; font-size: 8pt; text-align: left; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; }
thead th.right { text-align: right; }
tbody tr:nth-child(even) { background: #f8fafc; }
tbody tr.opening-row { background: #eff6ff; }
tbody td { padding: 5px 8px; font-size: 8.5pt; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
tbody td.mono { font-family: 'Courier New', monospace; }
tbody td.right { text-align: right; }
tbody td.debit  { text-align: right; color: #dc2626; font-family: 'Courier New', monospace; }
tbody td.credit { text-align: right; color: #059669; font-family: 'Courier New', monospace; }
tbody td.balance { text-align: right; font-weight: bold; font-family: 'Courier New', monospace; border-left: 1px solid #e5e7eb; background: #f0f9ff; }
tfoot tr { background: #f1f5f9; border-top: 2px solid #cbd5e1; }
tfoot td { padding: 6px 8px; font-size: 9pt; font-weight: bold; }
tfoot td.balance { text-align: right; font-family: 'Courier New', monospace; color: {{ $closingBalance >= 0 ? '#059669' : '#dc2626' }}; border-left: 1px solid #e5e7eb; background: #e0f2fe; }

/* ── Footer ── */
.footer { margin-top: 16px; padding-top: 8px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 7.5pt; color: #9ca3af; }
.watermark { font-size: 7.5pt; color: #d1d5db; margin-top: 2px; }
</style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div>
        <div class="bank-name">{{ $tenantName }}</div>
        <div class="bank-sub">Generated: {{ now()->format('d M Y, H:i') }}</div>
    </div>
    <div class="title-block">
        <h2>STATEMENT OF ACCOUNT</h2>
        <p>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>
</div>

{{-- Account Info --}}
<div class="info-grid">
    <div class="info-cell">
        <div class="info-label">Account Name</div>
        <div class="info-value">{{ $account->customer?->full_name ?? $account->account_name }}</div>
        <div class="info-sub">{{ $account->customer?->phone ?? '' }}</div>
    </div>
    <div class="info-cell">
        <div class="info-label">Account Number</div>
        <div class="info-value primary">{{ $account->account_number }}</div>
        <div class="info-sub">{{ ucfirst($account->type) }} Account &bull; {{ $account->currency }}</div>
    </div>
    <div class="info-cell">
        <div class="info-label">Opening Balance</div>
        <div class="info-value">&#8358;{{ number_format($openingBalance, 2) }}</div>
        <div class="info-sub">As of {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</div>
    </div>
    <div class="info-cell">
        <div class="info-label">Closing Balance</div>
        <div class="info-value green">&#8358;{{ number_format($closingBalance, 2) }}</div>
        <div class="info-sub">As of {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
    </div>
</div>

{{-- Transactions Table --}}
<table>
    <thead>
        <tr>
            <th style="width:72px">Date</th>
            <th style="width:72px">Value Date</th>
            <th>Description</th>
            <th style="width:110px">Reference</th>
            <th class="right" style="width:90px">Debit (DR)</th>
            <th class="right" style="width:90px">Credit (CR)</th>
            <th class="right" style="width:100px">Balance (&#8358;)</th>
        </tr>
    </thead>
    <tbody>
        {{-- Opening row --}}
        <tr class="opening-row">
            <td>{{ \Carbon\Carbon::parse($startDate)->format('Y-m-d') }}</td>
            <td>&mdash;</td>
            <td><em>Brought Forward</em></td>
            <td class="mono" style="font-size:7.5pt;color:#9ca3af">OPENING-BAL</td>
            <td></td>
            <td></td>
            <td class="balance">{{ number_format($openingBalance, 2) }}</td>
        </tr>

        @foreach($transactions as $txn)
        @php $isCredit = $txn->amount > 0; @endphp
        <tr>
            <td>{{ $txn->created_at->format('Y-m-d') }}</td>
            <td>{{ $txn->created_at->format('Y-m-d') }}</td>
            <td>
                {{ $txn->description ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $txn->type)) }}
                @if($txn->status !== 'success') [{{ strtoupper($txn->status) }}] @endif
            </td>
            <td class="mono" style="font-size:7.5pt;color:#6b7280">{{ $txn->reference }}</td>
            <td class="debit">{{ !$isCredit && $txn->status === 'success' ? number_format(abs($txn->amount), 2) : '' }}</td>
            <td class="credit">{{ $isCredit && $txn->status === 'success' ? number_format($txn->amount, 2) : '' }}</td>
            <td class="balance">{{ number_format($txn->running_balance, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" style="font-size:9pt;color:#374151">Closing Balance</td>
            <td></td>
            <td class="balance">{{ number_format($closingBalance, 2) }}</td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    <p>This is a computer-generated document. No signature is required.</p>
    <p class="watermark">{{ $tenantName }} &bull; {{ $account->account_number }} &bull; Confidential &mdash; This document is password-protected. Password: account number</p>
</div>

</body>
</html>
