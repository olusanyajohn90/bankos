@php
    $currentActive = $active ?? '';
    $tabs = [
        ['key' => 'runs',        'label' => 'Payroll Runs',  'route' => 'payroll.runs.index'],
        ['key' => 'setup',       'label' => 'Setup',         'route' => 'payroll.setup.index'],
        ['key' => 'my-payslips', 'label' => 'My Payslips',   'route' => 'payroll.my-payslips'],
    ];
@endphp

<div class="border-b border-gray-200 dark:border-gray-700 mb-2">
    <nav class="-mb-px flex space-x-1 sm:space-x-2" aria-label="Payroll navigation">
        @foreach($tabs as $tab)
        @php
            $isActive = $currentActive === $tab['key'];
        @endphp
        <a href="{{ route($tab['route']) }}"
           class="{{ $isActive
               ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
               : 'bg-transparent text-gray-600 dark:text-gray-400 border-transparent hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700/50'
           }} inline-flex items-center px-4 py-2 rounded-t-lg border-2 text-sm font-medium transition-colors whitespace-nowrap">
            @if($tab['key'] === 'runs')
                <svg class="w-4 h-4 mr-1.5 {{ $isActive ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            @elseif($tab['key'] === 'setup')
                <svg class="w-4 h-4 mr-1.5 {{ $isActive ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            @elseif($tab['key'] === 'my-payslips')
                <svg class="w-4 h-4 mr-1.5 {{ $isActive ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            @endif
            {{ $tab['label'] }}
        </a>
        @endforeach
    </nav>
</div>
