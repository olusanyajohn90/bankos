<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTransactionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [10, 30, 60];

    /**
     * @param string $type  debit|credit
     */
    public function __construct(
        public readonly string $customerId,
        public readonly string $transactionId,
        public readonly string $type,
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     * Fetches the transaction, customer, account, and tenant, then sends
     * email and/or push notification to the customer.
     */
    public function handle(): void
    {
        $transaction = Transaction::findOrFail($this->transactionId);
        $account     = Account::findOrFail($transaction->account_id);
        $customer    = Customer::findOrFail($this->customerId);
        $tenant      = Tenant::findOrFail($transaction->tenant_id);

        $typeLabel  = $this->type === 'debit' ? 'Debit' : 'Credit';
        $amountFmt  = number_format((float) $transaction->amount, 2);
        $currency   = $transaction->currency ?? 'NGN';
        $subject    = "{$typeLabel} Alert: {$currency} {$amountFmt} on {$account->account_number}";

        $emailAddress = $customer->email ?? null;
        if (!$emailAddress) {
            Log::warning('SendTransactionNotification: customer has no email', [
                'customer_id'    => $this->customerId,
                'transaction_id' => $this->transactionId,
            ]);
            return;
        }

        $body = view('emails.transaction-notification', [
            'customer'    => $customer,
            'transaction' => $transaction,
            'account'     => $account,
            'tenant'      => $tenant,
            'type'        => $this->type,
            'amountFmt'   => $amountFmt,
            'currency'    => $currency,
        ])->render();

        Mail::html($body, function ($message) use ($emailAddress, $subject, $tenant) {
            $message->to($emailAddress)
                    ->subject($subject)
                    ->from(
                        config('mail.from.address'),
                        $tenant->name ?? config('mail.from.name')
                    );
        });

        Log::info('Transaction notification sent', [
            'customer_id'    => $this->customerId,
            'transaction_id' => $this->transactionId,
            'type'           => $this->type,
            'email'          => $emailAddress,
        ]);
    }
}
