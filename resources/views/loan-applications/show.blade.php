<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('loan-applications.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text">Loan Application · {{ $app->reference }}</h2>
                <p class="text-sm text-bankos-text-sec mt-0.5">{{ $app->customer_name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Application details --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Request summary --}}
            <div class="card">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Application Details</h3>
                <div class="grid grid-cols-2 gap-y-4 gap-x-6">
                    <div><p class="text-xs text-gray-400 mb-1">Loan Type</p><p class="text-sm font-semibold text-gray-900 capitalize">{{ str_replace('_',' ',$app->loan_type) }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Requested Amount</p><p class="text-sm font-semibold text-gray-900">₦{{ number_format($app->requested_amount, 2) }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Tenor Requested</p><p class="text-sm font-semibold text-gray-900">{{ $app->requested_tenor_months }} months</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Monthly Income</p><p class="text-sm font-semibold text-gray-900">₦{{ number_format($app->monthly_income ?? 0, 0) }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Employment</p><p class="text-sm font-semibold text-gray-900 capitalize">{{ str_replace('_',' ',$app->employment_status ?? 'Not stated') }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Employer</p><p class="text-sm font-semibold text-gray-900">{{ $app->employer_name ?? '—' }}</p></div>
                    <div class="col-span-2"><p class="text-xs text-gray-400 mb-1">Purpose</p><p class="text-sm text-gray-900">{{ $app->purpose ?? '—' }}</p></div>
                    @if($app->collateral_description)
                    <div class="col-span-2"><p class="text-xs text-gray-400 mb-1">Collateral</p><p class="text-sm text-gray-900">{{ $app->collateral_description }} @if($app->collateral_value) · <span class="font-semibold">₦{{ number_format($app->collateral_value, 0) }}</span>@endif</p></div>
                    @endif
                </div>
            </div>

            {{-- Customer profile --}}
            <div class="card">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Customer Profile</h3>
                <div class="grid grid-cols-2 gap-y-3 gap-x-6">
                    <div><p class="text-xs text-gray-400 mb-1">Name</p><p class="text-sm font-semibold">{{ $app->customer_name }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Phone</p><p class="text-sm font-semibold">{{ $app->customer_phone }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Email</p><p class="text-sm font-semibold">{{ $app->customer_email }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">KYC Tier</p><p class="text-sm font-semibold">Tier {{ $app->kyc_tier }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Active Loans</p>
                        <p class="text-sm font-semibold {{ $activeLoans->count() > 0 ? 'text-amber-600' : 'text-green-600' }}">
                            {{ $activeLoans->count() }} active {{ $activeLoans->count() === 1 ? 'loan' : 'loans' }}
                        </p>
                    </div>
                    <div><p class="text-xs text-gray-400 mb-1">Outstanding</p>
                        <p class="text-sm font-semibold">₦{{ number_format($activeLoans->sum('outstanding_balance'), 0) }}</p>
                    </div>
                </div>
                @if($customer)
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <a href="{{ route('customers.show', $customer->id) }}" class="text-xs font-semibold text-bankos-primary hover:underline">View Full Customer Profile →</a>
                </div>
                @endif
            </div>

            {{-- Officer notes & actions --}}
            @if(in_array($app->status, ['pending','under_review']))
            <div class="card">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Review Decision</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {{-- Approve --}}
                    <form method="POST" action="{{ route('loan-applications.approve', $app->id) }}">
                        @csrf
                        <textarea name="officer_notes" placeholder="Approval notes (optional)..." rows="3"
                                  class="form-input w-full text-sm mb-3"></textarea>
                        <button type="submit" class="btn btn-primary w-full" onclick="return confirm('Approve this application?')">
                            ✓ Approve Application
                        </button>
                    </form>
                    {{-- Reject --}}
                    <form method="POST" action="{{ route('loan-applications.reject', $app->id) }}">
                        @csrf
                        <textarea name="officer_notes" placeholder="Rejection reason (required)..." rows="3"
                                  class="form-input w-full text-sm mb-3" required></textarea>
                        <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white w-full" onclick="return confirm('Reject this application?')">
                            ✗ Reject Application
                        </button>
                    </form>
                </div>
            </div>

            {{-- Convert to loan --}}
            <div class="card border-2 border-green-200">
                <h3 class="text-sm font-semibold text-green-700 uppercase tracking-wide mb-4">⚡ Convert to Loan Now</h3>
                <form method="POST" action="{{ route('loan-applications.convert', $app->id) }}">
                    @csrf
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Loan Product</label>
                            <select name="loan_product_id" class="form-select text-sm w-full" required>
                                @foreach($loanProducts as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} · {{ $p->interest_rate }}% p.a.</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Disbursement Account</label>
                            <select name="account_id" class="form-select text-sm w-full" required>
                                @foreach($accounts as $a)
                                <option value="{{ $a->id }}">{{ $a->account_number }} · {{ $a->account_type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Approved Amount (₦)</label>
                            <input type="number" name="amount" value="{{ $app->requested_amount }}" min="1000" step="500"
                                   class="form-input text-sm w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tenor (months)</label>
                            <input type="number" name="tenor_months" value="{{ $app->requested_tenor_months }}" min="1" max="120"
                                   class="form-input text-sm w-full" required>
                        </div>
                    </div>
                    <button type="submit" class="btn bg-green-600 hover:bg-green-700 text-white w-full"
                            onclick="return confirm('Convert this application into an active loan?')">
                        Convert to Active Loan
                    </button>
                </form>
            </div>
            @elseif($app->officer_notes)
            <div class="card">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Officer Notes</h3>
                <p class="text-sm text-gray-700">{{ $app->officer_notes }}</p>
                @if($app->reviewed_at)
                <p class="text-xs text-gray-400 mt-2">Reviewed {{ \Carbon\Carbon::parse($app->reviewed_at)->format('d M Y, H:i') }}</p>
                @endif
            </div>
            @endif
        </div>

        {{-- Sidebar status --}}
        <div class="space-y-4">
            @php
            $sc = ['pending'=>['bg-amber-100','text-amber-700'],'under_review'=>['bg-blue-100','text-blue-700'],'approved'=>['bg-green-100','text-green-700'],'rejected'=>['bg-red-100','text-red-700'],'converted'=>['bg-purple-100','text-purple-700'],'cancelled'=>['bg-gray-100','text-gray-500']][$app->status]??['bg-gray-100','text-gray-500'];
            @endphp
            <div class="card text-center">
                <span class="inline-block text-sm font-bold px-4 py-2 rounded-full {{ $sc[0] }} {{ $sc[1] }} mb-3">
                    {{ strtoupper(str_replace('_',' ',$app->status)) }}
                </span>
                <p class="text-xs text-gray-400">Submitted {{ \Carbon\Carbon::parse($app->created_at)->diffForHumans() }}</p>
                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($app->created_at)->format('d M Y, H:i') }}</p>
            </div>

            @if($activeLoans->isNotEmpty())
            <div class="card">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Active Loans</p>
                @foreach($activeLoans as $l)
                <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                    <div>
                        <p class="text-xs font-semibold text-gray-900">{{ $l->loan_reference }}</p>
                        <p class="text-xs text-gray-400 capitalize">{{ $l->status }}</p>
                    </div>
                    <p class="text-xs font-bold {{ $l->status === 'overdue' ? 'text-red-600' : 'text-gray-700' }}">₦{{ number_format($l->outstanding_balance, 0) }}</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
