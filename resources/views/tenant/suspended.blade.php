<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Suspended — bankOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 flex items-center justify-center p-4">

<div class="w-full max-w-lg text-center">
    <!-- Logo -->
    <div class="flex items-center justify-center gap-3 mb-10">
        <div class="w-10 h-10 rounded-xl bg-gray-800 grid place-items-center shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        </div>
        <span class="text-2xl font-bold text-gray-900">bank<span class="text-gray-600">OS</span></span>
    </div>

    <!-- Suspension Icon -->
    <div class="w-24 h-24 bg-red-100 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
        </svg>
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-3">Account Suspended</h1>
    <p class="text-lg text-gray-600 mb-6">
        Your institution's bankOS access has been temporarily suspended.
    </p>

    @php
        $tenant = auth()->user()?->tenant;
    @endphp

    @if($tenant?->suspension_reason)
    <div class="bg-white rounded-2xl border border-red-200 shadow-sm p-6 mb-8 text-left">
        <p class="text-xs font-semibold text-red-500 uppercase tracking-wider mb-2">Reason for Suspension</p>
        <p class="text-gray-700 leading-relaxed">{{ $tenant->suspension_reason }}</p>
        @if($tenant->suspended_at)
        <p class="text-xs text-gray-400 mt-3">Suspended on {{ $tenant->suspended_at->format('d F Y \a\t H:i') }}</p>
        @endif
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-8">
        <p class="text-gray-600 text-sm leading-relaxed">
            To restore access, please contact the bankOS support team. Provide your institution name and reference the suspension notice.
        </p>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="mailto:support@bankos.ng?subject=Suspension%20Appeal%20—%20{{ urlencode($tenant?->name ?? 'My Institution') }}"
           class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-xl transition-colors shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            Contact Support
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center gap-2 border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold py-3 px-8 rounded-xl transition-colors w-full sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Sign Out
            </button>
        </form>
    </div>

    <p class="text-xs text-gray-400 mt-10">&copy; {{ date('Y') }} bankOS. All rights reserved.</p>
</div>

</body>
</html>
