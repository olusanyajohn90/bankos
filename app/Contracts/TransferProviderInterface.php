<?php

namespace App\Contracts;

interface TransferProviderInterface
{
    /**
     * Perform a name enquiry / account verification.
     *
     * @return array{success: bool, account_name?: string, account_number?: string, bank_code?: string, session_id?: string, message?: string}
     */
    public function nameEnquiry(string $accountNumber, string $bankCode, array $config): array;

    /**
     * Initiate an outward transfer.
     *
     * @return array{success: bool, message: string, reference?: string, provider_reference?: string}
     */
    public function initiateTransfer(array $transferData, array $config): array;

    /**
     * Query the status of a previously initiated transfer.
     *
     * @return array{success: bool, status: string, message?: string}
     */
    public function queryStatus(string $reference, array $config): array;

    /**
     * Return the provider's short code (e.g. 'nip', 'paystack').
     */
    public function getProviderCode(): string;

    /**
     * Return the list of supported destination banks.
     *
     * @return array<int, array{code: string, name: string}>
     */
    public function getSupportedBanks(array $config): array;
}
