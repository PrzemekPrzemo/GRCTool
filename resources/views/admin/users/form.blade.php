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
