<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors(['email' => 'Logowanie przez Google nie powiodło się. Spróbuj ponownie.']);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            AuditLogger::log('google_login_rejected', null, ['email' => $googleUser->getEmail(), 'reason' => 'not_provisioned']);

            return redirect()->route('login')->withErrors(['email' => 'Konto nieprowizjonowane. Skontaktuj się z administratorem systemu GRC.']);
        }

        if (! $user->is_active) {
            AuditLogger::log('google_login_rejected', $user, ['reason' => 'account_disabled']);

            return redirect()->route('login')->withErrors(['email' => 'Konto dezaktywowane.']);
        }

        // Link Google ID on first OAuth login
        if (! $user->google_id) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
                'auth_provider' => 'google',
            ]);
        }

        auth()->login($user, remember: true);
        $user->update(['last_login_at' => now(), 'last_login_ip' => request()->ip()]);
        AuditLogger::log('login_google', $user);

        \App\Models\UserSession::create([
            'user_id'       => $user->id,
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent(),
            'auth_provider' => 'google',
            'logged_in_at'  => now(),
            'session_token' => session()->getId(),
        ]);

        // Google users skip MFA TOTP — Google handles their 2FA
        session(['mfa_verified' => true]);

        return redirect()->intended(route('dashboard'));
    }
}
