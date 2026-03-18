<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * All supported trigger events.
     */
    public const EVENTS = [
        // Loan lifecycle
        'loan_approved'              => 'Loan Approved',
        'loan_rejected'              => 'Loan Rejected',
        'loan_disbursed'             => 'Loan Disbursed',
        'repayment_received'         => 'Repayment Received',
        'repayment_overdue'          => 'Repayment Overdue',
        'repayment_reminder'         => 'Repayment Reminder',
        // Loan activities
        'loan_topup_approved'        => 'Loan Top-up Approved',
        'loan_topup_rejected'        => 'Loan Top-up Rejected',
        'loan_restructured'          => 'Loan Restructured',
        'loan_restructure_rejected'  => 'Loan Restructure Rejected',
        'loan_liquidation_partial'   => 'Partial Liquidation Posted',
        'loan_liquidation_full'      => 'Loan Fully Settled (Liquidation)',
        // Account & KYC
        'account_opened'             => 'Account Opened',
        'kyc_approved'               => 'KYC Approved',
        'kyc_rejected'               => 'KYC Rejected',
        // Account transactions
        'deposit_received'           => 'Deposit / Credit Alert',
        'withdrawal_posted'          => 'Withdrawal / Debit Alert',
        'transfer_sent'              => 'Transfer Sent (Debit Alert)',
        'transfer_received'          => 'Transfer Received (Credit Alert)',
        // Auth
        'otp'                        => 'OTP / Verification Code',
        'password_reset'             => 'Password Reset',
    ];

    public const CHANNELS = ['sms', 'whatsapp', 'email', 'push'];

    /**
     * Send a notification to a customer for a given event.
     * Respects customer opt-in preferences.
     */
    public function send(Customer $customer, string $event, array $data = []): void
    {
        $tenantId = $customer->tenant_id;

        foreach (self::CHANNELS as $channel) {
            // Check customer opt-in
            $optInField = 'notify_' . $channel;
            if (!$customer->{$optInField}) {
                continue;
            }

            // Load template
            $template = NotificationTemplate::where('tenant_id', $tenantId)
                ->where('event', $event)
                ->where('channel', $channel)
                ->where('active', true)
                ->first();

            if (!$template) {
                continue;
            }

            $rendered   = $template->render($data);
            $recipient  = $this->getRecipient($customer, $channel);

            if (!$recipient) {
                continue;
            }

            $log = NotificationLog::create([
                'tenant_id'   => $tenantId,
                'customer_id' => $customer->id,
                'channel'     => $channel,
                'recipient'   => $recipient,
                'event'       => $event,
                'message'     => $rendered['body'],
                'status'      => 'pending',
            ]);

            try {
                $response = $this->dispatch($channel, $recipient, $rendered);
                $log->update(['status' => 'sent', 'provider_response' => $response, 'sent_at' => now()]);
            } catch (\Throwable $e) {
                Log::warning("Notification failed [{$channel}] event={$event} recipient={$recipient}: " . $e->getMessage());
                $log->update(['status' => 'failed', 'provider_response' => $e->getMessage()]);
            }
        }
    }

    /**
     * Dispatch to the appropriate provider.
     * Returns the provider response string.
     */
    private function dispatch(string $channel, string $recipient, array $rendered): string
    {
        return match ($channel) {
            'sms'       => $this->sendSms($recipient, $rendered['body']),
            'whatsapp'  => $this->sendWhatsapp($recipient, $rendered['body']),
            'email'     => $this->sendEmail($recipient, $rendered['subject'], $rendered['body']),
            'push'      => $this->sendPush($recipient, $rendered['subject'], $rendered['body']),
            default     => throw new \InvalidArgumentException("Unknown channel: {$channel}"),
        };
    }

    private function sendSms(string $phone, string $message): string
    {
        // Termii / Africa's Talking integration point
        // TODO: implement actual HTTP call when API keys are configured
        Log::info("SMS → {$phone}: {$message}");
        return 'simulated:ok';
    }

    private function sendWhatsapp(string $phone, string $message): string
    {
        // Twilio / Meta Cloud API integration point
        Log::info("WhatsApp → {$phone}: {$message}");
        return 'simulated:ok';
    }

    private function sendEmail(string $email, string $subject, string $body): string
    {
        $html = view('emails.notification', compact('subject', 'body'))->render();

        Mail::html($html, function ($message) use ($email, $subject) {
            $message->to($email)->subject($subject);
        });

        return 'sent:ok';
    }

    private function sendPush(string $token, string $title, string $body): string
    {
        // Firebase FCM integration point
        Log::info("Push → {$token} [{$title}]: {$body}");
        return 'simulated:ok';
    }

    private function getRecipient(Customer $customer, string $channel): ?string
    {
        return match ($channel) {
            'sms', 'whatsapp' => $customer->phone,
            'email'           => $customer->email,
            'push'            => null, // FCM token — not stored yet
            default           => null,
        };
    }
}
