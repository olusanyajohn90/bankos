<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Portal Investment Products</h2>
        <p class="text-sm text-bankos-text-sec mt-1">Configure fixed deposit / investment products shown to customers in the portal</p>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Product List --}}
        <div class="lg:col-span-2 space-y-4">
            @forelse($products as $p)
            <div class="card p-5" x-data="{ editing: false }">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-base">{{ $p->name }}</h3>
                            @if($p->is_active)
                            <span class="badge badge-active text-xs">Active</span>
                            @else
                            <span class="badge badge-danger text-xs">Inactive</span>
                            @endif
                        </div>
                        @if($p->description)
                        <p class="text-sm text-bankos-text-sec mt-1">{{ $p->description }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 ml-4 shrink-0">
                        <button @click="editing = !editing" class="btn btn-secondary text-xs py-1 px-3">Edit</button>
                        <form method="POST" action="{{ route('investment-products.toggle', $p->id) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-secondary text-xs py-1 px-3 {{ $p->is_active?'text-red-600 hover:border-red-400':'' }}">
                                {{ $p->is_active ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('investment-products.destroy', $p->id) }}" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-secondary text-xs py-1 px-3 text-red-600 hover:border-red-400"
                                    onclick="return confirm('Delete this product?')">Delete</button>
                        </form>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm mb-3">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                        <p class="text-xs text-bankos-muted">Duration</p>
                        <p class="font-bold mt-0.5">{{ $p->duration_days }} days</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                        <p class="text-xs text-bankos-muted">Interest Rate</p>
                        <p class="font-bold mt-0.5 text-bankos-success">{{ $p->interest_rate }}% p.a.</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                        <p class="text-xs text-bankos-muted">Min Amount</p>
                        <p class="font-bold mt-0.5">₦{{ number_format($p->min_amount, 0) }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                        <p class="text-xs text-bankos-muted">Max Amount</p>
                        <p class="font-bold mt-0.5">{{ $p->max_amount ? '₦'.number_format($p->max_amount,0) : 'Unlimited' }}</p>
                    </div>
                </div>

                {{-- Edit form (hidden by default) --}}
                <div x-show="editing" x-collapse style="display:none" class="mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                    <form method="POST" action="{{ route('investment-products.update', $p->id) }}">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-2 gap-4 mb-3">
                            <div>
                                <label class="block text-xs font-medium mb-1">Name</label>
                                <input type="text" name="name" value="{{ $p->name }}" required class="input w-full text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Duration (days)</label>
                                <input type="number" name="duration_days" value="{{ $p->duration_days }}" min="1" required class="input w-full text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Interest Rate (% p.a.)</label>
                                <input type="number" name="interest_rate" value="{{ $p->interest_rate }}" step="0.01" min="0" required class="input w-full text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Min Amount (₦)</label>
                                <input type="number" name="min_amount" value="{{ $p->min_amount }}" min="0" required class="input w-full text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Max Amount (₦) — blank = unlimited</label>
                                <input type="number" name="max_amount" value="{{ $p->max_amount }}" min="0" class="input w-full text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Sort Order</label>
                                <input type="number" name="sort_order" value="{{ $p->sort_order }}" min="0" class="input w-full text-sm">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs font-medium mb-1">Description (optional)</label>
                            <textarea name="description" rows="2" class="input w-full text-sm resize-none">{{ $p->description }}</textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="btn btn-primary text-sm">Save Changes</button>
                            <button type="button" @click="editing=false" class="btn btn-secondary text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            @empty
            <div class="card p-12 text-center text-bankos-muted">
                <p class="text-4xl mb-3">📈</p>
                <p class="font-medium">No investment products configured yet</p>
                <p class="text-sm mt-1">Add your first product using the form →</p>
            </div>
            @endforelse
        </div>

        {{-- Add New Product --}}
        <div class="card p-5 h-fit">
            <h3 class="font-bold text-base mb-4">Add New Product</h3>
            @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg text-sm text-red-700 dark:text-red-400">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('investment-products.store') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium mb-1">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. 90-Day Fixed Deposit" required class="input w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Description</label>
                        <textarea name="description" rows="2" placeholder="Brief description shown to customers…" class="input w-full text-sm resize-none">{{ old('description') }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium mb-1">Duration (days) <span class="text-red-500">*</span></label>
                            <input type="number" name="duration_days" value="{{ old('duration_days') }}" min="1" placeholder="90" required class="input w-full text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Interest Rate (% p.a.) <span class="text-red-500">*</span></label>
                            <input type="number" name="interest_rate" value="{{ old('interest_rate') }}" step="0.01" min="0" placeholder="12.5" required class="input w-full text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Min Amount (₦) <span class="text-red-500">*</span></label>
                            <input type="number" name="min_amount" value="{{ old('min_amount') }}" min="0" placeholder="100000" required class="input w-full text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Max Amount (₦)</label>
                            <input type="number" name="max_amount" value="{{ old('max_amount') }}" min="0" placeholder="Unlimited" class="input w-full text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="input w-full text-sm">
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
