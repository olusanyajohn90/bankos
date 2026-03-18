<?php
$base = __DIR__ . '/../database/migrations/';
$fixes = [
    '2024_01_01_000012_create_kyc_documents_table.php' => ['reviewed_by'],
    '2026_03_10_000002_create_loan_restructures_table.php' => ['reviewed_by'],
    '2024_01_01_000009_create_transactions_table.php' => ['performed_by'],
    '2026_03_10_000001_create_loan_liquidations_table.php' => ['processed_by'],
    '2026_03_15_200001_create_proxy_actions_log_table.php' => ['actor_id'],
    '2026_03_18_000001_create_platform_events_and_super_admin_log.php' => ['actor_id'],
    '2026_03_19_100001_create_aml_tables.php' => ['assigned_to', 'reviewed_by', 'reporting_officer'],
    '2026_03_19_300003_create_ip_whitelist_table.php' => ['created_by'],
    '2026_03_19_500001_add_approved_fields_to_loans.php' => ['approved_by', 'rejected_by'],
];
foreach ($fixes as $filename => $columns) {
    $path = $base . $filename;
    $content = file_get_contents($path);
    foreach ($columns as $col) {
        $content = str_replace("->uuid('" . $col . "')", "->unsignedBigInteger('" . $col . "')->nullable()", $content);
        $content = str_replace('->uuid("' . $col . '")', '->unsignedBigInteger("' . $col . '")->nullable()', $content);
    }
    file_put_contents($path, $content);
    echo 'Fixed: ' . $filename . ' [' . implode(', ', $columns) . ']' . PHP_EOL;
}
echo 'Done.' . PHP_EOL;
