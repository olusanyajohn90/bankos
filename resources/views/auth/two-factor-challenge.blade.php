<x-guest-layout>
    <div x-data="{ useRecovery: false }" class="w-full">

        {{-- Flash error --}}
        @if (session('error'))
            <div class="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-50 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Two-Factor Authentication</h2>
            <p x-show="!useRecovery" class="mt-1.5 text-sm text-gray-500">
                Enter the 6-digit code from your authenticator app.
            </p>
            <p x-show="useRecovery" class="mt-1.5 text-sm text-gray-500" style="display:none;">
                Enter one of your recovery codes to access your account.
            </p>
        </div>

        {{-- TOTP Form --}}
        <div x-show="!useRecovery">
            <form method="POST" action="{{ route('two-factor.verify') }}">
                @csrf
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Authentication Code</label>
                    <input
                        type="text"
                        name="code"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        maxlength="6"
                        autofocus
                        placeholder="000000"
                        class="w-full text-center text-2xl tracking-widest font-mono border border-gray-300 rounded-xl px-4 py-3 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    @error('code')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                    Verify &amp; Sign In
                </button>
            </form>
        </div>

        {{-- Recovery Code Form --}}
        <div x-show="useRecovery" style="display:none;">
            <form method="POST" action="{{ route('two-factor.verify') }}">
                @csrf
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Recovery Code</label>
                    <input
                        type="text"
                        name="code"
                        autocomplete="off"
                        placeholder="xxxxxxxxxx-xxxxxxxxxx"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                    >
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                    Use Recovery Code
                </button>
            </form>
        </div>

        {{-- Toggle --}}
        <div class="mt-5 text-center">
            <button
                type="button"
                @click="useRecovery = !useRecovery"
                class="text-sm text-blue-600 hover:underline"
            >
                <span x-show="!useRecovery">Use a recovery code instead</span>
                <span x-show="useRecovery" style="display:none;">Use authenticator code instead</span>
            </button>
        </div>

        <p class="mt-6 text-center text-xs text-gray-400">
            <a href="{{ route('login') }}" class="hover:underline">Back to login</a>
        </p>
    </div>
</x-guest-layout>
