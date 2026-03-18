<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class CustomReportService
{
    const DATA_SOURCES = [
        'customers' => [
            'columns' => ['id', 'customer_number', 'first_name', 'last_name', 'email', 'phone', 'kyc_tier', 'kyc_status', 'portal_active', 'created_at', 'branch_id'],
            'label'   => 'Customers',
            'table'   => 'customers',
        ],
        'accounts' => [
            'columns' => ['id', 'account_number', 'account_name', 'type', 'currency', 'available_balance', 'ledger_balance', 'status', 'created_at'],
            'label'   => 'Accounts',
            'table'   => 'accounts',
        ],
        'transactions' => [
            'columns' => ['id', 'reference', 'type', 'amount', 'currency', 'description', 'status', 'created_at'],
            'label'   => 'Transactions',
            'table'   => 'transactions',
        ],
        'loans' => [
            'columns' => ['id', 'loan_number', 'principal_amount', 'outstanding_balance', 'status', 'interest_rate', 'disbursed_at', 'expected_maturity_date'],
            'label'   => 'Loans',
            'table'   => 'loans',
        ],
    ];

    const FILTER_OPERATORS = [
        'equals', 'not_equals', 'contains', 'greater_than', 'less_than', 'between', 'is_null', 'is_not_null', 'in_last_days',
    ];

    public static function available(string $dataSource): array
    {
        return self::DATA_SOURCES[$dataSource]['columns'] ?? [];
    }

    public static function run(string $tenantId, object $report): Collection
    {
        $source     = $report->data_source;
        $sourceConf = self::DATA_SOURCES[$source] ?? null;
        if (! $sourceConf) {
            return collect();
        }

        $table           = $sourceConf['table'];
        $allowedColumns  = $sourceConf['columns'];
        $selectedColumns = is_array($report->selected_columns)
            ? $report->selected_columns
            : json_decode($report->selected_columns, true) ?? $allowedColumns;

        // Whitelist columns
        $selectedColumns = array_intersect($selectedColumns, $allowedColumns);
        if (empty($selectedColumns)) {
            $selectedColumns = $allowedColumns;
        }

        $selectCols = array_map(fn($c) => "{$table}.{$c}", $selectedColumns);

        $query = DB::table($table)->select($selectCols);

        // Always scope to tenant_id
        if (in_array('tenant_id', DB::getSchemaBuilder()->getColumnListing($table))) {
            $query->where("{$table}.tenant_id", $tenantId);
        }

        // Apply filters
        $filters = is_array($report->filters)
            ? $report->filters
            : json_decode($report->filters ?? '[]', true) ?? [];

        foreach ($filters as $filter) {
            $col      = $filter['column'] ?? null;
            $operator = $filter['operator'] ?? null;
            $value    = $filter['value'] ?? null;

            if (! $col || ! in_array($col, $allowedColumns)) {
                continue;
            }

            $colExpr = "{$table}.{$col}";

            switch ($operator) {
                case 'equals':
                    $query->where($colExpr, '=', $value);
                    break;
                case 'not_equals':
                    $query->where($colExpr, '!=', $value);
                    break;
                case 'contains':
                    $query->where($colExpr, 'like', "%{$value}%");
                    break;
                case 'greater_than':
                    $query->where($colExpr, '>', $value);
                    break;
                case 'less_than':
                    $query->where($colExpr, '<', $value);
                    break;
                case 'between':
                    $parts = is_array($value) ? $value : explode(',', $value);
                    if (count($parts) === 2) {
                        $query->whereBetween($colExpr, [trim($parts[0]), trim($parts[1])]);
                    }
                    break;
                case 'is_null':
                    $query->whereNull($colExpr);
                    break;
                case 'is_not_null':
                    $query->whereNotNull($colExpr);
                    break;
                case 'in_last_days':
                    $days = (int) $value;
                    $query->where($colExpr, '>=', Carbon::now()->subDays($days));
                    break;
            }
        }

        // Apply sorting
        if ($report->sort_column && in_array($report->sort_column, $allowedColumns)) {
            $dir = in_array($report->sort_direction, ['asc', 'desc']) ? $report->sort_direction : 'asc';
            $query->orderBy("{$table}.{$report->sort_column}", $dir);
        }

        return $query->limit(10000)->get();
    }

    public static function toCsv(Collection $data, object $report): string
    {
        if ($data->isEmpty()) {
            return '';
        }

        $rows   = $data->toArray();
        $first  = (array) $rows[0];
        $headers = array_keys($first);

        $csv = implode(',', array_map([self::class, 'csvEscape'], $headers)) . "\n";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map([self::class, 'csvEscape'], (array) $row)) . "\n";
        }

        return $csv;
    }

    private static function csvEscape(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        $str = (string) $value;
        if (str_contains($str, ',') || str_contains($str, '"') || str_contains($str, "\n")) {
            $str = '"' . str_replace('"', '""', $str) . '"';
        }
        return $str;
    }
}
