<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\BankList;
use App\Models\ExchangeRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankApiController extends BaseApiController
{
    /**
     * List all active Nigerian banks.
     */
    public function list(Request $request): JsonResponse
    {
        $request->validate([
            'search'        => 'sometimes|string|max:100',
            'microfinance'  => 'sometimes|boolean',
        ]);

        $query = BankList::active()->orderBy('bank_name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('bank_name', 'like', "%{$search}%");
        }

        if ($request->boolean('microfinance')) {
            $query->where('is_microfinance', true);
        }

        $banks = $query->get(['cbn_code', 'bank_name', 'nibss_code', 'is_microfinance']);

        return $this->success($banks, 'Banks retrieved');
    }

    /**
     * Current exchange rates (USD, GBP, EUR vs NGN).
     */
    public function exchangeRates(Request $request): JsonResponse
    {
        $pairs = ['USD/NGN', 'GBP/NGN', 'EUR/NGN'];

        $rates = ExchangeRate::whereIn('pair', $pairs)
            ->orderBy('pair')
            ->orderByDesc('effective_date')
            ->get()
            ->unique('pair')
            ->values();

        if ($rates->isEmpty()) {
            return $this->success([], 'No exchange rates available');
        }

        $formatted = $rates->map(fn ($r) => [
            'pair'           => $r->pair,
            'buy_rate'       => (float) $r->buy_rate,
            'sell_rate'      => (float) $r->sell_rate,
            'mid_rate'       => (float) $r->mid_rate,
            'effective_date' => $r->effective_date?->toDateString(),
        ]);

        return $this->success($formatted, 'Exchange rates retrieved');
    }
}
