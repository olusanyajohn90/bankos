<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    /**
     * Login — supports guard=customer (default) or guard=staff.
     * Rate limit: 5 attempts/minute (applied at route level).
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'required|string|max:255',
            'guard'       => 'sometimes|in:customer,staff',
        ]);

        $guard = $request->input('guard', 'customer');

        if ($guard === 'staff') {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            if ($user->status !== 'active') {
                return $this->error('Account is not active.', 403);
            }

            $token = $user->createToken($request->device_name)->plainTextToken;

            return $this->success([
                'token' => $token,
                'type'  => 'Bearer',
                'guard' => 'staff',
                'user'  => [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'tenant_id' => $user->tenant_id,
                ],
            ], 'Login successful');
        }

        // Customer guard
        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->portal_password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$customer->portal_active) {
            return $this->error('Portal access is not active for this account.', 403);
        }

        if ($customer->status !== 'active') {
            return $this->error('Customer account is not active.', 403);
        }

        // Revoke existing mobile tokens to enforce single-session
        $customer->tokens()->where('name', $request->device_name)->delete();

        $token = $customer->createToken($request->device_name)->plainTextToken;

        $customer->update(['last_login_at' => now()]);

        return $this->success([
            'token'    => $token,
            'type'     => 'Bearer',
            'guard'    => 'customer',
            'customer' => new CustomerResource($customer),
        ], 'Login successful');
    }

    /**
     * Forgot password — sends reset link (stub; returns success to avoid enumeration).
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        // In production: dispatch a password-reset notification job here.
        // We return a generic message to prevent user enumeration.
        return $this->success(null, 'If that email exists, a reset link has been sent.');
    }

    /**
     * Logout — revoke the current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * Refresh — revoke current token and issue a new one.
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $request->validate(['device_name' => 'sometimes|string|max:255']);

        $currentToken = $request->user()->currentAccessToken();
        $deviceName   = $request->input('device_name', $currentToken->name);

        $currentToken->delete();

        $newToken = $request->user()->createToken($deviceName)->plainTextToken;

        return $this->success([
            'token' => $newToken,
            'type'  => 'Bearer',
        ], 'Token refreshed');
    }

    /**
     * Me — return the authenticated user/customer profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user instanceof Customer) {
            return $this->success(new CustomerResource($user), 'Profile retrieved');
        }

        return $this->success([
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'tenant_id' => $user->tenant_id,
            'guard'     => 'staff',
        ], 'Profile retrieved');
    }
}
