<?php

namespace App\Services;

use App\Models\TrustedDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * "Zapamiętaj to urządzenie" dla MFA — pozwala nie pytać o kod TOTP przy
 * każdym logowaniu z tego samego, wcześniej zweryfikowanego urządzenia.
 * Zaufanie jest cookie'ą (szyfrowaną przez Laravel, httpOnly) + rekordem w DB
 * przechowującym tylko hash tokenu (nigdy surowego sekretu). Okno ważności
 * jest kroczące: każde udane pominięcie MFA przedłuża je o kolejne 7 dni,
 * więc regularnie używane urządzenie pyta o kod raz na tydzień, a porzucone
 * wygasa samo.
 */
class TrustedDeviceService
{
    public const COOKIE_NAME = 'grc_trusted_device';

    public const TRUST_DAYS = 7;

    public function remember(User $user, Request $request): void
    {
        $token = Str::random(64);

        TrustedDevice::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'label' => $this->labelFor($request),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'last_used_at' => now(),
            'expires_at' => now()->addDays(self::TRUST_DAYS),
        ]);

        $this->queueCookie($user->id, $token);
    }

    /**
     * Zwraca true, jeśli żądanie niesie ważne ciasteczko zaufanego urządzenia
     * dla tego użytkownika. Przy sukcesie przedłuża okno ważności i odświeża
     * ciasteczko (kroczące zaufanie).
     */
    public function isTrusted(User $user, Request $request): bool
    {
        $cookie = $request->cookie(self::COOKIE_NAME);
        if (! $cookie || ! str_contains($cookie, '|')) {
            return false;
        }

        [$userId, $token] = explode('|', $cookie, 2);
        if ((int) $userId !== $user->id) {
            return false;
        }

        $device = TrustedDevice::where('user_id', $user->id)
            ->where('token_hash', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();

        if (! $device) {
            return false;
        }

        $device->update(['last_used_at' => now(), 'expires_at' => now()->addDays(self::TRUST_DAYS)]);
        $this->queueCookie((int) $userId, $token);

        return true;
    }

    public function forgetAll(User $user): void
    {
        $user->trustedDevices()->delete();
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }

    private function queueCookie(int $userId, string $token): void
    {
        Cookie::queue(Cookie::make(
            self::COOKIE_NAME,
            $userId.'|'.$token,
            self::TRUST_DAYS * 24 * 60,
            secure: (bool) config('session.secure'),
            httpOnly: true,
            sameSite: 'lax',
        ));
    }

    private function labelFor(Request $request): string
    {
        $agent = (string) $request->userAgent();

        return match (true) {
            str_contains($agent, 'iPhone') => 'iPhone',
            str_contains($agent, 'iPad') => 'iPad',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'Macintosh') => 'Mac',
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Linux') => 'Linux',
            default => 'Nieznane urządzenie',
        };
    }
}
