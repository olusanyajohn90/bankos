<?php

namespace App\Http\Controllers;

use App\Services\CustomReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomReportController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;

        $reports = DB::table('custom_reports')
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->get();

        // Attach schedule info
        $schedules = DB::table('custom_report_schedules')
            ->whereIn('report_id', $reports->pluck('id'))
            ->where('is_active', true)
            ->get()
            ->keyBy('report_id');

        return view('custom-reports.index', compact('reports', 'schedules'));
    }

    public function create()
    {
        $dataSources = CustomReportService::DATA_SOURCES;
        return view('custom-reports.create', compact('dataSources'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string',
            'data_source'      => 'required|in:' . implode(',', array_keys(CustomReportService::DATA_SOURCES)),
            'selected_columns' => 'required|array|min:1',
            'filters'          => 'nullable|array',
            'sort_column'      => 'nullable|string',
            'sort_direction'   => 'nullable|in:asc,desc',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $id       = (string) Str::uuid();

        DB::table('custom_reports')->insert([
            'id'               => $id,
            'tenant_id'        => $tenantId,
            'name'             => $r->name,
            'description'      => $r->description,
            'data_source'      => $r->data_source,
            'selected_columns' => json_encode($r->selected_columns),
            'filters'          => json_encode($r->filters ?? []),
            'sort_column'      => $r->sort_column,
            'sort_direction'   => $r->sort_direction ?? 'asc',
            'created_by'       => Auth::id(),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('custom-reports.show', $id)->with('success', 'Report saved successfully.');
    }

    public function show(string $id)
    {
        $tenantId = Auth::user()->tenant_id;
        $report   = DB::table('custom_reports')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();
        abort_if(!$report, 404);

        $data = CustomReportService::run($tenantId, $report);

        // Update last_run stats
        DB::table('custom_reports')->where('id', $id)->update([
            'last_run_at'        => now(),
            'last_run_row_count' => $data->count(),
            'updated_at'         => now(),
        ]);

        // Paginate manually
        $perPage    = 50;
        $page       = request()->get('page', 1);
        $rows       = $data->forPage($page, $perPage);
        $total      = $data->count();
        $totalPages = (int) ceil($total / $perPage);

        $schedule = DB::table('custom_report_schedules')
            ->where('report_id', $id)
            ->first();

        $columns = $data->isNotEmpty() ? array_keys((array) $data->first()) : [];

        return view('custom-reports.show', compact(
            'report', 'rows', 'columns', 'total', 'page', 'perPage', 'totalPages', 'schedule'
        ));
    }

    public function export(Request $r, string $id)
    {
        $tenantId = Auth::user()->tenant_id;
        $report   = DB::table('custom_reports')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();
        abort_if(!$report, 404);

        $data   = CustomReportService::run($tenantId, $report);
        $format = $r->query('format', 'csv');

        if ($format === 'pdf') {
            $columns = $data->isNotEmpty() ? array_keys((array) $data->first()) : [];
            $rows    = $data;
            $pdf = Pdf::loadView('custom-reports.pdf', compact('report', 'rows', 'columns'))
                ->setPaper('a4', 'landscape');
            return $pdf->download(Str::slug($report->name) . '.pdf');
        }

        // Default: CSV
        $csv      = CustomReportService::toCsv($data, $report);
        $filename = Str::slug($report->name) . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function destroy(string $id)
    {
        $tenantId = Auth::user()->tenant_id;
        DB::table('custom_reports')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();

        return redirect()->route('custom-reports.index')->with('success', 'Report deleted.');
    }

    public function schedule(Request $r, string $id)
    {
        $r->validate([
            'frequency'    => 'required|in:daily,weekly,monthly',
            'day_of_week'  => 'nullable|integer|min:0|max:6',
            'day_of_month' => 'nullable|integer|min:1|max:28',
            'time'         => 'required|regex:/^\d{2}:\d{2}$/',
            'recipients'   => 'required|string',
            'format'       => 'required|in:csv,pdf',
        ]);

        $tenantId   = Auth::user()->tenant_id;
        $report     = DB::table('custom_reports')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();
        abort_if(!$report, 404);

        $recipients = array_filter(array_map('trim', explode(',', $r->recipients)));
        $recipients = array_values(array_filter($recipients, fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL)));

        // Upsert schedule
        $existing = DB::table('custom_report_schedules')->where('report_id', $id)->first();
        $schedData = [
            'report_id'    => $id,
            'tenant_id'    => $tenantId,
            'frequency'    => $r->frequency,
            'day_of_week'  => $r->day_of_week,
            'day_of_month' => $r->day_of_month,
            'time'         => $r->time,
            'recipients'   => json_encode($recipients),
            'format'       => $r->format,
            'is_active'    => true,
            'updated_at'   => now(),
        ];

        if ($existing) {
            DB::table('custom_report_schedules')->where('id', $existing->id)->update($schedData);
        } else {
            $schedData['id']         = (string) Str::uuid();
            $schedData['created_at'] = now();
            DB::table('custom_report_schedules')->insert($schedData);
        }

        return back()->with('success', 'Schedule saved.');
    }

    public function unschedule(string $id)
    {
        $tenantId = Auth::user()->tenant_id;
        $report   = DB::table('custom_reports')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();
        abort_if(!$report, 404);

        DB::table('custom_report_schedules')->where('report_id', $id)->delete();

        return back()->with('success', 'Schedule removed.');
    }

    public function preview(Request $r)
    {
        $r->validate([
            'data_source'      => 'required|in:' . implode(',', array_keys(CustomReportService::DATA_SOURCES)),
            'selected_columns' => 'nullable|array',
            'filters'          => 'nullable|array',
            'sort_column'      => 'nullable|string',
            'sort_direction'   => 'nullable|in:asc,desc',
        ]);

        $tenantId = Auth::user()->tenant_id;

        $tempReport = (object) [
            'data_source'      => $r->data_source,
            'selected_columns' => $r->selected_columns ?? CustomReportService::available($r->data_source),
            'filters'          => $r->filters ?? [],
            'sort_column'      => $r->sort_column,
            'sort_direction'   => $r->sort_direction ?? 'asc',
        ];

        $data    = CustomReportService::run($tenantId, $tempReport);
        $preview = $data->take(20);
        $columns = $preview->isNotEmpty() ? array_keys((array) $preview->first()) : [];

        return response()->json([
            'columns' => $columns,
            'rows'    => $preview->values(),
            'total'   => $data->count(),
        ]);
    }
}
