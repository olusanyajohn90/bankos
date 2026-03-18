<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('nip.index') }}"
               class="text-bankos-text-sec hover:text-bankos-primary transition-colors"
               title="Back to NIP Transfers">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    New NIP Transfer
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Send funds via NIBSS Instant Payment</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto"
         x-data="{
             step: 1,
             verifying: false,
             verified: false,
             verifiedName: '',
             verifiedAccount: '',
             verifiedBank: '',
             verifiedBankCode: '',
             errorMsg: '',

             get selectedSourceId() {
                 return document.getElementById('source_account_id')?.value ?? '';
             },
             get selectedBankCode() {
                 return document.getElementById('bank_code_select')?.value ?? '';
             },
             get selectedBankName() {
                 const sel = document.getElementById('bank_code_select');
                 return sel?.options[sel.selectedIndex]?.text ?? '';
             },
             get enteredAccountNumber() {
                 return document.getElementById('account_number_input')?.value?.trim() ?? '';
             },

             async verifyAccount() {
                 const accountNumber = this.enteredAccountNumber;
                 const bankCode      = this.selectedBankCode;

                 if (!this.selectedSourceId) {
                     this.errorMsg = 'Please select a source account first.';
                     return;
                 }
                 if (!bankCode) {
                     this.errorMsg = 'Please select the destination bank.';
                     return;
                 }
                 if (!accountNumber || accountNumber.length < 10) {
                     this.errorMsg = 'Please enter a valid 10-digit account number.';
                     return;
                 }

                 this.verifying = true;
                 this.errorMsg  = '';
                 this.verified  = false;

                 try {
                     const response = await fetch('{{ route('nip.name-enquiry') }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                             'Accept': 'application/json',
                         },
                         body: JSON.stringify({
                             account_number: accountNumber,
                             bank_code: bankCode,
                         }),
                     });

                     const data = await response.json();

                     if (data.account_name) {
                         this.verified        = true;
                         this.verifiedName    = data.account_name;
                         this.verifiedAccount = accountNumber;
                         this.verifiedBank    = this.selectedBankName;
                         this.verifiedBankCode = bankCode;
                         this.step            = 2;
                     } else {
                         this.errorMsg = data.error ?? 'Account not found. Please check the details and try again.';
                     }
                 } catch (e) {
                     this.errorMsg = 'A network error occurred. Please check your connection and try again.';
                 } finally {
                     this.verifying = false;
                 }
             }
         }">

        {{-- ══════════════════════════════════════════
             STEP 1: Verify Beneficiary
        ══════════════════════════════════════════ --}}
        <div x-show="step === 1" class="space-y-5">

            {{-- Step indicator --}}
            <div class="flex items-center gap-2 text-xs text-bankos-text-sec mb-1">
                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-bankos-primary text-white font-bold text-xs">1</span>
                <span class="font-medium text-bankos-text">Verify Beneficiary</span>
                <span class="mx-2 text-bankos-border">—</span>
                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-bankos-border text-bankos-muted font-bold text-xs">2</span>
                <span class="text-bankos-muted">Transfer Details</span>
            </div>

            <div class="card p-6 border border-bankos-border space-y-5">

                {{-- Source Account --}}
                <div>
                    <label for="source_account_id"
                           class="block text-sm font-medium text-bankos-text-sec mb-1.5">
                        Source Account <span class="text-red-500">*</span>
                    </label>
                    <select id="source_account_id" class="form-input w-full" required>
                        <option value="">— Select debit account —</option>
                        @foreach($sourceAccounts as $acct)
                            <option value="{{ $acct->id }}"
                                {{ old('source_account_id') == $acct->id ? 'selected' : '' }}>
                                {{ $acct->account_number }} &mdash; {{ $acct->account_name }}
                                &nbsp;(Bal: ₦{{ number_format($acct->available_balance, 2) }})
                            </option>
                        @endforeach
                    </select>
                    @if($sourceAccounts->isEmpty())
                        <p class="text-xs text-red-500 mt-1.5">
                            No active accounts are available for outward transfers.
                        </p>
                    @endif
                </div>

                {{-- Destination Bank --}}
                <div>
                    <label for="bank_code_select"
                           class="block text-sm font-medium text-bankos-text-sec mb-1.5">
                        Destination Bank <span class="text-red-500">*</span>
                    </label>
                    <select id="bank_code_select" class="form-input w-full" required>
                        <option value="">— Select bank —</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->cbn_code }}">{{ $bank->bank_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Beneficiary Account Number --}}
                <div>
                    <label for="account_number_input"
                           class="block text-sm font-medium text-bankos-text-sec mb-1.5">
                        Beneficiary Account Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="account_number_input"
                           inputmode="numeric"
                           maxlength="10"
                           pattern="\d{10}"
                           class="form-input w-full font-mono tracking-widest text-lg"
                           placeholder="0123456789"
                           autocomplete="off"
                           required>
                    <p class="text-xs text-bankos-muted mt-1">Enter the 10-digit NUBAN account number.</p>
                </div>

                {{-- Error Message --}}
                <div x-show="errorMsg" x-cloak>
                    <div class="flex items-start gap-3 rounded-lg bg-red-50 dark:bg-red-900/20
                                border border-red-200 dark:border-red-800 px-4 py-3">
                        <svg class="h-4 w-4 text-red-500 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm text-red-700 dark:text-red-300" x-text="errorMsg"></p>
                    </div>
                </div>

                {{-- Verify Button --}}
                <button type="button"
                        @click="verifyAccount()"
                        :disabled="verifying"
                        class="btn btn-primary w-full gap-2 py-2.5 text-base">
                    <svg x-show="verifying"
                         class="animate-spin h-4 w-4 text-white flex-shrink-0"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <svg x-show="!verifying"
                         class="h-4 w-4 text-white flex-shrink-0"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="verifying ? 'Verifying Account…' : 'Verify Account'"></span>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             STEP 2: Transfer Details & Confirm
        ══════════════════════════════════════════ --}}
        <div x-show="step === 2" x-cloak class="space-y-5">

            {{-- Step indicator --}}
            <div class="flex items-center gap-2 text-xs text-bankos-text-sec mb-1">
                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-green-500 text-white font-bold text-xs">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="text-bankos-text-sec">Beneficiary Verified</span>
                <span class="mx-2 text-bankos-border">—</span>
                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-bankos-primary text-white font-bold text-xs">2</span>
                <span class="font-medium text-bankos-text">Transfer Details</span>
            </div>

            {{-- Verified Beneficiary Summary --}}
            <div class="rounded-xl bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800 p-5">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="h-5 w-5 text-green-600 dark:text-green-400 flex-shrink-0"
                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-semibold text-green-800 dark:text-green-300 text-sm">Account Verified</span>
                </div>
                <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-2 text-sm">
                    <dt class="text-bankos-text-sec">Account Name</dt>
                    <dd class="font-semibold text-bankos-text" x-text="verifiedName"></dd>
                    <dt class="text-bankos-text-sec">Account Number</dt>
                    <dd class="font-mono text-bankos-text" x-text="verifiedAccount"></dd>
                    <dt class="text-bankos-text-sec">Bank</dt>
                    <dd class="text-bankos-text" x-text="verifiedBank"></dd>
                </dl>
                <button type="button"
                        @click="step = 1; verified = false; verifiedName = ''; errorMsg = ''"
                        class="mt-3 text-xs text-bankos-primary hover:underline">
                    &larr; Change beneficiary
                </button>
            </div>

            {{-- Transfer Form --}}
            <form action="{{ route('nip.store') }}" method="POST" class="space-y-5"
                  onsubmit="return confirm('Confirm transfer? This will debit the selected account immediately.')">
                @csrf

                {{-- Hidden fields from Step 1 --}}
                <input type="hidden" name="source_account_id"
                       :value="document.getElementById('source_account_id')?.value">
                <input type="hidden" name="beneficiary_bank_code"
                       :value="verifiedBankCode">
                <input type="hidden" name="beneficiary_account_number"
                       :value="verifiedAccount">
                <input type="hidden" name="beneficiary_account_name"
                       :value="verifiedName">

                <div class="card p-6 border border-bankos-border space-y-5">

                    {{-- Amount --}}
                    <div>
                        <label for="amount"
                               class="block text-sm font-medium text-bankos-text-sec mb-1.5">
                            Amount (₦) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center
                                        pointer-events-none text-bankos-muted font-bold text-lg">₦</div>
                            <input type="number"
                                   id="amount"
                                   name="amount"
                                   step="0.01"
                                   min="1"
                                   class="form-input pl-8 w-full text-xl font-bold @error('amount') border-red-400 @enderror"
                                   placeholder="0.00"
                                   value="{{ old('amount') }}"
                                   required>
                        </div>
                        @error('amount')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Narration --}}
                    <div>
                        <label for="narration"
                               class="block text-sm font-medium text-bankos-text-sec mb-1.5">
                            Narration
                            <span class="font-normal text-bankos-muted">(optional)</span>
                        </label>
                        <input type="text"
                               id="narration"
                               name="narration"
                               maxlength="255"
                               class="form-input w-full"
                               placeholder="e.g. Payment for invoice #001"
                               value="{{ old('narration') }}">
                    </div>

                    {{-- Inline validation errors --}}
                    @if($errors->any())
                    <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
                        <ul class="list-disc list-inside space-y-1 text-sm text-red-700 dark:text-red-300">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Submit --}}
                    <button type="submit"
                            class="btn btn-primary w-full py-3 text-base font-semibold shadow-md gap-2">
                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Send Transfer
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
