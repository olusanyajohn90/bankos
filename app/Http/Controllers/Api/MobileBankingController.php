<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileBankingController extends Controller
{
    // ── API endpoints (JSON) ────────────────────────────────────────

    public function devices(Request $request)
    {
        try {
            $customerId = $request->user()->id ?? null;
            $devices = MobileDevice::where('customer_id', $customerId)
                ->orderByDesc('last_active_at')
                ->get();

            return response()->json(['data' => $devices]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function registerDevice(Request $request)
    {
        $request->validate([
            'device_id'   => 'required|string|max:200',
            'device_name' => 'nullable|string|max:200',
            'platform'    => 'required|in:ios,android,web',
            'push_token'  => 'nullable|string|max:500',
            'app_version' => 'nullable|string|max:20',
        ]);

        try {
            $customerId = $request->user()->id;

            $device = MobileDevice::updateOrCreate(
                ['customer_id' => $customerId, 'device_id' => $request->device_id],
                [
                    'device_name'    => $request->device_name,
                    'platform'       => $request->platform,
                    'push_token'     => $request->push_token,
                    'app_version'    => $request->app_version,
                    'is_active'      => true,
                    'last_active_at' => now(),
                ]
            );

            return response()->json(['data' => $device, 'message' => 'Device registered.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── Admin web views ─────────────────────────────────────────────

    public function mobileDashboard()
    {
        try {
            $totalDevices = MobileDevice::count();
            $activeDevices = MobileDevice::where('is_active', true)->count();
            $inactiveDevices = $totalDevices - $activeDevices;

            // Platform breakdown
            $byPlatform = MobileDevice::select('platform', DB::raw('COUNT(*) as count'))
                ->groupBy('platform')
                ->get();

            // Active in last 7 days
            $recentActive = MobileDevice::where('last_active_at', '>=', now()->subDays(7))->count();

            // Active in last 30 days
            $monthlyActive = MobileDevice::where('last_active_at', '>=', now()->subDays(30))->count();

            // Registrations trend (last 30 days)
            $registrationTrend = MobileDevice::where('created_at', '>=', now()->subDays(30))
                ->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD') as date"),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD')"))
                ->orderBy('date')
                ->get();

            // App version distribution
            $byVersion = MobileDevice::whereNotNull('app_version')
                ->select('app_version', DB::raw('COUNT(*) as count'))
                ->groupBy('app_version')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            // Recent registrations
            $recentDevices = MobileDevice::with('customer')
                ->latest()
                ->limit(10)
                ->get();

        } catch (\Exception $e) {
            return view('mobile.dashboard', [
                'error' => $e->getMessage(),
                'totalDevices' => 0, 'activeDevices' => 0, 'inactiveDevices' => 0,
                'byPlatform' => collect(), 'recentActive' => 0, 'monthlyActive' => 0,
                'registrationTrend' => collect(), 'byVersion' => collect(), 'recentDevices' => collect(),
            ]);
        }

        return view('mobile.dashboard', compact(
            'totalDevices', 'activeDevices', 'inactiveDevices', 'byPlatform',
            'recentActive', 'monthlyActive', 'registrationTrend', 'byVersion', 'recentDevices'
        ));
    }
}
