<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Restricted — bankOS</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 48px 40px;
            text-align: center;
            max-width: 440px;
            width: 100%;
            box-shadow: 0 4px 32px rgba(0,0,0,0.10);
            border: 1px solid #e2e8f0;
        }
        .icon-wrap {
            width: 72px;
            height: 72px;
            background: #fef2f2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 2px solid #fecaca;
        }
        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 32px;
        }
        .brand-icon {
            width: 36px;
            height: 36px;
            background: #1d4ed8;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .brand-name {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        h1 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .subtitle {
            font-size: 14px;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .ip-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            margin: 20px 0;
            font-size: 13px;
            color: #64748b;
        }
        .ip-box code {
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
            background: #e2e8f0;
            padding: 2px 8px;
            border-radius: 5px;
        }
        .contact-note {
            font-size: 13px;
            color: #94a3b8;
            line-height: 1.5;
        }
        .divider {
            border: none;
            border-top: 1px solid #f1f5f9;
            margin: 24px 0;
        }
        .error-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            border-radius: 99px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
    <div class="card">
        {{-- Brand --}}
        <div class="brand">
            <div class="brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            </div>
            <span class="brand-name">bankOS</span>
        </div>

        {{-- Error Badge --}}
        <div class="error-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            403 Access Restricted
        </div>

        {{-- Icon --}}
        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>

        <h1>Access Restricted</h1>
        <p class="subtitle">
            Your IP address is not authorized to access this institution's bankOS system.
            IP restrictions are enforced by your institution's security policy.
        </p>

        @isset($ip)
        <div class="ip-box">
            Your IP address: <code>{{ $ip }}</code>
        </div>
        @endisset

        <hr class="divider">

        <p class="contact-note">
            Contact your IT administrator or branch manager to add your IP address to the whitelist.
            Do not attempt repeated access from unauthorized locations.
        </p>
    </div>
</body>
</html>
