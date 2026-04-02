@extends('layouts.app')
@section('title', 'Create Project')
@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('projects.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Project</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Set up a new project with a Kanban board</p>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/30 dark:border-red-800 dark:text-red-300 rounded-lg text-sm">
            <ul class="list-disc ml-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('projects.store') }}" method="POST" class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Project Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-bankos-primary focus:border-transparent"
                    placeholder="e.g. Core Banking Platform v2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Project Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code') }}" required maxlength="10"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-bankos-primary focus:border-transparent uppercase"
                    placeholder="e.g. CBP">
                <p class="text-xs text-gray-400 mt-1">Used as task prefix (e.g. CBP-1, CBP-2)</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="color" value="{{ old('color', '#3B82F6') }}"
                        class="w-10 h-10 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer p-0.5">
                    <span class="text-xs text-gray-400">Project accent color</span>
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea name="description" rows="3"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-bankos-primary focus:border-transparent"
                    placeholder="Brief description of the project goals...">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ old('start_date') }}"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-bankos-primary focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ old('end_date') }}"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-bankos-primary focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Visibility</label>
                <select name="visibility"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-bankos-primary focus:border-transparent">
                    <option value="public">Public (visible to all team members)</option>
                    <option value="private">Private (members only)</option>
                </select>
            </div>
        </div>

        {{-- Members --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Invite Members</label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                @foreach($users as $user)
                    @if($user->id !== auth()->id())
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:bg-gray-50 dark:hover:bg-bankos-dark-bg p-1.5 rounded">
                        <input type="checkbox" name="members[]" value="{{ $user->id }}" class="rounded text-bankos-primary focus:ring-bankos-primary">
                        <span class="truncate">{{ $user->name }}</span>
                    </label>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
            <a href="{{ route('projects.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-bankos-primary hover:bg-blue-700 rounded-lg transition-colors">Create Project</button>
        </div>
    </form>
</div>
@endsection
