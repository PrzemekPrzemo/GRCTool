<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SsoRoleMapping;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class SsoRoleMappingController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole(['ciso', 'admin']), 403);

        $data = $request->validate([
            'entra_type' => ['required', 'in:app_role,group'],
            'entra_value' => ['required', 'string', 'max:191'],
            'label' => ['nullable', 'string', 'max:191'],
            'system_role' => ['required', 'string', Rule::in(Role::pluck('name'))],
        ]);

        $mapping = SsoRoleMapping::create([
            'provider' => 'azure',
            'entra_type' => $data['entra_type'],
            'entra_value' => trim($data['entra_value']),
            'label' => $data['label'] ?? null,
            'system_role' => $data['system_role'],
        ]);

        AuditLogger::log('sso_role_mapping_created', null, $mapping->only(['entra_type', 'entra_value', 'system_role']));

        return back()->with('status', 'Mapowanie dodane.');
    }

    public function destroy(SsoRoleMapping $mapping): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole(['ciso', 'admin']), 403);

        AuditLogger::log('sso_role_mapping_deleted', null, $mapping->only(['entra_type', 'entra_value', 'system_role']));
        $mapping->delete();

        return back()->with('status', 'Mapowanie usunięte.');
    }
}
