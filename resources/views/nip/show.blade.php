<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('nip.index') }}"
                   class="text-bankos-text-sec hover:text-bankos-primary transition-colors"
                   title="Back to NIP Transfers">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                        Transfer Detail
                    </h2>
                    <p class="text-sm text-bankos-text-sec mt-1">
                        NIP Outward &middot; {{ $transfer->created_at->format('d M Y, H:i') }}
                    </p>
                </div>
            </div>

            {{-- Header status badge --}}
            @php
                $statusColors = [
                    'successful'   => 'badge-active',
                    'failed'       => 'badge-danger',
                    'reversed'     => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                    'initiated'    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                    'pending'      => 'badge-pending',
                    'name_enquiry' => 'badge-pending',
                ];
                $statusClass = $statusColors[$transfer->status] ?? 'badge-dormant';
            @endphp
            <span class="badge {{ $statusClass }} px-3 py-1 text-sm uppercase tracking-wider">
                {{ ucwords(str_replace('_', ' ', $transfer->status)) }}
            </span>
        </div>
    </x-slot>

    {{-- ══════════════════════════════════════════
         Status Banner
    ══════════════════════════════════════════ --}}
    @php
        $bannerConfig = [
            'successful' => [
                'bg'   => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800',
                'icon' => 'text-green-500',
                'text' => 'text-green-800 dark:text-green-200',
                'msg'  => 'This transfer was completed successfully.',
                'svg'  => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />',
            ],
            'failed' => [
                'bg'   => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                'icon' => 'text-red-500',
                'text' => 'text-red-800 dark:text-red-200',
                'msg'  => 'This transfer failed.' . ($transfer->failure_reason ? ' Reason: ' . $transfer->failure_reason : ''),
                'svg'  => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />',
            ],
            'reversed' => [
                'bg'   => 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800',
                'icon' => 'text-orange-500',
                'text' => 'text-orange-800 dark:text-orange-200',
                'msg'  => 'This transfer has been reversed.',
                'svg'  => '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />',
            ],
            'initiated' => [
                'bg'   => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800',
                'icon' => 'text-blue-500',
                'text' => 'text-blue-800 dark:text-blue-200',
                'msg'  => 'Transfer has been sent to NIBSS and is awaiting confirmation.',
                'svg'  => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />',
            ],
        ];
        $banner = $bannerConfig[$transfer->status] ?? [
            'bg'   => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
            'icon' => 'text-yellow-500',
            'text' => 'text-yellow-800 dark:text-yellow-200',
            'msg'  => 'Transfer is ' . ucwords(str_replace('_', ' ', $transfer->status)) . '.',
            'svg'  => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />',
        ];
    @endphp

    <div class="flex items-start gap-3 rounded-xl border {{ $banner['bg'] }} px-5 py-4 mb-6">
        <svg class="h-5 w-5 {{ $banner['icon'] }} mt-0.5 flex-shrink-0"
             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            {!! $banner['svg'] !!}
        </svg>
        <p class="text-sm font-medium {{ $banner['text'] }}">{{ $banner['msg'] }}</p>
    </div>

    {{-- ══════════════════════════════════════════
         Amount Hero
    ══════════════════════════════════════════ --}}
    <div class="text-center mb-8">
        <p class="text-xs text-bankos-text-sec uppercase tracking-widest font-medium mb-1">Transfer Amount</p>
        <p class="text-5xl font-extrabold text-bankos-text dark:text-white">
            ₦{{ number_format($transfer->amount, 2) }}
        </p>
        @if($transfer->narration)
            <p class="mt-2 text-sm text-bankos-text-sec italic">&ldquo;{{ $transfer->narration }}&rdquo;</p>
        @endif
    </div>

    {{-- ══════════════════════════════════════════
         Main Content Grid
    ══════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left / Main: Sender + Beneficiary + NIBSS --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Sender Details --}}
            <div class="card p-6 border border-bankos-border space-y-4">
                <h3 class="font-semibold text-bankos-text border-b border-bankos-border pb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-bankos-muted" xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sender Details
                </h3>

                @php
                    $senderRows = [
                        'Account Name'   => $transfer->sender_account_name
                                           ?? $transfer->sourceAccount?->account_name
                                           ?? '—',
                        'Account Number' => $transfer->sender_account_number
                                           ?? $transfer->sourceAccount?->account_number
                                           ?? '—',
                    ];
                @endphp

                @foreach($senderRows as $label => $value)
                <div class="flex justify-between items-start gap-4 text-sm">
                    <span class="text-bankos-text-sec font-medium w-40 flex-shrink-0">{{ $label }}</span>
                    <span class="text-bankos-text text-right font-mono">{{ $value }}</span>
                </div>
                @endforeach

                @if($transfer->sourceAccount)
                <div class="flex justify-between items-center gap-4 text-sm">
                    <span class="text-bankos-text-sec font-medium w-40 flex-shrink-0">Source Account</span>
                    <a href="{{ route('accounts.show', $transfer->sourceAccount) }}"
                       class="text-bankos-primary hover:underline font-mono text-right">
                        {{ $transfer->sourceAccount->account_number }}
                    </a>
                </div>

                @if($transfer->sourceAccount->customer ?? null)
                <div class="flex justify-between items-center gap-4 text-sm">
                    <span class="text-bankos-text-sec font-medium w-40 flex-shrink-0">Customer</span>
                    <a href="{{ route('customers.show', $transfer->sourceAccount->customer) }}"
                       class="text-bankos-primary hover:underline text-right">
                        {{ $transfer->sourceAccount->customer->first_name }}
                        {{ $transfer->sourceAccount->customer->last_name }}
                    </a>
                </div>
                @endif
                @endif

                @if($transfer->initiatedBy)
                <div class="flex justify-between items-start gap-4 text-sm">
                    <span class="text-bankos-text-sec font-medium w-40 flex-shrink-0">Initiated By</span>
                    <span class="text-bankos-text text-right">{{ $transfer->initiatedBy->name }}</span>
                </div>
                @endif
            </div>

            {{-- Beneficiary Details --}}
            <div class="card p-6 border border-bankos-border space-y-4">
                <h3 class="font-semibold text-bankos-text border-b border-bankos-border pb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-bankos-muted" xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 16l-4-4m0 0l4-4m-4 4h18m-6 4v1a3 3 0 003 3h4a3 3 0 003-3V7a3 3 0 00-3-3h-4a3 3 0 00-3 3v1"/>
                    </svg>
                    Beneficiary Details
                </h3>

                @foreach([
                    'Account Name'   => $transfer->beneficiary_account_name ?? '—',
                    'Account Number' => $transfer->beneficiary_account_number,
                    'Bank'           => $transfer->beneficiary_bank_name ?? '—',
                ] as $label => $value)
                <div class="flex justify-between items-start gap-4 text-sm">
                    <span class="text-bankos-text-sec font-medium w-40 flex-shrink-0">{{ $label }}</span>
                    <span class="text-bankos-text text-right font-mono">{{ $value }}</span>
                </div>
                @endforeach
            </div>

            {{-- NIBSS / Technical Info --}}
            <div class="card p-6 border border-bankos-border space-y-4">
                <h3 class="font-semibold text-bankos-text border-b border-bankos-border pb-2">
                    NIBSS Information
                </h3>

                <div class="flex justify-between items-start gap-4 text-sm">
                    <span class="text-bankos-text-sec font-medium w-44 flex-shrink-0">Session ID</span>
                    <span class="text-bankos-text text-right font-mono text-xs break-all">
                        {{ $transfer->session_id ?? '—' }}
                    </span>
                </div>

                <div class="flex justify-between items-start gap-4 text-sm">
                    <span class="text-bankos-text-sec font-medium w-44 flex-shrink-0">Response Code</span>
                    <span class="text-bankos-text text-right font-mono">
                        {{ $transfer->nibss_response_code ?? '—' }}
                    </span>
                </div>

                @if($transfer->status === 'failed' && $transfer->failure_reason)
                <div class="flex justify-between items-start gap-4 text-sm">
                    <span class="text-bankos-text-sec font-medium w-44 flex-shrink-0">Failure Reason</span>
                    <span class="text-red-600 dark:text-red-400 text-right text-xs leading-relaxed">
                        {{ $transfer->failure_reason }}
                    </span>
                </div>
                @endif

                @if($transfer->narration)
                <div class="flex justify-between items-start gap-4 text-sm">
                    <span class="text-bankos-text-sec font-medium w-44 flex-shrink-0">Narration</span>
                    <span class="text-bankos-text text-right">{{ $transfer->narration }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Right Column: Timeline + Source Account Summary --}}
        <div class="space-y-6">

            {{-- Timeline --}}
            <div class="card p-6 border border-bankos-border">
                <h3 class="font-semibold text-bankos-text border-b border-bankos-border pb-2 mb-5">
                    Timeline
                </h3>

                @php
                    $timelineSteps = [
                        [
                            'label'  => 'Created',
                            'time'   => $transfer->created_at,
                            'always' => true,
                        ],
                        [
                            'label'  => 'Initiated',
                            'time'   => $transfer->initiated_at ?? null,
                            'always' => false,
                        ],
                        [
                            'label'  => 'Completed',
                            'time'   => $transfer->completed_at ?? null,
                            'always' => false,
                        ],
                        [
                            'label'  => 'Reversed',
                            'time'   => $transfer->reversed_at ?? null,
                            'always' => false,
                        ],
                    ];
                @endphp

                <ol class="relative border-l-2 border-bankos-border dark:border-bankos-dark-border ml-3 space-y-7">
                    @foreach($timelineSteps as $tStep)
                        @if($tStep['always'] || $tStep['time'])
                        <li class="ml-6 relative">
                            <span class="absolute -left-[1.85rem] top-0.5 flex items-center justify-center
                                         w-6 h-6 rounded-full border-2 border-white dark:border-bankos-dark-surface
                                         {{ $tStep['time'] ? 'bg-bankos-primary' : 'bg-gray-200 dark:bg-gray-700' }}">
                                @if($tStep['time'])
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500"></span>
                                @endif
                            </span>
                            <p class="font-medium text-sm text-bankos-text">{{ $tStep['label'] }}</p>
                            <p class="text-xs text-bankos-text-sec mt-0.5">
                                {{ $tStep['time'] ? $tStep['time']->format('d M Y, H:i:s') : '—' }}
                            </p>
                        </li>
                        @endif
                    @endforeach

                    {{-- Terminal status node --}}
                    @php
                        $terminalColors = [
                            'successful' => 'bg-green-500',
                            'failed'     => 'bg-red-500',
                            'reversed'   => 'bg-orange-500',
                        ];
                        $terminalColor = $terminalColors[$transfer->status] ?? 'bg-gray-300 dark:bg-gray-600';
                        $terminalTextColor = [
                            'successful' => 'text-green-700 dark:text-green-400',
                            'failed'     => 'text-red-600 dark:text-red-400',
                            'reversed'   => 'text-orange-600 dark:text-orange-400',
                        ][$transfer->status] ?? 'text-bankos-text';
                    @endphp
                    <li class="ml-6 relative">
                        <span class="absolute -left-[1.85rem] top-0.5 flex items-center justify-center
                                     w-6 h-6 rounded-full border-2 border-white dark:border-bankos-dark-surface
                                     {{ $terminalColor }}">
                            @if($transfer->status === 'successful')
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @elseif(in_array($transfer->status, ['failed', 'reversed']))
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <span class="w-2 h-2 rounded-full bg-white opacity-80"></span>
                            @endif
                        </span>
                        <p class="font-semibold text-sm {{ $terminalTextColor }}">
                            {{ ucwords(str_replace('_', ' ', $transfer->status)) }}
                        </p>
                    </li>
                </ol>
            </div>

            {{-- Source Account Quick Info --}}
            @if($transfer->sourceAccount)
            <div class="card p-5 border border-bankos-border space-y-3">
                <h3 class="font-semibold text-bankos-text text-sm border-b border-bankos-border pb-2">
                    Source Account
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-bankos-text-sec">Name</span>
                        <span class="text-bankos-text font-medium">{{ $transfer->sourceAccount->account_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-bankos-text-sec">Number</span>
                        <span class="font-mono text-bankos-text">{{ $transfer->sourceAccount->account_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-bankos-text-sec">Balance</span>
                        <span class="font-bold text-bankos-text">
                            ₦{{ number_format($transfer->sourceAccount->available_balance, 2) }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('accounts.show', $transfer->sourceAccount) }}"
                   class="text-xs text-bankos-primary hover:underline">
                    View account &rarr;
                </a>
            </div>
            @endif

        </div>
    </div>

    {{-- Back Link --}}
    <div class="mt-8 pt-6 border-t border-bankos-border">
        <a href="{{ route('nip.index') }}"
           class="inline-flex items-center gap-2 text-sm text-bankos-text-sec hover:text-bankos-primary transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to NIP Transfers
        </a>
    </div>
</x-app-layout>
