<x-app-layout>
    <x-slot name="header">Compliance Assistant</x-slot>

    <div class="space-y-6" x-data="complianceChat()">

        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden" style="height: 70vh; display: flex; flex-direction: column;">
            {{-- Chat Header --}}
            <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                <h3 class="font-semibold">Compliance AI Assistant</h3>
                <p class="text-sm text-white/70">Ask questions about KYC, AML, CBN regulations, NFIU reporting, and more.</p>
            </div>

            {{-- Messages --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-4" id="chatMessages">
                @if($session && !empty($session->messages))
                    @foreach($session->messages as $msg)
                    <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[75%] rounded-xl px-4 py-3 {{ $msg['role'] === 'user' ? 'bg-bankos-primary text-white' : 'bg-gray-100 dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text' }}">
                            <p class="text-sm whitespace-pre-wrap">{{ $msg['content'] }}</p>
                        </div>
                    </div>
                    @endforeach
                @else
                <div class="text-center py-12">
                    <p class="text-bankos-muted text-sm">Start a conversation about compliance regulations, policies, or procedures.</p>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        <button @click="sendMessage('What are the CBN KYC requirements for individual customers?')" class="px-3 py-1.5 bg-gray-100 dark:bg-bankos-dark-bg rounded-lg text-xs text-bankos-text-sec hover:bg-gray-200">KYC Requirements</button>
                        <button @click="sendMessage('What triggers a Suspicious Transaction Report in Nigeria?')" class="px-3 py-1.5 bg-gray-100 dark:bg-bankos-dark-bg rounded-lg text-xs text-bankos-text-sec hover:bg-gray-200">STR Triggers</button>
                        <button @click="sendMessage('What is the minimum Capital Adequacy Ratio for Nigerian banks?')" class="px-3 py-1.5 bg-gray-100 dark:bg-bankos-dark-bg rounded-lg text-xs text-bankos-text-sec hover:bg-gray-200">Capital Adequacy</button>
                    </div>
                </div>
                @endif

                {{-- Dynamic messages --}}
                <template x-for="msg in messages" :key="msg.timestamp">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="msg.role === 'user' ? 'bg-bankos-primary text-white' : 'bg-gray-100 dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text'" class="max-w-[75%] rounded-xl px-4 py-3">
                            <p class="text-sm whitespace-pre-wrap" x-text="msg.content"></p>
                        </div>
                    </div>
                </template>

                <div x-show="loading" class="flex justify-start">
                    <div class="bg-gray-100 dark:bg-bankos-dark-bg rounded-xl px-4 py-3">
                        <div class="flex gap-1">
                            <span class="w-2 h-2 bg-bankos-muted rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                            <span class="w-2 h-2 bg-bankos-muted rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                            <span class="w-2 h-2 bg-bankos-muted rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Input --}}
            <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">
                <form @submit.prevent="sendMessage(input)" class="flex gap-3">
                    <input type="text" x-model="input" placeholder="Ask about compliance regulations..." class="flex-1 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-4 py-3" :disabled="loading">
                    <button type="submit" :disabled="loading || !input.trim()" class="px-6 py-3 bg-bankos-primary text-white rounded-lg text-sm hover:bg-bankos-primary/90 disabled:opacity-50">Send</button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function complianceChat() {
            return {
                messages: [],
                input: '',
                loading: false,
                async sendMessage(text) {
                    if (!text || !text.trim()) return;
                    const userMsg = text.trim();
                    this.input = '';
                    this.messages.push({ role: 'user', content: userMsg, timestamp: Date.now() });
                    this.loading = true;

                    this.$nextTick(() => {
                        const el = document.getElementById('chatMessages');
                        el.scrollTop = el.scrollHeight;
                    });

                    try {
                        const res = await fetch('{{ route("compliance-auto.compliance-chat.message") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ message: userMsg })
                        });
                        const data = await res.json();
                        this.messages.push({ role: 'assistant', content: data.response, timestamp: Date.now() });
                    } catch (e) {
                        this.messages.push({ role: 'assistant', content: 'Sorry, I encountered an error. Please try again.', timestamp: Date.now() });
                    }

                    this.loading = false;
                    this.$nextTick(() => {
                        const el = document.getElementById('chatMessages');
                        el.scrollTop = el.scrollHeight;
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
