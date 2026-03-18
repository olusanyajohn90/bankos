<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class WeeklyStatementNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  object  $customer      Customer model instance
     * @param  object  $account       Account model instance
     * @param  object  $tenant        Tenant model instance
     * @param  \Illuminate\Support\Collection  $transactions  Last 7 days transactions
     * @param  string  $periodStart   e.g. "09 Mar 2026"
     * @param  string  $periodEnd     e.g. "15 Mar 2026"
     */
    public function __construct(
        public $customer,
        public $account,
        public $tenant,
        public $transactions,
        public string $periodStart,
        public string $periodEnd
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $customer     = $this->customer;
        $account      = $this->account;
        $tenant       = $this->tenant;
        $transactions = $this->transactions;
        $currency     = $account->currency ?? 'NGN';

        $customerName = trim(
            ($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '')
        );
        if (empty(trim($customerName))) {
            $customerName = 'Valued Customer';
        }

        $mail = (new MailMessage)
            ->subject("Your Weekly Account Summary — {$account->account_name}")
            ->greeting("Hello {$customerName},")
            ->line(
                "Here is your account summary for **{$account->account_name}** (No: {$account->account_number}) "
                . "for the period **{$this->periodStart}** to **{$this->periodEnd}**."
            )
            ->line('---');

        // List each transaction as a line
        foreach ($transactions as $txn) {
            $date        = \Carbon\Carbon::parse($txn->created_at)->format('d M Y');
            $type        = ucfirst(strtolower($txn->type ?? 'N/A'));
            $amount      = number_format((float) $txn->amount, 2);
            $description = $txn->description ?? $txn->narration ?? '—';

            $mail->line("{$date} | {$type} | {$currency} {$amount} | {$description}");
        }

        $closingBalance = number_format((float) $account->available_balance, 2);

        $mail->line('---')
             ->line("Closing Balance: **{$currency} {$closingBalance}**")
             ->salutation(
                 "{$tenant->name} — This is an automated statement. "
                 . "To unsubscribe contact your branch."
             );

        return $mail;
    }
}
