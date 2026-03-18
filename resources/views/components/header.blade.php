<header class="bg-white dark:bg-bankos-dark-surface border-b border-bankos-border dark:border-bankos-dark-border h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-40 sticky top-0">
    <div class="flex items-center gap-4">
        <!-- Mobile menu button -->
        <button @click="sidebarOpen = true" class="md:hidden p-2 -ml-2 text-bankos-muted hover:text-bankos-text dark:hover:text-white rounded-md">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>

        <!-- Current Institution -->
        @if(auth()->user()->tenant)
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-bankos-dark-bg rounded-lg">
            <div class="w-2 h-2 rounded-full bg-green-500"></div>
            <span class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">{{ auth()->user()->tenant->name }}</span>
            <span class="text-xs text-bankos-muted ml-1">({{ auth()->user()->tenant->short_name }})</span>
        </div>
        @else
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-bankos-dark-bg rounded-lg">
            <span class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">System Administration</span>
        </div>
        @endif
    </div>

    <div class="flex items-center gap-3">
        <!-- Theme Toggle -->
        <button @click="darkMode = !darkMode" class="p-2 text-bankos-muted hover:text-bankos-text dark:hover:text-white rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-bankos-primary focus:ring-offset-2">
            <!-- Sun icon for dark mode -->
            <svg x-show="darkMode" class="w-5 h-5 hidden dark:block" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="4.22" x2="19.78" y2="5.64"></line></svg>
            <!-- Moon icon for light mode -->
            <svg x-show="!darkMode" class="w-5 h-5 dark:hidden" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>

        <!-- Notifications -->
        <button class="relative p-2 text-bankos-muted hover:text-bankos-text dark:hover:text-white rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-bankos-primary focus:ring-offset-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            <span class="absolute top-1 right-1 flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-bankos-danger"></span>
            </span>
        </button>

        <div class="w-px h-6 bg-bankos-border dark:bg-bankos-dark-border mx-1"></div>

        <!-- User Dropdown (Alpine.js) -->
        <div class="relative" x-data="{ open: false }" @click.away="open = false" @close.stop="open = false">
            <button @click="open = ! open" class="flex items-center gap-2 focus:outline-none">
                <div class="w-8 h-8 rounded-full bg-bankos-primary text-white flex items-center justify-center font-bold text-sm shadow-sm ring-2 ring-transparent transition hover:ring-bankos-primary/50">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="hidden md:flex flex-col items-start leading-none gap-0.5">
                    <span class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">{{ Auth::user()->getFirstNameAttribute() }}</span>
                    <span class="text-[10px] uppercase tracking-wider text-bankos-muted font-bold">{{ Auth::user()->roles->first()->name ?? 'User' }}</span>
                </div>
                <svg class="w-4 h-4 ml-1 text-bankos-muted" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 transform"
                 x-transition:enter-end="opacity-100 scale-100 transform"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100 transform"
                 x-transition:leave-end="opacity-0 scale-95 transform"
                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-bankos-dark-surface rounded-xl shadow-lg border border-bankos-border dark:border-bankos-dark-border py-1 z-50 divide-y divide-gray-100 dark:divide-bankos-dark-border"
                 style="display: none;">
                
                <div class="px-4 py-3 sm:hidden">
                    <p class="text-sm text-bankos-text dark:text-bankos-dark-text font-medium">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-bankos-muted truncate">{{ Auth::user()->email }}</p>
                </div>

                <div class="py-1">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors">
                        Your Profile
                    </a>
                    <a href="#" class="block px-4 py-2 text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors">
                        Preferences
                    </a>
                </div>
                
                <div class="py-1">
                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-bankos-danger hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors font-medium">
                            Log out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
