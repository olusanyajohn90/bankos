<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('mandates.index') }}" class="text-bankos-muted hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">New Account Mandate</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Configure corporate signing instructions for an account</p>
            </div>
        </div>
    </x-slot>

    <form action="{{ route('mandates.store') }}" method="POST" x-data="{
        signatories: [],
        addSignatory() {
            this.signatories.push({ name: '', sigClass: 'A', phone: '', email: '', user_id: '' });
        },
        removeSignatory(index) {
            this.signatories.splice(index, 1);
        }
    }">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left: Main mandate details -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Account + Rule Card -->
                <div class="card p-6">
                    <h3 class="font-bold text-base mb-5 border-b border-bankos-border dark:border-bankos-dark-border pb-3">Mandate Details</h3>

                    <div class="space-y-5">
                        <!-- Account -->
                        <div>
                            <label for="account_id" class="block text-sm font-medium text-bankos-text dark:text-white mb-1.5">
                                Account <span class="text-red-500">*</span>
                            </label>
                            <select id="account_id" name="account_id" class="form-select w-full" required>
                                <option value="">— Select Account —</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>
                                        {{ $account->account_number }} — {{ $account->account_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('account_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Signing Rule -->
                        <div>
                            <label for="signing_rule" class="block text-sm font-medium text-bankos-text dark:text-white mb-1.5">
                                Signing Rule <span class="text-red-500">*</span>
                            </label>
                            <select id="signing_rule" name="signing_rule" class="form-select w-full" required>
                                <option value="sole"        @selected(old('signing_rule', 'sole') === 'sole')>Sole Signatory — Single authorised signatory</option>
                                <option value="any_one"     @selected(old('signing_rule') === 'any_one')>Any One — Any one of the listed signatories</option>
                                <option value="any_two"     @selected(old('signing_rule') === 'any_two')>Any Two — Any two signatories must sign</option>
                                <option value="a_and_b"     @selected(old('signing_rule') === 'a_and_b')>A and B — One Class A and one Class B</option>
                                <option value="a_and_any_b" @selected(old('signing_rule') === 'a_and_any_b')>A and Any B — Class A plus any Class B or C</option>
                                <option value="all"         @selected(old('signing_rule') === 'all')>All Signatories — Every signatory must approve</option>
                            </select>
                            @error('signing_rule')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Sole Amount Threshold -->
                        <div>
                            <label for="max_amount_sole" class="block text-sm font-medium text-bankos-text dark:text-white mb-1.5">
                                Max Amount for Sole Signatory (₦)
                                <span class="font-normal text-bankos-muted text-xs ml-1">Optional — transactions below this amount can be approved by a single signatory</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-bankos-muted font-medium text-sm">₦</span>
                                <input type="number" id="max_amount_sole" name="max_amount_sole" value="{{ old('max_amount_sole') }}"
                                    class="form-input w-full pl-8" placeholder="e.g. 500000.00" step="0.01" min="0">
                            </div>
                            @error('max_amount_sole')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Effective Dates -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="effective_from" class="block text-sm font-medium text-bankos-text dark:text-white mb-1.5">Effective From</label>
                                <input type="date" id="effective_from" name="effective_from" value="{{ old('effective_from') }}" class="form-input w-full">
                                @error('effective_from')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="effective_to" class="block text-sm font-medium text-bankos-text dark:text-white mb-1.5">Effective To</label>
                                <input type="date" id="effective_to" name="effective_to" value="{{ old('effective_to') }}" class="form-input w-full">
                                @error('effective_to')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-bankos-text dark:text-white mb-1.5">Description / Notes</label>
                            <textarea id="description" name="description" rows="3" class="form-input w-full" placeholder="Any additional instructions or context...">{{ old('description') }}</textarea>
                            @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <!-- Signatories Card -->
                <div class="card p-6">
                    <div class="flex justify-between items-center mb-5 border-b border-bankos-border dark:border-bankos-dark-border pb-3">
                        <h3 class="font-bold text-base">Signatories</h3>
                        <button type="button" @click="addSignatory()"
                            class="btn btn-secondary text-sm flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            Add Signatory
                        </button>
                    </div>

                    <div x-show="signatories.length === 0" class="py-8 text-center text-bankos-muted text-sm">
                        No signatories added yet. Click "Add Signatory" to begin.
                    </div>

                    <div class="space-y-4">
                        <template x-for="(sig, index) in signatories" :key="index">
                            <div class="p-4 border border-bankos-border dark:border-bankos-dark-border rounded-lg bg-gray-50/50 dark:bg-bankos-dark-bg/30 relative">
                                <!-- Row header -->
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider" x-text="`Signatory #${index + 1}`"></p>
                                    <button type="button" @click="removeSignatory(index)"
                                        class="text-bankos-muted hover:text-red-500 transition-colors flex items-center gap-1 text-xs">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        Remove
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Name -->
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-medium text-bankos-text-sec mb-1">Full Name <span class="text-red-500">*</span></label>
                                        <input type="text" :name="`signatories[${index}][name]`" x-model="sig.name"
                                            class="form-input w-full text-sm" placeholder="Signatory full name" required>
                                    </div>
                                    <!-- Class -->
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-text-sec mb-1">Signatory Class <span class="text-red-500">*</span></label>
                                        <select :name="`signatories[${index}][class]`" x-model="sig.sigClass" class="form-select w-full text-sm">
                                            <option value="A">Class A</option>
                                            <option value="B">Class B</option>
                                            <option value="C">Class C</option>
                                        </select>
                                    </div>
                                    <!-- Phone -->
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-text-sec mb-1">Phone</label>
                                        <input type="tel" :name="`signatories[${index}][phone]`" x-model="sig.phone"
                                            class="form-input w-full text-sm" placeholder="e.g. 08012345678">
                                    </div>
                                    <!-- Email -->
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-text-sec mb-1">Email</label>
                                        <input type="email" :name="`signatories[${index}][email]`" x-model="sig.email"
                                            class="form-input w-full text-sm" placeholder="signatory@example.com">
                                    </div>
                                    <!-- Link to Staff -->
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-text-sec mb-1">
                                            Link to Staff User
                                            <span class="font-normal text-bankos-muted">(optional)</span>
                                        </label>
                                        <select :name="`signatories[${index}][user_id]`" x-model="sig.user_id" class="form-select w-full text-sm">
                                            <option value="">— None —</option>
                                            @foreach($staff as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Right: Help / actions -->
            <div class="space-y-4">
                <!-- Signing rule guide -->
                <div class="card p-5 bg-blue-50/50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800">
                    <h4 class="font-semibold text-sm text-bankos-text dark:text-white mb-3">Signing Rule Guide</h4>
                    <dl class="space-y-2.5 text-xs text-bankos-text-sec">
                        <div>
                            <dt class="font-semibold text-bankos-text dark:text-white">Sole Signatory</dt>
                            <dd>A single signatory can authorise any transaction.</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-bankos-text dark:text-white">Any One</dt>
                            <dd>Any single signatory from the list can sign.</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-bankos-text dark:text-white">Any Two</dt>
                            <dd>Any two signatories must both approve.</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-bankos-text dark:text-white">A and B</dt>
                            <dd>One Class-A signatory and one Class-B must both sign.</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-bankos-text dark:text-white">A and Any B</dt>
                            <dd>Class-A signatory plus any signatory from Class B or C.</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-bankos-text dark:text-white">All Signatories</dt>
                            <dd>Every active signatory must approve each transaction.</dd>
                        </div>
                    </dl>
                </div>

                <!-- Note -->
                <div class="card p-5 bg-amber-50/50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-800">
                    <h4 class="font-semibold text-sm text-bankos-text dark:text-white mb-2">Note</h4>
                    <p class="text-xs text-bankos-text-sec">If an active mandate already exists for the selected account it will be automatically deactivated and replaced by this new mandate.</p>
                </div>

                <!-- Submit -->
                <div class="card p-5">
                    <button type="submit" class="btn btn-primary w-full">
                        Create Mandate
                    </button>
                    <a href="{{ route('mandates.index') }}" class="btn btn-secondary w-full mt-2 text-center block">
                        Cancel
                    </a>
                </div>
            </div>

        </div>
    </form>
</x-app-layout>
