<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Loyalty Program</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">Manage your customer loyalty program, tiers, and point awards</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('marketing.loyalty.members') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-bankos-border dark:border-bankos-dark-border text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors text-bankos-text dark:text-bankos-dark-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    All Members
                </a>
            </div>
        </div>
    </x-slot>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-yellow-600 dark:text-yellow-400" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                </div>
                <div>
                    <p class="text-xs text-bankos-muted dark:text-bankos-dark-text-sec">Program Status</p>
                    <p class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ $program && $program->is_active ? 'Active' : 'Inactive' }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-600 dark:text-blue-400" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                </div>
                <div>
                    <p class="text-xs text-bankos-muted dark:text-bankos-dark-text-sec">Total Members</p>
                    <p class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ number_format($memberCount) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-green-600 dark:text-green-400" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline></svg>
                </div>
                <div>
                    <p class="text-xs text-bankos-muted dark:text-bankos-dark-text-sec">Points Distributed</p>
                    <p class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ number_format($totalDistributed) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-purple-600 dark:text-purple-400" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <div>
                    <p class="text-xs text-bankos-muted dark:text-bankos-dark-text-sec">Points Redeemed</p>
                    <p class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ number_format($totalRedeemed) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Tier Breakdown --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
            <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Tier Breakdown</h3>
            @if($tierBreakdown->count())
                <div class="space-y-3">
                    @foreach($tierBreakdown as $tier => $count)
                    @php
                        $tierColors = [
                            'bronze' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                            'silver' => 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                            'gold'   => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                            'platinum' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                        ];
                        $colorClass = $tierColors[strtolower($tier)] ?? 'bg-bankos-bg text-bankos-text dark:bg-bankos-dark-bg dark:text-bankos-dark-text';
                    @endphp
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">{{ ucfirst($tier) }}</span>
                        <span class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">{{ number_format($count) }} members</span>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-bankos-muted">No members enrolled yet.</p>
            @endif
        </div>

        {{-- Top 10 Members --}}
        <div class="lg:col-span-2 bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
            <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Top Members by Points</h3>
            @if($topMembers->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-bankos-border dark:border-bankos-dark-border">
                            <th class="text-left py-2 text-xs font-medium text-bankos-muted uppercase">Customer</th>
                            <th class="text-left py-2 text-xs font-medium text-bankos-muted uppercase">Tier</th>
                            <th class="text-right py-2 text-xs font-medium text-bankos-muted uppercase">Earned</th>
                            <th class="text-right py-2 text-xs font-medium text-bankos-muted uppercase">Redeemed</th>
                            <th class="text-right py-2 text-xs font-medium text-bankos-muted uppercase">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @foreach($topMembers as $member)
                        <tr>
                            <td class="py-2 font-medium text-bankos-text dark:text-bankos-dark-text">{{ $member->customer?->first_name }} {{ $member->customer?->last_name }}</td>
                            <td class="py-2"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-bankos-bg dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text">{{ ucfirst($member->current_tier) }}</span></td>
                            <td class="py-2 text-right text-bankos-text dark:text-bankos-dark-text">{{ number_format($member->total_earned) }}</td>
                            <td class="py-2 text-right text-bankos-text-sec dark:text-bankos-dark-text-sec">{{ number_format($member->total_redeemed) }}</td>
                            <td class="py-2 text-right font-semibold text-bankos-text dark:text-bankos-dark-text">{{ number_format($member->current_balance) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <p class="text-sm text-bankos-muted">No loyalty members yet.</p>
            @endif
        </div>
    </div>

    {{-- Actions Row --}}
    <div class="flex flex-wrap gap-3 mb-8">
        {{-- Setup/Edit Program --}}
        <div x-data="{ showSetup: false }">
            <button @click="showSetup = true" class="inline-flex items-center gap-2 px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                {{ $program ? 'Edit Program' : 'Setup Program' }}
            </button>

            {{-- Setup Modal --}}
            <div x-show="showSetup" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape.window="showSetup = false">
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.away="showSetup = false"
                     x-data="{
                        tiers: {{ json_encode($program->tiers ?? [['name' => 'Bronze', 'min_points' => 0], ['name' => 'Silver', 'min_points' => 500], ['name' => 'Gold', 'min_points' => 2000]]) }},
                        earningRules: {{ json_encode($program->earning_rules ?? [['action' => 'deposit', 'points_per_unit' => 1, 'unit' => 1000]]) }},
                        redemptionOptions: {{ json_encode($program->redemption_options ?? [['name' => 'Fee waiver', 'points_cost' => 100]]) }}
                     }">
                    <h3 class="text-lg font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Loyalty Program Setup</h3>
                    <form action="{{ route('marketing.loyalty.setup') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Program Name *</label>
                                <input type="text" name="name" value="{{ $program->name ?? '' }}" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Description</label>
                                <textarea name="description" rows="2" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">{{ $program->description ?? '' }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Points Expiry (months)</label>
                                <input type="number" name="points_expiry_months" value="{{ $program->points_expiry_months ?? 12 }}" min="1" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                            </div>

                            {{-- Tiers --}}
                            <div>
                                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-2">Tiers</label>
                                <template x-for="(tier, i) in tiers" :key="i">
                                    <div class="flex items-center gap-2 mb-2">
                                        <input type="text" x-model="tier.name" :name="'tiers['+i+'][name]'" placeholder="Tier name" class="flex-1 rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                        <input type="number" x-model="tier.min_points" :name="'tiers['+i+'][min_points]'" placeholder="Min points" class="w-32 rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                        <button type="button" @click="tiers.splice(i, 1)" class="text-red-500 hover:text-red-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        </button>
                                    </div>
                                </template>
                                <button type="button" @click="tiers.push({name: '', min_points: 0})" class="text-sm text-bankos-primary hover:underline">+ Add Tier</button>
                            </div>

                            {{-- Earning Rules --}}
                            <div>
                                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-2">Earning Rules</label>
                                <template x-for="(rule, i) in earningRules" :key="i">
                                    <div class="flex items-center gap-2 mb-2">
                                        <select x-model="rule.action" :name="'earning_rules['+i+'][action]'" class="rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                            <option value="deposit">Deposit</option>
                                            <option value="loan_repayment">Loan Repayment</option>
                                            <option value="referral">Referral</option>
                                            <option value="account_opening">Account Opening</option>
                                        </select>
                                        <input type="number" x-model="rule.points_per_unit" :name="'earning_rules['+i+'][points_per_unit]'" placeholder="Points" class="w-24 rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                        <span class="text-xs text-bankos-muted">per</span>
                                        <input type="number" x-model="rule.unit" :name="'earning_rules['+i+'][unit]'" placeholder="Unit" class="w-24 rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                        <button type="button" @click="earningRules.splice(i, 1)" class="text-red-500 hover:text-red-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        </button>
                                    </div>
                                </template>
                                <button type="button" @click="earningRules.push({action: 'deposit', points_per_unit: 1, unit: 1000})" class="text-sm text-bankos-primary hover:underline">+ Add Rule</button>
                            </div>

                            {{-- Redemption Options --}}
                            <div>
                                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-2">Redemption Options</label>
                                <template x-for="(opt, i) in redemptionOptions" :key="i">
                                    <div class="flex items-center gap-2 mb-2">
                                        <input type="text" x-model="opt.name" :name="'redemption_options['+i+'][name]'" placeholder="Option name" class="flex-1 rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                        <input type="number" x-model="opt.points_cost" :name="'redemption_options['+i+'][points_cost]'" placeholder="Points cost" class="w-32 rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                        <button type="button" @click="redemptionOptions.splice(i, 1)" class="text-red-500 hover:text-red-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        </button>
                                    </div>
                                </template>
                                <button type="button" @click="redemptionOptions.push({name: '', points_cost: 0})" class="text-sm text-bankos-primary hover:underline">+ Add Option</button>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="showSetup = false" class="px-4 py-2 text-sm text-bankos-muted hover:text-bankos-text dark:hover:text-white">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90">Save Program</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Award Points --}}
        <div x-data="{ showAward: false }">
            <button @click="showAward = true" class="inline-flex items-center gap-2 px-4 py-2 border border-bankos-border dark:border-bankos-dark-border text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors text-bankos-text dark:text-bankos-dark-text">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Award Points
            </button>
            <div x-show="showAward" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape.window="showAward = false">
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 w-full max-w-md" @click.away="showAward = false">
                    <h3 class="text-lg font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Award Points</h3>
                    <form action="{{ route('marketing.loyalty.award') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Customer *</label>
                                <select name="customer_id" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                    <option value="">Select customer</option>
                                    @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }} ({{ $c->customer_number }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Points *</label>
                                <input type="number" name="points" required min="1" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Reason</label>
                                <input type="text" name="description" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="e.g. Referral bonus">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="showAward = false" class="px-4 py-2 text-sm text-bankos-muted hover:text-bankos-text">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90">Award</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Redeem Points --}}
        <div x-data="{ showRedeem: false }">
            <button @click="showRedeem = true" class="inline-flex items-center gap-2 px-4 py-2 border border-bankos-border dark:border-bankos-dark-border text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors text-bankos-text dark:text-bankos-dark-text">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                Redeem Points
            </button>
            <div x-show="showRedeem" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape.window="showRedeem = false">
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 w-full max-w-md" @click.away="showRedeem = false">
                    <h3 class="text-lg font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Redeem Points</h3>
                    <form action="{{ route('marketing.loyalty.redeem') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Customer *</label>
                                <select name="customer_id" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                    <option value="">Select customer</option>
                                    @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }} ({{ $c->customer_number }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Points to Redeem *</label>
                                <input type="number" name="points" required min="1" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Description</label>
                                <input type="text" name="description" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="e.g. Fee waiver">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="showRedeem = false" class="px-4 py-2 text-sm text-bankos-muted hover:text-bankos-text">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90">Redeem</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-700 dark:text-green-400">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-400">
        {{ session('error') }}
    </div>
    @endif
</x-app-layout>
