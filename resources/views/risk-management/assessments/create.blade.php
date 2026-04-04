<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">New Risk Assessment</h2></div>
            <a href="{{ route('risk-management.assessments') }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('risk-management.assessments.store') }}" class="card p-6 space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div><label class="label">Risk Type</label><select name="risk_type" class="input w-full" required>@foreach(['credit','liquidity','market','operational','concentration'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select></div>
                <div><label class="label">Severity</label><select name="severity" class="input w-full" required>@foreach(['low','medium','high','critical'] as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach</select></div>
            </div>
            <div><label class="label">Title</label><input type="text" name="title" value="{{ old('title') }}" class="input w-full" required>@error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="label">Description</label><textarea name="description" class="input w-full" rows="3">{{ old('description') }}</textarea></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="label">Exposure Amount (₦)</label><input type="number" step="0.01" name="exposure_amount" value="{{ old('exposure_amount') }}" class="input w-full"></div>
                <div><label class="label">Assign To</label><select name="assigned_to" class="input w-full"><option value="">Unassigned</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></div>
            </div>
            <div><label class="label">Mitigation Plan</label><textarea name="mitigation_plan" class="input w-full" rows="3">{{ old('mitigation_plan') }}</textarea></div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('risk-management.assessments') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Assessment</button>
            </div>
        </form>
    </div>
</x-app-layout>
