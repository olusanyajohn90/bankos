<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Get Started — bankOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">

<div class="w-full max-w-lg">
    <!-- Logo -->
    <div class="flex items-center justify-center gap-3 mb-8">
        <div class="w-10 h-10 rounded-xl bg-blue-600 grid place-items-center shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        </div>
        <span class="text-2xl font-bold text-gray-900">bank<span class="text-blue-600">OS</span></span>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 text-center">
        <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Welcome to bankOS</h1>
        <p class="text-gray-500 mb-8 leading-relaxed">
            Set up your institution in minutes. We'll walk you through configuring your core banking platform step by step.
        </p>

        <!-- What you'll need -->
        <div class="bg-blue-50 rounded-xl p-5 text-left mb-8 space-y-3">
            <p class="text-sm font-semibold text-blue-800 mb-3">What you'll need:</p>
            @foreach([
                'CBN license number (if applicable)',
                'Institution logo (PNG/JPG, max 2MB)',
                'Contact email and phone number',
                'Your admin account details',
            ] as $item)
            <div class="flex items-center gap-2 text-sm text-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                {{ $item }}
            </div>
            @endforeach
        </div>

        <a href="{{ route('setup.step1') }}"
           class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 px-6 rounded-xl transition-colors shadow-md hover:shadow-lg text-center">
            Start Setup — Takes about 5 minutes
        </a>

        <p class="mt-4 text-sm text-gray-400">
            Already have an account?
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Sign in</a>
        </p>
    </div>

    <p class="text-center text-xs text-gray-400 mt-6">&copy; {{ date('Y') }} bankOS. Nigerian Banking Technology.</p>
</div>

</body>
</html>
