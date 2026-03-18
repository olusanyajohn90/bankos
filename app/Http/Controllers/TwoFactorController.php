<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class TwoFactorController extends Controller
{
    /**
     * Generate a TOTP secret (base32-encoded, 16 chars).
     * Uses a simplified TOTP without external package dependency.
     */
    private function generateSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 32; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $secret;
    }

    /**
     * Base32 decode for TOTP verification.
     */
    private function base32Decode(string $base32): string
    {
        $base32 = strtoupper($base32);
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        foreach (str_split($base32) as $char) {
            $pos = strpos($chars, $char);
            if ($pos === false) continue;
            $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $bytes = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $bytes .= chr(bindec($byte));
            }
        }
        return $bytes;
    }

    /**
     * Generate a 6-digit TOTP code for a given secret and time counter.
     */
    private function generateTotp(string $secret, int $counter): string
    {
        $key = $this->base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $time, $key, true);
        $offset = ord($hash[19]) & 0xf;
        $otp = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;
        return str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a TOTP code (checks current + 1 window either side for clock drift).
     */
    private function verifyTotp(string $secret, string $code): bool
    {
        $counter = (int) floor(time() / 30);
        for ($i = -1; $i <= 1; $i++) {
            if ($this->generateTotp($secret, $counter + $i) === $code) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build a QR code URI for the authenticator app.
     * Returns otpauth:// URI which can be rendered as a QR code via a JS library.
     */
    private function buildOtpauthUri(User $user, string $secret): string
    {
        $issuer = urlencode(config('app.name', 'bankOS'));
        $account = urlencode($user->email);
        return "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Generate 8 single-use recovery codes.
     */
    private function generateRecoveryCodes(): array
    {
        return array_map(fn() => Str::random(10) . '-' . Str::random(10), range(1, 8));
    }

    // -----------------------------------------------------------------------
    // 2FA Setup (authenticated)
    // -----------------------------------------------------------------------

    /** GET /user/two-factor — show 2FA settings page. */
    public function show(): View
    {
        $user = auth()->user();
        $qrUri = null;
        $secret = null;

        // If enabled but not confirmed, re-show QR
        if ($user->two_factor_secret && !$user->two_factor_confirmed_at) {
            $secret = Crypt::decryptString($user->two_factor_secret);
            $qrUri = $this->buildOtpauthUri($user, $secret);
        }

        $recoveryCodes = null;
        if ($user->two_factor_confirmed_at && $user->two_factor_recovery_codes) {
            $recoveryCodes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true);
        }

        return view('profile.two-factor', compact('user', 'qrUri', 'secret', 'recoveryCodes'));
    }

    /** POST /user/two-factor/enable — generate secret and show QR. */
    public function enable(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->two_factor_confirmed_at) {
            return back()->with('error', '2FA is already enabled.');
        }

        $secret = $this->generateSecret();
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_secret'         => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes)),
            'two_factor_confirmed_at'   => null,
        ]);

        return redirect()->route('two-factor.show')
            ->with('success', 'Scan the QR code with your authenticator app, then confirm below.');
    }

    /** POST /user/two-factor/confirm — verify first OTP before activating. */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string|size:6']);

        $user = auth()->user();

        if (!$user->two_factor_secret) {
            return back()->with('error', 'Please enable 2FA first.');
        }

        $secret = Crypt::decryptString($user->two_factor_secret);

        if (!$this->verifyTotp($secret, $request->code)) {
            return back()->with('error', 'Invalid verification code. Please try again.');
        }

        $user->update(['two_factor_confirmed_at' => now()]);

        return redirect()->route('two-factor.show')
            ->with('success', '2FA has been activated. Save your recovery codes in a safe place.');
    }

    /** DELETE /user/two-factor — disable 2FA after password confirmation. */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required|string']);

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Incorrect password.');
        }

        $user->update([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ]);

        return redirect()->route('two-factor.show')
            ->with('success', 'Two-factor authentication has been disabled.');
    }

    // -----------------------------------------------------------------------
    // 2FA Challenge (during login)
    // -----------------------------------------------------------------------

    /** GET /two-factor-challenge — show 2FA code entry form. */
    public function challenge(): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }
        return view('auth.two-factor-challenge');
    }

    /** POST /two-factor-challenge — verify code and complete login. */
    public function verify(Request $request): RedirectResponse
    {
        $userId = session('2fa_user_id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expired. Please log in again.');
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        $code = trim($request->input('code', ''));

        // Try TOTP code
        if ($user->two_factor_secret && strlen($code) === 6 && ctype_digit($code)) {
            $secret = Crypt::decryptString($user->two_factor_secret);
            if ($this->verifyTotp($secret, $code)) {
                return $this->completeTwoFactorLogin($request, $user);
            }
        }

        // Try recovery code
        if ($user->two_factor_recovery_codes && strlen($code) > 6) {
            $codes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true);
            $normalised = strtolower($code);
            $index = array_search($normalised, array_map('strtolower', $codes));
            if ($index !== false) {
                // Consume the recovery code (one-time use)
                unset($codes[$index]);
                $user->update([
                    'two_factor_recovery_codes' => Crypt::encryptString(json_encode(array_values($codes))),
                ]);
                return $this->completeTwoFactorLogin($request, $user);
            }
        }

        return back()->with('error', 'Invalid authentication code. Please try again.');
    }

    private function completeTwoFactorLogin(Request $request, User $user): RedirectResponse
    {
        $request->session()->forget('2fa_user_id');

        Auth::login($user);

        $request->session()->regenerate();

        $user->update([
            'last_login_at'      => now(),
            'last_login_ip'      => $request->ip(),
            'failed_login_count' => 0,
        ]);

        return redirect()->intended(\App\Providers\RouteServiceProvider::HOME);
    }
}
