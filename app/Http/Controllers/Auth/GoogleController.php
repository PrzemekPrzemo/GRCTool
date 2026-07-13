<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! $this->isEnabled()) {
            return redirect()->route('login')->withErrors(['email' => 'Logowanie przez Google nie jest skonfigurowane.']);
        }

        $driver = Socialite::driver('google');

        // Restrict to Google Workspace domain when configured
        if ($domain = config('services.google.workspace_domain')) {
            $driver->with(['hd' => $domain]);
        }

        return $driver->redirect();
    }

    public function callback(): RedirectResponse
    {
        if (! $this->isEnabled()) {
            return redirect()->route('login')->withErrors(['email' => 'Logowanie przez Google nie jest skonfigurowane.']);
        }

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

        // Block locally-provisioned accounts from logging in via Google
        if ($user->auth_provider !== 'google' && $user->google_id === null) {
            AuditLogger::log('google_login_rejected', $user, ['reason' => 'local_account_google_attempt']);

            return redirect()->route('login')->withErrors(['email' => 'To konto używa logowania lokalnego. Zaloguj się hasłem.']);
        }

        // On first OAuth link: store Google ID and avatar
        if (! $user->google_id) {
            $avatarUrl = $googleUser->getAvatar();
            // Only accept HTTPS avatar URLs
            $safeAvatar = (is_string($avatarUrl) && str_starts_with($avatarUrl, 'https://')) ? $avatarUrl : null;
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'avatar_url' => $safeAvatar,
                'auth_provider' => 'google',
            ])->save();
        } elseif ($user->google_id !== $googleUser->getId()) {
            // Google ID mismatch — possible account takeover attempt
            AuditLogger::log('google_login_rejected', $user, [
                'reason' => 'google_id_mismatch',
                'stored_google_id' => substr($user->google_id, 0, 6).'…',
            ]);

            return redirect()->route('login')->withErrors(['email' => 'Weryfikacja konta Google nie powiodła się. Skontaktuj się z administratorem.']);
        }

        auth()->login($user, remember: true);
        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => request()->ip()])->save();
        request()->session()->regenerate();
        AuditLogger::log('login_google', $user);

        UserSession::create([
            'user_id' => $user->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'auth_provider' => 'google',
            'logged_in_at' => now(),
            'session_token' => session()->getId(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    private function isEnabled(): bool
    {
        return (bool) config('services.google.client_id') && (bool) config('services.google.client_secret');
    }
}
