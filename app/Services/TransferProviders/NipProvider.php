<?php

namespace App\Services\TransferProviders;

use App\Contracts\TransferProviderInterface;
use App\Models\BankList;
use App\Models\NipOutwardTransfer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NipProvider implements TransferProviderInterface
{
    public function getProviderCode(): string
    {
        return 'nip';
    }

    // ── Name Enquiry ────────────────────────────────────────────────────────

    public function nameEnquiry(string $accountNumber, string $bankCode, array $config): array
    {
        $sessionId = $this->generateSessionId($config);

        Log::info('NipProvider name enquiry', [
            'account_number' => $accountNumber,
            'bank_code'      => $bankCode,
            'session_id'     => $sessionId,
        ]);

        // ── SIMULATION (replace with real NIBSS HTTP call) ──────────────
        if (rand(1, 10) === 1) {
            return [
                'success' => false,
                'message' => 'Account not found',
            ];
        }

        $mockNames = [
            'JOHN DOE ENTERPRISES',
            'AMINA IBRAHIM',
            'EMEKA OKAFOR',
            'FATIMA ABUBAKAR',
            'CHIDI OKEKE GLOBAL RESOURCES',
            'NGOZI ADEYEMI',
            'IBRAHIM MUSA',
            'BLESSING NWOSU',
            'SAMUEL OSEI',
            'AISHA BELLO',
        ];

        return [
            'success'        => true,
            'account_name'   => $mockNames[array_rand($mockNames)],
            'account_number' => $accountNumber,
            'bank_code'      => $bankCode,
            'session_id'     => $sessionId,
        ];
    }

    // ── Initiate Transfer ───────────────────────────────────────────────────

    public function initiateTransfer(array $transferData, array $config): array
    {
        $reference = 'NIP-' . strtoupper(Str::random(10));

        Log::info('NipProvider initiating transfer', [
            'reference' => $reference,
            'amount'    => $transferData['amount'] ?? 0,
        ]);

        // ── SIMULATION — 90 % success rate ──────────────────────────────
        $success = rand(1, 10) > 1;

        if ($success) {
            return [
                'success'            => true,
                'message'            => 'Transfer completed successfully via NIBSS NIP.',
                'reference'          => $reference,
                'provider_reference' => $this->generateSessionId($config),
                'response_code'      => '00',
            ];
        }

        return [
            'success'       => false,
            'message'       => 'NIBSS returned a non-zero response code (simulated failure).',
            'reference'     => $reference,
            'response_code' => '96',
        ];
    }

    // ── Query Status ────────────────────────────────────────────────────────

    public function queryStatus(string $reference, array $config): array
    {
        // Look up the NIP outward transfer by session_id
        $transfer = NipOutwardTransfer::where('session_id', $reference)->first();

        if (!$transfer) {
            return [
                'success' => false,
                'status'  => 'unknown',
                'message' => 'Transfer not found for the given reference.',
            ];
        }

        return [
            'success' => true,
            'status'  => $transfer->status,
            'message' => 'Status retrieved successfully.',
        ];
    }

    // ── Supported Banks ─────────────────────────────────────────────────────

    public function getSupportedBanks(array $config): array
    {
        return BankList::where('is_active', true)
            ->orderBy('bank_name')
            ->get(['cbn_code as code', 'bank_name as name'])
            ->toArray();
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Generate a NIBSS NIP session ID (40 chars).
     */
    private function generateSessionId(array $config): string
    {
        $datePart        = now()->format('YmdHis');
        $institutionCode = str_pad($config['institution_code'] ?? '000000000', 9, '0', STR_PAD_LEFT);
        $randomPart      = str_pad((string) mt_rand(0, 99999999999999999), 17, '0', STR_PAD_LEFT);

        return $datePart . $institutionCode . $randomPart;
    }
}
