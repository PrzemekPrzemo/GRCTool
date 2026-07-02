<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class EntraIdSettingsController extends Controller
{
    public function show(): View
    {
        abort_unless(auth()->user()->hasAnyRole(['ciso', 'admin']), 403);

        $settings = [
            'azure_client_id'  => AppSetting::get('azure_client_id', ''),
            'azure_tenant_id'  => AppSetting::get('azure_tenant_id', ''),
            'azure_enabled'    => AppSetting::get('azure_enabled', '0'),
            'secret_saved'     => (bool) AppSetting::get('azure_client_secret_encrypted'),
        ];

        return view('admin.entra-settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole(['ciso', 'admin']), 403);

        $data = $request->validate([
            'azure_client_id'     => ['required', 'string', 'max:128'],
            'azure_tenant_id'     => ['required', 'string', 'max:128'],
            'azure_client_secret' => ['nullable', 'string', 'max:512'],
            'azure_enabled'       => ['nullable', 'boolean'],
        ]);

        AppSetting::set('azure_client_id', $data['azure_client_id']);
        AppSetting::set('azure_tenant_id', $data['azure_tenant_id']);
        AppSetting::set('azure_enabled', $request->boolean('azure_enabled') ? '1' : '0');

        if (! empty($data['azure_client_secret'])) {
            AppSetting::set('azure_client_secret_encrypted', Crypt::encryptString($data['azure_client_secret']));
        }

        AuditLogger::log('entra_id_settings_updated', null);

        return redirect()->route('admin.entra.show')
            ->with('status', 'Konfiguracja Entra ID zapisana.');
    }
}
