<?php

namespace App\Services\Nip;

use App\Models\BankList;
use App\Models\NipOutwardTransfer;
use App\Models\Tenant;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NipService
{
    // ── Session ID ─────────────────────────────────────────────────────────────

    /**
     * Generate a NIBSS NIP session ID.
     *
     * Format: YYYYMMDDHHMMSS (14) + institutionCode padded to 9 chars + randomNumeric (12) = 35 … wait,
     * spec says total 40 chars: date-time (14) + institution (9) + random numeric (12) = 35 chars.
     * We left-pad institution code to 9 chars and generate 17 random digits to reach exactly 40.
     *   14 + 9 + 17 = 40
     */
    public function generateSessionId(Tenant $tenant): string
    {
        $datePart        = now()->format('YmdHis');                           // 14 chars
        $institutionCode = str_pad($tenant->nibss_institution_code ?? '000000000', 9, '0', STR_PAD_LEFT);  // 9 chars
        $randomPart      = str_pad((string) mt_rand(0, 99999999999999999), 17, '0', STR_PAD_LEFT);         // 17 chars

        return $datePart . $institutionCode . $randomPart; // 40 chars total
    }

    // ── Name Enquiry ───────────────────────────────────────────────────────────

    /**
     * Perform a NIP name enquiry.
     *
     * In production: replace the simulation block with an HTTP call to NIBSS.
     *
     * @return array{success: bool, account_name?: string, account_number?: string, bank_code?: string, session_id?: string, message?: string}
     */
    public function nameEnquiry(string $accountNumber, string $bankCode, Tenant $tenant): array
    {
        $sessionId = $this->generateSessionId($tenant);

        Log::info('NIP name enquiry attempt', [
            'account_number' => $accountNumber,
            'bank_code'      => $bankCode,
            'session_id'     => $sessionId,
            'tenant_id'      => $tenant->id,
        ]);

        // ── SIMULATION ─────────────────────────────────────────────────────────
        // Simulate a ~90 % success rate. Replace this block with the real
        // NIBSS HTTP request when API credentials are available.
        if (rand(1, 10) === 1) {
            return [
                'success' => false,
                'message' => 'Account not found',
            ];
        }

        // Generate a plausible mock account name
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

    // ── Initiate Transfer ──────────────────────────────────────────────────────

    /**
     * Initiate a NIP outward transfer.
     *
     * All balance mutations and record updates are wrapped in a DB transaction.
     *
     * @return array{success: bool, message: string, session_id: string}
     */
    public function initiateTransfer(NipOutwardTransfer $transfer, Tenant $tenant): array
    {
        return DB::transaction(function () use ($transfer, $tenant) {

            // 1. Validate source account
            $account = $transfer->sourceAccount()->lockForUpdate()->first();

            if (! $account) {
                return ['success' => false, 'message' => 'Source account not found.', 'session_id' => $transfer->session_id];
            }

            if ($account->pnd_active) {
                return ['success' => false, 'message' => 'Source account has a Post-No-Debit restriction.', 'session_id' => $transfer->session_id];
            }

            if ($account->status !== 'active') {
                return ['success' => false, 'message' => 'Source account is not active.', 'session_id' => $transfer->session_id];
            }

            if ((float) $account->available_balance < (float) $transfer->amount) {
                return ['success' => false, 'message' => 'Insufficient available balance.', 'session_id' => $transfer->session_id];
            }

            // 2. Debit source account
            $account->decrement('available_balance', $transfer->amount);
            $account->decrement('ledger_balance', $transfer->amount);

            // 3. Create debit transaction record
            $debitTx = Transaction::create([
                'tenant_id'   => $tenant->id,
                'account_id'  => $account->id,
                'type'        => 'debit',
                'amount'      => $transfer->amount,
                'currency'    => 'NGN',
                'description' => 'NIP Transfer to ' . ($transfer->beneficiary_account_name ?? $transfer->beneficiary_account_number),
                'status'      => 'success',
                'reference'   => 'NIP-' . strtoupper(Str::random(10)),
                'performed_by' => $transfer->initiated_by,
            ]);

            // 4. Mark as initiated
            $transfer->update([
                'status'       => 'initiated',
                'initiated_at' => now(),
            ]);

            // 5. SIMULATE NIBSS call — 90 % success rate
            // Replace this with a real HTTP call to NIBSS in production.
            $nibssSuccess = rand(1, 10) > 1;

            if ($nibssSuccess) {
                // 6. Success path
                $transfer->update([
                    'status'              => 'successful',
                    'completed_at'        => now(),
                    'nibss_response_code' => '00',
                    'nibss_session_id'    => $transfer->session_id,
                ]);

                Log::info('NIP transfer successful', [
                    'session_id' => $transfer->session_id,
                    'amount'     => $transfer->amount,
                    'tenant_id'  => $tenant->id,
                ]);

                return [
                    'success'    => true,
                    'message'    => 'Transfer of ₦' . number_format($transfer->amount, 2) . ' completed successfully.',
                    'session_id' => $transfer->session_id,
                ];
            }

            // 7. Failure path — reverse the debit
            $failureReason = 'NIBSS returned a non-zero response code (simulated failure).';

            $account->increment('available_balance', $transfer->amount);
            $account->increment('ledger_balance', $transfer->amount);

            Transaction::create([
                'tenant_id'              => $tenant->id,
                'account_id'             => $account->id,
                'type'                   => 'credit',
                'amount'                 => $transfer->amount,
                'currency'               => 'NGN',
                'description'            => 'NIP Transfer Reversal — ' . $transfer->session_id,
                'status'                 => 'success',
                'reference'              => 'REV-' . strtoupper(Str::random(10)),
                'related_transaction_id' => $debitTx->id,
                'performed_by'           => $transfer->initiated_by,
            ]);

            $transfer->update([
                'status'         => 'failed',
                'failure_reason' => $failureReason,
            ]);

            Log::warning('NIP transfer failed and reversed', [
                'session_id' => $transfer->session_id,
                'reason'     => $failureReason,
                'tenant_id'  => $tenant->id,
            ]);

            return [
                'success'    => false,
                'message'    => 'Transfer failed: ' . $failureReason . ' Your account has been reversed.',
                'session_id' => $transfer->session_id,
            ];
        });
    }

    // ── Callback Handler ───────────────────────────────────────────────────────

    /**
     * Handle an asynchronous callback / notification from NIBSS.
     *
     * Expects $payload to contain 'session_id' and 'response_code'.
     * Response code '00' = successful; anything else = failed with reversal.
     */
    public function handleCallback(array $payload): void
    {
        $sessionId    = $payload['session_id'] ?? $payload['sessionId'] ?? null;
        $responseCode = $payload['response_code'] ?? $payload['responseCode'] ?? null;

        if (! $sessionId) {
            Log::warning('NIP callback received without session_id', $payload);
            return;
        }

        $transfer = NipOutwardTransfer::where('session_id', $sessionId)->first();

        if (! $transfer) {
            Log::warning('NIP callback: transfer not found', ['session_id' => $sessionId]);
            return;
        }

        // Already in a terminal state — ignore duplicate callbacks
        if (in_array($transfer->status, ['successful', 'failed', 'reversed'], true)) {
            Log::info('NIP callback: transfer already in terminal state, skipping', [
                'session_id' => $sessionId,
                'status'     => $transfer->status,
            ]);
            return;
        }

        DB::transaction(function () use ($transfer, $responseCode, $payload) {
            if ($responseCode === '00') {
                $transfer->update([
                    'status'              => 'successful',
                    'completed_at'        => now(),
                    'nibss_response_code' => $responseCode,
                ]);

                Log::info('NIP callback: transfer marked successful', ['session_id' => $transfer->session_id]);
            } else {
                // Reverse the debit if account still exists
                $account = $transfer->sourceAccount;

                if ($account) {
                    $account->increment('available_balance', $transfer->amount);
                    $account->increment('ledger_balance', $transfer->amount);

                    Transaction::create([
                        'tenant_id'   => $transfer->tenant_id,
                        'account_id'  => $account->id,
                        'type'        => 'credit',
                        'amount'      => $transfer->amount,
                        'currency'    => 'NGN',
                        'description' => 'NIP Transfer Reversal (callback) — ' . $transfer->session_id,
                        'status'      => 'success',
                        'reference'   => 'REV-' . strtoupper(Str::random(10)),
                    ]);
                }

                $transfer->update([
                    'status'              => 'reversed',
                    'failure_reason'      => 'NIBSS callback returned code: ' . ($responseCode ?? 'unknown'),
                    'nibss_response_code' => $responseCode,
                    'reversed_at'         => now(),
                ]);

                Log::warning('NIP callback: transfer reversed', [
                    'session_id'    => $transfer->session_id,
                    'response_code' => $responseCode,
                ]);
            }
        });
    }

    // ── Bank List Seeder ───────────────────────────────────────────────────────

    /**
     * Seed the bank_list table with Nigerian commercial banks and MFBs.
     * Uses upsert on cbn_code to avoid duplicates.
     */
    public function seedBankList(): void
    {
        $banks = [
            // Commercial Banks
            ['cbn_code' => '044', 'bank_name' => 'Access Bank Plc',              'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '023', 'bank_name' => 'Citibank Nigeria Limited',      'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '050', 'bank_name' => 'EcoBank Nigeria',               'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '011', 'bank_name' => 'First Bank of Nigeria Limited', 'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '214', 'bank_name' => 'First City Monument Bank',      'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '070', 'bank_name' => 'Fidelity Bank Plc',             'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '058', 'bank_name' => 'Guaranty Trust Bank',           'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '030', 'bank_name' => 'Heritage Banking Company Ltd',  'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '301', 'bank_name' => 'Jaiz Bank Plc',                 'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '082', 'bank_name' => 'Keystone Bank Limited',         'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '526', 'bank_name' => 'Parallex Bank Limited',         'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '076', 'bank_name' => 'Polaris Bank Limited',          'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '101', 'bank_name' => 'ProvidusBank Plc',              'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '221', 'bank_name' => 'Stanbic IBTC Bank Plc',         'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '068', 'bank_name' => 'Standard Chartered Bank',       'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '232', 'bank_name' => 'Sterling Bank Plc',             'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '100', 'bank_name' => 'Suntrust Bank Nigeria Limited', 'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '032', 'bank_name' => 'Union Bank of Nigeria Plc',     'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '033', 'bank_name' => 'United Bank for Africa Plc',    'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '035', 'bank_name' => 'Wema Bank Plc',                 'nibss_code' => null, 'is_microfinance' => false],
            ['cbn_code' => '057', 'bank_name' => 'Zenith Bank Plc',               'nibss_code' => null, 'is_microfinance' => false],
            // Microfinance Banks
            ['cbn_code' => '090001', 'bank_name' => 'ASO Savings and Loans',       'nibss_code' => null, 'is_microfinance' => true],
            ['cbn_code' => '090003', 'bank_name' => 'Jubilee Life Microfinance Bank','nibss_code' => null, 'is_microfinance' => true],
            ['cbn_code' => '090097', 'bank_name' => 'Ekondo Microfinance Bank',    'nibss_code' => null, 'is_microfinance' => true],
        ];

        $now  = now()->toDateTimeString();
        $rows = array_map(fn ($b) => array_merge($b, [
            'is_active'  => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]), $banks);

        BankList::upsert(
            $rows,
            ['cbn_code'],                                          // unique key
            ['bank_name', 'nibss_code', 'is_active', 'is_microfinance', 'updated_at']  // columns to update
        );

        Log::info('NIP bank list seeded', ['count' => count($rows)]);
    }
}
