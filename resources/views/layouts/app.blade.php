<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'bankOS') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- CDN dependencies for simple components inline (optional) -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- Tenant White-Label CSS Variables -->
        <style>
        :root {
            --bankos-primary: {{ $tenantBranding['primary_color'] ?? '#2563eb' }};
            --bankos-secondary: {{ $tenantBranding['secondary_color'] ?? '#0c2461' }};
        }
        </style>
    </head>
    <body x-data="{ sidebarOpen: false }" class="bg-bankos-bg dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text font-sans antialiased" :class="{ 'overflow-hidden md:overflow-auto': sidebarOpen }">
        
        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm md:hidden" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;"></div>

        <!-- Sidebar Navigation -->
        <x-sidebar />

        <!-- Main Content Wrapper -->
        <div class="md:pl-64 flex flex-col flex-1 min-h-screen">
            <!-- Header -->
            <x-header />

            <!-- Main Content Area -->
            <main class="flex-1 pb-8">
                <!-- Page Header (Optional slot) -->
                @if (isset($header))
                    <div class="px-4 sm:px-6 lg:px-8 py-6 border-b border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-surface/50">
                        {{ $header }}
                    </div>
                @endif

                <!-- Page Content -->
                <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
                    <!-- Global Flash Messages -->
                    @if (session('success'))
                        <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/30 p-4 md:p-5 border border-green-200 dark:border-green-800 shadow-sm">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                </div>
                                <div class="ml-3 w-full">
                                    <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/30 p-4 md:p-5 border border-red-200 dark:border-red-800 shadow-sm">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                </div>
                                <div class="ml-3 w-full">
                                    <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/30 p-4 md:p-5 border border-red-200 dark:border-red-800 shadow-sm">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-500 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                </div>
                                <div class="ml-3 w-full">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Please correct the following errors:</h3>
                                    <div class="mt-2 text-sm text-red-700 dark:text-red-400">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- End Flash Messages -->

                    @hasSection('content')
                        @yield('content')
                    @else
                        {{ $slot }}
                    @endif
                </div>
            </main>
        </div>
        @stack('scripts')
    </body>
</html>
