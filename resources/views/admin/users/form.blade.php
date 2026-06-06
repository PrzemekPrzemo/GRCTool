@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">{{ $user->exists ? 'Edycja: '.$user->email : 'Nowy user' }}</h1>
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="bg-white rounded shadow p-6 max-w-2xl space-y-4">
    @csrf @if($user->exists) @method('PUT') @endif

    <div class="grid grid-cols-2 gap-4">
        <div><label class="text-sm">Imię i nazwisko *</label><input name="name" required value="{{ old('name', $user->name) }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
        @if(! $user->exists)
        <div><label class="text-sm">Email *</label><input name="email" type="email" required value="{{ old('email') }}" class="w-full px-3 py-2 border border-slate-300 rounded"></div>
        @endif
    </div>

    <div><label class="text-sm">Business Unit</label>
        <select name="business_unit_id" class="w-full px-3 py-2 border border-slate-300 rounded">
            <option value="">—</option>
            @foreach($businessUnits as $bu)<option value="{{ $bu->id }}" @selected(old('business_unit_id', $user->business_unit_id)==$bu->id)>{{ $bu->code }} – {{ $bu->name }}</option>@endforeach
        </select>
    </div>

    <fieldset class="border border-slate-200 rounded p-4">
        <legend class="text-sm font-medium px-2">External user (np. audytor zewnętrzny)</legend>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_external" value="1" @checked(old('is_external', $user->is_external))> Konto zewnętrzne</label>
        <input name="external_org" placeholder="Organizacja (np. Auditor Big Four)" value="{{ old('external_org', $user->external_org) }}" class="w-full px-3 py-2 border border-slate-300 rounded mt-2">
    </fieldset>

    @if($user->exists)
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))> Konto aktywne</label>
    @endif

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Metoda logowania</label>
        <div class="flex gap-3">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="auth_provider" value="local"
                       {{ old('auth_provider', $user->auth_provider ?? 'local') === 'local' ? 'checked' : '' }}
                       class="text-emerald-600">
                <span class="text-sm text-slate-700">Lokalne (hasło + MFA)</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="auth_provider" value="google"
                       {{ old('auth_provider', $user->auth_provider ?? 'local') === 'google' ? 'checked' : '' }}
                       class="text-emerald-600">
                <span class="text-sm text-slate-700 flex items-center gap-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Google Workspace
                </span>
            </label>
        </div>
        <p class="mt-1 text-xs text-slate-500">Konta Google Workspace logują się przez OAuth — hasło tymczasowe nie jest wysyłane.</p>
        @if($user->exists && $user->google_id)
        <div class="mt-1 flex items-center gap-2 text-sm text-blue-600">
            <svg class="w-4 h-4" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Połączono z Google ({{ $user->email }})
        </div>
        @endif
    </div>

    <fieldset class="border border-slate-200 rounded p-4">
        <legend class="text-sm font-medium px-2">Role (RBAC)</legend>
        <div class="grid grid-cols-2 gap-2 text-sm">
            @foreach($roles as $r)
                <label class="flex items-start gap-2">
                    <input type="checkbox" name="roles[]" value="{{ $r->name }}" @checked(in_array($r->name, $currentRoles)) class="mt-1">
                    <div><strong>{{ $r->name }}</strong><span class="text-xs text-slate-500 block">{{ $r->permissions_count ?? $r->permissions->count() }} permissions</span></div>
                </label>
            @endforeach
        </div>
    </fieldset>

    <div class="flex gap-2"><button class="px-4 py-2 bg-emerald-600 text-white rounded">{{ $user->exists ? 'Zapisz' : 'Utwórz (hasło tymczasowe wygenerowane)' }}</button><a href="{{ route('admin.users.index') }}" class="px-4 py-2 border border-slate-300 rounded">Anuluj</a></div>
</form>
@endsection
