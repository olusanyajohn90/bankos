<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateToPostgres extends Command
{
    protected $signature = 'db:migrate-to-postgres
                            {--source=mysql : Source connection name}
                            {--target=pgsql : Target connection name}
                            {--chunk=500 : Rows per chunk}
                            {--tables= : Comma-separated list of specific tables (default: all)}';

    protected $description = 'Copy all data from MySQL to PostgreSQL, table by table';

    /** @var array<string,list<string>> Boolean column names per table on target */
    private array $booleanColumns = [];

    public function handle(): int
    {
        $source = $this->option('source');
        $target = $this->option('target');
        $chunk  = (int) $this->option('chunk');

        $this->info("Source : {$source}");
        $this->info("Target : {$target}");
        $this->info("Chunk  : {$chunk} rows");
        $this->newLine();

        // Verify connections
        try {
            DB::connection($source)->getPdo();
            $this->info("✓ Connected to source ({$source})");
        } catch (\Exception $e) {
            $this->error('Cannot connect to source: ' . $e->getMessage());
            return self::FAILURE;
        }
        try {
            DB::connection($target)->getPdo();
            $this->info("✓ Connected to target ({$target})");
        } catch (\Exception $e) {
            $this->error('Cannot connect to target: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();

        // Resolve table list
        $allTables = $this->getSourceTables($source);

        if ($this->option('tables')) {
            $only      = array_map('trim', explode(',', $this->option('tables')));
            $allTables = array_values(array_filter($allTables, fn ($t) => in_array($t, $only, true)));
        } else {
            // Always skip migrations table — target already has correct history
            $allTables = array_values(array_filter($allTables, fn ($t) => $t !== 'migrations'));
        }

        $this->info('Tables to copy: ' . count($allTables));
        $this->newLine();

        // Pre-load boolean columns for all target tables so we can cast 0/1 → true/false
        $this->loadBooleanColumns($target, $allTables);

        // Disable FK constraint enforcement on PostgreSQL target
        DB::connection($target)->statement("SET session_replication_role = 'replica'");
        $this->line('<fg=yellow>FK constraints disabled on target</>');
        $this->newLine();

        $errors = [];

        foreach ($allTables as $index => $table) {
            $num = $index + 1;
            $pad = str_pad($num, strlen((string) count($allTables)), ' ', STR_PAD_LEFT);

            try {
                $total = DB::connection($source)->table($table)->count();

                if ($total === 0) {
                    $this->line("[{$pad}] <fg=gray>{$table}</> — empty, skipped");
                    continue;
                }

                // Wipe target table.
                // Do NOT use ->truncate() on PostgreSQL — it emits TRUNCATE ... CASCADE which
                // wipes FK-dependent child tables when a parent table is processed later.
                // DELETE FROM is non-cascading and its FK trigger checks are already disabled
                // by the session_replication_role = replica we set above.
                $targetDriver = DB::connection($target)->getDriverName();
                if ($targetDriver === 'pgsql') {
                    DB::connection($target)->statement("DELETE FROM \"{$table}\"");
                } else {
                    DB::connection($target)->table($table)->truncate();
                }

                $copied = 0;
                $offset = 0;

                while (true) {
                    $rows = DB::connection($source)
                        ->table($table)
                        ->skip($offset)
                        ->take($chunk)
                        ->get();

                    if ($rows->isEmpty()) {
                        break;
                    }

                    $batch = array_map(
                        fn ($row) => $this->castRow((array) $row, $table),
                        $rows->all()
                    );

                    DB::connection($target)->table($table)->insert($batch);

                    $copied += count($batch);
                    $offset += $chunk;

                    if ($offset < $total) {
                        $pct = round(($copied / $total) * 100);
                        $this->line("[{$pad}] <fg=cyan>{$table}</> — {$copied}/{$total} ({$pct}%)", null, 'v');
                    }
                }

                $this->line("[{$pad}] <fg=green>✓ {$table}</> — {$copied} rows");

            } catch (\Exception $e) {
                $errors[$table] = $e->getMessage();
                $this->line("[{$pad}] <fg=red>✗ {$table}</> — " . $e->getMessage());
            }
        }

        $this->newLine();

        // Re-enable FK constraints
        DB::connection($target)->statement("SET session_replication_role = DEFAULT");
        $this->line('<fg=yellow>FK constraints re-enabled on target</>');
        $this->newLine();

        if (empty($errors)) {
            $this->info('All tables copied successfully.');
            return self::SUCCESS;
        }

        $this->warn(count($errors) . ' table(s) had errors:');
        foreach ($errors as $table => $msg) {
            $this->line("  <fg=red>{$table}</>: {$msg}");
        }
        return self::FAILURE;
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────

    private function getSourceTables(string $connection): array
    {
        $driver = DB::connection($connection)->getDriverName();

        if ($driver === 'pgsql') {
            $rows = DB::connection($connection)
                ->select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
            return array_column(array_map(fn ($r) => (array) $r, $rows), 'tablename');
        }

        // MySQL
        $database = DB::connection($connection)->getDatabaseName();
        $rows     = DB::connection($connection)
            ->select(
                'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME',
                [$database]
            );
        return array_column(array_map(fn ($r) => (array) $r, $rows), 'TABLE_NAME');
    }

    /**
     * Pre-fetch which columns are typed as boolean in the PostgreSQL target.
     * MySQL stores booleans as TINYINT(1) and returns them as integers (0/1).
     * PostgreSQL's PDO driver rejects integer values for boolean columns, so we cast.
     */
    private function loadBooleanColumns(string $target, array $tables): void
    {
        if (DB::connection($target)->getDriverName() !== 'pgsql') {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($tables), '?'));

        $rows = DB::connection($target)->select(
            "SELECT table_name, column_name
             FROM information_schema.columns
             WHERE table_schema = 'public'
               AND table_name IN ({$placeholders})
               AND data_type = 'boolean'",
            $tables
        );

        foreach ($rows as $row) {
            $this->booleanColumns[$row->table_name][] = $row->column_name;
        }
    }

    /**
     * Cast a MySQL row for insertion into PostgreSQL.
     * Converts integer 0/1 values to PHP booleans for boolean-typed columns.
     */
    private function castRow(array $row, string $table): array
    {
        $boolCols = $this->booleanColumns[$table] ?? [];

        if (empty($boolCols)) {
            return $row;
        }

        foreach ($boolCols as $col) {
            if (array_key_exists($col, $row) && $row[$col] !== null) {
                $row[$col] = (bool) $row[$col];
            }
        }

        return $row;
    }
}
