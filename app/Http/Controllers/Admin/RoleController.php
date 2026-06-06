<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    const BUILTIN_ROLES = [
        'admin', 'ciso', 'security_engineer', 'risk_owner', 'control_owner',
        'audit_lead', 'external_auditor', 'board_viewer', 'asset_owner', 'vendor_manager',
        'compliance_officer', 'sales', 'client_contact', 'auditor_sysops',
    ];

    public function index(): View
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $roles = Role::withCount(['permissions', 'users'])->orderBy('name')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $role = new Role();

        return view('admin.roles.form', compact('role'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')
            ->with('status', "Utworzono rolę „{$role->name}".");
    }

    public function edit(Role $role): View
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $role->load('permissions');

        return view('admin.roles.form', compact('role'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')
            ->with('status', "Zaktualizowano uprawnienia roli „{$role->name}".");
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        if (in_array($role->name, self::BUILTIN_ROLES)) {
            return redirect()->back()
                ->with('error', 'Role wbudowane nie mogą być usunięte.');
        }

        $userCount = $role->users()->count();
        if ($userCount > 0) {
            return redirect()->back()
                ->with('error', "Nie można usunąć roli — przypisano ją do {$userCount} użytkowników.");
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('status', "Usunięto rolę „{$role->name}".");
    }
}
