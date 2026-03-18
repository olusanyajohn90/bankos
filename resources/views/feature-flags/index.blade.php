<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Feature Flags</h2>
            <p class="text-sm text-bankos-text-sec mt-1">Enable or disable product features for your institution</p>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <div class="space-y-6">
        @foreach($features as $group => $flags)
        <div class="card p-0 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-bankos-border flex items-center justify-between">
                <p class="font-bold text-sm">{{ $group }}</p>
                <span class="text-xs text-gray-400">{{ $flags->count() }} features</span>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($flags as $flag)
                <div class="flex items-center justify-between px-5 py-4"
                     x-data="{
                         on: {{ $flag['enabled'] ? 'true' : 'false' }},
                         busy: false,
                         async toggle() {
                             if (this.busy) return;
                             this.busy = true;
                             try {
                                 const r = await fetch('/feature-flags/{{ $flag['key'] }}', {
                                     method: 'PATCH',
                                     headers: {
                                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                         'Accept': 'application/json'
                                     }
                                 });
                                 const d = await r.json();
                                 this.on = d.enabled;
                             } finally {
                                 this.busy = false;
                             }
                         }
                     }">
                    <div class="flex-1 min-w-0 pr-6">
                        <p class="text-sm font-semibold text-gray-800">{{ $flag['label'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $flag['desc'] }}</p>
                    </div>

                    <div class="flex items-center gap-3 flex-shrink-0">
                        <span class="text-xs font-bold w-7 text-right"
                              :class="on ? 'text-green-600' : 'text-gray-400'"
                              x-text="on ? 'ON' : 'OFF'"></span>

                        {{-- Toggle track --}}
                        <button type="button"
                                role="switch"
                                :aria-checked="on.toString()"
                                :disabled="busy"
                                @click="toggle()"
                                :style="{ background: on ? '#2563EB' : '#D1D5DB' }"
                                style="position:relative;display:inline-flex;align-items:center;height:24px;width:44px;flex-shrink:0;cursor:pointer;border-radius:9999px;padding:2px;border:none;outline:none;transition:background-color 0.2s ease;">
                            {{-- Toggle thumb --}}
                            <span :style="{ transform: on ? 'translateX(20px)' : 'translateX(0px)' }"
                                  style="display:inline-block;height:20px;width:20px;border-radius:50%;background:white;box-shadow:0 1px 3px rgba(0,0,0,0.25);transition:transform 0.2s ease;">
                            </span>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</x-app-layout>
