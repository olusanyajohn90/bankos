<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Two-Factor Authentication</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto">

        {{-- ── Status Banner ─────────────────────────────────────────────────── --}}
        @if($user->two_factor_confirmed_at)
            <div class="mb-6 flex items-center gap-3 px-5 py-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-800 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600 dark:text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-green-800 dark:text-green-300">2FA is Active</p>
                    <p class="text-xs text-green-700 dark:text-green-400">Your account is protected with two-factor authentication.</p>
                </div>
                <span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-300 border border-green-200 dark:border-green-700">ENABLED</span>
            </div>
        @elseif($user->two_factor_secret)
            <div class="mb-6 flex items-center gap-3 px-5 py-4 rounded-xl bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-yellow-100 dark:bg-yellow-800 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-yellow-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">Confirmation Required</p>
                    <p class="text-xs text-yellow-700 dark:text-yellow-400">Scan the QR code and enter your code to complete setup.</p>
                </div>
                <span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">PENDING</span>
            </div>
        @else
            <div class="mb-6 flex items-center gap-3 px-5 py-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">2FA is Disabled</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Enable two-factor authentication to secure your account.</p>
                </div>
                <span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-600">DISABLED</span>
            </div>
        @endif

        {{-- ── Main Card ─────────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-2xl border border-bankos-border dark:border-bankos-dark-border shadow-sm p-6 md:p-8">

            @if($user->two_factor_confirmed_at)
                {{-- ── ACTIVE: show recovery codes + disable form ─────────────── --}}
                <h3 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text mb-1">Manage Two-Factor Authentication</h3>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mb-6">
                    2FA was enabled on {{ $user->two_factor_confirmed_at->format('M d, Y') }}.
                    You can view your recovery codes or disable 2FA below.
                </p>

                @if($recoveryCodes)
                <div x-data="{ show: false }" class="mb-6">
                    <button @click="show = !show" type="button" class="text-sm text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1.5 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <span x-text="show ? 'Hide Recovery Codes' : 'Show Recovery Codes'">Show Recovery Codes</span>
                    </button>
                    <div x-show="show" x-transition style="display:none;">
                        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-xl">
                            <p class="text-xs font-semibold text-yellow-800 dark:text-yellow-300 mb-3">
                                Store these codes safely. Each code can only be used once.
                            </p>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($recoveryCodes as $code)
                                <code class="font-mono text-xs text-yellow-900 dark:text-yellow-200 bg-yellow-100 dark:bg-yellow-900/40 px-3 py-1.5 rounded-lg border border-yellow-200 dark:border-yellow-700">{{ $code }}</code>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <hr class="border-bankos-border dark:border-bankos-dark-border mb-6">

                <div x-data="{ showDisable: false }">
                    <button @click="showDisable = !showDisable" type="button" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-red-200 dark:border-red-700 text-red-600 dark:text-red-400 text-sm font-medium hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        Disable Two-Factor Authentication
                    </button>

                    <div x-show="showDisable" x-transition class="mt-5 p-5 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl" style="display:none;">
                        <p class="text-sm text-red-700 dark:text-red-300 font-medium mb-4">
                            This will remove 2FA protection from your account. Confirm your password to proceed.
                        </p>
                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf @method('DELETE')
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Current Password</label>
                                <input type="password" name="password" class="border border-gray-300 dark:border-gray-600 rounded-xl px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm w-full max-w-xs focus:outline-none focus:ring-2 focus:ring-red-500">
                                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" onclick="return confirm('Disable 2FA? Your account will be less secure.')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors">
                                Confirm &amp; Disable
                            </button>
                        </form>
                    </div>
                </div>

            @elseif($user->two_factor_secret && !$user->two_factor_confirmed_at)
                {{-- ── PENDING: Show QR + confirm form ────────────────────────── --}}
                <h3 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text mb-1">Scan QR Code</h3>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mb-6">
                    Open your authenticator app (Google Authenticator, Authy, etc.) and scan the QR code below.
                    Then enter the 6-digit code it generates to confirm and activate 2FA.
                </p>

                <div class="flex flex-col items-center mb-6">
                    <div id="qrcode" class="rounded-2xl border-4 border-white shadow-lg p-2 bg-white mb-3"></div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Or enter the setup key manually:
                        <code class="ml-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-2 py-0.5 rounded font-mono text-xs">{{ $secret }}</code>
                    </p>
                </div>

                <form method="POST" action="{{ route('two-factor.confirm') }}">
                    @csrf
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Verification Code</label>
                        <input
                            type="text"
                            name="code"
                            inputmode="numeric"
                            maxlength="6"
                            autocomplete="one-time-code"
                            autofocus
                            placeholder="000000"
                            class="border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-3 text-center text-xl tracking-widest font-mono bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-full max-w-xs focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        @error('code') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            Confirm &amp; Activate 2FA
                        </button>
                    </div>
                </form>

                @push('scripts')
                <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        QRCode.toCanvas(document.getElementById('qrcode'), @json($qrUri), { width: 200, margin: 1 });
                    });
                </script>
                @endpush

            @else
                {{-- ── NOT ENABLED: Show enable button ────────────────────────── --}}
                <h3 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text mb-1">Enhance Your Account Security</h3>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mb-6">
                    Two-factor authentication adds an extra layer of protection. After enabling, you will be asked
                    for a one-time code from your authenticator app each time you sign in.
                </p>

                <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="flex flex-col items-center text-center p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mb-2">
                            <span class="text-blue-600 font-bold text-sm">1</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Install an authenticator app</p>
                    </div>
                    <div class="flex flex-col items-center text-center p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mb-2">
                            <span class="text-blue-600 font-bold text-sm">2</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Scan the QR code shown</p>
                    </div>
                    <div class="flex flex-col items-center text-center p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mb-2">
                            <span class="text-blue-600 font-bold text-sm">3</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Enter your code to confirm</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('two-factor.enable') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Enable Two-Factor Authentication
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
