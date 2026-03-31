<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('marketing.segments') }}" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Create Segment</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">Build a customer segment using rules</p>
            </div>
        </div>
    </x-slot>

    <div x-data="segmentBuilder()" class="max-w-4xl space-y-6">
        <form action="{{ route('marketing.segments.store') }}" method="POST" @submit="prepareSubmit">
            @csrf

            {{-- Basic Info --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 mb-6">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Segment Info</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Name *</label>
                        <input type="text" name="name" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="e.g. High-value savings customers">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Description</label>
                        <textarea name="description" rows="2" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="Optional description"></textarea>
                    </div>
                </div>
            </div>

            {{-- Rules Builder --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Rules</h3>
                    <button type="button" @click="addRule()" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-bankos-primary text-white rounded-lg hover:bg-bankos-primary/90">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Add Rule
                    </button>
                </div>

                <p class="text-xs text-bankos-muted mb-4">All rules are combined with AND logic. Customers must match every rule.</p>

                <div class="space-y-3">
                    <template x-for="(rule, index) in rules" :key="index">
                        <div class="flex items-start gap-3 p-4 rounded-lg bg-bankos-bg dark:bg-bankos-dark-bg border border-bankos-border/50 dark:border-bankos-dark-border">
                            <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs text-bankos-muted mb-1">Field</label>
                                    <select x-model="rule.field" @change="onFieldChange(index)" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-surface text-sm px-3 py-2">
                                        <option value="">-- Select --</option>
                                        <option value="status">Status</option>
                                        <option value="gender">Gender</option>
                                        <option value="kyc_tier">KYC Tier</option>
                                        <option value="account_type">Account Type</option>
                                        <option value="available_balance">Available Balance</option>
                                        <option value="has_loan">Has Loan</option>
                                        <option value="loan_status">Loan Status</option>
                                        <option value="has_insurance">Has Insurance</option>
                                        <option value="branch_id">Branch</option>
                                        <option value="age">Age</option>
                                        <option value="days_since_last_transaction">Days Since Last Txn</option>
                                        <option value="created_at">Account Created Date</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-bankos-muted mb-1">Operator</label>
                                    <select x-model="rule.operator" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-surface text-sm px-3 py-2">
                                        <template x-for="op in getOperators(rule.field)" :key="op.value">
                                            <option :value="op.value" x-text="op.label"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-bankos-muted mb-1">Value</label>
                                    <template x-if="getValueType(rule.field) === 'select'">
                                        <select x-model="rule.value" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-surface text-sm px-3 py-2">
                                            <template x-for="opt in getValueOptions(rule.field)" :key="opt.value">
                                                <option :value="opt.value" x-text="opt.label"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="getValueType(rule.field) === 'number'">
                                        <input type="number" x-model="rule.value" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-surface text-sm px-3 py-2" placeholder="Enter value">
                                    </template>
                                    <template x-if="getValueType(rule.field) === 'date'">
                                        <input type="date" x-model="rule.value" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-surface text-sm px-3 py-2">
                                    </template>
                                    <template x-if="getValueType(rule.field) === 'text'">
                                        <input type="text" x-model="rule.value" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-surface text-sm px-3 py-2" placeholder="Enter value">
                                    </template>
                                    <template x-if="getValueType(rule.field) === 'branch'">
                                        <select x-model="rule.value" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-surface text-sm px-3 py-2">
                                            <option value="">-- Select --</option>
                                            @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </template>
                                </div>
                            </div>
                            <button type="button" @click="removeRule(index)" class="mt-5 text-red-400 hover:text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <div x-show="rules.length === 0" class="text-center py-8 text-bankos-muted text-sm">
                    No rules added yet. Click "Add Rule" to start building your segment.
                </div>

                {{-- Hidden inputs for rules --}}
                <template x-for="(rule, index) in rules" :key="'hidden-'+index">
                    <div>
                        <input type="hidden" :name="'rules['+index+'][field]'" :value="rule.field">
                        <input type="hidden" :name="'rules['+index+'][operator]'" :value="rule.operator">
                        <input type="hidden" :name="'rules['+index+'][value]'" :value="rule.value">
                    </div>
                </template>
            </div>

            {{-- Preview --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Preview</h3>
                    <button type="button" @click="preview()" :disabled="rules.length === 0 || previewing" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium bg-bankos-bg dark:bg-bankos-dark-bg border border-bankos-border dark:border-bankos-dark-border rounded-lg hover:bg-bankos-light dark:hover:bg-bankos-dark-border disabled:opacity-50 text-bankos-text dark:text-bankos-dark-text">
                        <svg x-show="!previewing" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                        <svg x-show="previewing" class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-dasharray="30" stroke-dashoffset="10"></circle></svg>
                        <span x-text="previewing ? 'Checking...' : 'Check Match Count'"></span>
                    </button>
                </div>
                <div x-show="previewCount !== null" class="text-center py-4">
                    <p class="text-3xl font-bold text-bankos-primary" x-text="previewCount?.toLocaleString()"></p>
                    <p class="text-sm text-bankos-muted mt-1">customers match this segment</p>
                    <div x-show="previewSample && previewSample.length > 0" class="mt-4">
                        <p class="text-xs text-bankos-muted mb-2">Sample customers:</p>
                        <div class="flex flex-wrap gap-2 justify-center">
                            <template x-for="s in previewSample" :key="s.id">
                                <span class="inline-flex px-2 py-1 rounded bg-bankos-bg dark:bg-bankos-dark-bg text-xs text-bankos-text dark:text-bankos-dark-text" x-text="s.first_name + ' ' + s.last_name"></span>
                            </template>
                        </div>
                    </div>
                </div>
                <div x-show="previewCount === null" class="text-center py-4 text-sm text-bankos-muted">
                    Click "Check Match Count" to see how many customers match your rules.
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('marketing.segments') }}" class="px-4 py-2 text-sm text-bankos-muted hover:text-bankos-text dark:hover:text-white">Cancel</a>
                <button type="submit" :disabled="rules.length === 0" class="px-6 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90 transition-colors disabled:opacity-50">
                    Save Segment
                </button>
            </div>
        </form>
    </div>

    <script>
    function segmentBuilder() {
        return {
            rules: [],
            previewCount: null,
            previewSample: null,
            previewing: false,

            addRule() {
                this.rules.push({ field: '', operator: 'equals', value: '' });
            },

            removeRule(index) {
                this.rules.splice(index, 1);
            },

            onFieldChange(index) {
                const ops = this.getOperators(this.rules[index].field);
                if (ops.length > 0) {
                    this.rules[index].operator = ops[0].value;
                }
                this.rules[index].value = '';
            },

            getOperators(field) {
                const numericFields = ['available_balance', 'age', 'days_since_last_transaction'];
                const dateFields = ['created_at'];
                const equalsOnlyFields = ['gender', 'kyc_tier', 'status', 'account_type', 'has_loan', 'loan_status', 'has_insurance', 'branch_id'];

                if (numericFields.includes(field)) {
                    return [
                        { value: 'greater_than', label: 'Greater than' },
                        { value: 'less_than', label: 'Less than' },
                        { value: 'equals', label: 'Equals' },
                    ];
                }
                if (dateFields.includes(field)) {
                    return [
                        { value: 'after', label: 'After' },
                        { value: 'before', label: 'Before' },
                        { value: 'equals', label: 'On' },
                    ];
                }
                return [{ value: 'equals', label: 'Is' }];
            },

            getValueType(field) {
                const selectFields = ['gender', 'kyc_tier', 'status', 'account_type', 'has_loan', 'loan_status', 'has_insurance'];
                const numberFields = ['available_balance', 'age', 'days_since_last_transaction'];
                const dateFields = ['created_at'];

                if (field === 'branch_id') return 'branch';
                if (selectFields.includes(field)) return 'select';
                if (numberFields.includes(field)) return 'number';
                if (dateFields.includes(field)) return 'date';
                return 'text';
            },

            getValueOptions(field) {
                switch (field) {
                    case 'status':
                        return [
                            { value: 'active', label: 'Active' },
                            { value: 'inactive', label: 'Inactive' },
                            { value: 'dormant', label: 'Dormant' },
                        ];
                    case 'gender':
                        return [
                            { value: 'male', label: 'Male' },
                            { value: 'female', label: 'Female' },
                        ];
                    case 'kyc_tier':
                        return [
                            { value: 'level_1', label: 'Level 1' },
                            { value: 'level_2', label: 'Level 2' },
                            { value: 'level_3', label: 'Level 3' },
                        ];
                    case 'account_type':
                        return [
                            { value: 'savings', label: 'Savings' },
                            { value: 'current', label: 'Current' },
                        ];
                    case 'has_loan':
                    case 'has_insurance':
                        return [
                            { value: 'yes', label: 'Yes' },
                            { value: 'no', label: 'No' },
                        ];
                    case 'loan_status':
                        return [
                            { value: 'active', label: 'Active' },
                            { value: 'overdue', label: 'Overdue' },
                            { value: 'closed', label: 'Closed' },
                        ];
                    default:
                        return [];
                }
            },

            async preview() {
                if (this.rules.length === 0) return;
                this.previewing = true;
                this.previewCount = null;
                this.previewSample = null;

                try {
                    const res = await fetch('{{ route("marketing.segments.preview") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ rules: this.rules }),
                    });
                    const data = await res.json();
                    this.previewCount = data.count;
                    this.previewSample = data.sample;
                } catch (e) {
                    console.error(e);
                }

                this.previewing = false;
            },

            prepareSubmit() {
                // Rules are already in hidden inputs
            }
        };
    }
    </script>
</x-app-layout>
