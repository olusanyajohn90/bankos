<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    @page {
        size: 85.6mm 54mm;
        margin: 0;
    }

    body {
        width: 85.6mm;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8pt;
        background: #fff;
    }

    /* ── FRONT CARD ────────────────────────────────────────── */
    .card-front {
        width: 85.6mm;
        height: 54mm;
        position: relative;
        overflow: hidden;
        background: {{ $template?->background_color ?? '#f8fafc' }};
        page-break-after: always;
    }

    /* Security micro-pattern background */
    .security-bg {
        position: absolute;
        inset: 0;
        opacity: 0.06;
        background-image: repeating-linear-gradient(
            45deg,
            {{ $template?->primary_color ?? '#1e40af' }} 0px,
            {{ $template?->primary_color ?? '#1e40af' }} 1px,
            transparent 1px,
            transparent 8px
        ),
        repeating-linear-gradient(
            -45deg,
            {{ $template?->primary_color ?? '#1e40af' }} 0px,
            {{ $template?->primary_color ?? '#1e40af' }} 1px,
            transparent 1px,
            transparent 8px
        );
    }

    /* Decorative curved element - top right */
    .curve-accent {
        position: absolute;
        top: -10mm;
        right: -8mm;
        width: 32mm;
        height: 32mm;
        border-radius: 50%;
        background: {{ $template?->primary_color ?? '#1e40af' }};
        opacity: 0.08;
    }
    .curve-accent-2 {
        position: absolute;
        top: -6mm;
        right: -4mm;
        width: 22mm;
        height: 22mm;
        border-radius: 50%;
        background: {{ $template?->secondary_color ?? '#3b82f6' }};
        opacity: 0.12;
    }

    /* Header bar */
    .header {
        position: relative;
        z-index: 2;
        background: linear-gradient(135deg,
            {{ $template?->primary_color ?? '#1e40af' }} 0%,
            {{ $template?->secondary_color ?? '#1d4ed8' }} 60%,
            {{ $template?->primary_color ?? '#1e40af' }} 100%
        );
        width: 100%;
        padding: 2.5mm 3.5mm;
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 11mm;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 2mm;
    }

    @if($template?->logo_path && file_exists(storage_path('app/public/' . $template->logo_path)))
    .logo {
        height: 6mm;
        width: auto;
        max-width: 18mm;
        object-fit: contain;
        filter: brightness(0) invert(1);
    }
    @endif

    .org-name {
        color: {{ $template?->text_color ?? '#ffffff' }};
        font-size: 8pt;
        font-weight: bold;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        line-height: 1.1;
    }

    .org-tagline {
        color: rgba(255,255,255,0.65);
        font-size: 5pt;
        letter-spacing: 0.3px;
    }

    .id-badge {
        background: rgba(255,255,255,0.2);
        border: 0.5pt solid rgba(255,255,255,0.4);
        color: {{ $template?->text_color ?? '#ffffff' }};
        font-size: 6pt;
        font-weight: bold;
        padding: 0.8mm 2mm;
        border-radius: 1mm;
        letter-spacing: 1.5px;
        text-transform: uppercase;
    }

    /* Thin colored band below header */
    .accent-strip {
        height: 1.2mm;
        background: linear-gradient(to right,
            {{ $template?->secondary_color ?? '#3b82f6' }},
            {{ $template?->accent_color ?? '#f59e0b' ?? $template?->secondary_color ?? '#93c5fd' }},
            {{ $template?->primary_color ?? '#1e40af' }}
        );
    }

    /* Body */
    .body {
        position: relative;
        z-index: 2;
        display: flex;
        padding: 2.5mm 3mm 0;
        gap: 2.5mm;
        height: 33mm;
    }

    /* Photo */
    .photo-wrap {
        width: 18mm;
        flex-shrink: 0;
        padding-top: 1mm;
    }

    .photo, .photo-placeholder {
        width: 18mm;
        height: 22mm;
        border-radius: 1.5mm;
        border: 1.5pt solid {{ $template?->primary_color ?? '#1e40af' }};
        object-fit: cover;
        display: block;
    }

    .photo-placeholder {
        background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
    }

    /* Chip icon below photo */
    .chip-icon {
        width: 8mm;
        height: 6mm;
        margin: 1.5mm auto 0;
        background: linear-gradient(135deg, #d4a853, #f0c862, #c9933f);
        border-radius: 1mm;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        grid-template-rows: 1fr 1fr 1fr;
        gap: 0.4mm;
        padding: 0.8mm;
    }

    .chip-cell {
        background: rgba(0,0,0,0.2);
        border-radius: 0.2mm;
    }

    /* Staff info */
    .info {
        flex: 1;
        padding-top: 1.5mm;
        min-width: 0;
    }

    .staff-name {
        font-size: 9.5pt;
        font-weight: bold;
        color: #0f172a;
        line-height: 1.2;
        margin-bottom: 0.5mm;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .staff-title {
        font-size: 6.5pt;
        color: {{ $template?->primary_color ?? '#1e40af' }};
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2mm;
        padding-bottom: 1.5mm;
        border-bottom: 0.5pt solid {{ $template?->primary_color ?? '#1e40af' }}33;
    }

    .info-row {
        display: flex;
        margin-bottom: 1.2mm;
        align-items: baseline;
    }

    .info-label {
        font-size: 5.5pt;
        color: #64748b;
        width: 14mm;
        flex-shrink: 0;
        text-transform: uppercase;
        letter-spacing: 0.2px;
    }

    .info-value {
        font-size: 6.5pt;
        color: #1e293b;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* QR */
    .qr-area {
        width: 14mm;
        flex-shrink: 0;
        padding-top: 2mm;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .qr-area svg {
        width: 13mm;
        height: 13mm;
        display: block;
    }

    .qr-scan-text {
        font-size: 4pt;
        color: #94a3b8;
        text-align: center;
        margin-top: 0.8mm;
        letter-spacing: 0.1px;
    }

    /* Bottom footer strip */
    .footer-strip {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 9.5mm;
        background: linear-gradient(135deg,
            {{ $template?->primary_color ?? '#1e40af' }},
            {{ $template?->secondary_color ?? '#2563eb' }}
        );
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 3.5mm;
        z-index: 3;
    }

    .card-number {
        font-family: 'Courier New', monospace;
        font-size: 7.5pt;
        font-weight: bold;
        color: {{ $template?->text_color ?? '#ffffff' }};
        letter-spacing: 2px;
    }

    .expiry-block {
        text-align: right;
    }

    .expiry-label {
        color: rgba(255,255,255,0.6);
        font-size: 4.5pt;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
    }

    .expiry-value {
        color: {{ $template?->text_color ?? '#ffffff' }};
        font-size: 7pt;
        font-weight: bold;
        letter-spacing: 0.5px;
    }

    /* ── BACK CARD ─────────────────────────────────────────── */
    .card-back {
        width: 85.6mm;
        height: 54mm;
        position: relative;
        overflow: hidden;
        background: {{ $template?->background_color ?? '#f8fafc' }};
    }

    .back-security-bg {
        position: absolute;
        inset: 0;
        opacity: 0.04;
        background-image: repeating-linear-gradient(
            90deg,
            {{ $template?->primary_color ?? '#1e40af' }} 0px,
            {{ $template?->primary_color ?? '#1e40af' }} 1px,
            transparent 1px,
            transparent 5px
        );
    }

    /* Magnetic stripe at top */
    .mag-stripe {
        width: 100%;
        height: 10mm;
        background: linear-gradient(to bottom, #1a1a2e, #16213e, #0f3460);
        position: relative;
        z-index: 2;
    }

    .mag-stripe-text {
        position: absolute;
        right: 3mm;
        bottom: 1.5mm;
        font-size: 4pt;
        color: rgba(255,255,255,0.3);
        letter-spacing: 0.5px;
    }

    /* Back body */
    .back-body {
        position: relative;
        z-index: 2;
        padding: 2mm 3.5mm;
        display: flex;
        flex-direction: column;
        gap: 2mm;
    }

    /* Signature strip */
    .signature-strip {
        background: white;
        border: 0.5pt solid #e2e8f0;
        border-radius: 1mm;
        padding: 1mm 2mm;
        height: 8mm;
        display: flex;
        align-items: center;
        gap: 3mm;
    }

    .sig-label {
        font-size: 5pt;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        flex-shrink: 0;
        writing-mode: vertical-rl;
        transform: rotate(180deg);
    }

    .sig-lines {
        flex: 1;
        border-bottom: 0.5pt solid #94a3b8;
        margin-bottom: 3mm;
    }

    /* Info grid on back */
    .back-info {
        display: flex;
        gap: 4mm;
    }

    .back-col {
        flex: 1;
    }

    .back-info-row {
        margin-bottom: 1mm;
    }

    .back-label {
        font-size: 5pt;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.2px;
        display: block;
    }

    .back-value {
        font-size: 6.5pt;
        color: #1e293b;
        font-weight: 600;
        display: block;
    }

    /* Terms text */
    .terms {
        font-size: 4.5pt;
        color: #94a3b8;
        line-height: 1.4;
        border-top: 0.5pt solid #e2e8f0;
        padding-top: 1mm;
    }

    /* Back footer */
    .back-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 8mm;
        background: linear-gradient(135deg,
            {{ $template?->primary_color ?? '#1e40af' }},
            {{ $template?->secondary_color ?? '#2563eb' }}
        );
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 3mm;
        z-index: 3;
    }

    .back-footer-text {
        color: rgba(255,255,255,0.7);
        font-size: 5pt;
        letter-spacing: 0.3px;
    }

    .back-footer-num {
        font-family: 'Courier New', monospace;
        color: rgba(255,255,255,0.9);
        font-size: 6pt;
        letter-spacing: 1px;
    }

    /* Barcode-like visual */
    .barcode-visual {
        display: flex;
        gap: 0.4mm;
        align-items: stretch;
        height: 6mm;
        margin: 1mm 0;
    }

    @for ($i = 0; $i < 35; $i++)
    .bc-{{ $i }} {
        width: {{ [0.5,1,0.8,1.5,0.6,1.2,0.9,0.7][$i % 8] }}mm;
        background: #1e293b;
    }
    @endfor
</style>
</head>
<body>

{{-- ══════════════ FRONT SIDE ══════════════ --}}
<div class="card-front">
    <div class="security-bg"></div>
    <div class="curve-accent"></div>
    <div class="curve-accent-2"></div>

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            @if($template?->logo_path && file_exists(storage_path('app/public/' . $template->logo_path)))
                <img src="{{ storage_path('app/public/' . $template->logo_path) }}" class="logo" alt="Logo">
            @endif
            <div>
                <div class="org-name">{{ $profile->branch?->tenant?->name ?? config('app.name', 'BankOS') }}</div>
                <div class="org-tagline">Microfinance Bank</div>
            </div>
        </div>
        <div class="id-badge">STAFF ID</div>
    </div>

    <div class="accent-strip"></div>

    {{-- Body --}}
    <div class="body">

        {{-- Photo + Chip --}}
        <div class="photo-wrap">
            @if($card->photo_path && file_exists(storage_path('app/public/' . $card->photo_path)))
                <img src="{{ storage_path('app/public/' . $card->photo_path) }}" class="photo" alt="">
            @else
                <div class="photo-placeholder" style="font-size:20pt; color:#94a3b8;">&#9786;</div>
            @endif
            <div class="chip-icon">
                <div class="chip-cell"></div><div class="chip-cell"></div><div class="chip-cell"></div>
                <div class="chip-cell" style="grid-column:1/4; background:rgba(0,0,0,0.1);"></div>
                <div class="chip-cell"></div><div class="chip-cell"></div><div class="chip-cell"></div>
            </div>
        </div>

        {{-- Info --}}
        <div class="info">
            <div class="staff-name">{{ $profile->user?->name ?? '—' }}</div>
            <div class="staff-title">{{ $profile->job_title ?? 'Staff Member' }}</div>

            <div class="info-row">
                <span class="info-label">Staff ID</span>
                <span class="info-value">{{ $profile->staff_code ?? $profile->employee_number ?? '—' }}</span>
            </div>

            @if($template?->show_department ?? true)
            <div class="info-row">
                <span class="info-label">Dept</span>
                <span class="info-value">{{ $profile->orgDepartment?->name ?? $profile->department ?? '—' }}</span>
            </div>
            @endif

            @if($template?->show_grade ?? true)
            <div class="info-row">
                <span class="info-label">Grade</span>
                <span class="info-value">GL {{ $profile->grade_level ?? '—' }}</span>
            </div>
            @endif

            <div class="info-row">
                <span class="info-label">Branch</span>
                <span class="info-value">{{ $profile->branch?->name ?? 'Head Office' }}</span>
            </div>
        </div>

        {{-- QR code --}}
        @if($template?->show_qr ?? true)
        <div class="qr-area">
            {!! $qrSvg !!}
            <div class="qr-scan-text">Scan to<br>Verify</div>
        </div>
        @endif

    </div>

    {{-- Bottom strip --}}
    <div class="footer-strip">
        <div class="card-number">{{ $card->card_number }}</div>
        <div class="expiry-block">
            <span class="expiry-label">Valid Thru</span>
            <span class="expiry-value">{{ $card->expiry_date->format('m/Y') }}</span>
        </div>
    </div>
</div>

{{-- ══════════════ BACK SIDE ══════════════ --}}
<div class="card-back">
    <div class="back-security-bg"></div>

    {{-- Magnetic stripe --}}
    <div class="mag-stripe">
        <span class="mag-stripe-text">AUTHORIZED USE ONLY</span>
    </div>

    {{-- Back body --}}
    <div class="back-body">
        {{-- Signature strip --}}
        <div class="signature-strip">
            <span class="sig-label">Signature</span>
            <div style="flex:1; border-bottom: 0.5pt solid #cbd5e1;"></div>
        </div>

        {{-- Info columns --}}
        <div class="back-info">
            <div class="back-col">
                <div class="back-info-row">
                    <span class="back-label">Card Number</span>
                    <span class="back-value" style="font-family:'Courier New',monospace; font-size:6pt;">{{ $card->card_number }}</span>
                </div>
                <div class="back-info-row">
                    <span class="back-label">Issue Date</span>
                    <span class="back-value">{{ $card->issued_date->format('d/m/Y') }}</span>
                </div>
                <div class="back-info-row">
                    <span class="back-label">Expiry</span>
                    <span class="back-value">{{ $card->expiry_date->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="back-col">
                <div class="back-info-row">
                    <span class="back-label">Staff ID</span>
                    <span class="back-value">{{ $profile->staff_code ?? '—' }}</span>
                </div>
                @if($template?->show_blood_group ?? false)
                <div class="back-info-row">
                    <span class="back-label">Blood Group</span>
                    <span class="back-value">{{ $profile->blood_group ?? '—' }}</span>
                </div>
                @endif
                @if($template?->show_emergency_contact ?? false)
                <div class="back-info-row">
                    <span class="back-label">Emergency</span>
                    <span class="back-value" style="font-size:5.5pt;">{{ $profile->emergency_contact_phone ?? '—' }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Terms --}}
        <div class="terms">
            This card is the property of {{ config('app.name', 'BankOS') }}. If found, please return to the nearest branch or call our security desk. Misuse of this card is a criminal offence. Card is not transferable.
        </div>
    </div>

    {{-- Back footer --}}
    <div class="back-footer">
        <span class="back-footer-text">{{ config('app.name', 'BankOS') }} · CBN Licensed MFB</span>
        <span class="back-footer-num">{{ substr($card->card_number, -8) }}</span>
    </div>
</div>

</body>
</html>
