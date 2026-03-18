<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Institution Details — bankOS Setup</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4 py-12">

<div class="w-full max-w-xl">
    <!-- Logo -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-8 h-8 rounded-lg bg-blue-600 grid place-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        </div>
        <span class="text-lg font-bold text-gray-900">bank<span class="text-blue-600">OS</span></span>
    </div>

    <!-- Progress Stepper -->
    @include('setup._stepper', ['current' => 1])

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 mt-6">
        <h2 class="text-xl font-bold text-gray-900 mb-1">Institution Details</h2>
        <p class="text-sm text-gray-500 mb-6">Tell us about your financial institution.</p>

        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4">
            <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('setup.step1.store') }}">
            @csrf

            <div class="space-y-5">
                <!-- Institution Full Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Institution Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $data['step1']['name'] ?? '') }}"
                           placeholder="e.g. Unity Microfinance Bank"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Short Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Short Name / Slug <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="short_name" value="{{ old('short_name', $data['step1']['short_name'] ?? '') }}"
                           placeholder="e.g. unity_mfb"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-gray-400 mt-1">Letters, numbers, hyphens and underscores only.</p>
                </div>

                <!-- Institution Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Institution Type <span class="text-red-500">*</span>
                    </label>
                    <select name="type"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                        <option value="">Select type...</option>
                        @foreach(['microfinance' => 'Microfinance Bank (MFB)', 'commercial' => 'Commercial Bank', 'cooperative' => 'Cooperative Society', 'digital' => 'Digital Lender / Fintech'] as $val => $label)
                            <option value="{{ $val }}" {{ old('type', $data['step1']['type'] ?? '') == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- CBN License -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">CBN License Number</label>
                    <input type="text" name="cbn_license_number" value="{{ old('cbn_license_number', $data['step1']['cbn_license_number'] ?? '') }}"
                           placeholder="Optional"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Contact Email + Phone -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Email <span class="text-red-500">*</span></label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $data['step1']['contact_email'] ?? '') }}"
                               placeholder="admin@mybank.ng"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone <span class="text-red-500">*</span></label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $data['step1']['contact_phone'] ?? '') }}"
                               placeholder="+234 800 000 0000"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Address <span class="text-red-500">*</span></label>
                    <input type="text" name="address" value="{{ old('address', $data['step1']['address'] ?? '') }}"
                           placeholder="123 Bank Street, Lagos, Nigeria"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('setup.start') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    Back
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-8 rounded-xl transition-colors shadow-sm">
                    Continue
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline ml-1"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
