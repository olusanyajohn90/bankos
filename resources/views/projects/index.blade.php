@extends('layouts.app')
@section('title', 'Projects')
@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ statusFilter: '{{ request('status') }}' }">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Projects</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Manage your team's projects and boards</p>
        </div>
        <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 bg-bankos-primary hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            New Project
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/30 dark:border-green-800 dark:text-green-300 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('projects.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ !request('status') ? 'bg-bankos-primary text-white' : 'bg-white dark:bg-bankos-dark-surface text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">All</a>
        @foreach(['active' => 'Active', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'archived' => 'Archived'] as $val => $label)
        <a href="{{ route('projects.index', ['status' => $val]) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request('status') === $val ? 'bg-bankos-primary text-white' : 'bg-white dark:bg-bankos-dark-surface text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">{{ $label }}</a>
        @endforeach
    </div>

    {{-- Project Grid --}}
    @if($projects->isEmpty())
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto text-gray-300 dark:text-gray-600 mb-4"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">No projects yet</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create your first project to start managing tasks</p>
            <a href="{{ route('projects.create') }}" class="mt-4 inline-flex items-center gap-2 bg-bankos-primary hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Project
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($projects as $project)
            <a href="{{ route('projects.show', $project) }}" class="block bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border hover:shadow-md transition-shadow overflow-hidden group">
                {{-- Color bar --}}
                <div class="h-1.5" style="background-color: {{ $project->color ?? '#3B82F6' }}"></div>

                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white truncate group-hover:text-bankos-primary transition-colors">{{ $project->name }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-mono">{{ $project->code }}</p>
                        </div>
                        @php
                            $statusColors = [
                                'active'    => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
                                'on_hold'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
                                'completed' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
                                'archived'  => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                            ];
                        @endphp
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $statusColors[$project->status] ?? $statusColors['active'] }}">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                    </div>

                    @if($project->description)
                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">{{ Str::limit($project->description, 100) }}</p>
                    @endif

                    {{-- Progress --}}
                    <div class="mb-3">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                            <span>Progress</span>
                            <span class="font-medium">{{ $project->progress }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all" style="width: {{ $project->progress }}%; background-color: {{ $project->color ?? '#3B82F6' }}"></div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-between">
                        {{-- Member avatars --}}
                        <div class="flex -space-x-2">
                            @foreach($project->members->take(4) as $member)
                            <div class="w-7 h-7 rounded-full bg-bankos-primary text-white text-xs font-semibold flex items-center justify-center ring-2 ring-white dark:ring-bankos-dark-surface" title="{{ $member->user->name ?? 'User' }}">
                                {{ strtoupper(substr($member->user->name ?? '?', 0, 1)) }}
                            </div>
                            @endforeach
                            @if($project->members->count() > 4)
                            <div class="w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs font-semibold flex items-center justify-center ring-2 ring-white dark:ring-bankos-dark-surface">
                                +{{ $project->members->count() - 4 }}
                            </div>
                            @endif
                        </div>

                        {{-- Task count --}}
                        <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                            {{ $project->tasks->count() }} tasks
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
