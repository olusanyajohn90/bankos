<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Branding — bankOS Setup</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4 py-12">

<div class="w-full max-w-xl" x-data="{
    primary: '{{ old('primary_color', $data['step2']['primary_color'] ?? '#2563eb') }}',
    secondary: '{{ old('secondary_color', $data['step2']['secondary_color'] ?? '#0c2461') }}',
    previewName: '{{ $data['step1']['name'] ?? 'Your Bank Name' }}'
}">
    <!-- Logo -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-8 h-8 rounded-lg bg-blue-600 grid place-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        </div>
        <span class="text-lg font-bold text-gray-900">bank<span class="text-blue-600">OS</span></span>
    </div>

    @include('setup._stepper', ['current' => 2])

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 mt-6">
        <h2 class="text-xl font-bold text-gray-900 mb-1">Brand Your Platform</h2>
        <p class="text-sm text-gray-500 mb-6">Upload your logo and choose your institution's colors.</p>

        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4">
            <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('setup.step2.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- Live Preview -->
            <div class="rounded-xl border border-gray-200 overflow-hidden mb-6">
                <div class="h-12 flex items-center px-4 gap-3" :style="'background-color: ' + secondary">
                    <div class="w-6 h-6 rounded bg-white/20 grid place-items-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    </div>
                    <span class="text-white font-bold text-sm" x-text="previewName"></span>
                </div>
                <div class="bg-gray-50 p-4 flex gap-3">
                    <div class="h-8 rounded-lg px-4 flex items-center text-white text-xs font-semibold" :style="'background-color: ' + primary">Primary Button</div>
                    <div class="h-8 rounded-lg px-4 flex items-center text-xs font-semibold border-2" :style="'color: ' + primary + '; border-color: ' + primary">Secondary</div>
                </div>
            </div>

            <div class="space-y-5">
                <!-- Logo Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Institution Logo</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition-colors">
                        <input type="file" name="logo" id="logo" accept="image/png,image/jpg,image/jpeg,image/svg+xml" class="hidden">
                        <label for="logo" class="cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                            <p class="text-sm text-gray-500">Click to upload PNG, JPG or SVG</p>
                            <p class="text-xs text-gray-400 mt-1">Max 2MB — recommended 200×60px</p>
                        </label>
                    </div>
                    @if(!empty($data['step2']['logo_path']))
                        <p class="text-xs text-green-600 mt-1">Logo already uploaded.</p>
                    @endif
                </div>

                <!-- Colors -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Primary Color</label>
                        <div class="flex gap-2 items-center">
                            <input type="color" name="primary_color" x-model="primary"
                                   class="h-10 w-14 rounded-lg border border-gray-300 cursor-pointer p-1">
                            <input type="text" x-model="primary" placeholder="#2563eb"
                                   class="flex-1 border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Secondary Color</label>
                        <div class="flex gap-2 items-center">
                            <input type="color" name="secondary_color" x-model="secondary"
                                   class="h-10 w-14 rounded-lg border border-gray-300 cursor-pointer p-1">
                            <input type="text" x-model="secondary" placeholder="#0c2461"
                                   class="flex-1 border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Preset palettes -->
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-2">Quick Palettes</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach([
                            ['#2563eb','#0c2461','Blue (Default)'],
                            ['#16a34a','#052e16','Green'],
                            ['#9333ea','#3b0764','Purple'],
                            ['#dc2626','#450a0a','Red'],
                            ['#d97706','#451a03','Amber'],
                            ['#0891b2','#082f49','Cyan'],
                        ] as [$p, $s, $label])
                        <button type="button"
                                @click="primary = '{{ $p }}'; secondary = '{{ $s }}'"
                                class="flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full border border-gray-200 hover:border-gray-400 transition-colors">
                            <span class="w-3 h-3 rounded-full" style="background: {{ $p }}"></span>
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-100">
                <a href="{{ route('setup.step1') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    Back
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-8 rounded-xl transition-colors shadow-sm">
                    Continue
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline ml-1"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
