@extends('layouts.app')

@section('title', 'Approval Matrices — Maker-Checker Configuration')

@section('content')
<div class="space-y-6" x-data="{ showCreate: false, activeType: '' }">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Approval Matrices</h1>
            <p class="text-sm text-gray-500 mt-0.5">Configure maker-checker workflows for every action type in the system.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('hr.approvals.requests') }}" class="btn btn-secondary text-sm">
                View Pending Requests →
            </a>
            <button @click="showCreate = !showCreate" class="btn btn-primary text-sm">
                + New Matrix
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Create Form --}}
    <div x-show="showCreate" x-cloak class="card p-6 border-blue-200 bg-blue-50/30">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Create New Approval Matrix</h2>
        <form action="{{ route('hr.approvals.matrix.store') }}" method="POST" x-data="{ steps: [{step_name:'', approver_type:'any_manager', approver_value:'', timeout_hours:48, on_timeout:'escalate'}] }">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Matrix Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Loan Disbursement > ₦5M" class="form-input w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Action Type <span class="text-red-500">*</span></label>
                    <select name="action_type" required class="form-input w-full text-sm">
                        <option value="">Select action type…</option>
                        @foreach($actionTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Min Amount (₦) — trigger condition</label>
                    <input type="number" name="min_amount" min="0" step="1000" placeholder="0 = always trigger" class="form-input w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Max Amount (₦) — upper limit</label>
                    <input type="number" name="max_amount" min="0" step="1000" placeholder="Leave blank = no upper limit" class="form-input w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Escalation After (hours)</label>
                    <input type="number" name="escalation_hours" value="48" min="1" required class="form-input w-full text-sm">
                </div>
                <div class="flex items-center gap-3 pt-5">
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="requires_checker" value="1" checked class="rounded">
                        Requires Checker (Maker-Checker)
                    </label>
                </div>
            </div>
            <input type="hidden" name="total_steps" :value="steps.length">

            {{-- Steps --}}
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-700">Approval Steps</h3>
                    <button type="button" @click="steps.push({step_name:'', approver_type:'any_manager', approver_value:'', timeout_hours:48, on_timeout:'escalate'})" class="text-xs text-indigo-600 hover:text-indigo-800">+ Add Step</button>
                </div>
                <div class="space-y-3">
                    <template x-for="(step, i) in steps" :key="i">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 p-3 bg-white rounded-lg border border-gray-200">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Step Name</label>
                                <input type="text" :name="'steps['+i+'][step_name]'" x-model="step.step_name" required placeholder="e.g. Branch Manager" class="form-input w-full text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Approver Type</label>
                                <select :name="'steps['+i+'][approver_type]'" x-model="step.approver_type" class="form-input w-full text-sm">
                                    <option value="any_manager">Direct Manager</option>
                                    <option value="role">By Role</option>
                                    <option value="department_head">Dept Head</option>
                                    <option value="user">Specific User</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Role / Value</label>
                                <input type="text" :name="'steps['+i+'][approver_value]'" x-model="step.approver_value" placeholder="role name or user ID" class="form-input w-full text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Timeout (hrs)</label>
                                <input type="number" :name="'steps['+i+'][timeout_hours]'" x-model="step.timeout_hours" min="1" required class="form-input w-full text-sm">
                            </div>
                            <div class="flex items-end gap-2">
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-500 mb-1">On Timeout</label>
                                    <select :name="'steps['+i+'][on_timeout]'" x-model="step.on_timeout" class="form-input w-full text-sm">
                                        <option value="escalate">Escalate</option>
                                        <option value="auto_approve">Auto-Approve</option>
                                        <option value="auto_reject">Auto-Reject</option>
                                    </select>
                                </div>
                                <button type="button" x-show="steps.length > 1" @click="steps.splice(i,1)" class="text-red-500 hover:text-red-700 text-xs pb-1">✕</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary text-sm">Create Matrix</button>
                <button type="button" @click="showCreate = false" class="btn btn-secondary text-sm">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Matrices by Action Type --}}
    @if($matrices->isEmpty())
        <div class="card p-12 text-center">
            <div class="text-4xl mb-3">⚙️</div>
            <h3 class="text-lg font-semibold text-gray-700 mb-1">No Approval Matrices Configured</h3>
            <p class="text-sm text-gray-400 mb-4">Set up your first maker-checker workflow to control approvals for loans, expenses, and more.</p>
            <button @click="showCreate = true" class="btn btn-primary text-sm">Create First Matrix</button>
        </div>
    @else
        @foreach($matrices as $actionType => $group)
            <div class="card overflow-hidden">
                <div class="px-5 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-700">
                        {{ $actionTypes[$actionType] ?? ucwords(str_replace('_', ' ', $actionType)) }}
                        <span class="ml-2 text-xs text-gray-400">({{ $group->count() }} {{ Str::plural('rule', $group->count()) }})</span>
                    </h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($group as $matrix)
                        <div class="px-5 py-4 flex flex-col md:flex-row md:items-center gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-gray-900 text-sm">{{ $matrix->name }}</span>
                                    @if($matrix->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactive</span>
                                    @endif
                                    @if($matrix->requires_checker)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Maker-Checker</span>
                                    @endif
                                </div>
                                @if($matrix->min_amount || $matrix->max_amount)
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        Amount: ₦{{ number_format($matrix->min_amount ?? 0) }}
                                        @if($matrix->max_amount) – ₦{{ number_format($matrix->max_amount) }} @else + @endif
                                    </p>
                                @endif
                                <div class="flex items-center gap-3 mt-2 flex-wrap">
                                    @foreach($matrix->steps->sortBy('step_number') as $step)
                                        <div class="flex items-center gap-1 text-xs text-gray-600">
                                            <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">{{ $step->step_number }}</span>
                                            {{ $step->step_name }}
                                            <span class="text-gray-400">({{ $step->approver_type }})</span>
                                        </div>
                                        @if(!$loop->last)
                                            <span class="text-gray-300">→</span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs text-gray-400">{{ $matrix->requests_count }} requests</span>
                                <form action="{{ route('hr.approvals.matrix.toggle', $matrix) }}" method="POST">
                                    @csrf
                                    <button class="text-xs {{ $matrix->is_active ? 'text-yellow-600 hover:text-yellow-800' : 'text-green-600 hover:text-green-800' }}">
                                        {{ $matrix->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('hr.approvals.matrix.destroy', $matrix) }}" method="POST" onsubmit="return confirm('Delete this matrix?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-500 hover:text-red-700">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
