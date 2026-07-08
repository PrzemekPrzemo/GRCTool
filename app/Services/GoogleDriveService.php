<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

/**
 * Cienka warstwa nad Google Drive API. Dane dostępowe (service account JSON)
 * są konfigurowane w panelu Admin → Google Drive i trzymane zaszyfrowane w app_settings.
 * Tryb "links" (ręczne linki do Drive) działa zawsze i nie wymaga tego serwisu.
 */
class GoogleDriveService
{
    public function isApiEnabled(): bool
    {
        return AppSetting::get('google_drive_enabled', '0') === '1'
            && (bool) AppSetting::get('google_drive_credentials_encrypted');
    }

    public function folderId(): ?string
    {
        return AppSetting::get('google_drive_folder_id');
    }

    public function clientEmail(): ?string
    {
        return AppSetting::get('google_drive_client_email');
    }

    /**
     * @return array<string, mixed> zdekodowany JSON service accounta
     */
    private function credentials(): array
    {
        $encrypted = AppSetting::get('google_drive_credentials_encrypted');
        if (! $encrypted) {
            throw new RuntimeException('Google Drive API nie jest skonfigurowane (brak service account).');
        }

        $json = Crypt::decryptString($encrypted);
        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('Zapisane dane service accounta Google Drive są nieprawidłowe.');
        }

        return $decoded;
    }

    private function client(): \Google\Client
    {
        if (! class_exists(\Google\Client::class)) {
            throw new RuntimeException('Pakiet google/apiclient nie jest zainstalowany na serwerze.');
        }

        $client = new \Google\Client();
        $client->setApplicationName(config('app.name', 'GRC Platform'));
        $client->setAuthConfig($this->credentials());
        $client->setScopes([\Google\Service\Drive::DRIVE_READONLY]);

        return $client;
    }

    /**
     * @return array{id: string, name: ?string, webViewLink: ?string, mimeType: ?string, modifiedTime: ?string, version: ?string}
     */
    public function fetchFileMetadata(string $fileId): array
    {
        if (! $this->isApiEnabled()) {
            throw new RuntimeException('Synchronizacja przez Google Drive API jest wyłączona.');
        }

        $service = new \Google\Service\Drive($this->client());
        $file = $service->files->get($fileId, [
            'fields' => 'id,name,webViewLink,mimeType,modifiedTime,version',
            'supportsAllDrives' => true,
        ]);

        return [
            'id' => $file->getId(),
            'name' => $file->getName(),
            'webViewLink' => $file->getWebViewLink(),
            'mimeType' => $file->getMimeType(),
            'modifiedTime' => $file->getModifiedTime(),
            'version' => $file->getVersion(),
        ];
    }

    /**
     * Wywoływane z panelu ustawień, żeby zweryfikować że service account działa.
     *
     * @return array{email: ?string, folderId: ?string}
     */
    public function testConnection(): array
    {
        $service = new \Google\Service\Drive($this->client());
        // about->get zwraca informacje o kontekście service accounta — wystarczy do sprawdzenia poprawności kluczy.
        $about = $service->about->get(['fields' => 'user']);

        return [
            'email' => $about->getUser()?->getEmailAddress() ?? $this->clientEmail(),
            'folderId' => $this->folderId(),
        ];
    }
}
