<?php

namespace App\Console\Commands;

use App\Services\CustomReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SendScheduledReports extends Command
{
    protected $signature = 'reports:send-scheduled';
    protected $description = 'Send scheduled custom reports to their recipients';

    public function handle(): int
    {
        $now       = Carbon::now();
        $today     = $now->dayOfWeek;       // 0=Sunday
        $dayOfMonth = (int) $now->format('j');

        $schedules = DB::table('custom_report_schedules')
            ->where('is_active', true)
            ->get();

        $sent = 0;

        foreach ($schedules as $schedule) {
            if (! $this->isDue($schedule, $now, $today, $dayOfMonth)) {
                continue;
            }

            $report = DB::table('custom_reports')
                ->where('id', $schedule->report_id)
                ->first();

            if (! $report) {
                continue;
            }

            $tenantId  = $schedule->tenant_id;
            $data      = CustomReportService::run($tenantId, $report);
            $recipients = json_decode($schedule->recipients, true) ?? [];

            if (empty($recipients)) {
                continue;
            }

            if ($schedule->format === 'csv') {
                $content   = CustomReportService::toCsv($data, $report);
                $filename  = Str::slug($report->name) . '.csv';
                $mime      = 'text/csv';
            } else {
                $columns = $data->isNotEmpty() ? array_keys((array) $data->first()) : [];
                $rows    = $data;
                $content  = Pdf::loadView('custom-reports.pdf', compact('report', 'rows', 'columns'))
                    ->setPaper('a4', 'landscape')
                    ->output();
                $filename = Str::slug($report->name) . '.pdf';
                $mime     = 'application/pdf';
            }

            $periodLabel = $now->format('Y-m-d');

            foreach ($recipients as $email) {
                try {
                    Mail::raw(
                        "Please find attached your scheduled report: {$report->name}\n\nGenerated: {$periodLabel}\nRows: {$data->count()}",
                        function ($m) use ($email, $report, $content, $filename, $mime) {
                            $m->to($email)
                              ->subject("Scheduled Report: {$report->name}")
                              ->attachData($content, $filename, ['mime' => $mime]);
                        }
                    );
                } catch (\Throwable $e) {
                    $this->warn("Failed to send to {$email}: {$e->getMessage()}");
                }
            }

            // Update last_sent_at
            DB::table('custom_report_schedules')
                ->where('id', $schedule->id)
                ->update(['last_sent_at' => now(), 'updated_at' => now()]);

            $sent++;
            $this->info("Sent report [{$report->name}] to " . count($recipients) . ' recipient(s).');
        }

        $this->info("Done. {$sent} scheduled report(s) sent.");
        return 0;
    }

    private function isDue(object $schedule, Carbon $now, int $todayDow, int $dayOfMonth): bool
    {
        // Check time window (within the current hour, or just run all that match time)
        [$hour, $minute] = explode(':', $schedule->time);
        if ((int) $hour !== (int) $now->format('G')) {
            return false;
        }

        return match ($schedule->frequency) {
            'daily'   => true,
            'weekly'  => $schedule->day_of_week !== null && (int) $schedule->day_of_week === $todayDow,
            'monthly' => $schedule->day_of_month !== null && (int) $schedule->day_of_month === $dayOfMonth,
            default   => false,
        };
    }
}
