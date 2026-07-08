<?php

namespace App\Services\Security;

use App\Models\AppSetting;
use Google\Client;
use Google\Service\AlertCenter;
use Google\Service\AlertCenter\Alert;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

/**
 * Ciągnie alerty Google Workspace Alert Center (phishing/malware w poczcie, podejrzane
 * logowania, DLP i udostępnianie na Drive) — u nas Google to poczta + Drive.
 * Współdzieli service account z integracją Google Drive (Admin → Google Drive), ale wymaga
 * domain-wide delegation ze scope'em apps.alerts.readonly i konta admina do impersonacji.
 */
class GoogleWorkspaceAlertService
{
    public function isEnabled(): bool
    {
        return AppSetting::get('google_workspace_alerts_enabled', '0') === '1'
            && (bool) AppSetting::get('google_drive_credentials_encrypted')
            && (bool) AppSetting::get('google_workspace_admin_email');
    }

    /**
     * @return array<string, mixed>
     */
    private function credentials(): array
    {
        $encrypted = AppSetting::get('google_drive_credentials_encrypted');
        if (! $encrypted) {
            throw new RuntimeException('Google service account nie jest skonfigurowany (Admin → Google Drive).');
        }

        $decoded = json_decode(Crypt::decryptString($encrypted), true);
        if (! is_array($decoded)) {
            throw new RuntimeException('Zapisane dane service accounta Google są nieprawidłowe.');
        }

        return $decoded;
    }

    private function client(): Client
    {
        if (! class_exists(Client::class)) {
            throw new RuntimeException('Pakiet google/apiclient nie jest zainstalowany na serwerze.');
        }

        $adminEmail = AppSetting::get('google_workspace_admin_email');
        if (! $adminEmail) {
            throw new RuntimeException('Brak adresu e-mail administratora do impersonacji (Admin → Google Drive → Google Workspace Alert Center).');
        }

        $client = new Client;
        $client->setApplicationName(config('app.name', 'GRC Platform'));
        $client->setAuthConfig($this->credentials());
        $client->setScopes(['https://www.googleapis.com/auth/apps.alerts.readonly']);
        $client->setSubject($adminEmail);

        return $client;
    }

    /**
     * @return array<int, Alert>
     */
    public function fetchAlerts(int $pageSize = 50): array
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('Synchronizacja Google Workspace Alert Center jest wyłączona.');
        }

        $service = new AlertCenter($this->client());
        $response = $service->alerts->listAlerts(['pageSize' => $pageSize, 'orderBy' => 'createTime desc']);

        return $response->getAlerts() ?? [];
    }

    public function testConnection(): array
    {
        $alerts = $this->fetchAlerts(1);

        return ['sample_count' => count($alerts)];
    }
}
