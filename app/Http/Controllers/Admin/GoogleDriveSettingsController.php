<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\AuditLogger;
use App\Services\GoogleDriveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class GoogleDriveSettingsController extends Controller
{
    public function show(): View
    {
        abort_unless(auth()->user()->hasAnyRole(['ciso', 'admin']), 403);

        $settings = [
            'google_drive_enabled' => AppSetting::get('google_drive_enabled', '0'),
            'google_drive_folder_id' => AppSetting::get('google_drive_folder_id', ''),
            'google_drive_client_email' => AppSetting::get('google_drive_client_email', ''),
            'credentials_saved' => (bool) AppSetting::get('google_drive_credentials_encrypted'),
        ];

        return view('admin.google-drive-settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole(['ciso', 'admin']), 403);

        $data = $request->validate([
            'google_drive_folder_id' => ['nullable', 'string', 'max:191'],
            'google_drive_credentials' => ['nullable', 'string'],
            'google_drive_enabled' => ['nullable', 'boolean'],
        ]);

        AppSetting::set('google_drive_folder_id', $data['google_drive_folder_id'] ?? '');

        if (! empty($data['google_drive_credentials'])) {
            $decoded = json_decode($data['google_drive_credentials'], true);
            if (! is_array($decoded) || empty($decoded['client_email'])) {
                return back()->withErrors(['google_drive_credentials' => 'To nie wygląda na poprawny plik JSON service accounta (brak pola client_email).'])->withInput();
            }

            AppSetting::set('google_drive_credentials_encrypted', Crypt::encryptString($data['google_drive_credentials']));
            AppSetting::set('google_drive_client_email', $decoded['client_email']);
        }

        AppSetting::set('google_drive_enabled', $request->boolean('google_drive_enabled') ? '1' : '0');

        AuditLogger::log('google_drive_settings_updated');

        return redirect()->route('admin.google-drive.show')->with('status', 'Konfiguracja Google Drive zapisana.');
    }

    public function test(GoogleDriveService $drive): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole(['ciso', 'admin']), 403);

        if (! $drive->isApiEnabled()) {
            return back()->with('error', 'Włącz komunikację API i zapisz dane service accounta przed testem połączenia.');
        }

        try {
            $result = $drive->testConnection();
        } catch (\Throwable $e) {
            return back()->with('error', 'Test połączenia nieudany: '.$e->getMessage());
        }

        return back()->with('status', 'Połączenie OK. Service account: '.($result['email'] ?? '—'));
    }
}
