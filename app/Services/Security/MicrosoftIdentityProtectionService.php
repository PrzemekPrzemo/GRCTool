<?php

namespace App\Services\Security;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Ciągnie sygnały Entra ID Identity Protection (ryzykowni użytkownicy, risk detections)
 * przez Microsoft Graph — Entra ID jest u nas dostawcą tożsamości, nie pełnym M365 Defender,
 * więc korzystamy z /identityProtection zamiast /security/incidents.
 * Współdzieli rejestrację aplikacji z logowaniem SSO (Admin → Entra ID / SSO),
 * ale wymaga dodatkowego uprawnienia aplikacji: IdentityRiskEvent.Read.All (z admin consent).
 */
class MicrosoftIdentityProtectionService
{
    public function isEnabled(): bool
    {
        return AppSetting::get('azure_identity_protection_enabled', '0') === '1'
            && (bool) AppSetting::get('azure_client_id')
            && (bool) AppSetting::get('azure_tenant_id')
            && (bool) AppSetting::get('azure_client_secret_encrypted');
    }

    private function accessToken(): string
    {
        $tenantId = AppSetting::get('azure_tenant_id');
        $clientId = AppSetting::get('azure_client_id');
        $secretEncrypted = AppSetting::get('azure_client_secret_encrypted');

        if (! $tenantId || ! $clientId || ! $secretEncrypted) {
            throw new RuntimeException('Entra ID nie jest skonfigurowane (brak client_id/tenant_id/secret).');
        }

        $response = Http::asForm()->post("https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token", [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => Crypt::decryptString($secretEncrypted),
            'scope' => 'https://graph.microsoft.com/.default',
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Nie udało się uzyskać tokenu Microsoft Graph: '.$response->body());
        }

        return $response->json('access_token');
    }

    /**
     * @return array<int, array<string, mixed>> surowe riskDetections z Microsoft Graph
     */
    public function fetchRiskDetections(int $top = 50): array
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('Synchronizacja Entra ID Identity Protection jest wyłączona.');
        }

        $token = $this->accessToken();

        $response = Http::withToken($token)
            ->get('https://graph.microsoft.com/v1.0/identityProtection/riskDetections', [
                '$top' => $top,
                '$orderby' => 'detectedDateTime desc',
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Błąd Microsoft Graph (riskDetections): '.$response->body());
        }

        return $response->json('value', []);
    }

    public function testConnection(): array
    {
        $detections = $this->fetchRiskDetections(1);

        return ['sample_count' => count($detections)];
    }
}
