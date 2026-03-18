@extends('layouts.app')

@section('content')
<style>
    @media print {
        .no-print { display: none !important; }
        body { background: #fff !important; color: #000 !important; }
        .payslip-wrapper { box-shadow: none !important; border: 1px solid #ccc !important; }
    }
</style>

<div class="max-w-4xl mx-auto space-y-4">

    {{-- Action Buttons (no-print) --}}
    <div class="no-print flex items-center justify-between">
        <a href="{{ url()->previous() }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print
            </button>
            <a href="{{ route('payroll.payslip.download', $item) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download PDF
            </a>
        </div>
    </div>

    {{-- Payslip Document --}}
    <div class="payslip-wrapper bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden print:shadow-none print:rounded-none">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-700 to-blue-900 text-white px-8 py-6">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    {{-- Logo placeholder --}}
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">{{ $run->tenant?->name ?? config('app.name', 'bankOS') }}</h1>
                        <p class="text-blue-200 text-sm mt-0.5">Employee Pay Slip</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold tracking-wide">PAY SLIP</p>
                    <p class="text-blue-200 text-sm mt-1">Pay Period: {{ $run->period_label }}</p>
                    <p class="text-blue-200 text-xs mt-0.5">Status:
                        <span class="uppercase font-semibold text-white">{{ $run->status }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="px-8 py-6 space-y-6">

            {{-- Staff & Payroll Info --}}
            <div class="grid grid-cols-2 gap-6">
                {{-- Staff Info --}}
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Employee Information</h3>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Name</span>
                        <span class="font-semibold text-gray-900">{{ $user?->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Employee No.</span>
                        <span class="font-mono font-medium text-gray-900">{{ $staff?->employee_number ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Job Title</span>
                        <span class="font-medium text-gray-900">{{ $staff?->job_title ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Department</span>
                        <span class="font-medium text-gray-900">{{ $staff?->orgDepartment?->name ?? $staff?->department ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Grade</span>
                        <span class="font-medium text-gray-900">{{ $staff?->payConfig?->payGrade?->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Date of Employment</span>
                        <span class="font-medium text-gray-900">{{ $staff?->joined_date?->format('d M Y') ?? '—' }}</span>
                    </div>
                </div>

                {{-- Payroll Info --}}
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Payroll Information</h3>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Pay Period</span>
                        <span class="font-semibold text-gray-900">{{ $run->period_label }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Pay Date</span>
                        <span class="font-medium text-gray-900">{{ $run->paid_at?->format('d M Y') ?? ($run->approved_at?->format('d M Y') ?? '—') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Run Status</span>
                        <span class="font-medium text-gray-900 uppercase">{{ $run->status }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Tax ID (TIN)</span>
                        <span class="font-medium text-gray-900">{{ $staff?->payConfig?->tax_id ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Pension Acct</span>
                        <span class="font-medium text-gray-900">{{ $staff?->payConfig?->pension_account_number ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">NHF No.</span>
                        <span class="font-medium text-gray-900">{{ $staff?->payConfig?->nhf_number ?? '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- Earnings & Deductions Table --}}
            <div class="grid grid-cols-2 gap-6">
                {{-- Earnings --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Earnings</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th class="text-left pb-2 font-semibold text-gray-700">Description</th>
                                <th class="text-right pb-2 font-semibold text-gray-700">Amount (₦)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($earnings as $line)
                            <tr>
                                <td class="py-1.5 text-gray-700">{{ $line->component_name }}</td>
                                <td class="py-1.5 text-right text-gray-900">{{ number_format($line->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-300">
                                <td class="pt-2 font-bold text-gray-900">GROSS SALARY</td>
                                <td class="pt-2 font-bold text-right text-gray-900">₦{{ number_format($item->gross_salary, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Deductions --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Deductions</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th class="text-left pb-2 font-semibold text-gray-700">Description</th>
                                <th class="text-right pb-2 font-semibold text-gray-700">Amount (₦)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($deductions as $line)
                            <tr>
                                <td class="py-1.5 text-gray-700">{{ $line->component_name }}</td>
                                <td class="py-1.5 text-right text-red-700">{{ number_format($line->amount, 2) }}</td>
                            </tr>
                            @endforeach
                            @foreach($other_deductions as $line)
                            <tr>
                                <td class="py-1.5 text-gray-700">{{ $line->component_name }}</td>
                                <td class="py-1.5 text-right text-red-700">{{ number_format($line->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-300">
                                <td class="pt-2 font-bold text-gray-900">TOTAL DEDUCTIONS</td>
                                <td class="pt-2 font-bold text-right text-red-700">₦{{ number_format($item->total_deductions, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Net Pay Box --}}
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-800 rounded-xl p-6 text-white text-center">
                <p class="text-sm font-medium text-emerald-200 uppercase tracking-widest mb-1">Net Pay</p>
                <p class="text-4xl font-extrabold tracking-tight">₦{{ number_format($item->net_salary, 2) }}</p>
                <p class="text-emerald-200 text-sm mt-1">{{ $run->period_label }}</p>
            </div>

            {{-- Bank Payment Info --}}
            @if($bank)
            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Bank Payment Details</h3>
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Bank Name</p>
                        <p class="font-semibold text-gray-900 mt-0.5">{{ $bank->bank_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Account Number</p>
                        <p class="font-mono font-semibold text-gray-900 mt-0.5">****{{ substr($bank->account_number, -4) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Account Name</p>
                        <p class="font-semibold text-gray-900 mt-0.5">{{ $bank->account_name }}</p>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                No primary bank account configured for this staff member.
            </div>
            @endif

            {{-- Statutory Summary --}}
            <div class="grid grid-cols-4 gap-3">
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">PAYE</p>
                    <p class="font-bold text-orange-700 text-sm mt-0.5">₦{{ number_format($item->paye, 2) }}</p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Employee Pension</p>
                    <p class="font-bold text-purple-700 text-sm mt-0.5">₦{{ number_format($item->employee_pension, 2) }}</p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Employer Pension</p>
                    <p class="font-bold text-indigo-700 text-sm mt-0.5">₦{{ number_format($item->employer_pension, 2) }}</p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">NHF</p>
                    <p class="font-bold text-yellow-700 text-sm mt-0.5">₦{{ number_format($item->nhf, 2) }}</p>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 border-t border-gray-200 px-8 py-4 text-center">
            <p class="text-xs text-gray-400">This is a computer-generated payslip and does not require a signature. | {{ config('app.name', 'bankOS') }} Payroll System | Generated: {{ now()->format('d M Y H:i') }}</p>
        </div>

    </div>
</div>
@endsection
