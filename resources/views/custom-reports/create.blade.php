<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('custom-reports.index') }}" class="text-bankos-text-sec hover:text-bankos-text">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            </a>
            <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Create Custom Report</h2>
        </div>
    </x-slot>

    <div x-data="reportBuilder()" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Left: Config --}}
        <div class="lg:col-span-2 space-y-5">
            <div class="card">
                <h3 class="font-bold mb-4">Report Settings</h3>
                <form method="POST" action="{{ route('custom-reports.store') }}" id="reportForm">
                    @csrf
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Report Name *</label>
                            <input type="text" name="name" class="input w-full" placeholder="Monthly Loan Summary" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Data Source *</label>
                            <select name="data_source" x-model="source" @change="updateColumns()" class="input w-full" required>
                                <option value="">Select source...</option>
                                @foreach($dataSources as $key => $ds)
                                <option value="{{ $key }}">{{ $ds['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Description</label>
                        <input type="text" name="description" class="input w-full" placeholder="Optional description...">
                    </div>

                    {{-- Column selection --}}
                    <div x-show="columns.length > 0" class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 mb-2">Select Columns</label>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="col in columns" :key="col.key">
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="checkbox" :name="'selected_columns[]'" :value="col.key" x-model="selectedColumns" class="rounded">
                                    <span x-text="col.label"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <div x-show="source !== ''" class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 mb-2">Filters</label>
                        <div class="space-y-2">
                            <template x-for="(filter, idx) in filters" :key="idx">
                                <div class="flex gap-2 items-center">
                                    <select :name="'filters[' + idx + '][column]'" x-model="filter.column" class="input flex-1">
                                        <template x-for="col in columns" :key="col.key">
                                            <option :value="col.key" x-text="col.label"></option>
                                        </template>
                                    </select>
                                    <select :name="'filters[' + idx + '][operator]'" x-model="filter.operator" class="input w-32">
                                        <option value="equals">equals</option>
                                        <option value="not_equals">not equals</option>
                                        <option value="contains">contains</option>
                                        <option value="greater_than">&gt;</option>
                                        <option value="less_than">&lt;</option>
                                        <option value="is_null">is empty</option>
                                        <option value="is_not_null">is not empty</option>
                                        <option value="in_last_days">in last N days</option>
                                    </select>
                                    <input type="text" :name="'filters[' + idx + '][value]'" x-model="filter.value" class="input flex-1" placeholder="Value">
                                    <button type="button" @click="filters.splice(idx,1)" class="text-red-500 hover:text-red-700">✕</button>
                                </div>
                            </template>
                            <button type="button" @click="filters.push({column:'',operator:'=',value:''})" class="text-sm text-blue-600 hover:underline">+ Add Filter</button>
                        </div>
                    </div>

                    {{-- Schedule --}}
                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-gray-500 mb-2">Schedule (optional)</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <select name="schedule_frequency" class="input w-full">
                                    <option value="">No schedule</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                            <div>
                                <input type="email" name="schedule_email" class="input w-full" placeholder="Send to email...">
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-primary">Save Report</button>
                        <button type="button" @click="preview()" class="btn bg-gray-100 hover:bg-gray-200 hover:bg-gray-300 text-gray-800">Preview (first 10 rows)</button>
                    </div>
                </form>
            </div>

            {{-- Preview area --}}
            <div x-show="previewLoading || previewError || previewData.length > 0" class="card p-0 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-bankos-border flex items-center justify-between">
                    <span class="font-bold text-sm">Preview</span>
                    <span x-show="previewData.length > 0" class="text-xs text-gray-400" x-text="previewData.length + ' rows'"></span>
                </div>
                {{-- Loading --}}
                <div x-show="previewLoading" class="px-5 py-8 text-center text-gray-400 text-sm">
                    <svg class="animate-spin h-5 w-5 mx-auto mb-2 text-bankos-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Loading preview...
                </div>
                {{-- Error --}}
                <div x-show="!previewLoading && previewError" class="px-5 py-6 text-center text-amber-600 text-sm" x-text="previewError"></div>
                {{-- Table --}}
                <div x-show="!previewLoading && previewData.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <template x-for="col in previewColumns" :key="col">
                                    <th class="px-4 py-3 text-left" x-text="col.replace(/_/g,' ')"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(row, i) in previewData" :key="i">
                                <tr class="hover:bg-gray-50">
                                    <template x-for="col in previewColumns" :key="col">
                                        <td class="px-4 py-2 text-gray-700 text-xs" x-text="row[col] !== null && row[col] !== undefined ? row[col] : '—'"></td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right: Data sources info --}}
        <div class="space-y-4">
            <div class="card">
                <h3 class="font-bold mb-3 text-sm">Available Data Sources</h3>
                <div class="space-y-3">
                    @foreach($dataSources as $key => $ds)
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="font-semibold text-sm">{{ $ds['label'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ count($ds['columns']) }} columns available</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
    function reportBuilder() {
        // Columns in DATA_SOURCES are plain strings — convert to {key, label} objects
        const rawSources = @json($dataSources);
        const dataSources = {};
        for (const [k, ds] of Object.entries(rawSources)) {
            dataSources[k] = {
                label: ds.label,
                columns: ds.columns.map(c => ({
                    key: c,
                    label: c.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
                }))
            };
        }

        return {
            source: '',
            columns: [],
            selectedColumns: [],
            filters: [],
            previewData: [],
            previewColumns: [],
            previewLoading: false,
            previewError: '',
            dataSources,

            updateColumns() {
                if (this.source && this.dataSources[this.source]) {
                    this.columns = this.dataSources[this.source].columns;
                    this.selectedColumns = this.columns.map(c => c.key);
                } else {
                    this.columns = [];
                    this.selectedColumns = [];
                }
                this.previewData = [];
                this.previewColumns = [];
            },

            async preview() {
                if (!this.source) { this.previewError = 'Please select a data source first.'; return; }
                this.previewLoading = true;
                this.previewError = '';
                this.previewData = [];

                const payload = {
                    _token: '{{ csrf_token() }}',
                    data_source: this.source,
                    selected_columns: this.selectedColumns,
                    filters: this.filters,
                };

                try {
                    const resp = await fetch('{{ route('custom-reports.preview') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await resp.json();
                    if (!resp.ok) { this.previewError = data.message || 'Preview failed.'; return; }
                    this.previewColumns = data.columns || this.selectedColumns;
                    this.previewData = data.rows || [];
                    if (this.previewData.length === 0) this.previewError = 'No data found for selected source and filters.';
                } catch (e) {
                    this.previewError = 'Network error. Please try again.';
                } finally {
                    this.previewLoading = false;
                }
            }
        };
    }
    </script>
</x-app-layout>
