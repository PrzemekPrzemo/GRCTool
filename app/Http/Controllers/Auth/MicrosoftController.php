<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditLogger;
use App\Services\Security\EntraRoleMappingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class MicrosoftController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! $this->isEnabled()) {
            return redirect()->route('login')->withErrors(['email' => 'Logowanie przez Microsoft nie jest skonfigurowane.']);
        }

        $this->applyDynamicConfig();

        try {
            // openid/profile/email dodają id_token do odpowiedzi tokenu (poza domyślnym
            // Graph-only User.Read) — wymagane, żeby odczytać claimy roles/groups do
            // synchronizacji ról, patrz EntraRoleMappingService.
            return Socialite::driver('azure')->scopes(['openid', 'profile', 'email'])->redirect();
        } catch (\Throwable $e) {
            Log::warning('Microsoft OAuth redirect failed.', ['error' => $e->getMessage()]);

            return redirect()->route('login')->withErrors(['email' => 'Logowanie przez Microsoft nie powiodło się. Sprawdź konfigurację Entra ID.']);
        }
    }

    public function callback(): RedirectResponse
    {
        if (! $this->isEnabled()) {
            return redirect()->route('login')->withErrors(['email' => 'Logowanie przez Microsoft nie jest skonfigurowane.']);
        }

        $this->applyDynamicConfig();

        try {
            $msUser = Socialite::driver('azure')->user();
        } catch (\Throwable $e) {
            // Prawdziwa przyczyna (np. AADSTS50011 redirect_uri mismatch, AADSTS650053
            // brak admin consent, AADSTS700016 zła aplikacja) trafia tylko do logu —
            // użytkownikowi pokazujemy generyczny komunikat, żeby nie ujawniać szczegółów OAuth.
            Log::warning('Microsoft OAuth callback failed.', ['error' => $e->getMessage()]);

            return redirect()->route('login')->withErrors(['email' => 'Logowanie przez Microsoft nie powiodło się. Spróbuj ponownie.']);
        }

        $email = $msUser->getEmail();

        if (! $email) {
            return redirect()->route('login')->withErrors(['email' => 'Konto Microsoft nie zwróciło adresu email. Sprawdź uprawnienia aplikacji (User.Read).']);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            AuditLogger::log('microsoft_login_rejected', null, ['email' => $email, 'reason' => 'not_provisioned']);

            return redirect()->route('login')->withErrors(['email' => 'Konto nieprowizjonowane. Skontaktuj się z administratorem systemu GRC.']);
        }

        if (! $user->is_active) {
            AuditLogger::log('microsoft_login_rejected', $user, ['reason' => 'account_disabled']);

            return redirect()->route('login')->withErrors(['email' => 'Konto dezaktywowane.']);
        }

        // Block locally-provisioned accounts from logging in via Microsoft
        if ($user->auth_provider !== 'microsoft' && $user->microsoft_id === null) {
            AuditLogger::log('microsoft_login_rejected', $user, ['reason' => 'local_account_microsoft_attempt']);

            return redirect()->route('login')->withErrors(['email' => 'To konto używa logowania lokalnego lub innego SSO. Zaloguj się odpowiednią metodą.']);
        }

        // On first OAuth link: store Microsoft ID and avatar
        if (! $user->microsoft_id) {
            $avatarUrl = $msUser->getAvatar();
            $safeAvatar = (is_string($avatarUrl) && str_starts_with($avatarUrl, 'https://')) ? $avatarUrl : null;
            $user->forceFill([
                'microsoft_id' => $msUser->getId(),
                'avatar_url' => $safeAvatar,
                'auth_provider' => 'microsoft',
            ])->save();
        } elseif ($user->microsoft_id !== $msUser->getId()) {
            AuditLogger::log('microsoft_login_rejected', $user, [
                'reason' => 'microsoft_id_mismatch',
                'stored_microsoft_id' => substr($user->microsoft_id, 0, 6).'…',
            ]);

            return redirect()->route('login')->withErrors(['email' => 'Weryfikacja konta Microsoft nie powiodła się. Skontaktuj się z administratorem.']);
        }

        $this->syncRolesFromEntra($user, $msUser);

        auth()->login($user, remember: true);
        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => request()->ip()])->save();
        request()->session()->regenerate();
        AuditLogger::log('login_microsoft', $user);

        UserSession::create([
            'user_id' => $user->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'auth_provider' => 'microsoft',
            'logged_in_at' => now(),
            'session_token' => session()->getId(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Nadaje role Spatie na podstawie claimów roles/groups z id_token, wg mapowania
     * skonfigurowanego w Admin → Entra ID → Mapowanie ról (sso_role_mappings).
     * Wyłączone domyślnie (azure_role_sync_enabled=0) — role nadal nadaje wtedy
     * wyłącznie administrator ręcznie, jak dotychczas. Gdy włączone, ale token nie
     * zawiera żadnej dopasowanej roli/grupy, role użytkownika NIE są dotykane
     * (nie czyścimy do zera przez błąd konfiguracji) — celowe zachowanie "fail keep",
     * nie "fail open" ani "fail closed".
     */
    private function syncRolesFromEntra(User $user, $msUser): void
    {
        $mapping = app(EntraRoleMappingService::class);

        if (! $mapping->isEnabled()) {
            return;
        }

        $idToken = $msUser->accessTokenResponseBody['id_token'] ?? null;
        $claims = $mapping->decodeIdTokenClaims($idToken);
        $resolvedRoles = $mapping->resolveSystemRoles($claims);

        if ($resolvedRoles === []) {
            AuditLogger::log('entra_role_sync_no_match', $user, [
                'token_roles' => $claims['roles'],
                'token_groups' => $claims['groups'],
            ]);

            return;
        }

        $user->syncRoles($resolvedRoles);
        AuditLogger::log('entra_role_sync', $user, ['roles' => $resolvedRoles]);
    }

    private function isEnabled(): bool
    {
        if (AppSetting::get('azure_enabled') === '1') {
            return true;
        }

        // Fallback: enabled when .env credentials are set
        return (bool) config('services.azure.client_id');
    }

    private function applyDynamicConfig(): void
    {
        $clientId = AppSetting::get('azure_client_id') ?: config('services.azure.client_id');
        $tenant = AppSetting::get('azure_tenant_id') ?: config('services.azure.tenant', 'common');

        $encryptedSecret = AppSetting::get('azure_client_secret_encrypted');
        if ($encryptedSecret) {
            try {
                $clientSecret = Crypt::decryptString($encryptedSecret);
            } catch (\Throwable) {
                $clientSecret = config('services.azure.client_secret');
            }
        } else {
            $clientSecret = config('services.azure.client_secret');
        }

        config([
            'services.azure.client_id' => $clientId,
            'services.azure.client_secret' => $clientSecret,
            'services.azure.tenant' => $tenant,
        ]);
    }
}
