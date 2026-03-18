<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject }}</title>
    <style>
        /* Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f0f4f8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        a { color: #1a56db; text-decoration: none; }
        img { border: 0; display: block; }

        /* Wrapper */
        .email-wrapper { max-width: 620px; margin: 32px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.10); }

        /* Header */
        .email-header { background: linear-gradient(135deg, #1a3a6b 0%, #1a56db 100%); padding: 32px 40px 28px; position: relative; overflow: hidden; }
        .email-header::after { content: ''; position: absolute; right: -60px; top: -60px; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.06); }
        .email-header::before { content: ''; position: absolute; right: 40px; bottom: -40px; width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.04); }
        .bank-logo { display: flex; align-items: center; gap: 12px; position: relative; z-index: 1; }
        .bank-logo-icon { width: 44px; height: 44px; background: rgba(255,255,255,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.2); }
        .bank-logo-icon svg { display: block; }
        .bank-name { color: #ffffff; font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
        .bank-tagline { color: rgba(255,255,255,0.65); font-size: 12px; font-weight: 400; margin-top: 2px; letter-spacing: 0.3px; }

        /* Subject bar */
        .subject-bar { background: #f8faff; border-bottom: 2px solid #e8effe; padding: 18px 40px; }
        .subject-bar h1 { font-size: 15px; font-weight: 700; color: #1e293b; letter-spacing: -0.2px; }
        .subject-bar .timestamp { font-size: 11px; color: #94a3b8; margin-top: 4px; font-family: 'SF Mono', 'Fira Code', monospace; }

        /* Body */
        .email-body { padding: 36px 40px; }

        /* Parsed content */
        .content-greeting { font-size: 16px; font-weight: 600; color: #1e293b; margin-bottom: 16px; }
        .content-text { font-size: 14px; color: #475569; line-height: 1.7; margin-bottom: 20px; }

        /* Key-value detail table */
        .detail-card { background: #f8faff; border: 1px solid #dde8ff; border-radius: 12px; overflow: hidden; margin: 24px 0; }
        .detail-card-header { background: linear-gradient(90deg, #1a3a6b 0%, #1a56db 100%); padding: 12px 20px; }
        .detail-card-header span { color: rgba(255,255,255,0.9); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; }
        .detail-row { display: flex; align-items: center; padding: 13px 20px; border-bottom: 1px solid #e8effe; }
        .detail-row:last-child { border-bottom: none; }
        .detail-row:nth-child(even) { background: rgba(26, 86, 219, 0.025); }
        .detail-label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 45%; flex-shrink: 0; }
        .detail-value { font-size: 13px; font-weight: 600; color: #1e293b; font-family: 'SF Mono', 'Fira Code', 'Consolas', monospace; }
        .detail-value.amount { font-size: 15px; color: #1a56db; font-weight: 800; }
        .detail-value.reference { color: #7c3aed; font-size: 12px; }

        /* Closing text */
        .closing-text { font-size: 14px; color: #475569; line-height: 1.7; margin-top: 20px; }

        /* Security notice */
        .security-notice { background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; border-radius: 8px; padding: 14px 18px; margin: 24px 0 0; }
        .security-notice p { font-size: 12px; color: #92400e; line-height: 1.6; }
        .security-notice strong { font-weight: 700; }

        /* Divider */
        .divider { height: 1px; background: linear-gradient(90deg, transparent, #dde8ff, transparent); margin: 28px 0; }

        /* Footer */
        .email-footer { background: #f8faff; border-top: 1px solid #e8effe; padding: 28px 40px; }
        .footer-bank-name { font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
        .footer-address { font-size: 11px; color: #94a3b8; line-height: 1.6; margin-bottom: 16px; }
        .footer-links { display: flex; gap: 16px; margin-bottom: 16px; flex-wrap: wrap; }
        .footer-links a { font-size: 11px; color: #64748b; font-weight: 500; }
        .footer-links a:hover { color: #1a56db; }
        .footer-legal { font-size: 10px; color: #b0bec5; line-height: 1.6; border-top: 1px solid #e8effe; padding-top: 16px; }
        .footer-regulated { display: inline-flex; align-items: center; gap: 6px; background: #f0f4f8; border: 1px solid #dde8ff; border-radius: 20px; padding: 5px 12px; margin-bottom: 12px; }
        .footer-regulated span { font-size: 10px; color: #64748b; font-weight: 600; }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-wrapper { margin: 0; border-radius: 0; }
            .email-header, .subject-bar, .email-body, .email-footer { padding-left: 24px; padding-right: 24px; }
            .detail-row { flex-direction: column; align-items: flex-start; gap: 4px; }
            .detail-label { width: 100%; }
        }
    </style>
</head>
<body>
<div style="padding: 20px 0; background:#f0f4f8;">
<div class="email-wrapper">

    {{-- ── Header ── --}}
    <div class="email-header">
        <div class="bank-logo">
            <div class="bank-logo-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 22V12H15V22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div>
                <div class="bank-name">bankOS</div>
                <div class="bank-tagline">Demo Microfinance Bank &middot; CBN Licensed</div>
            </div>
        </div>
    </div>

    {{-- ── Subject Bar ── --}}
    <div class="subject-bar">
        <h1>{{ $subject }}</h1>
        <div class="timestamp">{{ now()->format('l, d F Y \a\t g:i A') }} &middot; WAT (UTC+1)</div>
    </div>

    {{-- ── Body ── --}}
    <div class="email-body">
        @php
            $lines   = explode("\n", trim($body));
            $greeting = '';
            $details  = [];   // lines with ': ' separator → key-value table
            $textBlocks = []; // groups of plain text lines
            $currentBlock = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    if (!empty($currentBlock)) {
                        $textBlocks[] = implode(' ', $currentBlock);
                        $currentBlock = [];
                    }
                    continue;
                }
                // Detect key: value lines (must have ': ' and no more than 40 chars before colon)
                if (preg_match('/^([^:]{2,40}):\s+(.+)$/', $line, $m)) {
                    if (!empty($currentBlock)) {
                        $textBlocks[] = ['type' => 'text', 'content' => implode(' ', $currentBlock)];
                        $currentBlock = [];
                    }
                    $textBlocks[] = ['type' => 'detail', 'label' => trim($m[1]), 'value' => trim($m[2])];
                } else {
                    $currentBlock[] = $line;
                }
            }
            if (!empty($currentBlock)) {
                $textBlocks[] = ['type' => 'text', 'content' => implode(' ', $currentBlock)];
            }

            // Group consecutive details into tables
            $groups = [];
            $detailBuffer = [];
            foreach ($textBlocks as $block) {
                if (!is_array($block)) {
                    if (!empty($detailBuffer)) { $groups[] = ['type' => 'table', 'rows' => $detailBuffer]; $detailBuffer = []; }
                    $groups[] = ['type' => 'text', 'content' => $block];
                } elseif ($block['type'] === 'detail') {
                    $detailBuffer[] = $block;
                } else {
                    if (!empty($detailBuffer)) { $groups[] = ['type' => 'table', 'rows' => $detailBuffer]; $detailBuffer = []; }
                    $groups[] = $block;
                }
            }
            if (!empty($detailBuffer)) { $groups[] = ['type' => 'table', 'rows' => $detailBuffer]; }

            // Amount-like value detection
            $amountKeys = ['amount', 'balance', 'principal', 'outstanding', 'disbursed', 'paid', 'rebated', 'credited', 'debited', 'topup'];
            $refKeys = ['reference', 'ref', 'transaction ref', 'transaction reference'];
        @endphp

        @foreach($groups as $i => $group)
            @if($group['type'] === 'text')
                @php $text = $group['content']; @endphp
                @if($i === 0 && Str::startsWith($text, 'Dear'))
                    <p class="content-greeting">{{ $text }}</p>
                @elseif($loop->last)
                    <p class="closing-text">{{ $text }}</p>
                @else
                    <p class="content-text">{{ $text }}</p>
                @endif

            @elseif($group['type'] === 'table')
                <div class="detail-card">
                    <div class="detail-card-header"><span>Transaction Details</span></div>
                    @foreach($group['rows'] as $row)
                        @php
                            $labelLower = strtolower($row['label']);
                            $isAmount = collect($amountKeys)->contains(fn($k) => str_contains($labelLower, $k));
                            $isRef    = collect($refKeys)->contains(fn($k) => str_contains($labelLower, $k));
                            $valueClass = $isAmount ? 'detail-value amount' : ($isRef ? 'detail-value reference' : 'detail-value');
                        @endphp
                        <div class="detail-row">
                            <div class="detail-label">{{ $row['label'] }}</div>
                            <div class="{{ $valueClass }}">{{ $row['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach

        {{-- Security notice for transaction emails --}}
        @if(Str::contains(strtolower($subject), ['alert', 'credit', 'debit', 'transfer', 'disburs', 'liquidat', 'repayment', 'settled']))
        <div class="security-notice">
            <p><strong>Security Notice:</strong> Demo Microfinance Bank will <strong>never</strong> ask for your PIN, password, or OTP via email. If you did not authorise this transaction, please call <strong>0800-DEMO-MFB</strong> immediately or visit your nearest branch.</p>
        </div>
        @endif
    </div>

    {{-- ── Footer ── --}}
    <div class="email-footer">
        <div class="footer-regulated">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#1a56db" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span>Regulated by the Central Bank of Nigeria &middot; CBN/MFB/2024/001</span>
        </div>
        <div class="footer-bank-name">Demo Microfinance Bank Ltd.</div>
        <div class="footer-address">
            1 Banking Road, Victoria Island, Lagos, Nigeria<br>
            RC: 1234567 &middot; Tel: 0800-DEMO-MFB &middot; Email: support@demomfb.com
        </div>
        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Use</a>
            <a href="#">Contact Support</a>
            <a href="#">Unsubscribe</a>
        </div>
        <div class="footer-legal">
            This message was sent to you because you are a registered customer of Demo Microfinance Bank. This email and any attachments are confidential and intended solely for the addressee. If you have received this in error, please notify us immediately and delete the email. &copy; {{ date('Y') }} Demo Microfinance Bank Ltd. All rights reserved. Powered by <strong>bankOS</strong>.
        </div>
    </div>

</div>
</div>
</body>
</html>
