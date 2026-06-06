@extends('layouts.app')
@section('content')

@php
$builtinRoles = \App\Http\Controllers\Admin\RoleController::BUILTIN_ROLES;
$isBuiltin = $role->exists && in_array($role->name, $builtinRoles);

$moduleLabels = [
    'asset' => 'Aktywa', 'risk' => 'Ryzyka', 'risk_acceptance' => 'Akceptacje ryzyk',
    'control' => 'Kontrole', 'indicator' => 'Wskaźniki KPI/KRI',
    'vulnerability' => 'Podatności', 'incident' => 'Incydenty', 'nis2' => 'NIS2',
    'audit_engagement' => 'Engagementy audytowe', 'finding' => 'Wnioski audytu',
    'cap' => 'CAP (działania naprawcze)', 'report' => 'Raporty',
    'evidence' => 'Dowody (evidence)', 'policy' => 'Polityki',
    'third_party' => 'Podmioty trzecie (TPRM)', 'subprocessor' => 'Podprocesory',
    'questionnaire' => 'Ankiety', 'client' => 'Klienci',
    'business_unit' => 'Jednostki org.', 'project' => 'Projekty',
    'user' => 'Użytkownicy', 'role' => 'Role', 'audit_log' => 'Dziennik audytu',
    'framework' => 'Frameworki (legacy)', 'rcp' => 'Rejestr czynności (RODO)',
    'gdpr_breach' => 'Naruszenia RODO', 'dpia' => 'DPIA',
    'dsar' => 'Wnioski DSAR', 'training' => 'Szkolenia',
    'exception' => 'Wyjątki bezpieczeństwa', 'certificate' => 'Certyfikaty',
    'crypto_key' => 'Klucze kryptograficzne', 'bcp' => 'BCP/DR',
    'sdlc' => 'Secure SDLC', 'access_review' => 'Przeglądy dostępu (IAM)',
    'compliance' => 'Compliance',
];

$allPerms = \Spatie\Permission\Models\Permission::orderBy('name')->get();
$currentPerms = $role->exists ? $role->permissions->pluck('name')->flip()->all() : [];
$standardActions = ['view', 'create', 'update', 'delete'];
$modules = [];
$extras = [];
foreach ($allPerms as $perm) {
    [$mod, $action] = explode('.', $perm->name, 2);
    if (in_array($action, $standardActions)) {
        $modules[$mod][$action] = $perm->name;
    } else {
        $extras[] = $perm->name;
    }
}
ksort($modules);
@endphp

<div class="flex items-center gap-2 mb-4">
    <a href="{{ route('admin.roles.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Role</a>
    <span class="text-slate-300">/</span>
    <h1 class="text-2xl font-semibold">
        @if($role->exists)
            Edytuj: <span class="font-mono text-lg">{{ $role->name }}</span>
        @else
            Nowa rola
        @endif
    </h1>
</div>

@if(session('status'))
    <div class="mb-4 px-4 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded text-sm">{{ session('status') }}</div>
