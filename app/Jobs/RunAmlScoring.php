<?php

namespace App\Jobs;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RunAmlScoring implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * AML scoring should be reviewed manually if it fails once.
     */
    public int $tries = 1;

    public function __construct(
        public readonly string $transactionId,
    ) {
        $this->onQueue('aml');
    }

    /**
     * Execute the job.
     * Loads the transaction, runs AML scoring, and notifies the compliance officer
     * if an alert is created.
     */
    public function handle(): void
    {
        $transaction = Transaction::findOrFail($this->transactionId);

        // Run AML scoring — the service class will be implemented in the AML module
        // This job acts as the queue bridge to execute scoring asynchronously
        if (!class_exists(\App\Services\AmlScoringService::class)) {
            Log::warning('RunAmlScoring: AmlScoringService not found, skipping', [
                'transaction_id' => $this->transactionId,
            ]);
            return;
        }

        $alert = \App\Services\AmlScoringService::scoreTransaction($transaction);

        if ($alert) {
            Log::warning('RunAmlScoring: AML alert created', [
                'transaction_id' => $this->transactionId,
                'alert_id'       => $alert->id ?? null,
                'score'          => $alert->score ?? null,
            ]);

            // Notify compliance officer(s) for the tenant
            $this->notifyComplianceOfficers($transaction, $alert);
        }
    }

    /**
     * Notify compliance officers when an AML alert is triggered.
     */
    private function notifyComplianceOfficers(Transaction $transaction, mixed $alert): void
    {
        try {
            // Fetch users with compliance officer role for this tenant
            $officers = \App\Models\User::where('tenant_id', $transaction->tenant_id)
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['compliance_officer', 'aml_officer', 'tenant_admin']))
                ->get();

            if ($officers->isEmpty()) {
                Log::warning('RunAmlScoring: no compliance officers found for tenant', [
                    'tenant_id' => $transaction->tenant_id,
                ]);
                return;
            }

            foreach ($officers as $officer) {
                if (!$officer->email) {
                    continue;
                }

                Mail::html(
                    view('emails.aml-alert', [
                        'officer'     => $officer,
                        'transaction' => $transaction,
                        'alert'       => $alert,
                    ])->render(),
                    function ($message) use ($officer) {
                        $message->to($officer->email)
                                ->subject('[AML Alert] Suspicious transaction flagged — ' . now()->toDateString());
                    }
                );
            }
        } catch (\Throwable $e) {
            Log::error('RunAmlScoring: failed to notify compliance officers', [
                'transaction_id' => $this->transactionId,
                'error'          => $e->getMessage(),
            ]);
        }
    }
}
