<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trust Report | {{ $tenant->name ?? 'BankOS' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-6 py-8 flex items-center gap-6">
            @if($report->logo_path)
            <img src="{{ asset('storage/' . $report->logo_path) }}" alt="Logo" class="h-16 object-contain">
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $tenant->name ?? 'Financial Institution' }}</h1>
                <p class="text-gray-500 text-sm mt-1">Compliance Trust Report</p>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-6 py-10 space-y-8">

        {{-- Intro --}}
        @if($report->intro_text)
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-gray-700 leading-relaxed">{{ $report->intro_text }}</p>
        </div>
        @endif

        {{-- Compliance Badges --}}
        <div class="flex flex-wrap gap-3">
            @foreach($frameworks as $fw)
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border
                {{ $fw->compliance_score >= 80 ? 'bg-green-50 border-green-200 text-green-800' :
                   ($fw->compliance_score >= 60 ? 'bg-amber-50 border-amber-200 text-amber-800' :
                   'bg-red-50 border-red-200 text-red-800') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                <span class="text-sm font-medium">{{ $fw->code }}</span>
                <span class="text-sm font-bold">{{ $fw->compliance_score }}%</span>
            </div>
            @endforeach
        </div>

        {{-- Framework Details --}}
        @foreach($frameworks as $fw)
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $fw->name }}</h2>
                    @if($fw->description)
                    <p class="text-sm text-gray-500 mt-1">{{ $fw->description }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <span class="text-3xl font-bold {{ $fw->compliance_score >= 80 ? 'text-green-600' : ($fw->compliance_score >= 60 ? 'text-amber-500' : 'text-red-600') }}">{{ $fw->compliance_score }}%</span>
                    <p class="text-xs text-gray-400 mt-1">Compliance Score</p>
                </div>
            </div>

            <div class="w-full bg-gray-100 rounded-full h-3 mb-4">
                <div class="h-3 rounded-full transition-all {{ $fw->compliance_score >= 80 ? 'bg-green-500' : ($fw->compliance_score >= 60 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $fw->compliance_score }}%"></div>
            </div>

            <div class="grid grid-cols-4 gap-4 text-center">
                <div>
                    <p class="text-lg font-bold text-green-600">{{ $fw->compliant_controls }}</p>
                    <p class="text-xs text-gray-500">Compliant</p>
                </div>
                <div>
                    <p class="text-lg font-bold text-gray-900">{{ $fw->total_controls }}</p>
                    <p class="text-xs text-gray-500">Total Controls</p>
                </div>
                <div>
                    <p class="text-lg font-bold text-gray-400">{{ $fw->not_assessed_controls }}</p>
                    <p class="text-xs text-gray-500">Not Assessed</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mt-2">Last assessed</p>
                    <p class="text-sm font-medium text-gray-700">{{ $fw->last_assessed_at ? $fw->last_assessed_at->format('M d, Y') : 'N/A' }}</p>
                </div>
            </div>
        </div>
        @endforeach

    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-200 mt-12 py-8">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <p class="text-sm text-gray-400">Powered by <strong class="text-gray-600">BankOS Compliance Automation</strong></p>
            <p class="text-xs text-gray-300 mt-2">Report generated {{ now()->format('F d, Y') }}</p>
        </div>
    </footer>

</body>
</html>
