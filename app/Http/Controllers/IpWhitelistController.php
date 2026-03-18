<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IpWhitelistController extends Controller
{
    /** GET /ip-whitelist — list entries with current user IP highlighted. */
    public function index(Request $request)
    {
        $tenantId  = auth()->user()->tenant_id;
        $currentIp = $request->ip();

        $entries = DB::table('tenant_ip_whitelist as w')
            ->where('w.tenant_id', $tenantId)
            ->leftJoin('users as u', 'u.id', '=', 'w.created_by')
            ->select('w.*', 'u.name as created_by_name')
            ->orderBy('w.created_at', 'desc')
            ->get();

        $activeCount = $entries->where('is_active', true)->count();

        return view('ip-whitelist.index', compact('entries', 'currentIp', 'activeCount'));
    }

    /** POST /ip-whitelist — add a new IP entry. */
    public function store(Request $request)
    {
        $request->validate([
            'ip_address' => ['required', 'string', 'max:45', function ($attribute, $value, $fail) {
                if (!filter_var($value, FILTER_VALIDATE_IP)) {
                    $fail('The IP address is not valid (must be IPv4 or IPv6).');
                }
            }],
            'label' => 'required|string|max:100',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $exists = DB::table('tenant_ip_whitelist')
            ->where('tenant_id', $tenantId)
            ->where('ip_address', $request->ip_address)
            ->exists();

        if ($exists) {
            return back()->with('error', 'This IP address is already in the whitelist.');
        }

        DB::table('tenant_ip_whitelist')->insert([
            'id'         => (string) Str::uuid(),
            'tenant_id'  => $tenantId,
            'ip_address' => $request->ip_address,
            'label'      => $request->label,
            'is_active'  => true,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "IP address {$request->ip_address} ({$request->label}) added to whitelist.");
    }

    /** DELETE /ip-whitelist/{id} — remove an IP entry. */
    public function destroy(string $id)
    {
        $tenantId = auth()->user()->tenant_id;

        $deleted = DB::table('tenant_ip_whitelist')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();

        if (!$deleted) {
            return back()->with('error', 'Entry not found.');
        }

        return back()->with('success', 'IP address removed from whitelist.');
    }

    /** PATCH /ip-whitelist/{id}/toggle — enable/disable a specific entry. */
    public function toggle(string $id)
    {
        $tenantId = auth()->user()->tenant_id;

        $entry = DB::table('tenant_ip_whitelist')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$entry) {
            return back()->with('error', 'Entry not found.');
        }

        DB::table('tenant_ip_whitelist')
            ->where('id', $id)
            ->update([
                'is_active'  => !$entry->is_active,
                'updated_at' => now(),
            ]);

        $status = $entry->is_active ? 'disabled' : 'enabled';
        return back()->with('success', "IP address {$entry->ip_address} {$status}.");
    }
}
