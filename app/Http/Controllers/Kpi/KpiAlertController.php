<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\KpiAlert;
use Illuminate\Http\Request;

class KpiAlertController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $alerts = KpiAlert::where('recipient_id', $user->id)
            ->when($request->severity, fn($q) => $q->where('severity', $request->severity))
            ->when($request->status,   fn($q) => $q->where('status', $request->status))
            ->with(['kpiTarget.kpiDefinition'])
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $unreadRed    = KpiAlert::where('recipient_id', $user->id)->where('severity', 'red')->where('status', 'unread')->count();
        $unreadYellow = KpiAlert::where('recipient_id', $user->id)->where('severity', 'yellow')->where('status', 'unread')->count();

        return view('kpi.alerts', compact('alerts', 'unreadRed', 'unreadYellow'));
    }

    public function markRead(KpiAlert $kpiAlert)
    {
        $this->authorize('update', $kpiAlert);
        $kpiAlert->markRead();
        return back()->with('success', 'Alert marked as read.');
    }

    public function dismiss(KpiAlert $kpiAlert)
    {
        $this->authorize('update', $kpiAlert);
        $kpiAlert->dismiss();
        return back()->with('success', 'Alert dismissed.');
    }

    public function markAllRead(Request $request)
    {
        KpiAlert::where('recipient_id', auth()->id())
            ->where('status', 'unread')
            ->update(['status' => 'read', 'read_at' => now()]);

        return back()->with('success', 'All alerts marked as read.');
    }
}
