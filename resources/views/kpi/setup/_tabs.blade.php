@php
    $tabs = [
        ['route' => 'kpi.definitions.index', 'label' => 'KPI Definitions', 'key' => 'definitions'],
        ['route' => 'kpi.targets.index',     'label' => 'Targets',          'key' => 'targets'],
        ['route' => 'kpi.staff.index',       'label' => 'Staff Profiles',   'key' => 'staff'],
        ['route' => 'kpi.teams.index',       'label' => 'Teams',            'key' => 'teams'],
    ];
@endphp
<div class="flex gap-1 border-b border-gray-200">
    @foreach($tabs as $tab)
        <a href="{{ route($tab['route']) }}"
           class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors
               {{ $active === $tab['key']
                   ? 'border-indigo-600 text-indigo-600'
                   : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