@endif
@if($errors->any())
    <div class="mb-4 px-4 py-2 bg-red-50 border border-red-200 text-red-800 rounded text-sm">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}"
      x-data="roleForm()">
    @csrf
    @if($role->exists) @method('PUT') @endif

    {{-- Role name --}}
    <div class="bg-white rounded shadow p-4 mb-4">
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Nazwa roli
            @if($isBuiltin)
                <span class="ml-2 text-xs text-slate-400 font-normal">Wbudowane role mają zablokowaną zmianę nazwy</span>
            @endif
        </label>
        @if($role->exists)
            {{-- For editing, name is not submitted (built-in or custom) --}}
            <input type="text"
                   value="{{ $role->name }}"
                   @if($isBuiltin) disabled @else disabled @endif
                   class="w-full max-w-sm px-3 py-1.5 border border-slate-200 rounded text-sm font-mono bg-slate-50 text-slate-500 cursor-not-allowed">
        @else
            <input type="text"
                   name="name"
                   value="{{ old('name') }}"
                   placeholder="np. it_manager"
                   class="w-full max-w-sm px-3 py-1.5 border border-slate-300 rounded text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('name') border-red-400 @enderror"
                   required>
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-slate-500">Używaj snake_case, np. <span class="font-mono">it_manager</span></p>
        @endif
    </div>

    {{-- Permission grid --}}
    <div class="bg-white rounded shadow overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">Uprawnienia</h2>
            <div class="flex items-center gap-2">
                <button type="button" @click="selectAll()" class="px-2 py-1 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded transition-colors">Zaznacz wszystkie</button>
                <button type="button" @click="deselectAll()" class="px-2 py-1 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded transition-colors">Odznacz wszystkie</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="sticky left-0 bg-slate-50 px-3 py-2 text-left text-xs font-semibold text-slate-600 w-52 min-w-[13rem] z-10">Moduł</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-slate-500 w-20">View</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-slate-500 w-20">Create</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-slate-500 w-20">Update</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-slate-500 w-20">Delete</th>
                        <th class="px-3 py-2 text-center text-xs font-semibold text-slate-500 w-24">Wszystkie</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modules as $mod => $actions)
                    @php
                        $rowId = 'mod_' . $mod;
                        $rowPermNames = array_values($actions);
                    @endphp
                    <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors" x-data="moduleRow({{ json_encode($rowPermNames) }})">
                        <td class="sticky left-0 bg-white hover:bg-slate-50 px-3 py-2 z-10">
                            <span class="text-xs font-medium text-slate-700">{{ $moduleLabels[$mod] ?? $mod }}</span>
                            <span class="ml-1 text-xs text-slate-400 font-mono">({{ $mod }})</span>
                        </td>
                        @foreach($standardActions as $action)
                            <td class="px-3 py-2 text-center">
                                @if(isset($actions[$action]))
                                    <input type="checkbox"
                                           name="permissions[]"
                                           value="{{ $actions[$action] }}"
                                           @checked(array_key_exists($actions[$action], $currentPerms) || old('permissions') !== null && in_array($actions[$action], old('permissions', [])))
                                           x-model="checked"
                                           :value="'{{ $actions[$action] }}'"
                                           class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                                @else
                                    <span class="text-slate-200">—</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="px-3 py-2 text-center">
                            <button type="button"
                                    @click="toggleAll()"
                                    class="px-2 py-0.5 text-xs rounded transition-colors"
                                    :class="allChecked ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    x-text="allChecked ? 'Odznacz' : 'Zaznacz'">
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Extra permissions --}}
    @if(count($extras) > 0)
    <div class="bg-white rounded shadow p-4 mb-4">
        <h2 class="font-semibold text-slate-800 mb-3">Dodatkowe uprawnienia</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
            @foreach($extras as $permName)
            @php [$eMod, $eAction] = explode('.', $permName, 2); @endphp
            <label class="flex items-center gap-2 px-2 py-1.5 rounded border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50 cursor-pointer transition-colors text-xs">
                <input type="checkbox"
                       name="permissions[]"
                       value="{{ $permName }}"
                       @checked(array_key_exists($permName, $currentPerms) || old('permissions') !== null && in_array($permName, old('permissions', [])))
                       class="w-3.5 h-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <span class="font-mono text-slate-600">{{ $permName }}</span>
            </label>
            @endforeach
        </div>
    </div>
    @endif

    <div class="flex items-center gap-3">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded text-sm hover:bg-emerald-700 transition-colors font-medium">
            {{ $role->exists ? 'Zapisz uprawnienia' : 'Utwórz rolę' }}
        </button>
        <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded text-sm hover:bg-slate-200 transition-colors">Anuluj</a>
    </div>
</form>

<script>
function roleForm() {
    return {
        selectAll() {
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => { cb.checked = true; cb.dispatchEvent(new Event('change')); });
        },
        deselectAll() {
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => { cb.checked = false; cb.dispatchEvent(new Event('change')); });
        }
    }
}

function moduleRow(permNames) {
    return {
        checked: [],
        get allChecked() {
            if (permNames.length === 0) return false;
            return permNames.every(p => {
                const cb = document.querySelector(`input[name="permissions[]"][value="${p}"]`);
                return cb ? cb.checked : false;
            });
        },
        toggleAll() {
            const shouldCheck = !this.allChecked;
            permNames.forEach(p => {
                const cb = document.querySelector(`input[name="permissions[]"][value="${p}"]`);
                if (cb) { cb.checked = shouldCheck; cb.dispatchEvent(new Event('change')); }
            });
        }
    }
}
</script>
@endsection
