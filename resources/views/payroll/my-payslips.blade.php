@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Payslips</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View and download your monthly payslips.</p>
    </div>

    @include('payroll._tabs', ['active' => 'my-payslips'])

    @if(!$staffProfile)
        {{-- No staff profile --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6 flex items-start gap-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-200">Staff Profile Not Configured</h3>
                <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">Your staff profile is not yet configured. Please contact HR or your payroll administrator to set up your profile.</p>
            </div>
        </div>
    @else

        {{-- Summary Strip --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Latest Gross Pay</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">₦{{ number_format($latestGross, 2) }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Most recent payroll</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Latest Net Pay</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">₦{{ number_format($latestNet, 2) }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">After all deductions</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Earned {{ now()->year }}</p>
                <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">₦{{ number_format($totalThisYear, 2) }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Net pay year-to-date</p>
            </div>
        </div>

        {{-- Payslips Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Payslip History</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $payrollItems->total() }} records</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Period</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gross (₦)</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Deductions (₦)</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Net Pay (₦)</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($payrollItems as $payrollItem)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $payrollItem->payrollRun?->period_label ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">
                                ₦{{ number_format($payrollItem->gross_salary, 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-red-700 dark:text-red-400">
                                ₦{{ number_format($payrollItem->total_deductions, 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-bold text-emerald-700 dark:text-emerald-400">
                                ₦{{ number_format($payrollItem->net_salary, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @switch($payrollItem->payment_status)
                                    @case('paid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Paid</span>
                                        @break
                                    @case('failed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">Failed</span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300">Pending</span>
                                @endswitch
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('payroll.payslip.show', $payrollItem) }}" class="inline-flex items-center px-2.5 py-1 bg-blue-100 dark:bg-blue-900/40 hover:bg-blue-200 text-blue-800 dark:text-blue-300 text-xs font-medium rounded transition-colors">
                                        View
                                    </a>
                                    <a href="{{ route('payroll.payslip.download', $payrollItem) }}" class="inline-flex items-center px-2.5 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-medium rounded transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Download
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                                No payslips available yet. Payslips will appear here once payroll is processed and approved.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($payrollItems->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $payrollItems->links() }}
                </div>
            @endif
        </div>

    @endif

</div>
@endsection
