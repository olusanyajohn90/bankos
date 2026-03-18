<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $logs = NotificationLog::with('customer')
            ->when($request->channel, fn($q) => $q->where('channel', $request->channel))
            ->when($request->event,   fn($q) => $q->where('event', $request->event))
            ->when($request->status,  fn($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(25);

        $events   = NotificationService::EVENTS;
        $channels = NotificationService::CHANNELS;

        return view('notifications.index', compact('logs', 'events', 'channels'));
    }

    public function templates()
    {
        $templates = NotificationTemplate::orderBy('event')->orderBy('channel')->get();
        $events    = NotificationService::EVENTS;
        $channels  = NotificationService::CHANNELS;

        return view('notifications.templates', compact('templates', 'events', 'channels'));
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'event'   => 'required|string',
            'channel' => 'required|in:sms,whatsapp,email,push',
            'subject' => 'nullable|string|max:255',
            'body'    => 'required|string',
            'active'  => 'boolean',
        ]);

        $tenantId = auth()->user()->tenant_id;

        NotificationTemplate::updateOrCreate(
            ['tenant_id' => $tenantId, 'event' => $validated['event'], 'channel' => $validated['channel']],
            [
                'subject' => $validated['subject'] ?? null,
                'body'    => $validated['body'],
                'active'  => $request->boolean('active', true),
            ]
        );

        return back()->with('success', 'Template saved successfully.');
    }

    public function destroyTemplate(NotificationTemplate $template)
    {
        $template->delete();
        return back()->with('success', 'Template deleted.');
    }
}
