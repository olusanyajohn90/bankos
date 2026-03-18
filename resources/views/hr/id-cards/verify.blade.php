<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Card Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">

<div class="max-w-sm w-full">

    {{-- Logo / header --}}
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-600 text-white text-2xl font-bold mb-3">B</div>
        <h1 class="text-lg font-bold text-gray-800">ID Card Verification</h1>
        <p class="text-xs text-gray-500">Powered by BankOS</p>
    </div>

    @if($status === 'not_found')
        <div class="bg-white rounded-2xl shadow-md p-6 text-center">
            <div class="text-4xl mb-3">❌</div>
            <h2 class="text-lg font-bold text-red-700 mb-1">Card Not Found</h2>
            <p class="text-sm text-gray-500">This card number does not exist in our records.</p>
        </div>

    @elseif($status === 'valid')
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="bg-green-500 px-6 py-4 text-center">
                <div class="text-3xl mb-1">✅</div>
                <h2 class="text-lg font-bold text-white">Valid & Active</h2>
                <p class="text-green-100 text-xs mt-0.5">Verified {{ now()->format('d M Y, H:i') }}</p>
            </div>
            <div class="p-6 space-y-3">
                <div class="text-center mb-4">
                    <p class="text-xl font-bold text-gray-900">{{ $card->staffProfile?->user?->name }}</p>
                    <p class="text-sm text-blue-600 font-medium">{{ $card->staffProfile?->job_title ?? 'Staff' }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-400">Staff ID</p>
                        <p class="font-semibold text-gray-800">{{ $card->staffProfile?->staff_code ?? '—' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-400">Branch</p>
                        <p class="font-semibold text-gray-800">{{ $card->staffProfile?->branch?->name ?? '—' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-400">Card No.</p>
                        <p class="font-mono font-semibold text-gray-800">{{ $card->card_number }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-400">Expires</p>
                        <p class="font-semibold text-gray-800">{{ $card->expiry_date->format('M Y') }}</p>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400">Department</p>
                    <p class="font-semibold text-gray-800">{{ $card->staffProfile?->orgDepartment?->name ?? $card->staffProfile?->department ?? '—' }}</p>
                </div>
            </div>
        </div>

    @elseif($status === 'expired')
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="bg-amber-500 px-6 py-4 text-center">
                <div class="text-3xl mb-1">⚠️</div>
                <h2 class="text-lg font-bold text-white">Card Expired</h2>
                <p class="text-amber-100 text-xs mt-0.5">This card is no longer valid</p>
            </div>
            <div class="p-6">
                <p class="text-center font-semibold text-gray-800 mb-3">{{ $card->staffProfile?->user?->name }}</p>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-400">Card No.</p>
                        <p class="font-mono font-semibold">{{ $card->card_number }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-400">Expired</p>
                        <p class="font-semibold text-red-600">{{ $card->expiry_date->format('d M Y') }}</p>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-4 text-center">Please request a card renewal from HR.</p>
            </div>
        </div>

    @elseif($status === 'lost')
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="bg-red-600 px-6 py-4 text-center">
                <div class="text-3xl mb-1">🚨</div>
                <h2 class="text-lg font-bold text-white">Card Reported Lost</h2>
                <p class="text-red-200 text-xs mt-0.5">This card has been deactivated</p>
            </div>
            <div class="p-6 text-center">
                <p class="font-semibold text-gray-800 mb-2">{{ $card->staffProfile?->user?->name }}</p>
                <p class="font-mono text-sm text-gray-600 mb-3">{{ $card->card_number }}</p>
                @if($card->loss_report_date)
                    <p class="text-xs text-gray-500">Reported lost: {{ $card->loss_report_date->format('d M Y') }}</p>
                @endif
                <div class="mt-4 p-3 bg-red-50 rounded-lg">
                    <p class="text-xs text-red-700 font-medium">⚠️ Do not accept this card. Report any suspicious activity to security.</p>
                </div>
            </div>
        </div>

    @elseif($status === 'replaced')
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="bg-gray-500 px-6 py-4 text-center">
                <div class="text-3xl mb-1">🔄</div>
                <h2 class="text-lg font-bold text-white">Card Superseded</h2>
                <p class="text-gray-300 text-xs mt-0.5">A new card has been issued</p>
            </div>
            <div class="p-6 text-center">
                <p class="font-semibold text-gray-800 mb-2">{{ $card->staffProfile?->user?->name }}</p>
                <p class="font-mono text-sm text-gray-600 mb-3">{{ $card->card_number }}</p>
                <p class="text-xs text-gray-500">This card is no longer active. The cardholder has received a replacement.</p>
            </div>
        </div>

    @else
        <div class="bg-white rounded-2xl shadow-md p-6 text-center">
            <div class="text-4xl mb-3">⛔</div>
            <h2 class="text-lg font-bold text-gray-700 mb-1">Invalid Card</h2>
            <p class="text-sm text-gray-500">This card is not currently valid.</p>
        </div>
    @endif

    <p class="text-center text-xs text-gray-400 mt-6">Scan performed at {{ now()->format('d M Y H:i:s') }}</p>

</div>
</body>
</html>
