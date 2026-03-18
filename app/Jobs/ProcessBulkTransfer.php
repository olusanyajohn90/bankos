<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessBulkTransfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * Financial operations should not auto-retry without investigation.
     */
    public int $tries = 1;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $batchId,
        public readonly array $transferRows,
    ) {
        $this->onQueue('transfers');
    }

    /**
     * Execute the job.
     * Processes each transfer row: validates accounts, debits source, credits destinations,
     * and records transactions. Each row is atomic; failures are logged individually.
     */
    public function handle(): void
    {
        $successCount = 0;
        $failCount    = 0;
        $errors       = [];

        foreach ($this->transferRows as $index => $row) {
            try {
                DB::transaction(function () use ($row) {
                    $sourceAccount = Account::where('tenant_id', $this->tenantId)
                        ->where('id', $row['source_account_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    $destinationAccount = Account::where('tenant_id', $this->tenantId)
                        ->where('account_number', $row['destination_account_number'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    $amount = (float) $row['amount'];

                    if ($sourceAccount->balance < $amount) {
                        throw new \RuntimeException(
                            "Insufficient balance on account {$sourceAccount->account_number}. " .
                            "Required: {$amount}, Available: {$sourceAccount->balance}"
                        );
                    }

                    // Debit source account
                    $sourceAccount->decrement('balance', $amount);

                    Transaction::create([
                        'tenant_id'   => $this->tenantId,
                        'account_id'  => $sourceAccount->id,
                        'reference'   => $row['reference'] ?? ('BULK-DR-' . $this->batchId . '-' . uniqid()),
                        'type'        => 'debit',
                        'amount'      => $amount,
                        'currency'    => $row['currency'] ?? 'NGN',
                        'description' => $row['description'] ?? 'Bulk Transfer',
                        'status'      => 'completed',
                    ]);

                    // Credit destination account
                    $destinationAccount->increment('balance', $amount);

                    Transaction::create([
                        'tenant_id'   => $this->tenantId,
                        'account_id'  => $destinationAccount->id,
                        'reference'   => $row['reference'] ?? ('BULK-CR-' . $this->batchId . '-' . uniqid()),
                        'type'        => 'credit',
                        'amount'      => $amount,
                        'currency'    => $row['currency'] ?? 'NGN',
                        'description' => $row['description'] ?? 'Bulk Transfer',
                        'status'      => 'completed',
                    ]);
                });

                $successCount++;
            } catch (\Throwable $e) {
                $failCount++;
                $errors[] = [
                    'row'   => $index,
                    'error' => $e->getMessage(),
                    'data'  => $row,
                ];

                Log::error('Bulk transfer row failed', [
                    'batch_id'  => $this->batchId,
                    'tenant_id' => $this->tenantId,
                    'row_index' => $index,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        Log::info('Bulk transfer batch completed', [
            'batch_id'      => $this->batchId,
            'tenant_id'     => $this->tenantId,
            'total_rows'    => count($this->transferRows),
            'success_count' => $successCount,
            'fail_count'    => $failCount,
        ]);

        if ($failCount > 0) {
            Log::warning('Bulk transfer batch had failures — manual review required', [
                'batch_id' => $this->batchId,
                'errors'   => $errors,
            ]);
        }
    }
}
