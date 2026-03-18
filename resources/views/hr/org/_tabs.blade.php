<nav class="flex flex-wrap gap-1 mb-6 border-b border-gray-200 pb-2">
    @php
        $navItems = [
            ['label' => 'Org Structure',   'route' => 'hr.org.index',             'match' => 'hr.org.*'],
            ['label' => 'Leave Types',     'route' => 'hr.leave.types.index',     'match' => 'hr.leave.types.*'],
            ['label' => 'Leave Requests',  'route' => 'hr.leave.requests.index',  'match' => 'hr.leave.requests.*'],
            ['label' => 'My Leave',        'route' => 'hr.leave.my-requests',     'match' => 'hr.leave.my-requests'],
            ['label' => 'Disciplinary',    'route' => 'hr.disciplinary.index',    'match' => 'hr.disciplinary.*'],
            ['label' => 'Performance',     'route' => 'hr.performance.cycles.index', 'match' => 'hr.performance.*'],
            ['label' => 'Training',        'route' => 'hr.training.index',        'match' => 'hr.training.*'],
        ];
    @endphp

    @foreach ($navItems as $item)
        @if (Route::has($item['route']))
            <a href="{{ route($item['route']) }}"
               class="px-4 py-2 text-sm font-medium rounded-md transition-colors
                      {{ request()->routeIs($item['match'])
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-800' }}">
                {{ $item['label'] }}
            </a>
        @endif
    @endforeach
</nav>
