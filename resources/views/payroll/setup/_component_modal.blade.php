{{-- Reusable pay component form (used for both create and edit) --}}
@php $isEdit = !is_null($comp ?? null); @endphp

<x-modal name="{{ $isEdit ? 'edit-comp-'.$comp->id : 'add-component' }}" title="{{ $isEdit ? 'Edit: '.$comp->name : 'Add Pay Component' }}">
    <form action="{{ $action }}" method="POST" class="space-y-4 p-4">
        @csrf
        @if(isset($method) && $method === 'PATCH') @method('PATCH') @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required maxlength="100"
                    value="{{ $isEdit ? $comp->name : '' }}" placeholder="e.g. Housing Allowance"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" required maxlength="20"
                    value="{{ $isEdit ? $comp->code : '' }}" placeholder="e.g. HOUSING"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 uppercase">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type <span class="text-red-500">*</span></label>
                <select name="type" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                    <option value="earning"  {{ ($isEdit && $comp->type === 'earning')    ? 'selected' : '' }}>Earning</option>
                    <option value="deduction"{{ ($isEdit && $comp->type === 'deduction')  ? 'selected' : '' }}>Deduction</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Computation <span class="text-red-500">*</span></label>
                <select name="computation_type" required
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                    <option value="fixed"               {{ ($isEdit && $comp->computation_type === 'fixed')               ? 'selected' : '' }}>Fixed Amount</option>
                    <option value="percentage_of_basic" {{ ($isEdit && $comp->computation_type === 'percentage_of_basic') ? 'selected' : '' }}>% of Basic Salary</option>
                    <option value="percentage_of_gross" {{ ($isEdit && $comp->computation_type === 'percentage_of_gross') ? 'selected' : '' }}>% of Gross Salary</option>
                    <option value="formula"             {{ ($isEdit && $comp->computation_type === 'formula')             ? 'selected' : '' }}>Formula</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Value / Rate</label>
                <input type="number" name="value" step="0.01" min="0"
                    value="{{ $isEdit ? $comp->value : '' }}"
                    placeholder="Amount or percentage"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                <p class="text-xs text-gray-400 mt-1">Enter ₦ amount (fixed) or % rate</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Formula Key</label>
                <input type="text" name="formula_key" maxlength="50"
                    value="{{ $isEdit ? $comp->formula_key : '' }}"
                    placeholder="e.g. paye, pension_emp"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
                <p class="text-xs text-gray-400 mt-1">For formula type only</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-6">
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_statutory" value="0">
                <input type="checkbox" name="is_statutory" value="1"
                    id="{{ $isEdit ? 'stat_'.$comp->id : 'stat_new' }}"
                    {{ ($isEdit && $comp->is_statutory) ? 'checked' : '' }}
                    class="w-4 h-4 text-amber-600 rounded border-gray-300">
                <label for="{{ $isEdit ? 'stat_'.$comp->id : 'stat_new' }}" class="text-sm text-gray-700 dark:text-gray-300">
                    Statutory (legally mandated)
                </label>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_taxable" value="0">
                <input type="checkbox" name="is_taxable" value="1"
                    id="{{ $isEdit ? 'tax_'.$comp->id : 'tax_new' }}"
                    {{ ($isEdit && $comp->is_taxable) || !$isEdit ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 rounded border-gray-300">
                <label for="{{ $isEdit ? 'tax_'.$comp->id : 'tax_new' }}" class="text-sm text-gray-700 dark:text-gray-300">
                    Taxable (included in PAYE computation)
                </label>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                    id="{{ $isEdit ? 'act_'.$comp->id : 'act_new' }}"
                    {{ ($isEdit && $comp->is_active) || !$isEdit ? 'checked' : '' }}
                    class="w-4 h-4 text-green-600 rounded border-gray-300">
                <label for="{{ $isEdit ? 'act_'.$comp->id : 'act_new' }}" class="text-sm text-gray-700 dark:text-gray-300">
                    Active
                </label>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
            <button type="button" x-data @click="$dispatch('close-modal')"
                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
            </button>
            <button type="submit"
                class="px-4 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                {{ $isEdit ? 'Save Changes' : 'Create Component' }}
            </button>
        </div>
    </form>
</x-modal>
