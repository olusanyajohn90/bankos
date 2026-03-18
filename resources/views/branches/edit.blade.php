<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('branches.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center">
                    {{ isset($branch) ? 'Edit Branch: ' . $branch->name : 'Create New Branch' }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Configure physical branch details and routing</p>
            </div>
        </div>
    </x-slot>

    <div class="card max-w-4xl mx-auto shadow-md border-t-4 border-t-bankos-primary">
        <form action="{{ isset($branch) ? route('branches.update', $branch) : route('branches.store') }}" method="POST" class="space-y-6">
            @csrf
            @if(isset($branch))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Info -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-bankos-text border-b border-gray-100 dark:border-gray-800 pb-2">Basic Details</h3>
                    
                    <div>
                        <label for="name" class="form-label">Branch Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $branch->name ?? '') }}" class="form-input" required placeholder="e.g. Headquarters">
                    </div>

                    <div>
                        <label for="code" class="form-label">Branch Code (System) <span class="text-red-500">*</span></label>
                        <input type="text" name="code" id="code" value="{{ old('code', $branch->code ?? '') }}" class="form-input font-mono" required placeholder="e.g. HQ01">
                        <p class="form-hint">Unique internal identifier.</p>
                    </div>

                    <div>
                        <label for="status" class="form-label">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="active" {{ old('status', $branch->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $branch->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div>
                         <label for="manager_id" class="form-label">Branch Manager</label>
                         <select name="manager_id" id="manager_id" class="form-select">
                             <option value="">-- No Manager Assigned --</option>
                             @foreach($managers ?? [] as $manager)
                                <option value="{{ $manager->id }}" {{ old('manager_id', $branch->manager_id ?? '') == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->first_name }} {{ $manager->last_name }}
                                </option>
                             @endforeach
                         </select>
                    </div>
                </div>

                <!-- Locational Info -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-bankos-text border-b border-gray-100 dark:border-gray-800 pb-2">Location & Contact</h3>
                    
                    <div>
                        <label for="routing_number" class="form-label">Routing / Sort Code</label>
                        <input type="text" name="routing_number" id="routing_number" value="{{ old('routing_number', $branch->routing_number ?? '') }}" class="form-input font-mono" placeholder="e.g. 044123456">
                        <p class="form-hint">Bank clearing code for this branch.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $branch->phone ?? '') }}" class="form-input" placeholder="+234...">
                        </div>
                        <div>
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $branch->email ?? '') }}" class="form-input" placeholder="branch@bank.com">
                        </div>
                    </div>

                    <div>
                        <label for="street" class="form-label">Street Address <span class="text-red-500">*</span></label>
                        <textarea name="street" id="street" rows="2" class="form-input" required>{{ old('street', $branch->street ?? '') }}</textarea>
                    </div>

                    <div x-data="locationDropdowns()" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="state" class="form-label">State <span class="text-red-500">*</span></label>
                                <select name="state" id="state" x-model="selectedState" @change="updateLgas" class="form-select" required>
                                    <option value="">-- Select State --</option>
                                    @foreach(array_keys($states) as $state)
                                        <option value="{{ $state }}">{{ $state }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="local_government" class="form-label">Local Government (LGA) <span class="text-red-500">*</span></label>
                                <select name="local_government" id="local_government" x-model="selectedLga" class="form-select" required :disabled="!selectedState || lgas.length === 0">
                                    <option value="">-- Select LGA --</option>
                                    <template x-for="lga in lgas" :key="lga">
                                        <option :value="lga" x-text="lga"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="city" class="form-label">City / Town <span class="text-red-500">*</span></label>
                            <input type="text" name="city" id="city" value="{{ old('city', $branch->city ?? '') }}" class="form-input" required placeholder="e.g. Ikeja, Wuse, etc.">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-bankos-border dark:border-bankos-dark-border flex justify-end gap-3">
                <a href="{{ route('branches.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary shadow-md hover:-translate-y-0.5 transition-transform">
                    {{ isset($branch) ? 'Update Branch' : 'Create Branch' }}
                </button>
            </div>
        </form>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('locationDropdowns', () => ({
                statesConfig: @json($states),
                selectedState: '{{ old('state', $branch->state ?? '') }}',
                selectedLga: '{{ old('local_government', $branch->local_government ?? '') }}',
                lgas: [],

                init() {
                    // Populate LGAs based on initial state (for edit/validation retry)
                    if (this.selectedState && this.statesConfig[this.selectedState]) {
                        this.lgas = this.statesConfig[this.selectedState];
                    }
                },

                updateLgas() {
                    this.selectedLga = ''; // reset LGA
                    this.lgas = this.statesConfig[this.selectedState] || [];
                }
            }))
        })
    </script>
</x-app-layout>
