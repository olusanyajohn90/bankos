@extends('layouts.app')
@section('title', 'ID Card Templates')
@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="templateDesigner()">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('hr.id-cards.index') }}" class="text-xs text-gray-400 hover:text-gray-600">← ID Cards</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">ID Card Templates</h1>
            <p class="text-sm text-gray-500 mt-0.5">Design and manage your bank's staff ID card templates with live preview.</p>
        </div>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>@endif

    {{-- Existing Templates --}}
    @forelse($templates as $tpl)
    <div class="card p-5" x-data="{ editMode: false, uploading: false }">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Live Preview --}}
            <div class="flex flex-col items-center gap-3">
                <p class="text-xs font-semibold text-gray-500 uppercase">Card Preview</p>
                {{-- Mini card preview --}}
                <div class="rounded-xl overflow-hidden shadow-lg"
                     style="width:256px; height:162px; position:relative; background:{{ $tpl->background_color ?? '#f1f5f9' }};">
                    {{-- Header --}}
                    <div style="background: linear-gradient(135deg, {{ $tpl->primary_color ?? '#1e40af' }}, {{ $tpl->secondary_color ?? '#1d4ed8' }}); padding: 8px 12px; display:flex; align-items:center; justify-content:space-between;">
                        <div>
                            @if($tpl->logo_path)
                                <img src="{{ Storage::url($tpl->logo_path) }}" style="height:18px; object-fit:contain;" alt="Logo">
                            @else
                                <span style="color:{{ $tpl->text_color ?? '#ffffff' }}; font-size:9px; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px;">{{ config('app.name', 'BANKOS') }}</span>
                            @endif
                        </div>
                        <span style="background:rgba(255,255,255,0.25); color:{{ $tpl->text_color ?? '#ffffff' }}; font-size:7px; font-weight:bold; padding:2px 6px; border-radius:4px; letter-spacing:1px;">STAFF ID</span>
                    </div>
                    {{-- Body --}}
                    <div style="display:flex; padding:8px 10px; gap:8px; height:calc(162px - 42px - 22px);">
                        @if($tpl->show_photo)
                        <div style="width:44px; flex-shrink:0;">
                            <div style="width:44px; height:52px; border-radius:4px; background:#e2e8f0; border:1.5px solid {{ $tpl->primary_color ?? '#1e40af' }}; display:flex; align-items:center; justify-content:center; font-size:20px; color:#94a3b8;">👤</div>
                        </div>
                        @endif
                        <div style="flex:1;">
                            <div style="font-size:9px; font-weight:bold; color:#1e293b; margin-bottom:2px;">John A. Doe</div>
                            <div style="font-size:7px; font-weight:bold; color:{{ $tpl->primary_color ?? '#1e40af' }}; text-transform:uppercase; margin-bottom:4px;">Senior Manager</div>
                            <div style="font-size:6px; color:#64748b;">Staff ID: STF-00001</div>
                            @if($tpl->show_department)<div style="font-size:6px; color:#64748b;">Dept: Operations</div>@endif
                            @if($tpl->show_grade)<div style="font-size:6px; color:#64748b;">Grade: GL7</div>@endif
                        </div>
                        @if($tpl->show_qr)
                        <div style="width:36px; flex-shrink:0; text-align:center;">
                            <div style="width:36px; height:36px; background:#e2e8f0; border-radius:2px; display:flex; align-items:center; justify-content:center; font-size:16px;">▦</div>
                            <div style="font-size:4px; color:#64748b; margin-top:2px;">Scan</div>
                        </div>
                        @endif
                    </div>
                    {{-- Footer strip --}}
                    <div style="background: linear-gradient(135deg, {{ $tpl->primary_color ?? '#1e40af' }}, {{ $tpl->secondary_color ?? '#1d4ed8' }}); position:absolute; bottom:0; left:0; right:0; height:22px; display:flex; align-items:center; justify-content:space-between; padding:0 12px;">
                        <span style="font-family:monospace; font-size:6px; font-weight:bold; color:{{ $tpl->text_color ?? '#ffffff' }}; letter-spacing:1px;">ID-2026-00001</span>
                        <span style="font-size:5px; color:{{ $tpl->text_color ?? '#ffffff' }}; opacity:0.85;">Exp: {{ now()->addYears($tpl->expiry_years ?? 2)->format('M Y') }}</span>
                    </div>
                </div>

                @if($tpl->is_default)
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">Default Template</span>
                @else
                    <form action="{{ route('hr.id-cards.templates.set-default', $tpl) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Set as Default</button>
                    </form>
                @endif
            </div>

            {{-- Template Info / Edit --}}
            <div class="lg:col-span-2">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $tpl->name }}</h3>
                        <p class="text-xs text-gray-500">{{ $tpl->expiry_years }} year expiry · Used by {{ $tpl->idCards()->count() }} cards</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="editMode = !editMode" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                        <form action="{{ route('hr.id-cards.templates.destroy', $tpl) }}" method="POST" onsubmit="return confirm('Delete template?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-600">Delete</button>
                        </form>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-2 mb-3">
                    <div class="text-center">
                        <div class="w-8 h-8 rounded-full mx-auto border-2 border-gray-200" style="background:{{ $tpl->primary_color }}"></div>
                        <p class="text-xs text-gray-400 mt-1">Primary</p>
                    </div>
                    <div class="text-center">
                        <div class="w-8 h-8 rounded-full mx-auto border-2 border-gray-200" style="background:{{ $tpl->secondary_color }}"></div>
                        <p class="text-xs text-gray-400 mt-1">Secondary</p>
                    </div>
                    <div class="text-center">
                        <div class="w-8 h-8 rounded-full mx-auto border-2 border-gray-200" style="background:{{ $tpl->text_color ?? '#ffffff' }}"></div>
                        <p class="text-xs text-gray-400 mt-1">Text</p>
                    </div>
                    <div class="text-center">
                        <div class="w-8 h-8 rounded-full mx-auto border-2 border-gray-200" style="background:{{ $tpl->background_color ?? '#f1f5f9' }}"></div>
                        <p class="text-xs text-gray-400 mt-1">Background</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 text-xs mb-3">
                    @foreach(['show_qr' => 'QR Code','show_photo' => 'Photo','show_department' => 'Department','show_grade' => 'Grade','show_blood_group' => 'Blood Group','show_emergency_contact' => 'Emergency Contact'] as $field => $label)
                        <span class="px-2 py-0.5 rounded {{ $tpl->$field ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $tpl->$field ? '✓' : '✗' }} {{ $label }}
                        </span>
                    @endforeach
                </div>

                {{-- Logo Upload --}}
                <form action="{{ route('hr.id-cards.templates.upload-logo', $tpl) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2 mb-3">
                    @csrf
                    <label class="text-xs text-gray-500">Logo:</label>
                    <input type="file" name="logo" accept="image/*" class="text-xs text-gray-600 flex-1">
                    <button type="submit" class="btn text-xs bg-gray-700 hover:bg-gray-800 text-white px-3 py-1 rounded-lg">Upload</button>
                </form>
                @if($tpl->logo_path)
                    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                        <img src="{{ Storage::url($tpl->logo_path) }}" class="h-6 object-contain border rounded">
                        <span>Logo uploaded</span>
                    </div>
                @endif

                {{-- Edit Form --}}
                <div x-show="editMode" x-transition>
                    <form action="{{ route('hr.id-cards.templates.update', $tpl) }}" method="POST" class="space-y-3 border-t border-gray-100 pt-3">
                        @csrf @method('PATCH')
                        <div class="grid grid-cols-2 gap-3">
                            <div class="col-span-2"><label class="block text-xs text-gray-500 mb-1">Template Name</label>
                                <input type="text" name="name" value="{{ $tpl->name }}" required class="form-input w-full text-sm"></div>
                            <div><label class="block text-xs text-gray-500 mb-1">Primary Color</label>
                                <div class="flex gap-2">
                                    <input type="color" name="primary_color" value="{{ $tpl->primary_color ?? '#1e40af' }}" class="h-9 w-14 rounded border border-gray-300 cursor-pointer">
                                    <input type="text" value="{{ $tpl->primary_color ?? '#1e40af' }}" class="form-input flex-1 text-sm font-mono" oninput="this.previousElementSibling.value=this.value">
                                </div></div>
                            <div><label class="block text-xs text-gray-500 mb-1">Secondary Color</label>
                                <div class="flex gap-2">
                                    <input type="color" name="secondary_color" value="{{ $tpl->secondary_color ?? '#1d4ed8' }}" class="h-9 w-14 rounded border border-gray-300 cursor-pointer">
                                    <input type="text" value="{{ $tpl->secondary_color ?? '#1d4ed8' }}" class="form-input flex-1 text-sm font-mono" oninput="this.previousElementSibling.value=this.value">
                                </div></div>
                            <div><label class="block text-xs text-gray-500 mb-1">Text/Header Color</label>
                                <div class="flex gap-2">
                                    <input type="color" name="text_color" value="{{ $tpl->text_color ?? '#ffffff' }}" class="h-9 w-14 rounded border border-gray-300 cursor-pointer">
                                    <input type="text" value="{{ $tpl->text_color ?? '#ffffff' }}" class="form-input flex-1 text-sm font-mono" oninput="this.previousElementSibling.value=this.value">
                                </div></div>
                            <div><label class="block text-xs text-gray-500 mb-1">Card Background</label>
                                <div class="flex gap-2">
                                    <input type="color" name="background_color" value="{{ $tpl->background_color ?? '#f1f5f9' }}" class="h-9 w-14 rounded border border-gray-300 cursor-pointer">
                                    <input type="text" value="{{ $tpl->background_color ?? '#f1f5f9' }}" class="form-input flex-1 text-sm font-mono" oninput="this.previousElementSibling.value=this.value">
                                </div></div>
                            <div><label class="block text-xs text-gray-500 mb-1">Expiry (years)</label>
                                <input type="number" name="expiry_years" value="{{ $tpl->expiry_years ?? 2 }}" min="1" max="10" class="form-input w-full text-sm"></div>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['show_qr' => 'QR Code','show_photo' => 'Photo','show_department' => 'Department','show_grade' => 'Grade','show_blood_group' => 'Blood Group','show_emergency_contact' => 'Emergency Contact'] as $field => $label)
                            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                <input type="checkbox" name="{{ $field }}" value="1" {{ $tpl->$field ? 'checked' : '' }} class="rounded border-gray-300"> {{ $label }}
                            </label>
                            @endforeach
                            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                <input type="checkbox" name="is_default" value="1" {{ $tpl->is_default ? 'checked' : '' }} class="rounded border-gray-300"> Set as default
                            </label>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Save Changes</button>
                            <button type="button" @click="editMode=false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
        <div class="card p-8 text-center text-gray-400">No templates yet. Create your first ID card template below.</div>
    @endforelse

    {{-- Create New Template --}}
    <div class="card p-6" x-data="{ open: {{ $templates->isEmpty() ? 'true' : 'false' }} }">
        <button @click="open = !open" class="flex items-center justify-between w-full">
            <h2 class="text-sm font-semibold text-gray-700">+ Create New Template</h2>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :class="open ? 'rotate-180' : ''" class="transition-transform text-gray-400"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div x-show="open" x-transition class="mt-4">
            <form action="{{ route('hr.id-cards.templates.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-3"><label class="block text-xs text-gray-500 mb-1">Template Name *</label>
                        <input type="text" name="name" required class="form-input w-full text-sm" placeholder="e.g. Standard Blue, Executive Card"></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Primary Color *</label>
                        <div class="flex gap-2">
                            <input type="color" name="primary_color" value="#1e40af" id="newPrimary" class="h-9 w-14 rounded border border-gray-300 cursor-pointer">
                            <input type="text" value="#1e40af" class="form-input flex-1 text-sm font-mono" oninput="document.getElementById('newPrimary').value=this.value">
                        </div></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Secondary Color *</label>
                        <div class="flex gap-2">
                            <input type="color" name="secondary_color" value="#1d4ed8" id="newSecondary" class="h-9 w-14 rounded border border-gray-300 cursor-pointer">
                            <input type="text" value="#1d4ed8" class="form-input flex-1 text-sm font-mono" oninput="document.getElementById('newSecondary').value=this.value">
                        </div></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Text Color *</label>
                        <div class="flex gap-2">
                            <input type="color" name="text_color" value="#ffffff" id="newText" class="h-9 w-14 rounded border border-gray-300 cursor-pointer">
                            <input type="text" value="#ffffff" class="form-input flex-1 text-sm font-mono" oninput="document.getElementById('newText').value=this.value">
                        </div></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Background Color *</label>
                        <div class="flex gap-2">
                            <input type="color" name="background_color" value="#f1f5f9" id="newBg" class="h-9 w-14 rounded border border-gray-300 cursor-pointer">
                            <input type="text" value="#f1f5f9" class="form-input flex-1 text-sm font-mono" oninput="document.getElementById('newBg').value=this.value">
                        </div></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Card Expiry (years) *</label>
                        <input type="number" name="expiry_years" value="2" min="1" max="10" class="form-input w-full text-sm"></div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                    @foreach(['show_qr' => 'QR Code','show_photo' => 'Photo','show_department' => 'Department','show_grade' => 'Grade','show_blood_group' => 'Blood Group','show_emergency_contact' => 'Emergency Contact'] as $field => $label)
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="{{ $field }}" value="1" {{ in_array($field, ['show_qr','show_photo','show_department','show_grade']) ? 'checked' : '' }} class="rounded border-gray-300"> {{ $label }}
                    </label>
                    @endforeach
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="is_default" value="1" {{ $templates->isEmpty() ? 'checked' : '' }} class="rounded border-gray-300"> Set as default
                    </label>
                </div>
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create Template</button>
            </form>
        </div>
    </div>

</div>

<script>
function templateDesigner() {
    return {}
}
</script>
@endsection
