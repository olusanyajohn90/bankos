<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     * Adds: account lockout, 2FA intercept, login tracking.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Find user by email first (for lockout checks)
        $user = User::where('email', $request->email)->first();

        // --- Account Lockout Check ---
        if ($user && $user->locked_until && now()->lt($user->locked_until)) {
            $minutesLeft = (int) now()->diffInMinutes($user->locked_until, false);
            throw ValidationException::withMessages([
                'email' => "Your account is locked due to too many failed attempts. Try again in {$minutesLeft} minute(s).",
            ]);
        }

        // --- Attempt Authentication ---
        try {
            $request->authenticate();
        } catch (ValidationException $e) {
            // Increment failed login count
            if ($user) {
                $newCount = ($user->failed_login_count ?? 0) + 1;
                $updates = ['failed_login_count' => $newCount];

                if ($newCount >= 5) {
                    $updates['locked_until'] = now()->addMinutes(30);
                }

                $user->update($updates);

                if ($newCount >= 5) {
                    throw ValidationException::withMessages([
                        'email' => 'Too many failed login attempts. Your account has been locked for 30 minutes.',
                    ]);
                }

                $remaining = 5 - $newCount;
                throw ValidationException::withMessages([
                    'email' => "Invalid credentials. {$remaining} attempt(s) remaining before lockout.",
                ]);
            }

            throw $e;
        }

        // At this point authentication succeeded — $user is now authenticated
        $authenticatedUser = Auth::user();

        // --- Reset failed login count ---
        $authenticatedUser->update([
            'failed_login_count' => 0,
            'locked_until'       => null,
        ]);

        // --- 2FA Intercept ---
        if ($authenticatedUser->two_factor_confirmed_at) {
            // Stash user ID in session and log out (don't create full session yet)
            $userId = $authenticatedUser->id;
            Auth::guard('web')->logout();
            $request->session()->put('2fa_user_id', $userId);

            return redirect()->route('two-factor.challenge');
        }

        // --- Normal login completion ---
        $request->session()->regenerate();

        $authenticatedUser->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
