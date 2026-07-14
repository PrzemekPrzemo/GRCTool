@extends('layouts.app')
@section('content')

@php
$descriptions = [
    'admin' => 'Administrator systemu — RBAC, konfiguracja',
    'ciso' => 'CISO — pełny dostęp do wszystkich modułów',
    'security_engineer' => 'Security operacyjny — podatności, incydenty, SDLC',
    'risk_owner' => 'Właściciel ryzyka — edycja i akceptacja ryzyk',
    'control_owner' => 'Właściciel kontroli — zarządzanie kontrolami i evidence',
    'audit_lead' => 'Lider audytu — engagementy, findings, CAP',
    'external_auditor' => 'Audytor zewnętrzny — tylko odczyt',
    'board_viewer' => 'Zarząd — raporty i dashboard',
    'asset_owner' => 'Właściciel aktywa — aktywa i ankiety',
    'vendor_manager' => 'Manager dostawców — TPRM',
    'compliance_officer' => 'Compliance Officer — RODO, polityki, zgodność',
    'sales' => 'Sprzedaż — biblioteka odpowiedzi, ankiety klientów',
    'client_contact' => 'Kontakt klienta — Trust Portal (read-only)',
    'auditor_sysops' => 'Audytor systemu — pełny audit log (read-only)',
];

$builtinRoles = \App\Http\Controllers\Admin\RoleController::BUILTIN_ROLES;
@endphp

<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-semibold">Zarządzanie rolami</h1>
    <a href="{{ route('admin.roles.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm hover:bg-emerald-700 transition-colors">+ Nowa rola</a>
</div>

@if(session('status'))
    <div class="mb-4 px-4 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded text-sm">{{ session('status') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 px-4 py-2 bg-red-50 border border-red-200 text-red-800 rounded text-sm">{{ session('error') }}</div>
@endif

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Nazwa roli</th>
                <th class="px-3 py-2">Opis</th>
                <th class="px-3 py-2">Uprawnienia</th>
                <th class="px-3 py-2">Użytkownicy</th>
                <th class="px-3 py-2">Typ</th>
                <th class="px-3 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
            <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                <td class="px-3 py-2">
                    <span class="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded">{{ $role->name }}</span>
                </td>
                <td class="px-3 py-2 text-xs text-slate-600 max-w-xs">
                    {{ $descriptions[$role->name] ?? '—' }}
                </td>
                <td class="px-3 py-2 text-xs">
                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-emerald-700 hover:underline font-medium">
                        {{ $role->permissions_count }}
                    </a>
                </td>
                <td class="px-3 py-2 text-xs">
                    @if($role->users_count > 0)
                        <a href="{{ route('admin.users.index') }}?role={{ $role->name }}" class="text-slate-700 hover:underline font-medium">
                            {{ $role->users_count }}
                        </a>
                    @else
                        <span class="text-slate-400">0</span>
                    @endif
                </td>
                <td class="px-3 py-2 text-xs">
                    @if(in_array($role->name, $builtinRoles))
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">Wbudowana</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">Własna</span>
                    @endif
                </td>
                <td class="px-3 py-2 text-xs whitespace-nowrap">
                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-emerald-700 hover:underline mr-3">Edytuj uprawnienia</a>
                    @if(!in_array($role->name, $builtinRoles))
                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="inline" data-role-name="{{ $role->name }}" onsubmit="return confirm('Usunąć rolę „' + this.dataset.roleName + '“?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-700 hover:underline">Usuń</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endsection
