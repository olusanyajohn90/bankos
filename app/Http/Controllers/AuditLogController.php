<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    /** GET /audit-log — paginated list with filters. */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = DB::table('financial_audit_log as a')
            ->where('a.tenant_id', $tenantId)
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->select(
                'a.id', 'a.entity_type', 'a.entity_id', 'a.action',
                'a.before_state', 'a.after_state', 'a.ip_address',
                'a.user_agent', 'a.request_url', 'a.metadata',
                'a.customer_id', 'a.created_at',
                'u.name as actor_name', 'u.email as actor_email'
            )
            ->orderBy('a.created_at', 'desc');

        // Filters
        if ($request->filled('entity_type')) {
            $query->where('a.entity_type', $request->entity_type);
        }

        if ($request->filled('action')) {
            $query->where('a.action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('a.user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->where('a.created_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('a.created_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('u.name', 'like', "%{$q}%")
                    ->orWhere('u.email', 'like', "%{$q}%")
                    ->orWhere('a.entity_id', 'like', "%{$q}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        // Decode JSON states for display
        $logs->getCollection()->transform(function ($row) {
            $row->before_state = $row->before_state ? json_decode($row->before_state, true) : [];
            $row->after_state  = $row->after_state  ? json_decode($row->after_state, true)  : [];
            $row->metadata     = $row->metadata     ? json_decode($row->metadata, true)     : [];
            return $row;
        });

        // Entity types and actions for filter dropdowns
        $entityTypes = DB::table('financial_audit_log')
            ->where('tenant_id', $tenantId)
            ->distinct()
            ->pluck('entity_type')
            ->sort()
            ->values();

        $actions = DB::table('financial_audit_log')
            ->where('tenant_id', $tenantId)
            ->distinct()
            ->pluck('action')
            ->sort()
            ->values();

        $users = DB::table('users')
            ->where('tenant_id', $tenantId)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('audit-log.index', compact('logs', 'entityTypes', 'actions', 'users'));
    }

    /** GET /audit-log/export — CSV download. */
    public function export(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = DB::table('financial_audit_log as a')
            ->where('a.tenant_id', $tenantId)
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->select(
                'a.created_at', 'u.name as actor', 'u.email as actor_email',
                'a.entity_type', 'a.entity_id', 'a.action',
                'a.before_state', 'a.after_state', 'a.ip_address', 'a.request_url'
            )
            ->orderBy('a.created_at', 'desc');

        if ($request->filled('entity_type')) {
            $query->where('a.entity_type', $request->entity_type);
        }
        if ($request->filled('action')) {
            $query->where('a.action', $request->action);
        }
        if ($request->filled('date_from')) {
            $query->where('a.created_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('a.created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $rows = $query->limit(10000)->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-log-' . now()->format('Ymd-His') . '.csv"',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Timestamp', 'Actor', 'Email', 'Entity Type', 'Entity ID', 'Action', 'Before State', 'After State', 'IP Address', 'URL']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->created_at,
                    $row->actor ?? 'System',
                    $row->actor_email ?? '',
                    $row->entity_type,
                    $row->entity_id,
                    $row->action,
                    $row->before_state ?? '',
                    $row->after_state ?? '',
                    $row->ip_address,
                    $row->request_url ?? '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
