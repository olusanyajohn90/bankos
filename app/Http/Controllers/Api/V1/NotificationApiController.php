<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\NotificationResource;
use App\Models\Customer;
use App\Models\NotificationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationApiController extends BaseApiController
{
    private function resolveCustomer(Request $request): Customer
    {
        $user = $request->user();
        if ($user instanceof Customer) {
            return $user;
        }
        abort(401, 'Customer authentication required.');
    }

    /**
     * Paginated notifications list.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['per_page' => 'sometimes|integer|min:5|max:100']);

        $customer = $this->resolveCustomer($request);

        $perPage       = $request->integer('per_page', 20);
        $notifications = NotificationLog::where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->latest()
            ->paginate($perPage);

        $paginated = $notifications->through(fn ($n) => new NotificationResource($n));

        return $this->paginated($paginated, 'Notifications retrieved');
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        $notification = NotificationLog::where('id', $id)
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->firstOrFail();

        // NotificationLog doesn't have read_at by default; we update status as a proxy.
        if (!isset($notification->read_at)) {
            $notification->update(['status' => 'read']);
        }

        return $this->success(new NotificationResource($notification->fresh()), 'Notification marked as read');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        NotificationLog::where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->where('status', '!=', 'read')
            ->update(['status' => 'read']);

        return $this->success(null, 'All notifications marked as read');
    }

    /**
     * Register FCM token for push notifications.
     */
    public function fcmSubscribe(Request $request): JsonResponse
    {
        $request->validate(['fcm_token' => 'required|string|max:512']);

        $customer = $this->resolveCustomer($request);

        // Store the FCM token on the customer record.
        // The column `fcm_token` should exist on customers table; gracefully handle if absent.
        try {
            $customer->update(['fcm_token' => $request->fcm_token]);
        } catch (\Exception $e) {
            // Column may not exist yet; log and continue
            \Illuminate\Support\Facades\Log::warning('fcm_token column missing on customers table', [
                'customer_id' => $customer->id,
            ]);
        }

        return $this->success([
            'fcm_token' => $request->fcm_token,
        ], 'FCM token registered');
    }
}
