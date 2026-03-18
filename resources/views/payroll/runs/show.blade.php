@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('payroll.runs.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">Payroll Runs</a>
                <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $payrollRun->period_label }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $payrollRun->period_label }} Payroll Run</h1>
            @if($payrollRun->notes)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $payrollRun->notes }}</p>
            @endif
        </div>

        {{-- Status Badge + Actions --}}
        <div class="flex items-center gap-3 flex-wrap">
            @switch($payrollRun->status)
                @case('draft')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Draft</span>
                    @break
                @case('processing')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Processing</span>
                    @break
                @case('approved')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">Approved</span>
                    @break
                @case('paid')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Paid</span>
                    @break
                @case('cancelled')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">Cancelled</span>
                    @break
            @endswitch

            {{-- Process --}}
            @if($payrollRun->status === 'draft')
            <form action="{{ route('payroll.runs.process', $payrollRun) }}" method="POST" onsubmit="return confirm('Process payroll for {{ $payrollRun->period_label }}? This will compute all active staff salaries.')">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Process Run
                </button>
            </form>
            @endif

            {{-- Approve --}}
            @if($payrollRun->status === 'processing')
            <form action="{{ route('payroll.runs.approve', $payrollRun) }}" method="POST" onsubmit="return confirm('Approve payroll run for {{ $payrollRun->period_label }}?')">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Approve Run
                </button>
            </form>
            @endif

            {{-- Mark Paid --}}
            @if($payrollRun->status === 'approved')
            <form action="{{ route('payroll.runs.mark-paid', $payrollRun) }}" method="POST" onsubmit="return confirm('Mark this payroll run as PAID? This will update all staff payment records.')">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Mark as Paid
                </button>
            </form>
            @endif

            {{-- Cancel --}}
            @if(!in_array($payrollRun->status, ['paid', 'cancelled']))
            <form action="{{ route('payroll.runs.cancel', $payrollRun) }}" method="POST" onsubmit="return confirm('Cancel this payroll run? This action cannot be undone.')">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 text-red-700 dark:text-red-400 text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Cancel Run
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Run Metadata --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Run By</p>
                <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ $payrollRun->runBy?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Approved By</p>
                <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ $payrollRun->approvedBy?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Approved At</p>
                <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ $payrollRun->approved_at?->format('d M Y H:i') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Paid At</p>
                <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ $payrollRun->paid_at?->format('d M Y H:i') ?? '—' }}</p>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gross Salary</p>
            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">₦{{ number_format($payrollRun->total_gross ?? 0, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Deductions</p>
            <p class="mt-1 text-lg font-bold text-red-600 dark:text-red-400">₦{{ number_format($payrollRun->total_deductions ?? 0, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Net Pay</p>
            <p class="mt-1 text-lg font-bold text-emerald-600 dark:text-emerald-400">₦{{ number_format($payrollRun->total_net ?? 0, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Staff Count</p>
            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $payrollRun->staff_count ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">PAYE</p>
            <p class="mt-1 text-lg font-bold text-orange-600 dark:text-orange-400">₦{{ number_format($payrollRun->total_paye ?? 0, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pension (Employee)</p>
            <p class="mt-1 text-lg font-bold text-purple-600 dark:text-purple-400">₦{{ number_format($payrollRun->total_pension_employee ?? 0, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pension (Employer)</p>
            <p class="mt-1 text-lg font-bold text-indigo-600 dark:text-indigo-400">₦{{ number_format($payrollRun->total_pension_employer ?? 0, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">NHF</p>
            <p class="mt-1 text-lg font-bold text-yellow-600 dark:text-yellow-400">₦{{ number_format($payrollRun->total_nhf ?? 0, 2) }}</p>
        </div>
    </div>

    {{-- Payroll Items Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Staff Payroll Details</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Staff Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Emp. No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Grade</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gross (₦)</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">PAYE (₦)</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pension (₦)</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">NHF (₦)</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Net Pay (₦)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bank Acct</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pay Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($items as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $item->staffProfile?->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-gray-600 dark:text-gray-400">{{ $item->staffProfile?->employee_number ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->staffProfile?->payConfig?->payGrade?->code ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">₦{{ number_format($item->gross_salary, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right text-orange-700 dark:text-orange-400">₦{{ number_format($item->paye, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right text-purple-700 dark:text-purple-400">₦{{ number_format($item->employee_pension, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right text-yellow-700 dark:text-yellow-400">₦{{ number_format($item->nhf, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-emerald-700 dark:text-emerald-400">₦{{ number_format($item->net_salary, 2) }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-gray-600 dark:text-gray-400">
                            @if($item->bankDetail)
                                ****{{ substr($item->bankDetail->account_number, -4) }}
                            @else
                                <span class="text-red-500 text-xs">No account</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @switch($item->payment_status)
                                @case('paid')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Paid</span>
                                    @break
                                @case('failed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">Failed</span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Pending</span>
                            @endswitch
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('payroll.payslip.show', $item) }}" class="inline-flex items-center px-2.5 py-1 bg-blue-100 dark:bg-blue-900/40 hover:bg-blue-200 text-blue-800 dark:text-blue-300 text-xs font-medium rounded transition-colors">
                                Payslip
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-4 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                            No payroll items found. Process this run to compute staff salaries.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $items->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
