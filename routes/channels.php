<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
 * Customer portal channel — used by TransactionCompleted event.
 * Authenticated as the bankos-portal customer guard.
 */
Broadcast::channel('customer.{customerId}', function ($user, $customerId) {
    return $user->id === $customerId;
});

/*
 * Tenant compliance channel — used by AmlAlertCreated event.
 * Only staff with the 'view-aml-alerts' permission can listen.
 */
Broadcast::channel('tenant.{tenantId}.compliance', function ($user, $tenantId) {
    return $user->tenant_id === $tenantId && $user->can('view-aml-alerts');
});
