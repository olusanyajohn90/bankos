@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payroll Runs</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage monthly payroll processing for all staff.</p>
        </div>
    </div>

    @include('payroll._tabs', ['active' => 'runs'])

    {{-- Summary Strip --}}
    @php
        $allRuns = $runs->getCollection();
        $totalCount       = $runs->total();
        $draftCount       = $allRuns->where('status', 'draft')->count();
        $activeCount      = $allRuns->whereIn('status', ['processing', 'approved'])->count();
        $paidCount        = $allRuns->where('status', 'paid')->count();
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Runs</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $totalCount }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Draft</p>
            <p class="mt-1 text-2xl font-bold text-gray-400">{{ $draftCount }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Processing / Approved</p>
            <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $activeCount }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Paid</p>
            <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $paidCount }}</p>
        </div>
    </div>

    {{-- New Payroll Run Form --}}
    <div x-data="{ open: false }" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="p-4 flex items-center justify-between cursor-pointer select-none" @click="open = !open">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">New Payroll Run</h2>
            <svg :class="open ? 'rotate-180' : ''" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div x-show="open" x-collapse class="border-t border-gray-100 dark:border-gray-700 p-5">
            <form action="{{ route('payroll.runs.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payroll Month <span class="text-red-500">*</span></label>
                        <select name="period_month" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-blue-500 focus:border-blue-500" required>
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                    {{ date('F', mktime(0,0,0,$m,1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Year <span class="text-red-500">*</span></label>
                        <input type="number" name="period_year" value="{{ old('period_year', date('Y')) }}" min="2000" max="2099" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Optional note for this run" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="inline-flex items-center px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create Payroll Run
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Runs Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">All Payroll Runs</h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $runs->total() }} total</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Period</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Staff</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gross Salary (₦)</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Net Pay (₦)</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">PAYE (₦)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Run By</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($runs as $run)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $run->period_label }}</td>
                        <td class="px-4 py-3 text-center">
                            @switch($run->status)
                                @case('draft')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Draft</span>
                                    @break
                                @case('processing')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Processing</span>
                                    @break
                                @case('approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">Approved</span>
                                    @break
                                @case('paid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Paid</span>
                                    @break
                                @case('cancelled')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">Cancelled</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-700 dark:text-gray-300">{{ $run->staff_count ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">
                            {{ $run->total_gross ? '₦' . number_format($run->total_gross, 2) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-white">
                            {{ $run->total_net ? '₦' . number_format($run->total_net, 2) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">
                            {{ $run->total_paye ? '₦' . number_format($run->total_paye, 2) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $run->runBy?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $run->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                {{-- View (all) --}}
                                <a href="{{ route('payroll.runs.show', $run) }}" class="inline-flex items-center px-2.5 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-medium rounded transition-colors">
                                    View
                                </a>

                                {{-- Process (draft only) --}}
                                @if($run->status === 'draft')
                                <form action="{{ route('payroll.runs.process', $run) }}" method="POST" onsubmit="return confirm('Process payroll for {{ $run->period_label }}? This will compute all staff salaries.')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2.5 py-1 bg-blue-100 dark:bg-blue-900/40 hover:bg-blue-200 text-blue-800 dark:text-blue-300 text-xs font-medium rounded transition-colors">
                                        Process
                                    </button>
                                </form>
                                @endif

                                {{-- Approve (processing only) --}}
                                @if($run->status === 'processing')
                                <form action="{{ route('payroll.runs.approve', $run) }}" method="POST" onsubmit="return confirm('Approve payroll run for {{ $run->period_label }}?')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2.5 py-1 bg-green-100 dark:bg-green-900/40 hover:bg-green-200 text-green-800 dark:text-green-300 text-xs font-medium rounded transition-colors">
                                        Approve
                                    </button>
                                </form>
                                @endif

                                {{-- Mark Paid (approved only) --}}
                                @if($run->status === 'approved')
                                <form action="{{ route('payroll.runs.mark-paid', $run) }}" method="POST" onsubmit="return confirm('Mark this payroll run as paid? This action cannot be reversed.')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2.5 py-1 bg-emerald-100 dark:bg-emerald-900/40 hover:bg-emerald-200 text-emerald-800 dark:text-emerald-300 text-xs font-medium rounded transition-colors">
                                        Mark Paid
                                    </button>
                                </form>
                                @endif

                                {{-- Cancel (not paid) --}}
                                @if(!in_array($run->status, ['paid', 'cancelled']))
                                <form action="{{ route('payroll.runs.cancel', $run) }}" method="POST" onsubmit="return confirm('Cancel this payroll run? This cannot be undone.')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2.5 py-1 bg-red-100 dark:bg-red-900/40 hover:bg-red-200 text-red-800 dark:text-red-300 text-xs font-medium rounded transition-colors">
                                        Cancel
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                            No payroll runs found. Create your first run above.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($runs->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $runs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
