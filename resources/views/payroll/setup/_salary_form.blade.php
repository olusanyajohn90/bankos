{{-- Reusable staff salary config form (used for both create and edit) --}}
@php $isEdit = !is_null($config ?? null); @endphp

<form action="{{ $action }}" method="POST" class="space-y-4 p-4">
    @csrf
    @if($isEdit)
    <input type="hidden" name="staff_profile_id" value="{{ $config->staff_profile_id }}">
    @endif

    @if(!$isEdit)
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Staff Member <span class="text-red-500">*</span></label>
        <select name="staff_profile_id" required
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
            <option value="">Select staff...</option>
            @foreach($staff as $s)
            <option value="{{ $s->id }}">{{ $s->user?->name }} — {{ $s->job_title }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pay Grade</label>
        <select name="pay_grade_id"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
            <option value="">None / Custom</option>
            @foreach($payGrades->groupBy('level') as $lvl => $grades)
            <optgroup label="Level {{ str_pad($lvl, 2, '0', STR_PAD_LEFT) }}">
                @foreach($grades->sortBy('grade') as $g)
                <option value="{{ $g->id }}"
                    {{ ($isEdit && $config->pay_grade_id === $g->id) ? 'selected' : '' }}
                    data-basic="{{ $g->basic_min }}">
                    L{{ str_pad($lvl, 2, '0', STR_PAD_LEFT) }}G{{ $g->grade }} — {{ $g->name }}
                    (₦{{ number_format($g->basic_min) }} – ₦{{ number_format($g->basic_max) }})
                </option>
                @endforeach
            </optgroup>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Basic Salary (₦) <span class="text-red-500">*</span></label>
            <input type="number" name="basic_salary" required min="0" step="100"
                value="{{ $isEdit ? $config->basic_salary : '' }}"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Housing Allowance (₦) <span class="text-red-500">*</span></label>
            <input type="number" name="housing_allowance" required min="0" step="100"
                value="{{ $isEdit ? $config->housing_allowance : '' }}"
                placeholder="Typically 30% of basic"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Transport Allowance (₦) <span class="text-red-500">*</span></label>
            <input type="number" name="transport_allowance" required min="0" step="100"
                value="{{ $isEdit ? $config->transport_allowance : '' }}"
                placeholder="Typically 15% of basic"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meal Allowance (₦) <span class="text-red-500">*</span></label>
            <input type="number" name="meal_allowance" required min="0" step="100"
                value="{{ $isEdit ? $config->meal_allowance : '' }}"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
        </div>
    </div>

    <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
        <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">Statutory Details</h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pension Fund Administrator</label>
                <input type="text" name="pension_fund_administrator" maxlength="100"
                    value="{{ $isEdit ? $config->pension_fund_administrator : '' }}"
                    placeholder="e.g. ARM Pension, Stanbic IBTC"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pension Account No.</label>
                <input type="text" name="pension_account_number" maxlength="50"
                    value="{{ $isEdit ? $config->pension_account_number : '' }}"
                    placeholder="RSA Pin"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 font-mono">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4 mt-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tax ID (TIN)</label>
                <input type="text" name="tax_id" maxlength="50"
                    value="{{ $isEdit ? $config->tax_id : '' }}"
                    placeholder="FIRS TIN"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 font-mono">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NHF Number</label>
                <input type="text" name="nhf_number" maxlength="50"
                    value="{{ $isEdit ? $config->nhf_number : '' }}"
                    placeholder="Federal Mortgage Bank No."
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 font-mono">
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Effective Date <span class="text-red-500">*</span></label>
        <input type="date" name="effective_date" required
            value="{{ $isEdit ? $config->effective_date : now()->format('Y-m-d') }}"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2">
    </div>

    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
        <button type="button" x-data @click="$dispatch('close-modal')"
            class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            Cancel
        </button>
        <button type="submit"
            class="px-4 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
            {{ $submitLabel ?? 'Save' }}
        </button>
    </div>
</form>
