<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateStatementPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [30, 120];

    public function __construct(
        public readonly string $tenantId,
        public readonly string $accountId,
        public readonly string $customerId,
        public readonly string $from,
        public readonly string $to,
        public readonly bool $official,
        public readonly ?float $fee,
        public readonly string $reference,
        public readonly string $recipientEmail,
    ) {
        $this->onQueue('pdf');
    }

    /**
     * Execute the job.
     * Generates the account statement PDF and emails it to the recipient.
     */
    public function handle(): void
    {
        try {
            $account  = Account::where('tenant_id', $this->tenantId)
                               ->findOrFail($this->accountId);
            $customer = Customer::where('tenant_id', $this->tenantId)
                                ->findOrFail($this->customerId);

            $transactions = Transaction::where('tenant_id', $this->tenantId)
                ->where('account_id', $this->accountId)
                ->whereBetween('created_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59'])
                ->orderBy('created_at')
                ->get();

            // Build PDF data
            $data = [
                'account'      => $account,
                'customer'     => $customer,
                'transactions' => $transactions,
                'from'         => $this->from,
                'to'           => $this->to,
                'official'     => $this->official,
                'fee'          => $this->fee,
                'reference'    => $this->reference,
                'generated_at' => now()->toDateTimeString(),
            ];

            // Generate PDF using the view-based PDF approach
            // In production, use barryvdh/laravel-dompdf or similar
            $pdf = view('statements.pdf', $data)->render();

            // Email the statement
            Mail::send([], [], function ($message) use ($pdf, $customer) {
                $message->to($this->recipientEmail)
                        ->subject('Account Statement — ' . $this->from . ' to ' . $this->to)
                        ->html('<p>Dear ' . ($customer->full_name ?? 'Customer') . ',</p><p>Please find your account statement attached.</p>')
                        ->attachData($pdf, 'statement_' . $this->reference . '.html', [
                            'mime' => 'text/html',
                        ]);
            });

            Log::info('Statement PDF generated and emailed', [
                'reference'      => $this->reference,
                'account_id'     => $this->accountId,
                'recipient_email'=> $this->recipientEmail,
            ]);
        } catch (\Throwable $e) {
            Log::error('GenerateStatementPdf job failed', [
                'reference' => $this->reference,
                'error'     => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
