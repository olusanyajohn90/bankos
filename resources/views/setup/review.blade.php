<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review & Launch — bankOS Setup</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-4 py-12">

<div class="max-w-xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-8 h-8 rounded-lg bg-blue-600 grid place-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        </div>
        <span class="text-lg font-bold text-gray-900">bank<span class="text-blue-600">OS</span></span>
    </div>

    @include('setup._stepper', ['current' => 5])

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 mt-6">
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-1">Review Your Setup</h2>
            <p class="text-sm text-gray-500">Everything look good? Click Launch to go live.</p>
        </div>

        @if(session('error'))
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
        @endif

        <!-- Summary Sections -->
        <div class="space-y-4">
            <!-- Institution -->
            <div class="border border-gray-100 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-sm text-gray-900">Institution Details</h3>
                    <a href="{{ route('setup.step1') }}" class="text-xs text-blue-600 hover:underline">Edit</a>
                </div>
                <dl class="space-y-1.5 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Name</dt>
                        <dd class="font-medium text-gray-900">{{ $data['step1']['name'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Type</dt>
                        <dd class="font-medium text-gray-900 capitalize">{{ $data['step1']['type'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="font-medium text-gray-900">{{ $data['step1']['contact_email'] }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Branding -->
            <div class="border border-gray-100 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-sm text-gray-900">Branding</h3>
                    <a href="{{ route('setup.step2') }}" class="text-xs text-blue-600 hover:underline">Edit</a>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg border border-gray-200 shadow-sm" style="background-color: {{ $data['step2']['primary_color'] }}"></div>
                        <div>
                            <p class="text-xs text-gray-500">Primary</p>
                            <p class="text-sm font-mono font-medium text-gray-900">{{ $data['step2']['primary_color'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg border border-gray-200 shadow-sm" style="background-color: {{ $data['step2']['secondary_color'] }}"></div>
                        <div>
                            <p class="text-xs text-gray-500">Secondary</p>
                            <p class="text-sm font-mono font-medium text-gray-900">{{ $data['step2']['secondary_color'] }}</p>
                        </div>
                    </div>
                    @if(!empty($data['step2']['logo_path']))
                    <span class="ml-auto bg-green-100 text-green-700 text-xs px-2.5 py-1 rounded-full font-medium">Logo uploaded</span>
                    @endif
                </div>
            </div>

            <!-- Admin User -->
            <div class="border border-gray-100 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-sm text-gray-900">Admin Account</h3>
                    <a href="{{ route('setup.step3') }}" class="text-xs text-blue-600 hover:underline">Edit</a>
                </div>
                <dl class="space-y-1.5 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Name</dt>
                        <dd class="font-medium text-gray-900">{{ $data['step3']['name'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="font-medium text-gray-900">{{ $data['step3']['email'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Role</dt>
                        <dd class="font-medium text-gray-900">Tenant Administrator</dd>
                    </div>
                </dl>
            </div>

            <!-- Plan -->
            <div class="border border-gray-100 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-sm text-gray-900">Subscription</h3>
                    <a href="{{ route('setup.step4') }}" class="text-xs text-blue-600 hover:underline">Edit</a>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-bold text-gray-900 text-lg">{{ $plan->name }} Plan</p>
                        <p class="text-sm text-gray-500 capitalize">{{ $data['step4']['billing_cycle'] }} billing</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-blue-600 text-lg">
                            @if($data['step4']['billing_cycle'] === 'monthly')
                                {{ $plan->formattedMonthlyPrice() }}/mo
                            @else
                                {{ $plan->formattedYearlyPrice() }}/yr
                            @endif
                        </p>
                        <p class="text-xs text-gray-400">After 14-day trial</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terms -->
        <div class="mt-6 bg-gray-50 rounded-xl p-4 flex items-start gap-3">
            <input type="checkbox" id="agree_terms" required class="mt-1 accent-blue-600">
            <label for="agree_terms" class="text-xs text-gray-600 leading-relaxed">
                I agree to the bankOS
                <a href="#" class="text-blue-600 hover:underline">Terms of Service</a> and
                <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a>.
                I confirm that the information provided is accurate and complete.
            </label>
        </div>

        <form method="POST" action="{{ route('setup.complete') }}" class="mt-6">
            @csrf
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-xl transition-colors shadow-md hover:shadow-lg text-center text-lg">
                Launch My bankOS Platform
            </button>
        </form>

        <p class="text-center text-xs text-gray-400 mt-4">
            Your 14-day free trial starts now. No credit card required.
        </p>
    </div>
</div>

</body>
</html>
