<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\PostingFile;
use App\Models\PostingFileRecord;
use App\Models\Transaction;
use Illuminate\Support\Str;

class PostingFileService
{
    /**
     * Parse an uploaded CSV file, validate each row, and persist records.
     */
    public function validateAndStore(PostingFile $file, string $csvPath): void
    {
        $file->update(['status' => 'validating']);

        $handle = fopen($csvPath, 'r');
        $headers = array_map('trim', fgetcsv($handle));

        $required = ['identifier_type', 'identifier_value', 'amount', 'transaction_date'];
        $missing  = array_diff($required, $headers);

        if (!empty($missing)) {
            $file->update([
                'status'            => 'failed',
                'validation_errors' => ['Missing columns: ' . implode(', ', $missing)],
            ]);
            fclose($handle);
            return;
        }

        $rows        = 0;
        $validRows   = 0;
        $invalidRows = 0;
        $totalAmount = 0;
        $tenantId    = $file->tenant_id;

        // Collect existing identifier_values for duplicate detection within this file
        $seenIdentifiers = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rows++;
            $data = array_combine($headers, array_map('trim', $row));

            $identType  = strtoupper($data['identifier_type'] ?? '');
            $identValue = $data['identifier_value'] ?? '';
            $amount     = (float) ($data['amount'] ?? 0);
            $txDate     = $data['transaction_date'] ?? '';
            $channel    = $data['payment_channel'] ?? null;
            $narration  = $data['narration'] ?? null;

            $error  = null;
            $status = 'valid';

            // Duplicate detection within the same file
            $dedupKey = $identType . ':' . $identValue . ':' . $txDate;
            if (isset($seenIdentifiers[$dedupKey])) {
                $error  = 'Duplicate row in this file';
                $status = 'duplicate';
            } elseif (!in_array($identType, ['BVN', 'NIN', 'LOAN_ACCOUNT_NUMBER', 'ACCOUNT_NUMBER'])) {
                $error  = 'Invalid identifier_type. Must be BVN, NIN, LOAN_ACCOUNT_NUMBER, or ACCOUNT_NUMBER';
                $status = 'invalid';
            } elseif ($amount <= 0) {
                $error  = 'Amount must be greater than zero';
                $status = 'invalid';
            } elseif (!strtotime($txDate)) {
                $error  = 'Invalid transaction_date format';
                $status = 'invalid';
            } else {
                // Resolve identifier to an account/loan
                $resolved = $this->resolveIdentifier($identType, $identValue, $tenantId);
                if (!$resolved) {
                    $error  = "No matching record found for {$identType}={$identValue}";
                    $status = 'invalid';
                }
            }

            $seenIdentifiers[$dedupKey] = true;

            PostingFileRecord::create([
                'tenant_id'        => $tenantId,
                'posting_file_id'  => $file->id,
                'row_number'       => $rows,
                'identifier_type'  => $identType,
                'identifier_value' => $identValue,
                'amount'           => $amount,
                'transaction_date' => $txDate,
                'payment_channel'  => $channel,
                'narration'        => $narration,
                'status'           => $status,
                'error_message'    => $error,
            ]);

            if ($status === 'valid') {
                $validRows++;
                $totalAmount += $amount;
            } else {
                $invalidRows++;
            }
        }

        fclose($handle);

        $file->update([
            'status'         => 'validated',
            'total_records'  => $rows,
            'valid_records'  => $validRows,
            'invalid_records'=> $invalidRows,
            'total_amount'   => $totalAmount,
        ]);
    }

    /**
     * Post all validated records for a file.
     */
    public function post(PostingFile $file): void
    {
        $file->update(['status' => 'posting']);

        $posted = 0;

        foreach ($file->records()->where('status', 'valid')->cursor() as $record) {
            try {
                $resolved = $this->resolveIdentifier(
                    $record->identifier_type,
                    $record->identifier_value,
                    $file->tenant_id
                );

                if (!$resolved) {
                    $record->update(['status' => 'failed', 'error_message' => 'Could not resolve account at posting time']);
                    continue;
                }

                $tx = Transaction::create([
                    'tenant_id'   => $file->tenant_id,
                    'account_id'  => $resolved['account_id'],
                    'type'        => 'repayment',
                    'amount'      => $record->amount,
                    'currency'    => 'NGN',
                    'description' => $record->narration ?? 'Bulk posting from file ' . $file->reference,
                    'status'      => 'success',
                    'reference'   => 'BULK-' . strtoupper(Str::random(10)),
                ]);

                // Deduct from loan outstanding if applicable
                if (isset($resolved['loan_id'])) {
                    $loan = Loan::find($resolved['loan_id']);
                    if ($loan) {
                        $newBalance = max(0, (float)$loan->outstanding_balance - $record->amount);
                        $loan->update([
                            'outstanding_balance' => $newBalance,
                            'status'              => $newBalance <= 0 ? 'closed' : $loan->status,
                        ]);
                    }
                }

                $record->update(['status' => 'posted', 'transaction_id' => $tx->id]);
                $posted++;
            } catch (\Throwable $e) {
                $record->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            }
        }

        $file->update(['status' => 'posted', 'posted_records' => $posted]);
    }

    /**
     * Resolve an identifier to account/loan IDs.
     */
    private function resolveIdentifier(string $type, string $value, string $tenantId): ?array
    {
        return match ($type) {
            'LOAN_ACCOUNT_NUMBER' => $this->resolveByLoanNumber($value, $tenantId),
            'ACCOUNT_NUMBER'      => $this->resolveByAccountNumber($value, $tenantId),
            'BVN'                 => $this->resolveByBvn($value, $tenantId),
            'NIN'                 => $this->resolveByNin($value, $tenantId),
            default               => null,
        };
    }

    private function resolveByLoanNumber(string $value, string $tenantId): ?array
    {
        $loan = Loan::where('tenant_id', $tenantId)->where('loan_number', $value)->whereIn('status', ['active', 'overdue'])->first();
        return $loan ? ['account_id' => $loan->account_id, 'loan_id' => $loan->id] : null;
    }

    private function resolveByAccountNumber(string $value, string $tenantId): ?array
    {
        $account = Account::where('tenant_id', $tenantId)->where('account_number', $value)->where('status', 'active')->first();
        return $account ? ['account_id' => $account->id] : null;
    }

    private function resolveByBvn(string $value, string $tenantId): ?array
    {
        $customer = Customer::where('tenant_id', $tenantId)->where('bvn', $value)->first();
        if (!$customer) return null;
        $account = Account::where('tenant_id', $tenantId)->where('customer_id', $customer->id)->where('status', 'active')->first();
        return $account ? ['account_id' => $account->id] : null;
    }

    private function resolveByNin(string $value, string $tenantId): ?array
    {
        $customer = Customer::where('tenant_id', $tenantId)->where('nin', $value)->first();
        if (!$customer) return null;
        $account = Account::where('tenant_id', $tenantId)->where('customer_id', $customer->id)->where('status', 'active')->first();
        return $account ? ['account_id' => $account->id] : null;
    }
}
