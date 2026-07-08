<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\AuditLogger;
use App\Services\Security\AwsSecurityHubService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class AwsSecurityHubSettingsController extends Controller
{
    public function show(): View
    {
        abort_unless(auth()->user()->hasAnyRole(['ciso', 'admin']), 403);

        $settings = [
            'aws_security_hub_enabled' => AppSetting::get('aws_security_hub_enabled', '0'),
            'aws_region' => AppSetting::get('aws_region', ''),
            'aws_access_key_id' => AppSetting::get('aws_access_key_id', ''),
            'secret_saved' => (bool) AppSetting::get('aws_secret_access_key_encrypted'),
        ];

        return view('admin.aws-security-hub-settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole(['ciso', 'admin']), 403);

        $data = $request->validate([
            'aws_region' => ['required', 'string', 'max:32'],
            'aws_access_key_id' => ['required', 'string', 'max:128'],
            'aws_secret_access_key' => ['nullable', 'string', 'max:256'],
            'aws_security_hub_enabled' => ['nullable', 'boolean'],
        ]);

        AppSetting::set('aws_region', $data['aws_region']);
        AppSetting::set('aws_access_key_id', $data['aws_access_key_id']);
        AppSetting::set('aws_security_hub_enabled', $request->boolean('aws_security_hub_enabled') ? '1' : '0');

        if (! empty($data['aws_secret_access_key'])) {
            AppSetting::set('aws_secret_access_key_encrypted', Crypt::encryptString($data['aws_secret_access_key']));
        }

        AuditLogger::log('aws_security_hub_settings_updated');

        return redirect()->route('admin.aws-security-hub.show')->with('status', 'Konfiguracja AWS Security Hub zapisana.');
    }

    public function test(AwsSecurityHubService $securityHub): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole(['ciso', 'admin']), 403);

        if (! $securityHub->isEnabled()) {
            return back()->with('error', 'Włącz komunikację z Security Hub i zapisz klucze dostępowe przed testem.');
        }

        try {
            $result = $securityHub->testConnection();
        } catch (\Throwable $e) {
            return back()->with('error', 'Test połączenia nieudany: '.$e->getMessage());
        }

        return back()->with('status', "Połączenie OK. Pobrano {$result['sample_count']} przykładowych findings.");
    }
}
