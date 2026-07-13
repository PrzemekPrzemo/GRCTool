@extends('layouts.app')
@section('title', 'Konfiguracja Entra ID')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Konfiguracja Microsoft Entra ID (Azure AD)</h1>
        <p class="text-slate-500 mt-1 text-sm">Skonfiguruj SSO przez Microsoft Entra ID. Dane są szyfrowane przed zapisem w bazie.</p>
    </div>

    @if(session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded text-sm">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-900 rounded text-sm">{{ session('error') }}</div>
    @endif

    {{-- Current status --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-slate-700">Status logowania Microsoft</span>
            @if($settings['azure_enabled'] === '1')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Włączone
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    Wyłączone
                </span>
            @endif
        </div>
        <div class="mt-3 grid grid-cols-2 gap-3 text-sm text-slate-600">
            <div>
                <span class="font-medium">Client ID:</span>
                <span class="ml-1 font-mono text-xs">{{ $settings['azure_client_id'] ? substr($settings['azure_client_id'], 0, 8).'…' : '—' }}</span>
            </div>
            <div>
                <span class="font-medium">Tenant ID:</span>
                <span class="ml-1 font-mono text-xs">{{ $settings['azure_tenant_id'] ? substr($settings['azure_tenant_id'], 0, 8).'…' : '—' }}</span>
            </div>
            <div>
                <span class="font-medium">Client Secret:</span>
                <span class="ml-1">{{ $settings['secret_saved'] ? '●●●●●● (zapisany)' : '— (nie ustawiony)' }}</span>
            </div>
        </div>
    </div>

    {{-- Configuration form --}}
    <form method="POST" action="{{ route('admin.entra.update') }}" class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Application (Client) ID <span class="text-red-500">*</span>
            </label>
            <input type="text" name="azure_client_id" required
                   value="{{ old('azure_client_id', $settings['azure_client_id']) }}"
                   placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400">
            @error('azure_client_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            <p class="text-xs text-slate-400 mt-1">Entra admin center → App registrations → Overview → Application (client) ID</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Directory (Tenant) ID <span class="text-red-500">*</span>
            </label>
            <input type="text" name="azure_tenant_id" required
                   value="{{ old('azure_tenant_id', $settings['azure_tenant_id']) }}"
                   placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx lub 'common'"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400">
            @error('azure_tenant_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            <p class="text-xs text-slate-400 mt-1">Użyj UUID tenanta dla single-tenant lub <code class="bg-slate-100 px-1 rounded">common</code> dla multi-tenant</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Client Secret
                @if($settings['secret_saved'])
                    <span class="text-xs font-normal text-slate-400 ml-1">(zostaw puste, by zachować aktualny)</span>
                @else
                    <span class="text-red-500">*</span>
                @endif
            </label>
            <input type="password" name="azure_client_secret"
                   placeholder="{{ $settings['secret_saved'] ? '●●●●●●●●●● (nie zmieniaj)' : 'Wklej Client Secret z Azure Portal' }}"
                   autocomplete="off"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400">
            @error('azure_client_secret')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            <p class="text-xs text-slate-400 mt-1">Certificates & secrets → Client secrets → Value (widoczna tylko raz po utworzeniu)</p>
        </div>

        <div class="pt-2 border-t border-slate-100">
            <label class="flex items-center gap-3 cursor-pointer">
                <div class="relative" x-data="{ enabled: {{ $settings['azure_enabled'] === '1' ? 'true' : 'false' }} }">
                    <input type="checkbox" name="azure_enabled" value="1" class="sr-only" x-model="enabled">
                    <div class="w-10 h-6 rounded-full border-2 transition-colors cursor-pointer"
                         :class="enabled ? 'bg-emerald-500 border-emerald-500' : 'bg-slate-200 border-slate-300'">
                        <div class="w-4 h-4 bg-white rounded-full shadow transform transition-transform mt-0.5"
                             :class="enabled ? 'translate-x-4' : 'translate-x-0.5'"></div>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-700">Włącz logowanie przez Microsoft</p>
                    <p class="text-xs text-slate-400">Pokazuje przycisk "Zaloguj przez Microsoft" na stronie logowania</p>
                </div>
            </label>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('dashboard') }}" class="text-sm text-slate-500 hover:text-slate-700">Anuluj</a>
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition-colors">
                Zapisz konfigurację
            </button>
        </div>
    </form>

    {{-- Identity Protection sync --}}
    <div class="mt-6 bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="text-sm font-semibold text-slate-900 mb-1">Synchronizacja Entra ID Identity Protection</h3>
        <p class="text-xs text-slate-500 mb-4">Ciągnie ryzykowne logowania i detekcje ryzyka (risk detections) do rejestru incydentów. Korzysta z tej samej rejestracji aplikacji co logowanie SSO powyżej — wymaga dodatkowo uprawnienia aplikacji <code class="bg-slate-100 px-1 rounded">IdentityRiskEvent.Read.All</code> z admin consent.</p>

        <form method="PUT" action="{{ route('admin.entra.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="azure_client_id" value="{{ $settings['azure_client_id'] }}">
            <input type="hidden" name="azure_tenant_id" value="{{ $settings['azure_tenant_id'] }}">
            <input type="hidden" name="azure_enabled" value="{{ $settings['azure_enabled'] }}">

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="azure_identity_protection_enabled" value="1"
                       {{ $settings['azure_identity_protection_enabled'] === '1' ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-400">
                <div>
                    <p class="text-sm font-medium text-slate-700">Włącz synchronizację risk detections</p>
                    <p class="text-xs text-slate-400">Godzinowa komenda tworzy/aktualizuje incydenty na podstawie sygnałów ryzyka z Entra ID.</p>
                </div>
            </label>

            <div class="flex items-center justify-between pt-2">
                <span class="text-xs text-slate-400">{{ $settings['secret_saved'] ? 'Client secret już skonfigurowany powyżej.' : 'Najpierw zapisz Client ID/Tenant ID/Secret powyżej.' }}</span>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800">Zapisz</button>
            </div>
        </form>

        @if($settings['azure_identity_protection_enabled'] === '1')
        <form method="POST" action="{{ route('admin.entra.test-identity-protection') }}" class="mt-3">
            @csrf
            <button class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm hover:bg-slate-50">Testuj połączenie</button>
        </form>
        @endif
    </div>

    {{-- Role mapping --}}
    <div class="mt-6 bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="text-sm font-semibold text-slate-900 mb-1">Mapowanie ról Entra ID → role GRCTool</h3>
        <p class="text-xs text-slate-500 mb-4">Przy każdym logowaniu przez Microsoft role użytkownika w GRCTool są nadawane na podstawie App Role lub grupy Entra ID przypisanej w tokenie logowania. Instrukcja konfiguracji po stronie Entra — patrz sekcja niżej.</p>

        <form method="PUT" action="{{ route('admin.entra.update') }}" class="mb-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="azure_client_id" value="{{ $settings['azure_client_id'] }}">
            <input type="hidden" name="azure_tenant_id" value="{{ $settings['azure_tenant_id'] }}">
            <input type="hidden" name="azure_enabled" value="{{ $settings['azure_enabled'] }}">
            <input type="hidden" name="azure_identity_protection_enabled" value="{{ $settings['azure_identity_protection_enabled'] }}">

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="azure_role_sync_enabled" value="1"
                       {{ $settings['azure_role_sync_enabled'] === '1' ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-400">
                <div>
                    <p class="text-sm font-medium text-slate-700">Włącz synchronizację ról z Entra ID</p>
                    <p class="text-xs text-slate-400">Gdy wyłączone (domyślnie): role nadaje wyłącznie administrator ręcznie w Admin → Użytkownicy, jak dotychczas. Gdy włączone i token nie zawiera dopasowanej roli/grupy, role użytkownika pozostają bez zmian (nie są czyszczone).</p>
                </div>
            </label>

            <button type="submit" class="mt-3 px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800">Zapisz</button>
        </form>

        <div class="border-t border-slate-100 pt-4">
            <h4 class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Mapowania ({{ $roleMappings->count() }})</h4>

            @if($roleMappings->isEmpty())
                <p class="text-xs text-slate-400 mb-3">Brak skonfigurowanych mapowań.</p>
            @else
                <table class="w-full text-sm mb-4">
                    <thead>
                        <tr class="text-left text-xs text-slate-400 uppercase">
                            <th class="pb-2">Typ</th>
                            <th class="pb-2">Wartość Entra</th>
                            <th class="pb-2">Etykieta</th>
                            <th class="pb-2">Rola GRCTool</th>
                            <th class="pb-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roleMappings as $rm)
                        <tr class="border-t border-slate-50">
                            <td class="py-2 text-slate-600">{{ $rm->entra_type === 'app_role' ? 'App Role' : 'Grupa' }}</td>
                            <td class="py-2 font-mono text-xs text-slate-700">{{ $rm->entra_value }}</td>
                            <td class="py-2 text-slate-500">{{ $rm->label ?: '—' }}</td>
                            <td class="py-2"><span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-xs font-medium">{{ $rm->system_role }}</span></td>
                            <td class="py-2 text-right">
                                <form method="POST" action="{{ route('admin.entra.role-mappings.destroy', $rm) }}" onsubmit="return confirm('Usunąć to mapowanie?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs text-red-600 hover:underline">Usuń</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <form method="POST" action="{{ route('admin.entra.role-mappings.store') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-2 items-end bg-slate-50 rounded-lg p-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Typ</label>
                    <select name="entra_type" class="w-full px-2 py-1.5 border border-slate-300 rounded text-sm">
                        <option value="app_role">App Role</option>
                        <option value="group">Grupa (Object ID)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Wartość Entra</label>
                    <input type="text" name="entra_value" required placeholder="np. CISO lub GUID grupy"
                           class="w-full px-2 py-1.5 border border-slate-300 rounded text-sm font-mono">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Etykieta (opcjonalnie)</label>
                    <input type="text" name="label" placeholder="np. Zespół bezpieczeństwa"
                           class="w-full px-2 py-1.5 border border-slate-300 rounded text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Rola GRCTool</label>
                    <select name="system_role" required class="w-full px-2 py-1.5 border border-slate-300 rounded text-sm">
                        @foreach($availableRoles as $roleName)
                            <option value="{{ $roleName }}">{{ $roleName }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800">Dodaj</button>
            </form>
            @error('system_role')<p class="text-red-600 text-xs mt-2">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Setup instructions --}}
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-blue-900 mb-3">Instrukcja rejestracji aplikacji w Microsoft Entra</h3>
        <ol class="text-xs text-blue-800 space-y-1.5 list-decimal list-inside">
            <li>Przejdź do <strong>portal.azure.com</strong> → Microsoft Entra ID → App registrations → New registration</li>
            <li>Name: <code class="bg-blue-100 px-1 rounded">GRC Tool</code> (lub dowolna)</li>
            <li>Supported account types: <em>Single tenant</em> (zalecane) lub <em>Multitenant</em></li>
            <li>Redirect URI (Web): <code class="bg-blue-100 px-1 rounded">{{ url('/auth/microsoft/callback') }}</code></li>
            <li>Po rejestracji: skopiuj <strong>Application (client) ID</strong> i <strong>Directory (tenant) ID</strong></li>
            <li>Certificates & secrets → New client secret → skopiuj wartość (widoczna tylko raz)</li>
            <li>API permissions → Add → Microsoft Graph → Delegated → <strong>User.Read</strong>, <strong>openid</strong>, <strong>profile</strong>, <strong>email</strong> → Grant admin consent</li>
            <li>Dla synchronizacji Identity Protection: API permissions → Add → Microsoft Graph → <strong>Application</strong> → <strong>IdentityRiskEvent.Read.All</strong> → Grant admin consent</li>
            <li>Enterprise Applications → GRC Tool → Properties → <strong>Visible to users? = Yes</strong> → pojawi się w "Moje aplikacje"</li>
        </ol>
    </div>

    {{-- Role mapping setup instructions --}}
    <div class="mt-6 bg-amber-50 border border-amber-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-amber-900 mb-3">Instrukcja: powiązanie roli/grupy Entra ID z rolą GRCTool</h3>

        <p class="text-xs font-semibold text-amber-900 mb-1.5">Wariant A — App Role (zalecany, działa dla pojedynczych userów i całych grup)</p>
        <ol class="text-xs text-amber-800 space-y-1.5 list-decimal list-inside mb-4">
            <li>portal.azure.com → Microsoft Entra ID → App registrations → <strong>GRC Tool</strong> → App roles → Create app role</li>
            <li>Display name: np. <code class="bg-amber-100 px-1 rounded">CISO</code>; Allowed member types: <strong>Users/Groups</strong>; Value: np. <code class="bg-amber-100 px-1 rounded">CISO</code> (dokładnie ten string wpisujesz w polu "Wartość Entra" powyżej); zapisz</li>
            <li>Powtórz dla każdej roli GRCTool, którą chcesz sterować z Entra (np. <code class="bg-amber-100 px-1 rounded">security_engineer</code>, <code class="bg-amber-100 px-1 rounded">sales</code>)</li>
            <li>Entra ID → Enterprise applications → <strong>GRC Tool</strong> → Users and groups → Add user/group</li>
            <li>Wybierz konkretnego użytkownika <u>lub całą grupę</u> → wybierz utworzoną rolę App Role → Assign</li>
            <li>W GRCTool: dodaj mapowanie z Typ = <strong>App Role</strong>, Wartość Entra = dokładnie ta sama wartość "Value" co w kroku 2, Rola GRCTool = docelowa rola</li>
        </ol>

        <p class="text-xs font-semibold text-amber-900 mb-1.5">Wariant B — surowa grupa Entra ID (bez App Roles, po Object ID grupy)</p>
        <ol class="text-xs text-amber-800 space-y-1.5 list-decimal list-inside">
            <li>App registrations → <strong>GRC Tool</strong> → Token configuration → Add groups claim</li>
            <li>Zaznacz <strong>Security groups</strong> (lub All groups) dla ID token; zapisz</li>
            <li>Entra ID → Groups → skopiuj <strong>Object Id</strong> docelowej grupy</li>
            <li>W GRCTool: dodaj mapowanie z Typ = <strong>Grupa</strong>, Wartość Entra = ten Object ID, Rola GRCTool = docelowa rola</li>
        </ol>
        <p class="text-xs text-amber-700 mt-3">Uwaga: dla dużych organizacji (użytkownik należy do wielu grup) Entra może zwrócić w tokenie wskaźnik przeciążenia zamiast pełnej listy grup — w takim wypadku Wariant A (App Roles) jest niezawodny niezależnie od liczby grup.</p>
    </div>

</div>
@endsection
