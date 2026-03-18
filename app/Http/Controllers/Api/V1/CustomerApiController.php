<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerApiController extends BaseApiController
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
     * Get authenticated customer profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        return $this->success(new CustomerResource($customer), 'Profile retrieved');
    }

    /**
     * Update profile — name and phone only (not email/sensitive without KYC).
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name'  => 'sometimes|string|max:100',
            'last_name'   => 'sometimes|string|max:100',
            'middle_name' => 'sometimes|string|max:100|nullable',
            'phone'       => 'sometimes|string|max:20',
        ]);

        $customer = $this->resolveCustomer($request);

        // Filter to only safe-to-update fields (not email/BVN/NIN)
        $safeFields = array_intersect_key($data, array_flip(['first_name', 'last_name', 'middle_name', 'phone']));

        if (empty($safeFields)) {
            return $this->error('No updatable fields provided.', 422);
        }

        $customer->update($safeFields);

        return $this->success(new CustomerResource($customer->fresh()), 'Profile updated');
    }

    /**
     * KYC tier and status.
     */
    public function kycStatus(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        return $this->success([
            'kyc_tier'     => $customer->kyc_tier,
            'kyc_status'   => $customer->kyc_status,
            'bvn_verified' => (bool) $customer->bvn_verified,
            'nin_verified' => (bool) $customer->nin_verified,
        ], 'KYC status retrieved');
    }

    /**
     * Change portal PIN.
     */
    public function changePin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'old_pin'      => 'required|string|min:4',
            'new_pin'      => 'required|string|min:4|different:old_pin',
            'confirm_pin'  => 'required|string|same:new_pin',
        ]);

        $customer = $this->resolveCustomer($request);

        if (!Hash::check($data['old_pin'], $customer->portal_pin)) {
            return $this->error('Current PIN is incorrect.', 403);
        }

        $customer->update([
            'portal_pin' => Hash::make($data['new_pin']),
        ]);

        // Revoke all tokens to force re-login on other devices
        $customer->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return $this->success(null, 'PIN changed successfully. Please log in again on other devices.');
    }
}
