<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

class LoanRepaymentReminder extends Notification
{
    /**
     * @param  object  $loan    Row from loans + customers join (loan_number, outstanding_balance, expected_maturity_date, first_name, last_name)
     * @param  object  $tenant  Row from tenants (name, contact_email)
     */
    public function __construct(
        protected object $loan,
        protected object $tenant
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $customerName = trim("{$this->loan->first_name} {$this->loan->last_name}");
        $dueDate      = Carbon::parse($this->loan->expected_maturity_date)->format('d M Y');
        $amountDue    = number_format((float) $this->loan->outstanding_balance, 2);
        $bankName     = $this->tenant->name;
        $loanNumber   = $this->loan->loan_number ?? 'N/A';

        return (new MailMessage)
            ->subject("Repayment Reminder — Loan {$loanNumber}")
            ->greeting("Dear {$customerName},")
            ->line("This is a friendly reminder from **{$bankName}** that a repayment is due on your loan.")
            ->line("**Loan Number:** {$loanNumber}")
            ->line("**Amount Due:** NGN {$amountDue}")
            ->line("**Due Date:** {$dueDate}")
            ->line('Please ensure sufficient funds are available in your account before the due date to avoid penalties.')
            ->line('If you have already made this payment, please disregard this message.')
            ->salutation("Thank you,\n{$bankName}");
    }
}
