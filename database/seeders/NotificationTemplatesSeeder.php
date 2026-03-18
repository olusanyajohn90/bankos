<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class NotificationTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('short_name', 'demo')->firstOrFail();
        $tid    = $tenant->id;

        $templates = [

            // ── Loan Disbursed ──────────────────────────────────────────────
            [
                'event'   => 'loan_disbursed',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Dear {{customer_name}}, your loan of NGN {{amount}} has been disbursed to account {{account_number}}. Repayment starts {{first_repayment_date}}. - Demo MFB',
            ],
            [
                'event'   => 'loan_disbursed',
                'channel' => 'email',
                'subject' => 'Loan Disbursement Confirmation – {{loan_account_number}}',
                'body'    => "Dear {{customer_name}},\n\nWe are pleased to inform you that your loan has been approved and NGN {{amount}} has been credited to your account.\n\nLoan Reference: {{loan_account_number}}\nDisbursement Account: {{account_number}}\nFirst Repayment Due: {{first_repayment_date}}\nTenure: {{tenure}}\n\nPlease ensure your account is funded on your repayment dates to avoid penalties.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Loan Approved ───────────────────────────────────────────────
            [
                'event'   => 'loan_approved',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Dear {{customer_name}}, your loan application of NGN {{amount}} has been approved. Our team will contact you for disbursement. - Demo MFB',
            ],
            [
                'event'   => 'loan_approved',
                'channel' => 'email',
                'subject' => 'Your Loan Application Has Been Approved – {{loan_number}}',
                'body'    => "Dear {{customer_name}},\n\nCongratulations! Your loan application has been approved.\n\nLoan Reference: {{loan_number}}\nApproved Amount: NGN {{amount}}\nProduct: {{product_name}}\nTenure: {{tenure}}\n\nOur team will contact you shortly to complete the disbursement process.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Loan Rejected ───────────────────────────────────────────────
            [
                'event'   => 'loan_rejected',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Dear {{customer_name}}, we regret to inform you that your loan application of NGN {{amount}} has not been approved at this time. Please visit a branch for details. - Demo MFB',
            ],
            [
                'event'   => 'loan_rejected',
                'channel' => 'email',
                'subject' => 'Update on Your Loan Application',
                'body'    => "Dear {{customer_name}},\n\nThank you for applying for a loan with Demo Microfinance Bank.\n\nAfter careful review, we regret to inform you that your loan application of NGN {{amount}} could not be approved at this time.\n\nReason: {{reason}}\n\nYou are welcome to re-apply after addressing the above or visit your nearest branch to speak with a loan officer.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Repayment Received ──────────────────────────────────────────
            [
                'event'   => 'repayment_received',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Dear {{customer_name}}, we received your repayment of NGN {{amount}} on {{date}}. Outstanding balance: NGN {{outstanding_balance}}. - Demo MFB',
            ],
            [
                'event'   => 'repayment_received',
                'channel' => 'email',
                'subject' => 'Repayment Received – NGN {{amount}}',
                'body'    => "Dear {{customer_name}},\n\nWe have received your loan repayment. Here are the details:\n\nAmount Paid: NGN {{amount}}\nPayment Date: {{date}}\nLoan Reference: {{loan_number}}\nOutstanding Balance: NGN {{outstanding_balance}}\n\nThank you for keeping up with your repayments.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Repayment Overdue ───────────────────────────────────────────
            [
                'event'   => 'repayment_overdue',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'URGENT: Dear {{customer_name}}, your loan repayment of NGN {{amount}} was due on {{due_date}} and is now overdue. Please pay immediately to avoid penalties. - Demo MFB',
            ],
            [
                'event'   => 'repayment_overdue',
                'channel' => 'email',
                'subject' => 'URGENT: Overdue Loan Repayment – {{loan_number}}',
                'body'    => "Dear {{customer_name}},\n\nThis is an urgent reminder that your loan repayment is overdue.\n\nLoan Reference: {{loan_number}}\nAmount Due: NGN {{amount}}\nDue Date: {{due_date}}\nDays Overdue: {{days_overdue}}\n\nPlease make this payment immediately to avoid late charges and adverse credit reporting. You can visit any of our branches or make a transfer to your loan account.\n\nIf you have already made this payment, please disregard this notice.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Repayment Reminder ──────────────────────────────────────────
            [
                'event'   => 'repayment_reminder',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Dear {{customer_name}}, friendly reminder: your loan repayment of NGN {{amount}} is due on {{due_date}}. Please ensure your account is funded. - Demo MFB',
            ],
            [
                'event'   => 'repayment_reminder',
                'channel' => 'email',
                'subject' => 'Upcoming Loan Repayment Reminder – {{loan_number}}',
                'body'    => "Dear {{customer_name}},\n\nThis is a friendly reminder that your upcoming loan repayment is due soon.\n\nLoan Reference: {{loan_number}}\nAmount Due: NGN {{amount}}\nDue Date: {{due_date}}\n\nPlease ensure your account is adequately funded before the due date to avoid late fees.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── KYC Approved ────────────────────────────────────────────────
            [
                'event'   => 'kyc_approved',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Dear {{customer_name}}, your KYC verification is complete. Your account is now fully activated at Tier {{kyc_tier}}. - Demo MFB',
            ],
            [
                'event'   => 'kyc_approved',
                'channel' => 'email',
                'subject' => 'KYC Verification Successful – Account Activated',
                'body'    => "Dear {{customer_name}},\n\nWe are pleased to inform you that your identity verification (KYC) has been successfully completed.\n\nKYC Tier: {{kyc_tier}}\nAccount Status: Active\n\nYou can now access all banking services including loans and transfers. Log in to your account to get started.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── KYC Rejected ────────────────────────────────────────────────
            [
                'event'   => 'kyc_rejected',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Dear {{customer_name}}, your KYC verification was unsuccessful. Please visit a branch with valid ID documents to retry. - Demo MFB',
            ],
            [
                'event'   => 'kyc_rejected',
                'channel' => 'email',
                'subject' => 'Action Required: KYC Verification Unsuccessful',
                'body'    => "Dear {{customer_name}},\n\nUnfortunately, we were unable to complete your identity verification (KYC).\n\nReason: {{reason}}\n\nTo resolve this, please visit your nearest Demo MFB branch with the following:\n- A valid government-issued ID (National ID, Driver's License, or International Passport)\n- Proof of address (utility bill or bank statement)\n\nOur compliance team is available to assist you.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Account Opened ──────────────────────────────────────────────
            [
                'event'   => 'account_opened',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Dear {{customer_name}}, your savings account {{account_number}} has been opened successfully. Welcome to Demo MFB!',
            ],
            [
                'event'   => 'account_opened',
                'channel' => 'email',
                'subject' => 'Welcome to Demo MFB – Your Account is Ready',
                'body'    => "Dear {{customer_name}},\n\nWelcome to Demo Microfinance Bank! Your account has been successfully opened.\n\nAccount Number: {{account_number}}\nAccount Name: {{account_name}}\nProduct: {{product_name}}\nCurrency: {{currency}}\n\nYou can now make deposits, transfers, and apply for our loan products. If you have any questions, please don't hesitate to contact us.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Transfer Received ───────────────────────────────────────────
            [
                'event'   => 'transfer_received',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Dear {{customer_name}}, you have received an inbound transfer of NGN {{amount}} from {{sender_name}}. New balance: NGN {{new_balance}}. - Demo MFB',
            ],
            [
                'event'   => 'transfer_received',
                'channel' => 'email',
                'subject' => 'Credit Alert – NGN {{amount}} Received',
                'body'    => "Dear {{customer_name}},\n\nA transfer has been credited to your account.\n\nAmount: NGN {{amount}}\nSender: {{sender_name}}\nDate: {{date}}\nReference: {{reference}}\nNew Balance: NGN {{new_balance}}\n\nIf you did not expect this credit, please contact us immediately.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── OTP ─────────────────────────────────────────────────────────
            [
                'event'   => 'otp',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Your Demo MFB verification code is: {{otp_code}}. Valid for {{expiry_minutes}} minutes. Do not share this code with anyone.',
            ],
            [
                'event'   => 'otp',
                'channel' => 'email',
                'subject' => 'Your Verification Code',
                'body'    => "Dear {{customer_name}},\n\nYour one-time verification code is:\n\n{{otp_code}}\n\nThis code is valid for {{expiry_minutes}} minutes. Do not share it with anyone — Demo MFB staff will never ask for your OTP.\n\nIf you did not request this code, please contact us immediately.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Password Reset ──────────────────────────────────────────────
            [
                'event'   => 'password_reset',
                'channel' => 'email',
                'subject' => 'Password Reset Request',
                'body'    => "Dear {{customer_name}},\n\nWe received a request to reset the password for your account.\n\nClick the link below to reset your password (valid for {{expiry_minutes}} minutes):\n\n{{reset_link}}\n\nIf you did not request a password reset, please ignore this email or contact us if you believe your account may be compromised.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Loan Top-up Approved ────────────────────────────────────────
            [
                'event'   => 'loan_topup_approved',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Dear {{customer_name}}, your loan top-up of NGN {{topup_amount}} has been approved. New loan {{new_loan_number}} (NGN {{new_principal}}, {{new_tenure}}) created. - Demo MFB',
            ],
            [
                'event'   => 'loan_topup_approved',
                'channel' => 'email',
                'subject' => 'Loan Top-up Approved – {{new_loan_number}}',
                'body'    => "Dear {{customer_name}},\n\nYour loan top-up request has been approved and funds have been credited to your account.\n\nNew Loan Reference: {{new_loan_number}}\nTop-up Amount Disbursed: NGN {{topup_amount}}\nNew Combined Principal: NGN {{new_principal}}\nNew Interest Rate: {{new_rate}}\nNew Tenure: {{new_tenure}}\nDisbursement Account: {{account_number}}\n\nYour previous loan has been closed and replaced by the new loan above.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Loan Top-up Rejected ────────────────────────────────────────
            [
                'event'   => 'loan_topup_rejected',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Dear {{customer_name}}, your top-up request of NGN {{topup_amount}} on loan {{original_loan_number}} was not approved. Please visit a branch for details. - Demo MFB',
            ],
            [
                'event'   => 'loan_topup_rejected',
                'channel' => 'email',
                'subject' => 'Loan Top-up Request Unsuccessful',
                'body'    => "Dear {{customer_name}},\n\nWe regret to inform you that your loan top-up request has not been approved at this time.\n\nOriginal Loan: {{original_loan_number}}\nRequested Top-up: NGN {{topup_amount}}\nReason: {{reason}}\n\nPlease visit your nearest branch or speak with your loan officer if you have questions.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Loan Restructured ───────────────────────────────────────────
            [
                'event'   => 'loan_restructured',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Dear {{customer_name}}, your loan has been restructured. New loan {{new_loan_number}} (NGN {{new_outstanding}}, {{new_tenure}} at {{new_rate}}) replaces {{old_loan_number}}. - Demo MFB',
            ],
            [
                'event'   => 'loan_restructured',
                'channel' => 'email',
                'subject' => 'Your Loan Has Been Restructured – {{new_loan_number}}',
                'body'    => "Dear {{customer_name}},\n\nYour loan restructure request has been approved. Your loan has been rescheduled on new terms.\n\nPrevious Loan: {{old_loan_number}}\nPrevious Outstanding: NGN {{previous_outstanding}}\n\nNew Loan Reference: {{new_loan_number}}\nNew Outstanding Balance: NGN {{new_outstanding}}\nNew Interest Rate: {{new_rate}}\nNew Tenure: {{new_tenure}}\n\nYour previous loan has been closed and the new loan is now active. Please ensure timely repayments on the new schedule.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Loan Restructure Rejected ───────────────────────────────────
            [
                'event'   => 'loan_restructure_rejected',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Dear {{customer_name}}, your restructure request on loan {{loan_number}} was not approved. Please visit a branch for details. - Demo MFB',
            ],
            [
                'event'   => 'loan_restructure_rejected',
                'channel' => 'email',
                'subject' => 'Loan Restructure Request Not Approved',
                'body'    => "Dear {{customer_name}},\n\nWe regret to inform you that your loan restructure request has not been approved at this time.\n\nLoan Reference: {{loan_number}}\nReason: {{reason}}\n\nYour existing loan remains active under its current terms. Please visit your nearest branch or contact your loan officer for further assistance.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Partial Liquidation ─────────────────────────────────────────
            [
                'event'   => 'loan_liquidation_partial',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Dear {{customer_name}}, a partial payment of NGN {{amount}} has been applied to loan {{loan_number}}. Remaining balance: NGN {{outstanding_balance}} (Ref: {{reference}}). - Demo MFB',
            ],
            [
                'event'   => 'loan_liquidation_partial',
                'channel' => 'email',
                'subject' => 'Partial Loan Payment Received – {{loan_number}}',
                'body'    => "Dear {{customer_name}},\n\nA partial lump-sum payment has been applied to your loan.\n\nLoan Reference: {{loan_number}}\nAmount Applied: NGN {{amount}}\nRemaining Balance: NGN {{outstanding_balance}}\nTransaction Ref: {{reference}}\n\nThank you. Please continue making regular repayments as scheduled.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Full Liquidation / Settlement ───────────────────────────────
            [
                'event'   => 'loan_liquidation_full',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Congratulations {{customer_name}}! Your loan {{loan_number}} has been fully settled. NGN {{discount_amount}} interest rebated. Your loan is now closed. - Demo MFB',
            ],
            [
                'event'   => 'loan_liquidation_full',
                'channel' => 'email',
                'subject' => 'Loan Fully Settled – {{loan_number}} is Now Closed',
                'body'    => "Dear {{customer_name}},\n\nCongratulations! Your loan has been fully settled and closed.\n\nLoan Reference: {{loan_number}}\nAmount Paid: NGN {{amount}}\nUnearned Interest Rebated: NGN {{discount_amount}}\nTransaction Ref: {{reference}}\nStatus: CLOSED\n\nThank you for banking with Demo Microfinance Bank. You are welcome to apply for a new loan at any time.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Deposit / Credit Alert ──────────────────────────────────────
            [
                'event'   => 'deposit_received',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Credit Alert: NGN {{amount}} deposited to {{account_number}} on {{date}}. Desc: {{description}}. Bal: NGN {{new_balance}}. Ref: {{reference}}. - Demo MFB',
            ],
            [
                'event'   => 'deposit_received',
                'channel' => 'email',
                'subject' => 'Credit Alert – NGN {{amount}} Credited to {{account_number}}',
                'body'    => "Dear {{customer_name}},\n\nYour account has been credited.\n\nAmount: {{currency}} {{amount}}\nAccount: {{account_number}}\nDescription: {{description}}\nTransaction Ref: {{reference}}\nDate: {{date}}\nNew Balance: {{currency}} {{new_balance}}\n\nIf you did not authorise this transaction, please contact us immediately.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Withdrawal / Debit Alert ────────────────────────────────────
            [
                'event'   => 'withdrawal_posted',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Debit Alert: NGN {{amount}} withdrawn from {{account_number}} on {{date}}. Desc: {{description}}. Bal: NGN {{new_balance}}. Ref: {{reference}}. - Demo MFB',
            ],
            [
                'event'   => 'withdrawal_posted',
                'channel' => 'email',
                'subject' => 'Debit Alert – NGN {{amount}} Withdrawn from {{account_number}}',
                'body'    => "Dear {{customer_name}},\n\nA withdrawal has been posted to your account.\n\nAmount: {{currency}} {{amount}}\nAccount: {{account_number}}\nDescription: {{description}}\nTransaction Ref: {{reference}}\nDate: {{date}}\nNew Balance: {{currency}} {{new_balance}}\n\nIf you did not authorise this transaction, please contact us immediately.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Transfer Sent (Debit) ───────────────────────────────────────
            [
                'event'   => 'transfer_sent',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Debit Alert: NGN {{amount}} transferred from {{from_account}} to {{to_account}} on {{date}}. Bal: NGN {{new_balance}}. Ref: {{reference}}. - Demo MFB',
            ],
            [
                'event'   => 'transfer_sent',
                'channel' => 'email',
                'subject' => 'Transfer Debit Alert – NGN {{amount}} Sent',
                'body'    => "Dear {{customer_name}},\n\nA transfer has been debited from your account.\n\nAmount: {{currency}} {{amount}}\nFrom Account: {{from_account}}\nTo Account: {{to_account}} ({{beneficiary_name}})\nDescription: {{description}}\nTransaction Ref: {{reference}}\nDate: {{date}}\nNew Balance: {{currency}} {{new_balance}}\n\nIf you did not authorise this transfer, please contact us immediately.\n\nRegards,\nDemo Microfinance Bank",
            ],

            // ── Transfer Received (Credit) ──────────────────────────────────
            [
                'event'   => 'transfer_received',
                'channel' => 'sms',
                'subject' => null,
                'body'    => 'Credit Alert: NGN {{amount}} received in {{account_number}} from {{sender_name}} on {{date}}. Bal: NGN {{new_balance}}. Ref: {{reference}}. - Demo MFB',
            ],
            [
                'event'   => 'transfer_received',
                'channel' => 'email',
                'subject' => 'Credit Alert – NGN {{amount}} Transfer Received',
                'body'    => "Dear {{customer_name}},\n\nA transfer has been credited to your account.\n\nAmount: {{currency}} {{amount}}\nAccount: {{account_number}}\nSender: {{sender_name}}\nDescription: {{description}}\nTransaction Ref: {{reference}}\nDate: {{date}}\nNew Balance: {{currency}} {{new_balance}}\n\nRegards,\nDemo Microfinance Bank",
            ],
        ];

        foreach ($templates as $tmpl) {
            NotificationTemplate::firstOrCreate(
                ['tenant_id' => $tid, 'event' => $tmpl['event'], 'channel' => $tmpl['channel']],
                [
                    'subject' => $tmpl['subject'] ?? null,
                    'body'    => $tmpl['body'],
                    'active'  => true,
                ]
            );
        }

        $this->command->info('Notification templates seeded: ' . count($templates) . ' templates.');
    }
}
