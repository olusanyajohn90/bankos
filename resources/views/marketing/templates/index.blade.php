<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Message Templates</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">Reusable message templates for campaigns</p>
            </div>
            <button type="button" onclick="document.getElementById('newTemplateModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Template
            </button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-bankos-bg dark:bg-bankos-dark-bg">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Channel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Body Preview</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Created By</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($templates as $template)
                    <tr class="hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="px-6 py-3 font-medium text-bankos-text dark:text-bankos-dark-text">{{ $template->name }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $template->channel === 'sms' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : ($template->channel === 'email' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400') }}">
                                {{ strtoupper($template->channel) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-bankos-muted">{{ $template->subject ?? '-' }}</td>
                        <td class="px-6 py-3 text-bankos-muted max-w-xs truncate">{{ Str::limit($template->body, 60) }}</td>
                        <td class="px-6 py-3 text-bankos-muted">{{ $template->createdBy?->name ?? '-' }}</td>
                        <td class="px-6 py-3 text-right">
                            <form action="{{ route('marketing.templates.delete', $template->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this template?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 dark:text-red-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-bankos-muted">
                            No templates yet. Create one to speed up campaign creation.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($templates->hasPages())
        <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $templates->links() }}
        </div>
        @endif
    </div>

    {{-- New Template Modal --}}
    <div id="newTemplateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm" x-data="{ channel: 'sms' }">
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl shadow-xl border border-bankos-border dark:border-bankos-dark-border w-full max-w-lg mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="text-lg font-semibold text-bankos-text dark:text-bankos-dark-text">New Template</h3>
                <button type="button" onclick="document.getElementById('newTemplateModal').classList.add('hidden')" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form action="{{ route('marketing.templates.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Name *</label>
                    <input type="text" name="name" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="e.g. Welcome SMS">
                </div>
                <div>
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Channel *</label>
                    <select name="channel" x-model="channel" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                        <option value="sms">SMS</option>
                        <option value="email">Email</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </div>
                <div x-show="channel === 'email'" x-transition>
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Subject</label>
                    <input type="text" name="subject" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="Email subject line">
                </div>
                <div>
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Body *</label>
                    <textarea name="body" rows="5" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="Dear {first_name}, ..."></textarea>
                    <p class="text-xs text-bankos-muted mt-1">Placeholders: {first_name}, {last_name}, {full_name}, {phone}, {email}, {account_number}, {balance}</p>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('newTemplateModal').classList.add('hidden')" class="px-4 py-2 text-sm text-bankos-muted hover:text-bankos-text">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90">Save Template</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
