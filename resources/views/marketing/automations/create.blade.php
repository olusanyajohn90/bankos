<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('marketing.automations') }}" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Create Automation</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">Build an automated marketing workflow</p>
            </div>
        </div>
    </x-slot>

    <div x-data="{
        triggerType: 'account_opened',
        actions: [{ type: 'send_sms', template_id: '', delay_minutes: 0, points: '', title: '', assigned_to: '', segment_id: '' }],
        addAction() {
            this.actions.push({ type: 'send_sms', template_id: '', delay_minutes: 0, points: '', title: '', assigned_to: '', segment_id: '' });
        },
        removeAction(i) {
            this.actions.splice(i, 1);
        }
    }" class="max-w-3xl space-y-6">
        <form action="{{ route('marketing.automations.store') }}" method="POST">
            @csrf

            {{-- Basic Info --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 mb-6">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Automation Info</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Name *</label>
                        <input type="text" name="name" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="e.g. Welcome New Customers">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Description</label>
                        <textarea name="description" rows="2" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2"></textarea>
                    </div>
                </div>
            </div>

            {{-- Trigger --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 mb-6">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Trigger</h3>
                <div>
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">When this event occurs *</label>
                    <select name="trigger[type]" x-model="triggerType" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                        <option value="account_opened">Account Opened</option>
                        <option value="loan_disbursed">Loan Disbursed</option>
                        <option value="loan_repaid">Loan Repaid</option>
                        <option value="loan_overdue">Loan Overdue</option>
                        <option value="birthday">Customer Birthday</option>
                        <option value="dormant_90_days">Dormant (90 days)</option>
                        <option value="deposit_milestone">Deposit Milestone</option>
                        <option value="first_transaction">First Transaction</option>
                    </select>
                </div>

                <div x-show="triggerType === 'deposit_milestone'" class="mt-3">
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Milestone Amount</label>
                    <input type="number" name="trigger[milestone_amount]" class="w-full max-w-xs rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="e.g. 100000">
                </div>
            </div>

            {{-- Conditions (optional) --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 mb-6">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Conditions <span class="text-xs text-bankos-muted font-normal">(optional)</span></h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Segment Filter</label>
                        <select name="conditions[segment_id]" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                            <option value="">No filter (all customers)</option>
                            @foreach($segments as $seg)
                            <option value="{{ $seg->id }}">{{ $seg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Min Account Balance</label>
                        <input type="number" name="conditions[min_balance]" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="Optional">
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 mb-6">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Actions</h3>

                <template x-for="(action, ai) in actions" :key="ai">
                    <div class="border border-bankos-border dark:border-bankos-dark-border rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text" x-text="'Step ' + (ai+1)"></span>
                            <button type="button" @click="removeAction(ai)" class="text-red-500 text-xs hover:underline" x-show="actions.length > 1">Remove</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1">Action Type</label>
                                <select x-model="action.type" :name="'actions['+ai+'][type]'" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                    <option value="send_sms">Send SMS</option>
                                    <option value="send_email">Send Email</option>
                                    <option value="award_points">Award Points</option>
                                    <option value="create_task">Create Task</option>
                                    <option value="add_to_segment">Add to Segment</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1">Delay (minutes)</label>
                                <input type="number" x-model="action.delay_minutes" :name="'actions['+ai+'][delay_minutes]'" min="0" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" value="0">
                            </div>

                            {{-- Template select for SMS/Email --}}
                            <div x-show="action.type === 'send_sms' || action.type === 'send_email'">
                                <label class="block text-xs font-medium text-bankos-muted mb-1">Template</label>
                                <select :name="'actions['+ai+'][template_id]'" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                    <option value="">Select template</option>
                                    @foreach($templates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ $tpl->channel }})</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Points for award_points --}}
                            <div x-show="action.type === 'award_points'">
                                <label class="block text-xs font-medium text-bankos-muted mb-1">Points</label>
                                <input type="number" :name="'actions['+ai+'][points]'" min="1" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                            </div>

                            {{-- Task fields --}}
                            <div x-show="action.type === 'create_task'" class="md:col-span-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-muted mb-1">Task Title</label>
                                        <input type="text" :name="'actions['+ai+'][title]'" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-bankos-muted mb-1">Assign To</label>
                                        <select :name="'actions['+ai+'][assigned_to]'" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                            <option value="">Unassigned</option>
                                            @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Segment for add_to_segment --}}
                            <div x-show="action.type === 'add_to_segment'">
                                <label class="block text-xs font-medium text-bankos-muted mb-1">Segment</label>
                                <select :name="'actions['+ai+'][segment_id]'" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                    <option value="">Select segment</option>
                                    @foreach($segments as $seg)
                                    <option value="{{ $seg->id }}">{{ $seg->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </template>

                <button type="button" @click="addAction()" class="text-sm text-bankos-primary hover:underline">+ Add Action Step</button>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('marketing.automations') }}" class="px-4 py-2 text-sm text-bankos-muted hover:text-bankos-text">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90">Create Automation</button>
            </div>
        </form>
    </div>
</x-app-layout>
