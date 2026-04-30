<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class MfaService
{
    public function __construct(private Google2FA $google2fa) {}

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function setSecret(User $user, string $secret): void
    {
        $user->two_factor_secret = Crypt::encryptString($secret);
        $user->two_factor_recovery_codes = Crypt::encryptString(json_encode($this->generateRecoveryCodes()));
        $user->save();
    }

    public function getSecret(User $user): ?string
    {
        if (empty($user->two_factor_secret)) {
            return null;
        }

        return Crypt::decryptString($user->two_factor_secret);
    }

    public function getRecoveryCodes(User $user): array
    {
        if (empty($user->two_factor_recovery_codes)) {
            return [];
        }

        return json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true) ?? [];
    }

    public function verifyCode(User $user, string $code): bool
    {
        $secret = $this->getSecret($user);
        if (! $secret) {
            return false;
        }

        if ($this->google2fa->verifyKey($secret, $code, 1)) {
            return true;
        }

        // Try recovery codes
        $codes = $this->getRecoveryCodes($user);
        $idx = array_search($code, $codes, true);
        if ($idx !== false) {
            unset($codes[$idx]);
            $user->two_factor_recovery_codes = Crypt::encryptString(json_encode(array_values($codes)));
            $user->save();

            return true;
        }

        return false;
    }

    public function confirm(User $user): void
    {
        $user->two_factor_confirmed_at = now();
        $user->save();
    }

    public function buildQrCode(User $user, string $appName, string $secret): string
    {
        $url = $this->google2fa->getQRCodeUrl($appName, $user->email, $secret);
        $writer = new Writer(new GDLibRenderer(200));

        return base64_encode($writer->writeString($url));
    }

    private function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::upper(Str::random(5)).'-'.Str::upper(Str::random(5));
        }

        return $codes;
    }
}
