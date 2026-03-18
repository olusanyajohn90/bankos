@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payroll Setup</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure pay grades, components, staff salaries and bank details.</p>
        </div>
    </div>

    @include('payroll._tabs', ['active' => 'setup'])

    @if(session('success'))
        <div class="rounded-lg bg-green-50 dark:bg-green-900/20 p-4 text-sm text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4 text-sm text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tab Navigation --}}
    <div x-data="{ tab: '{{ request('tab', 'grades') }}' }">

        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                @foreach(['grades' => 'Pay Grades', 'components' => 'Pay Components', 'configs' => 'Staff Salaries', 'banks' => 'Bank Details'] as $key => $label)
                <button @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors">
                    {{ $label }}
                </button>
                @endforeach
            </nav>
        </div>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- TAB 1: PAY GRADES                                       --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'grades'" x-transition>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pay Grade Structure</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nigerian MFB salary structure: Levels 1–17, each with Grades 1–3. Higher grade = higher salary step within same level.</p>
                </div>
                <button x-data @click="$dispatch('open-modal', 'add-grade')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Pay Grade
                </button>
            </div>

            {{-- Level-Grade Matrix --}}
            @if($gradesByLevel->isEmpty())
            <div class="text-center py-16 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <p class="text-gray-500 dark:text-gray-400 font-medium">No pay grades configured</p>
                <p class="text-sm text-gray-400 mt-1">Add your first pay grade to get started</p>
            </div>
            @else
            <div class="space-y-4">
                @foreach($gradesByLevel as $level => $grades)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    {{-- Level header --}}
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-5 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-white font-bold text-sm">LEVEL {{ str_pad($level, 2, '0', STR_PAD_LEFT) }}</span>
                            @php $firstGrade = $grades->first(); @endphp
                            @if($firstGrade->typical_title)
                            <span class="text-blue-100 text-xs">— {{ $firstGrade->typical_title }}</span>
                            @endif
                        </div>
                        <span class="text-blue-100 text-xs">{{ $grades->count() }} grade(s)</span>
                    </div>

                    {{-- Grades within this level --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-px bg-gray-100 dark:bg-gray-700">
                        @foreach($grades->sortBy('grade') as $grade)
                        <div class="bg-white dark:bg-gray-800 p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                            Grade {{ $grade->grade }}
                                        </span>
                                        <span class="text-xs text-gray-400 font-mono">{{ $grade->code }}</span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mt-1">{{ $grade->name }}</p>
                                    @if($grade->typical_title && $grade->level > 1)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $grade->typical_title }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1 ml-2 shrink-0">
                                    @if($grade->is_active)
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    @else
                                    <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                    @endif
                                    <button
                                        x-data
                                        @click="$dispatch('open-modal', 'edit-grade-{{ $grade->id }}')"
                                        class="p-1 text-gray-400 hover:text-blue-600 rounded transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">Min Basic</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">₦{{ number_format($grade->basic_min) }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">Max Basic</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">₦{{ number_format($grade->basic_max) }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">Annual Increment</span>
                                    <span class="font-medium text-emerald-600">{{ $grade->annual_increment_pct }}%</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">Leave Allowance</span>
                                    <span class="font-medium text-amber-600">{{ $grade->leave_allowance_pct }}% of basic</span>
                                </div>
                            </div>

                            {{-- Salary range bar --}}
                            @php
                                $maxPossible = $gradesByLevel->flatten()->max('basic_max');
                                $fillPct = $maxPossible > 0 ? min(100, ($grade->basic_max / $maxPossible) * 100) : 0;
                            @endphp
                            <div class="mt-3">
                                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $fillPct }}%"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Edit modal per grade --}}
                        <x-modal name="edit-grade-{{ $grade->id }}" title="Edit Grade — Level {{ $grade->level }} Grade {{ $grade->grade }}">
                            <form action="{{ route('payroll.setup.pay-grades.update', $grade) }}" method="POST" class="space-y-4 p-4">
                                @csrf @method('PATCH')
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Level</label>
                                        <input type="number" name="level" value="{{ $grade->level }}" min="1" max="17" required
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Grade</label>
                                        <input type="number" name="grade" value="{{ $grade->grade }}" min="1" max="10" required
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code</label>
                                        <input type="text" name="code" value="{{ $grade->code }}" required maxlength="20"
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 uppercase">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                                        <input type="text" name="name" value="{{ $grade->name }}" required maxlength="100"
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Typical Job Title</label>
                                    <input type="text" name="typical_title" value="{{ $grade->typical_title }}" maxlength="100"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2"
                                        placeholder="e.g. Senior Manager">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min Basic (₦)</label>
                                        <input type="number" name="basic_min" value="{{ $grade->basic_min }}" min="0" step="1000" required
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Basic (₦)</label>
                                        <input type="number" name="basic_max" value="{{ $grade->basic_max }}" min="0" step="1000" required
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Annual Increment (%)</label>
                                        <input type="number" name="annual_increment_pct" value="{{ $grade->annual_increment_pct }}" min="0" max="50" step="0.5"
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Leave Allowance (%)</label>
                                        <input type="number" name="leave_allowance_pct" value="{{ $grade->leave_allowance_pct }}" min="0" max="100" step="0.5"
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" id="ia_{{ $grade->id }}" {{ $grade->is_active ? 'checked' : '' }}
                                        class="w-4 h-4 text-blue-600 rounded border-gray-300">
                                    <label for="ia_{{ $grade->id }}" class="text-sm text-gray-700 dark:text-gray-300">Active</label>
                                </div>
                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" x-data @click="$dispatch('close-modal')"
                                        class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </x-modal>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Summary stats --}}
            @if($payGrades->count() > 0)
            <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $payGrades->count() }}</div>
                    <div class="text-xs text-blue-500 mt-0.5">Total Grades</div>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ $gradesByLevel->count() }}</div>
                    <div class="text-xs text-purple-500 mt-0.5">Levels</div>
                </div>
                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">₦{{ number_format($payGrades->min('basic_min') / 1000, 0) }}k</div>
                    <div class="text-xs text-emerald-500 mt-0.5">Lowest Min Basic</div>
                </div>
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-amber-700 dark:text-amber-300">₦{{ number_format($payGrades->max('basic_max') / 1_000_000, 1) }}M</div>
                    <div class="text-xs text-amber-500 mt-0.5">Highest Max Basic</div>
                </div>
            </div>
            @endif
        </div>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- TAB 2: PAY COMPONENTS                                   --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'components'" x-transition>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pay Components</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Earnings and deductions applied across all staff payroll runs.</p>
                </div>
                <button x-data @click="$dispatch('open-modal', 'add-component')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Component
                </button>
            </div>

            @php
                $earnings   = $payComponents->where('type', 'earning');
                $deductions = $payComponents->where('type', 'deduction');
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Earnings --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="font-semibold text-sm text-gray-800 dark:text-gray-200">Earnings ({{ $earnings->count() }})</h3>
                    </div>
                    @if($earnings->isEmpty())
                    <div class="p-8 text-center text-gray-400 text-sm">No earning components</div>
                    @else
                    <div class="divide-y divide-gray-50 dark:divide-gray-700">
                        @foreach($earnings as $comp)
                        <div class="px-5 py-3 flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $comp->name }}</span>
                                    @if($comp->is_statutory)
                                    <span class="text-xs px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded">Statutory</span>
                                    @endif
                                    @if(!$comp->is_taxable)
                                    <span class="text-xs px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded">Tax-exempt</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $comp->code }} ·
                                    @if($comp->computation_type === 'fixed')
                                        Fixed ₦{{ number_format($comp->value) }}
                                    @elseif($comp->computation_type === 'percentage_of_basic')
                                        {{ $comp->value }}% of Basic
                                    @elseif($comp->computation_type === 'percentage_of_gross')
                                        {{ $comp->value }}% of Gross
                                    @else
                                        Formula: {{ $comp->formula_key }}
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($comp->is_active)
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                @else
                                <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                @endif
                                <button x-data @click="$dispatch('open-modal', 'edit-comp-{{ $comp->id }}')"
                                    class="p-1 text-gray-400 hover:text-blue-600 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                            </div>
                        </div>
                        @include('payroll.setup._component_modal', ['comp' => $comp, 'action' => route('payroll.setup.pay-components.update', $comp), 'method' => 'PATCH'])
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Deductions --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                        <h3 class="font-semibold text-sm text-gray-800 dark:text-gray-200">Deductions ({{ $deductions->count() }})</h3>
                    </div>
                    @if($deductions->isEmpty())
                    <div class="p-8 text-center text-gray-400 text-sm">No deduction components</div>
                    @else
                    <div class="divide-y divide-gray-50 dark:divide-gray-700">
                        @foreach($deductions as $comp)
                        <div class="px-5 py-3 flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $comp->name }}</span>
                                    @if($comp->is_statutory)
                                    <span class="text-xs px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded">Statutory</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $comp->code }} ·
                                    @if($comp->computation_type === 'fixed')
                                        Fixed ₦{{ number_format($comp->value) }}
                                    @elseif($comp->computation_type === 'percentage_of_basic')
                                        {{ $comp->value }}% of Basic
                                    @elseif($comp->computation_type === 'percentage_of_gross')
                                        {{ $comp->value }}% of Gross
                                    @else
                                        Formula: {{ $comp->formula_key }}
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($comp->is_active)
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                @else
                                <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                @endif
                                <button x-data @click="$dispatch('open-modal', 'edit-comp-{{ $comp->id }}')"
                                    class="p-1 text-gray-400 hover:text-blue-600 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                            </div>
                        </div>
                        @include('payroll.setup._component_modal', ['comp' => $comp, 'action' => route('payroll.setup.pay-components.update', $comp), 'method' => 'PATCH'])
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Nigerian statutory deductions info --}}
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
                    <div class="text-xs font-bold text-amber-700 dark:text-amber-300 mb-1">PAYE (Income Tax)</div>
                    <p class="text-xs text-amber-600 dark:text-amber-400">Finance Act 2021: 7%–24% progressive rates. Automatically computed from CRA deduction.</p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                    <div class="text-xs font-bold text-blue-700 dark:text-blue-300 mb-1">Pension (PRA 2014)</div>
                    <p class="text-xs text-blue-600 dark:text-blue-400">Employee: 8% of Basic+Housing+Transport. Employer: 10% (employer portion not deducted from net pay).</p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800 rounded-lg p-3">
                    <div class="text-xs font-bold text-green-700 dark:text-green-300 mb-1">NHF / NSITF</div>
                    <p class="text-xs text-green-600 dark:text-green-400">NHF: 2.5% of basic salary. NSITF: 1% of gross emolument (employer liability).</p>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- TAB 3: STAFF SALARIES                                   --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'configs'" x-transition>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Staff Salary Configurations</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Individual salary packages for each staff member.</p>
                </div>
                <button x-data @click="$dispatch('open-modal', 'add-config')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Assign Salary
                </button>
            </div>

            @if($staffPayConfigs->isEmpty())
            <div class="text-center py-16 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                <p class="text-gray-500">No staff salary configurations yet.</p>
            </div>
            @else
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Staff</th>
                            <th class="px-4 py-3 text-left">Grade</th>
                            <th class="px-4 py-3 text-right">Basic</th>
                            <th class="px-4 py-3 text-right">Gross Est.</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">PFA</th>
                            <th class="px-4 py-3 text-left">Effective</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                        @foreach($staffPayConfigs as $config)
                        @php
                            $gross = $config->basic_salary + $config->housing_allowance + $config->transport_allowance + $config->meal_allowance;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ $config->staffProfile->user?->name ?? 'Unknown' }}</div>
                                <div class="text-xs text-gray-400">{{ $config->staffProfile->job_title }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($config->payGrade)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                    L{{ str_pad($config->payGrade->level, 2, '0', STR_PAD_LEFT) }}G{{ $config->payGrade->grade }}
                                </span>
                                @else
                                <span class="text-gray-400 text-xs">Ungraded</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-800 dark:text-gray-200">₦{{ number_format($config->basic_salary) }}</td>
                            <td class="px-4 py-3 text-right text-emerald-600 font-medium">₦{{ number_format($gross) }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500 hidden md:table-cell">{{ $config->pension_fund_administrator ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ \Carbon\Carbon::parse($config->effective_date)->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <button x-data @click="$dispatch('open-modal', 'edit-config-{{ $config->id }}')"
                                    class="p-1 text-gray-400 hover:text-blue-600 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <x-modal name="edit-config-{{ $config->id }}" title="Edit Salary — {{ $config->staffProfile->user?->name }}">
                                    @include('payroll.setup._salary_form', ['config' => $config, 'staff' => $staff, 'payGrades' => $payGrades, 'action' => route('payroll.setup.pay-configs.store'), 'submitLabel' => 'Update'])
                                </x-modal>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $staffPayConfigs->links() }}
                </div>
            </div>
            @endif
        </div>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- TAB 4: BANK DETAILS                                     --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'banks'" x-transition>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Staff Bank Details</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Bank accounts for salary disbursement.</p>
                </div>
                <button x-data @click="$dispatch('open-modal', 'add-bank')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Bank Account
                </button>
            </div>

            @if($bankDetails->isEmpty())
            <div class="text-center py-16 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                <p class="text-gray-500">No bank details on file.</p>
            </div>
            @else
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Staff</th>
                            <th class="px-4 py-3 text-left">Bank</th>
                            <th class="px-4 py-3 text-left">Account Number</th>
                            <th class="px-4 py-3 text-left">Account Name</th>
                            <th class="px-4 py-3 text-center">Primary</th>
                            <th class="px-4 py-3 text-center">Verified</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                        @foreach($bankDetails as $bank)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{{ $bank->staffProfile->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $bank->bank_name }}</td>
                            <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-300">{{ $bank->account_number }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $bank->account_name }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($bank->is_primary)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">Primary</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($bank->is_verified)
                                <span class="inline-flex items-center gap-1 text-xs text-green-600"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Verified</span>
                                @else
                                <span class="text-xs text-gray-400">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

    </div>{{-- end x-data tab --}}
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- MODALS                                                              --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}

