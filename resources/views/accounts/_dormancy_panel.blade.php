{{--
    Dormancy & Closure Panel Partial
    Usage: @include('accounts._dormancy_panel', ['account' => $account])
--}}
<div class="card p-0 overflow-hidden">
    <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
        <h3 class="font-bold text-lg text-bankos-text dark:text-bankos-dark-text">Dormancy &amp; Account Closure</h3>
        <p class="text-xs text-bankos-muted mt-0.5">Manage account lifecycle — reactivate dormant accounts or initiate closure</p>
    </div>

    <div class="px-6 py-5 space-y-6">

        {{-- ── Dormancy Status ── --}}
        <div class="flex items-start gap-4">
            <div class="mt-0.5">
                @if($account->status === 'dormant')
                <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600 dark:text-amber-400"><path d="M17 18a5 5 0 0 0-10 0"></path><line x1="12" y1="9" x2="12" y2="2"></line><line x1="4.22" y1="10.22" x2="5.64" y2="11.64"></line><line x1="1" y1="18" x2="3" y2="18"></line><line x1="21" y1="18" x2="23" y2="18"></line><line x1="18.36" y1="11.64" x2="19.78" y2="10.22"></line></svg>
                </div>
                @elseif($account->status === 'closed')
                <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-500"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                @else
                <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-success"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                @endif
            </div>
            <div class="flex-1">
                <p class="font-semibold text-sm text-bankos-text dark:text-bankos-dark-text">
                    @if($account->status === 'dormant')
                        Account is Dormant
                    @elseif($account->status === 'closed')
                        Account is Closed
                    @else
                        Account is Active
                    @endif
                </p>
                <div class="text-sm text-bankos-text-sec mt-1 space-y-0.5">
                    @if($account->dormant_since)
                    <p>Dormant since: <span class="font-medium text-bankos-text dark:text-white">{{ $account->dormant_since->format('d M Y') }}</span></p>
                    @endif
                    @if($account->status === 'closed')
                    <p>Closed on: <span class="font-medium text-bankos-text dark:text-white">{{ $account->closed_at?->format('d M Y') }}</span></p>
                    @if($account->closure_reason)
                    <p>Reason: <span class="font-medium text-bankos-text dark:text-white">{{ $account->closure_reason }}</span></p>
                    @endif
                    @endif
                </div>
            </div>

            {{-- Reactivate button (only if dormant) --}}
            @if($account->status === 'dormant')
            @can('accounts.edit')
            <form action="{{ route('accounts.reactivate', $account) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="btn btn-secondary text-sm text-bankos-success border-bankos-success hover:bg-green-50 dark:hover:bg-green-900/20"
                        onclick="return confirm('Reactivate this account?')">
                    Reactivate
                </button>
            </form>
            @endcan
            @endif
        </div>

        {{-- ── Close Account Section ── --}}
        @if($account->status !== 'closed')
        @can('accounts.edit')
        <div class="border-t border-bankos-border dark:border-bankos-dark-border pt-5">
            @php
                $canClose = $account->ledger_balance == 0 && !$account->pnd_active && $account->activeLiens->isEmpty();
            @endphp

            @if(!$canClose)
            {{-- Blockers warning --}}
            <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 mb-4">
                <p class="text-sm font-semibold text-amber-800 dark:text-amber-400 mb-1.5 flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    Cannot close — resolve the following first:
                </p>
                <ul class="text-xs text-amber-700 dark:text-amber-300 space-y-1 list-disc list-inside">
                    @if($account->ledger_balance != 0)
                    <li>Account has a non-zero balance ({{ number_format($account->ledger_balance, 2) }})</li>
                    @endif
                    @if($account->pnd_active)
                    <li>Post-No-Debit restriction is active</li>
                    @endif
                    @if($account->activeLiens->isNotEmpty())
                    <li>{{ $account->activeLiens->count() }} active lien(s) remain on the account</li>
                    @endif
                </ul>
            </div>
            @endif

            <button
                @if(!$canClose) disabled @endif
                onclick="document.getElementById('close-account-form-{{ $account->id }}').classList.toggle('hidden')"
                class="flex items-center gap-2 text-sm font-semibold {{ $canClose ? 'text-red-600 hover:text-red-700 cursor-pointer' : 'text-gray-400 cursor-not-allowed' }} transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                Close This Account
            </button>

            @if($canClose)
            <form id="close-account-form-{{ $account->id }}"
                  action="{{ route('accounts.close', $account) }}" method="POST"
                  class="hidden mt-4 space-y-4 p-4 bg-red-50 dark:bg-red-900/10 rounded-lg border border-red-200 dark:border-red-800">
                @csrf
                @method('DELETE')
                <p class="text-sm font-semibold text-red-700 dark:text-red-400">
                    Permanently close account {{ $account->account_number }}
                </p>
                <p class="text-xs text-red-600 dark:text-red-300">
                    This action cannot be undone. The account will be marked as closed and all debits will be permanently blocked.
                </p>
                <div>
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                        Closure Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea name="closure_reason" rows="3" maxlength="500"
                              class="form-input w-full resize-none" required
                              placeholder="State the reason for closure (e.g. Customer request, Deceased, Fraud investigation)"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit"
                            class="btn text-sm bg-red-600 hover:bg-red-700 text-white border-red-600 font-semibold"
                            onclick="return confirm('Are you absolutely sure you want to close this account? This cannot be undone.')">
                        Confirm Account Closure
                    </button>
                    <button type="button"
                            onclick="document.getElementById('close-account-form-{{ $account->id }}').classList.add('hidden')"
                            class="btn btn-secondary text-sm">Cancel</button>
                </div>
            </form>
            @endif
        </div>
        @endcan
        @endif

    </div>
</div>
