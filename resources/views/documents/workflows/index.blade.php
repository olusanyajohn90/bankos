@extends('layouts.app')
@section('title', 'Document Workflows')
@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('documents.index') }}" class="text-xs text-gray-400 hover:text-gray-600">← Documents</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Approval Workflows</h1>
            <p class="text-sm text-gray-500 mt-0.5">Build multi-step workflows for document review, approval, and signing.</p>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ New Workflow</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>@endif

    {{-- New Workflow Form --}}
    <div x-show="showNew" x-transition class="card p-6" x-data="workflowBuilder()">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">New Workflow</h2>
        <form action="{{ route('documents.workflows.store') }}" method="POST" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-xs text-gray-500 mb-1">Workflow Name *</label>
                    <input type="text" name="name" required class="form-input w-full text-sm" placeholder="e.g. Board Resolution Approval"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Auto-trigger Category</label>
                    <input type="text" name="trigger_category" class="form-input w-full text-sm" placeholder="e.g. Board Resolution (auto-starts on upload)"></div>
                <div class="md:col-span-2"><label class="block text-xs text-gray-500 mb-1">Description</label>
                    <textarea name="description" rows="2" class="form-input w-full text-sm resize-none" placeholder="What this workflow is used for…"></textarea></div>
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="requires_all_signatures" value="1" class="rounded border-gray-300"> All steps must sign/approve
                </label>
            </div>

            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700">Workflow Steps</h3>
                    <button type="button" @click="addStep()" class="text-xs text-blue-600 hover:text-blue-800 font-medium">+ Add Step</button>
                </div>
                <div class="space-y-3">
                    <template x-for="(step, idx) in steps" :key="idx">
                        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                            <div class="flex-none text-sm font-bold text-gray-400 pt-2" x-text="idx + 1 + '.'"></div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 flex-1">
                                <input type="text" :name="'steps[' + idx + '][name]'" required placeholder="Step name" class="form-input text-sm col-span-2 md:col-span-1">
                                <select :name="'steps[' + idx + '][action_type]'" class="form-input text-sm">
                                    <option value="approve">Approve</option>
                                    <option value="sign">Sign</option>
                                    <option value="review">Review</option>
                                    <option value="acknowledge">Acknowledge</option>
                                </select>
                                <select :name="'steps[' + idx + '][assignee_user_id]'" class="form-input text-sm">
                                    <option value="">— Assignee —</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" :name="'steps[' + idx + '][deadline_hours]'" placeholder="Deadline (hrs)" class="form-input text-sm">
                            </div>
                            <button type="button" @click="removeStep(idx)" class="text-red-400 hover:text-red-600 pt-2 text-sm">✕</button>
                        </div>
                    </template>
                </div>
                <p x-show="steps.length === 0" class="text-xs text-gray-400 mt-2">No steps yet. Add at least one step.</p>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create Workflow</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Workflows List --}}
    <div class="space-y-4">
        @forelse($workflows as $wf)
        <div class="card p-5" x-data="{ expanded: false }">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-900">{{ $wf->name }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $wf->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $wf->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        @if($wf->trigger_category)<span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-700">Auto: {{ $wf->trigger_category }}</span>@endif
                    </div>
                    @if($wf->description)<p class="text-xs text-gray-500 mt-0.5">{{ $wf->description }}</p>@endif
                    <p class="text-xs text-gray-400 mt-1">{{ $wf->steps->count() }} steps · {{ $wf->instances_count }} uses</p>
                </div>
                <div class="flex gap-2">
                    <button @click="expanded = !expanded" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Steps</button>
                    <form action="{{ route('documents.workflows.toggle', $wf) }}" method="POST" class="inline">
                        @csrf
                        <button class="text-xs {{ $wf->is_active ? 'text-orange-500 hover:text-orange-700' : 'text-green-600 hover:text-green-800' }} font-medium">
                            {{ $wf->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <form action="{{ route('documents.workflows.destroy', $wf) }}" method="POST" onsubmit="return confirm('Delete workflow?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-400 hover:text-red-600">Delete</button>
                    </form>
                </div>
            </div>

            <div x-show="expanded" x-transition class="mt-4 space-y-2">
                @foreach($wf->steps as $step)
                <div class="flex items-center gap-3 text-sm">
                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center flex-none">{{ $step->step_order }}</span>
                    <span class="font-medium text-gray-800">{{ $step->name }}</span>
                    <span class="px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">{{ $step->action_label }}</span>
                    @if($step->assigneeUser)<span class="text-xs text-gray-500">→ {{ $step->assigneeUser->name }}</span>@endif
                    @if($step->deadline_hours)<span class="text-xs text-gray-400">{{ $step->deadline_hours }}h deadline</span>@endif
                    @if($step->is_optional)<span class="text-xs text-gray-400 italic">optional</span>@endif
                </div>
                @endforeach
            </div>
        </div>
        @empty
            <div class="card p-12 text-center text-gray-400">No workflows defined yet. Create one to route documents for review and signing.</div>
        @endforelse
    </div>

</div>

<script>
function workflowBuilder() {
    return {
        steps: [{ name:'', action_type:'approve', assignee_user_id:'', deadline_hours:'' }],
        addStep() { this.steps.push({ name:'', action_type:'approve', assignee_user_id:'', deadline_hours:'' }); },
        removeStep(idx) { this.steps.splice(idx, 1); },
    }
}
</script>
@endsection
