<x-guest-layout>
    <div class="min-h-screen flex text-bankos-text dark:bg-bankos-dark-bg dark:text-bankos-dark-text">
        <!-- Left Marketing Panel -->
        <div class="hidden lg:flex lg:w-1/2 bg-bankos-bg dark:bg-bankos-dark-surface relative overflow-hidden items-center justify-center p-12">
            <!-- Decorative Elements -->
            <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
                <div class="absolute top-20 left-10 w-64 h-64 bg-bankos-primary rounded-full mix-blend-multiply filter blur-3xl"></div>
                <div class="absolute bottom-20 right-10 w-80 h-80 bg-accent-indigo rounded-full mix-blend-multiply filter blur-3xl"></div>
                <!-- Naira Watermark -->
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-[400px] font-bold text-bankos-primary select-none opacity-5">₦</div>
            </div>

            <div class="relative z-10 max-w-xl">
                <!-- Logo Stub -->
                <div class="flex items-center gap-2 mb-12">
                    <div class="w-10 h-10 bg-bankos-primary rounded-lg grid place-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    </div>
                    <span class="text-2xl font-bold tracking-tight">bank<span class="text-bankos-primary">OS</span></span>
                </div>

                <h1 class="text-4xl sm:text-5xl font-bold leading-tight mb-6">
                    Modern banking & <br>lending infrastructure <br>for Africa
                </h1>
                
                <p class="text-bankos-text-sec dark:text-bankos-dark-text-sec text-lg mb-10">
                    The operating system for microfinance banks, digital lenders, and co-operative societies.
                </p>

                <div class="space-y-4 mb-12">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <span class="text-bankos-text dark:text-bankos-dark-text font-medium">CBN & NIBSS Compliant</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                        </div>
                        <span class="text-bankos-text dark:text-bankos-dark-text font-medium">AI-driven Credit Assessment</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"></path><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"></path></svg>
                        </div>
                        <span class="text-bankos-text dark:text-bankos-dark-text font-medium">Real-time Financial Dashboards</span>
                    </div>
                </div>

                <!-- Tag Strip -->
                <div class="flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-wider text-bankos-muted">
                    <span class="bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full">Compliance</span>
                    <span class="bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full">AI Credit</span>
                    <span class="bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full">Dashboards</span>
                </div>
            </div>
        </div>

        <!-- Right Login Panel -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-24 bg-white dark:bg-bankos-dark-bg relative">
            
            <!-- Mobile Logo -->
            <div class="absolute top-8 left-8 lg:hidden flex items-center gap-2">
                <div class="w-8 h-8 bg-bankos-primary rounded-md grid place-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </div>
                <span class="text-xl font-bold tracking-tight">bank<span class="text-bankos-primary">OS</span></span>
            </div>

            <div class="w-full max-w-md">
                <div class="mb-10">
                    <h2 class="text-2xl font-bold mb-2">Welcome back</h2>
                    <p class="text-bankos-text-sec dark:text-bankos-dark-text-sec">Sign in to your account to continue</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Institution Code -->
                    <div>
                        <x-input-label for="institution_code" value="Institution Code" class="dark:text-bankos-dark-text" />
                        <x-text-input id="institution_code" class="block mt-1 w-full input-field" type="text" name="institution_code" :value="old('institution_code')" required autofocus placeholder="e.g. demo" autocomplete="organization" />
                        <x-input-error :messages="$errors->get('institution_code')" class="mt-2" />
                    </div>

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" value="Email" class="dark:text-bankos-dark-text" />
                        <x-text-input id="email" class="block mt-1 w-full input-field" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="name@company.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex justify-between items-center">
                            <x-input-label for="password" value="Password" class="dark:text-bankos-dark-text" />
                            @if (Route::has('password.request'))
                                <a class="text-sm font-medium text-bankos-primary hover:text-blue-700 hover:underline" href="{{ route('password.request') }}">
                                    Forgot password?
                                </a>
                            @endif
                        </div>
                        
                        <div class="relative mt-1">
                            <x-text-input id="password" class="block w-full input-field pr-10"
                                            type="password"
                                            name="password"
                                            placeholder="••••••••"
                                            required autocomplete="current-password" />
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500">
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-2">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-bankos-primary shadow-sm focus:ring-bankos-primary dark:focus:ring-bankos-primary" name="remember">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                        </label>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full btn btn-primary py-2.5 text-base">
                            Sign in to bankOS
                        </button>
                    </div>
                </form>

                <div class="mt-8 text-center sm:text-left">
                    <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec">
                        Don't have an account? <a href="mailto:support@bankos.io" class="font-medium text-bankos-primary hover:underline">Contact your administrator</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple inline script for password toggle -->
    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const eye = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eye.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                pwd.type = 'password';
                eye.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }
    </script>
</x-guest-layout>