{{-- Add Pay Grade --}}
<x-modal name="add-grade" title="Add Pay Grade">
    <form action="{{ route('payroll.setup.pay-grades.store') }}" method="POST" class="space-y-4 p-4">
        @csrf
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-xs text-blue-700 dark:text-blue-300">
            Nigerian MFB structure: Levels 1–17 (entry to executive). Each level has Grades 1–3 representing salary steps.
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Level <span class="text-red-500">*</span></label>
                <select name="level" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                    @for($i = 1; $i <= 17; $i++)
                    <option value="{{ $i }}">Level {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Grade <span class="text-red-500">*</span></label>
                <select name="grade" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                    @for($g = 1; $g <= 5; $g++)
                    <option value="{{ $g }}">Grade {{ $g }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" required maxlength="20" placeholder="e.g. L01G01"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 uppercase"
                    x-data
                    @change="$el.value = $el.value.toUpperCase()">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required maxlength="100" placeholder="e.g. Level 1 Grade 1"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Typical Job Title</label>
            <input type="text" name="typical_title" maxlength="100" placeholder="e.g. Junior Officer"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min Basic Salary (₦) <span class="text-red-500">*</span></label>
                <input type="number" name="basic_min" required min="0" step="1000" placeholder="0"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Basic Salary (₦) <span class="text-red-500">*</span></label>
                <input type="number" name="basic_max" required min="0" step="1000" placeholder="0"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Annual Increment (%)</label>
                <input type="number" name="annual_increment_pct" value="5" min="0" max="50" step="0.5"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                <p class="text-xs text-gray-400 mt-1">% salary increase at annual review</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Leave Allowance (%)</label>
                <input type="number" name="leave_allowance_pct" value="10" min="0" max="100" step="0.5"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                <p class="text-xs text-gray-400 mt-1">% of basic paid as leave bonus</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" id="new_grade_active" checked
                class="w-4 h-4 text-blue-600 rounded border-gray-300">
            <label for="new_grade_active" class="text-sm text-gray-700 dark:text-gray-300">Active</label>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
            <button type="button" x-data @click="$dispatch('close-modal')"
                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
            </button>
            <button type="submit"
                class="px-4 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                Create Grade
            </button>
        </div>
    </form>
</x-modal>

{{-- Add Pay Component --}}
<x-modal name="add-component" title="Add Pay Component">
    @include('payroll.setup._component_modal', ['comp' => null, 'action' => route('payroll.setup.pay-components.store'), 'method' => 'POST'])
</x-modal>

{{-- Add Staff Pay Config --}}
<x-modal name="add-config" title="Assign Staff Salary">
    @include('payroll.setup._salary_form', ['config' => null, 'staff' => $staff, 'payGrades' => $payGrades, 'action' => route('payroll.setup.pay-configs.store'), 'submitLabel' => 'Save Configuration'])
</x-modal>

{{-- Add Bank Detail --}}
<x-modal name="add-bank" title="Add Bank Account">
    <form action="{{ route('payroll.setup.bank-details.store') }}" method="POST" class="space-y-4 p-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Staff Member <span class="text-red-500">*</span></label>
            <select name="staff_profile_id" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                <option value="">Select staff...</option>
                @foreach($staff as $s)
                <option value="{{ $s->id }}">{{ $s->user?->name }} — {{ $s->job_title }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank <span class="text-red-500">*</span></label>
            <select name="bank_name" required x-data x-model="selectedBank"
                @change="const b = $el.options[$el.selectedIndex]; $el.form.querySelector('[name=bank_code]').value = b.dataset.code ?? ''"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                <option value="">Select bank...</option>
                @foreach($nigerianBanks as $bank)
                <option value="{{ $bank['name'] }}" data-code="{{ $bank['code'] }}">{{ $bank['name'] }}</option>
                @endforeach
                <option value="Other">Other</option>
            </select>
        </div>
        <input type="hidden" name="bank_code" value="">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Number <span class="text-red-500">*</span></label>
            <input type="text" name="account_number" required maxlength="10" minlength="10" pattern="[0-9]{10}" placeholder="10-digit NUBAN"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 font-mono">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Name <span class="text-red-500">*</span></label>
            <input type="text" name="account_name" required maxlength="100" placeholder="As it appears on bank records"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 uppercase">
        </div>
        <div class="flex items-center gap-2">
            <input type="hidden" name="is_primary" value="0">
            <input type="checkbox" name="is_primary" value="1" id="bank_primary" checked
                class="w-4 h-4 text-blue-600 rounded border-gray-300">
            <label for="bank_primary" class="text-sm text-gray-700 dark:text-gray-300">Set as primary salary account</label>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
            <button type="button" x-data @click="$dispatch('close-modal')"
                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
            </button>
            <button type="submit"
                class="px-4 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                Add Bank Account
            </button>
        </div>
    </form>
</x-modal>

@endsection
