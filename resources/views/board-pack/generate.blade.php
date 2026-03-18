<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Board Pack Generator</h2>
            <p class="text-sm text-bankos-text-sec mt-1">Generate a comprehensive PDF report for your board of directors</p>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <div class="max-w-2xl space-y-5">

        <div class="card">
            <h3 class="font-bold mb-1">What's included</h3>
            <p class="text-sm text-gray-500 mb-4">Select the sections to include in the board pack PDF:</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach($sections as $key => $label)
                <div class="flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ $label }}
                </div>
                @endforeach
            </div>
        </div>

        <div class="card" x-data="{
            periodType: 'monthly',
            get periodLabel() {
                if (this.periodType === 'monthly') return 'Select a single calendar month';
                if (this.periodType === 'quarterly') return 'Select a quarter of a year';
                if (this.periodType === 'annual')    return 'Full calendar year — all 12 months';
                if (this.periodType === 'range')     return 'Custom from-month to to-month range';
                return '';
            }
        }">
            <h3 class="font-bold mb-4">Configure Board Pack</h3>
            <form method="POST" action="{{ route('board-pack.download') }}">
                @csrf

                {{-- Period Type --}}
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Reporting Period Type *</label>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @foreach(['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'annual' => 'Annual', 'range' => 'Custom Range'] as $val => $lbl)
                        <label class="relative cursor-pointer" @click="periodType = '{{ $val }}'">
                            <input type="radio" name="period_type" value="{{ $val }}" x-model="periodType" class="sr-only">
                            <div :class="periodType === '{{ $val }}' ? 'border-bankos-primary bg-blue-50 text-bankos-primary' : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                 class="border rounded-lg px-3 py-2.5 text-sm font-medium text-center transition-colors">
                                {{ $lbl }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-2" x-text="periodLabel"></p>
                </div>

                {{-- Monthly --}}
                <div x-show="periodType === 'monthly'" class="mb-4">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Month *</label>
                    <select name="period" class="input w-full">
                        @foreach($months as $m)
                        <option value="{{ $m['value'] }}" {{ $loop->first ? 'selected' : '' }}>{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Quarterly --}}
                <div x-show="periodType === 'quarterly'" class="mb-4 grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Year *</label>
                        <select name="year" class="input w-full">
                            @foreach($years as $y)
                            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Quarter *</label>
                        <select name="quarter" class="input w-full">
                            @foreach($quarters as $q => $ql)
                            <option value="{{ $q }}" {{ $q == ceil(now()->month / 3) ? 'selected' : '' }}>{{ $ql }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Annual --}}
                <div x-show="periodType === 'annual'" class="mb-4">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Year *</label>
                    <select name="annual_year" class="input w-full">
                        @foreach($years as $y)
                        <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Custom Range --}}
                <div x-show="periodType === 'range'" class="mb-4 grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">From Month *</label>
                        <select name="from_month" class="input w-full">
                            @foreach($months as $m)
                            <option value="{{ $m['value'] }}">{{ $m['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">To Month *</label>
                        <select name="to_month" class="input w-full">
                            @foreach($months as $m)
                            <option value="{{ $m['value'] }}" {{ $loop->first ? 'selected' : '' }}>{{ $m['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Sections --}}
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-semibold text-gray-500">Include Sections</label>
                        <div class="flex gap-3 text-xs text-bankos-primary font-medium">
                            <button type="button" onclick="document.querySelectorAll('input[name=\'sections[]\']').forEach(c=>c.checked=true)">All</button>
                            <span class="text-gray-300">|</span>
                            <button type="button" onclick="document.querySelectorAll('input[name=\'sections[]\']').forEach(c=>c.checked=false)">None</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach($sections as $key => $label)
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" name="sections[]" value="{{ $key }}" checked class="rounded">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Generate & Download PDF
                </button>
                <p class="text-xs text-gray-400 mt-2 text-center">PDF generation may take 15–30 seconds depending on data volume.</p>
            </form>
        </div>
    </div>
</x-app-layout>
