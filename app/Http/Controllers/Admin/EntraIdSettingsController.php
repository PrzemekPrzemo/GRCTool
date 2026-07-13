<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\SsoRoleMapping;
use App\Services\AuditLogger;
use App\Services\Security\MicrosoftIdentityProtectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class EntraIdSettingsController extends Controller
{
    public function show(): View
    {
        abort_unless(auth()->user()->hasAnyRole(['ciso', 'admin']), 403);

        $settings = [
            'azure_client_id' => AppSetting::get('azure_client_id', ''),
            'azure_tenant_id' => AppSetting::get('azure_tenant_id', ''),
            'azure_enabled' => AppSetting::get('azure_enabled', '0'),
            'secret_saved' => (bool) AppSetting::get('azure_client_secret_encrypted'),
            'azure_identity_protection_enabled' => AppSetting::get('azure_identity_protection_enabled', '0'),
            'azure_role_sync_enabled' => AppSetting::get('azure_role_sync_enabled', '0'),
        ];

        $roleMappings = SsoRoleMapping::where('provider', 'azure')->orderBy('entra_type')->orderBy('entra_value')->get();
        $availableRoles = Role::orderBy('name')->pluck('name');

        return view('admin.entra-settings', compact('settings', 'roleMappings', 'availableRoles'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole(['ciso', 'admin']), 403);

        $data = $request->validate([
            'azure_client_id' => ['required', 'string', 'max:128'],
            'azure_tenant_id' => ['required', 'string', 'max:128'],
            'azure_client_secret' => ['nullable', 'string', 'max:512'],
            'azure_enabled' => ['nullable', 'boolean'],
            'azure_identity_protection_enabled' => ['nullable', 'boolean'],
            'azure_role_sync_enabled' => ['nullable', 'boolean'],
        ]);

        AppSetting::set('azure_client_id', $data['azure_client_id']);
        AppSetting::set('azure_tenant_id', $data['azure_tenant_id']);
        AppSetting::set('azure_enabled', $request->boolean('azure_enabled') ? '1' : '0');
        AppSetting::set('azure_identity_protection_enabled', $request->boolean('azure_identity_protection_enabled') ? '1' : '0');
        AppSetting::set('azure_role_sync_enabled', $request->boolean('azure_role_sync_enabled') ? '1' : '0');

        if (! empty($data['azure_client_secret'])) {
            AppSetting::set('azure_client_secret_encrypted', Crypt::encryptString($data['azure_client_secret']));
        }

        AuditLogger::log('entra_id_settings_updated', null);

        return redirect()->route('admin.entra.show')
            ->with('status', 'Konfiguracja Entra ID zapisana.');
    }

    public function testIdentityProtection(MicrosoftIdentityProtectionService $service): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole(['ciso', 'admin']), 403);

        if (! $service->isEnabled()) {
            return back()->with('error', 'Włącz synchronizację Identity Protection i zapisz dane Entra ID przed testem.');
        }

        try {
            $result = $service->testConnection();
        } catch (\Throwable $e) {
            return back()->with('error', 'Test połączenia nieudany: '.$e->getMessage());
        }

        return back()->with('status', "Połączenie OK. Pobrano {$result['sample_count']} przykładowych detekcji ryzyka.");
    }
}
