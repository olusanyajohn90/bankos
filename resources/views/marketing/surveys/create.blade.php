<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('marketing.surveys') }}" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Create Survey</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">Design a new customer feedback survey</p>
            </div>
        </div>
    </x-slot>

    <div x-data="{
        surveyType: 'nps',
        questions: [{ type: 'rating', text: '', options: [] }],
        addQuestion(type) {
            this.questions.push({ type: type, text: '', options: type === 'multiple_choice' ? [''] : [] });
        },
        removeQuestion(i) {
            this.questions.splice(i, 1);
        },
        addOption(qi) {
            this.questions[qi].options.push('');
        },
        removeOption(qi, oi) {
            this.questions[qi].options.splice(oi, 1);
        }
    }" class="max-w-3xl space-y-6">
        <form action="{{ route('marketing.surveys.store') }}" method="POST">
            @csrf

            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 mb-6">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Survey Info</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Title *</label>
                        <input type="text" name="title" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="e.g. Q1 2026 Customer Satisfaction">
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Description</label>
                        <textarea name="description" rows="2" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Type *</label>
                        <select name="type" x-model="surveyType" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                            <option value="nps">NPS (Net Promoter Score)</option>
                            <option value="csat">CSAT (Customer Satisfaction)</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Target Segment</label>
                        <select name="segment_id" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                            <option value="">All Customers</option>
                            @foreach($segments as $seg)
                            <option value="{{ $seg->id }}">{{ $seg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Questions --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 mb-6">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Questions</h3>

                <template x-for="(q, qi) in questions" :key="qi">
                    <div class="border border-bankos-border dark:border-bankos-dark-border rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text" x-text="'Question ' + (qi+1)"></span>
                            <button type="button" @click="removeQuestion(qi)" class="text-red-500 text-xs hover:underline">Remove</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="md:col-span-2">
                                <input type="text" x-model="q.text" :name="'questions['+qi+'][text]'" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="Question text">
                            </div>
                            <div>
                                <select x-model="q.type" :name="'questions['+qi+'][type]'" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                    <option value="text">Text</option>
                                    <option value="rating">Rating (1-10)</option>
                                    <option value="multiple_choice">Multiple Choice</option>
                                </select>
                            </div>
                        </div>
                        <div x-show="q.type === 'multiple_choice'" class="mt-3">
                            <label class="text-xs font-medium text-bankos-muted mb-1 block">Options</label>
                            <template x-for="(opt, oi) in q.options" :key="oi">
                                <div class="flex items-center gap-2 mb-2">
                                    <input type="text" x-model="q.options[oi]" :name="'questions['+qi+'][options]['+oi+']'" class="flex-1 rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="Option">
                                    <button type="button" @click="removeOption(qi, oi)" class="text-red-500 hover:text-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </div>
                            </template>
                            <button type="button" @click="addOption(qi)" class="text-xs text-bankos-primary hover:underline">+ Add Option</button>
                        </div>
                    </div>
                </template>

                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="addQuestion('text')" class="px-3 py-1.5 text-xs border border-bankos-border dark:border-bankos-dark-border rounded-lg hover:bg-gray-50 dark:hover:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text">+ Text</button>
                    <button type="button" @click="addQuestion('rating')" class="px-3 py-1.5 text-xs border border-bankos-border dark:border-bankos-dark-border rounded-lg hover:bg-gray-50 dark:hover:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text">+ Rating</button>
                    <button type="button" @click="addQuestion('multiple_choice')" class="px-3 py-1.5 text-xs border border-bankos-border dark:border-bankos-dark-border rounded-lg hover:bg-gray-50 dark:hover:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text">+ Multiple Choice</button>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('marketing.surveys') }}" class="px-4 py-2 text-sm text-bankos-muted hover:text-bankos-text">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90">Create Survey</button>
            </div>
        </form>
    </div>
</x-app-layout>
