<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImpersonateController extends Controller
{
    public function handle(string $token)
    {
        $record = DB::table('superadmin_impersonations')
            ->where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            abort(403, 'Invalid or expired impersonation token.');
        }

        // Mark as used
        DB::table('superadmin_impersonations')
            ->where('token', $token)
            ->update(['used_at' => now()]);

        // Log in the target user
        $user = \App\Models\User::find($record->user_id);
        if (!$user || $user->status !== 'active') {
            abort(403, 'Target user is not available.');
        }

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'SuperAdmin impersonation session started. You are now logged in as ' . $user->name . '.');
    }
}
