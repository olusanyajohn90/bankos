<?php

$tenant = \App\Models\Tenant::first();

if (!$tenant) {
    echo "No tenant found.\n";
    exit;
}

\App\Models\LoanProduct::firstOrCreate(
    ['code' => 'LNP-001'],
    [
        'tenant_id' => $tenant->id,
        'name' => 'Small Business SME Loan',
        'interest_rate' => 5.0,
        'interest_method' => 'flat',
        'duration_type' => 'months',
        'min_amount' => 50000,
        'max_amount' => 5000000,
        'min_duration' => 1,
        'max_duration' => 24,
        'status' => 'active'
    ]
);

echo "Loan product seeded successfully.\n";
