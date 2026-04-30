@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-semibold mb-4">{{ $asset->exists ? 'Edycja aktywa: '.$asset->code : 'Nowe aktywo' }}</h1>
<form method="POST" action="{{ $asset->exists ? route('assets.update', $asset) : route('assets.store') }}" class="bg-white rounded shadow p-6 max-w-3xl space-y-4">
    @csrf
    @if($asset->exists) @method('PUT') @endif

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Kod *</label>
            <input name="code" required value="{{ old('code', $asset->code) }}" class="w-full px-3 py-2 border border-slate-300 rounded">
            @error('code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Typ *</label>
            <select name="type" required class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($types as $t)<option value="{{ $t }}" @selected(old('type', $asset->type)===$t)>{{ $t }}</option>@endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Nazwa *</label>
        <input name="name" required value="{{ old('name', $asset->name) }}" class="w-full px-3 py-2 border border-slate-300 rounded">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Opis</label>
        <textarea name="description" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded">{{ old('description', $asset->description) }}</textarea>
    </div>

    <fieldset class="border border-slate-200 rounded p-4">
        <legend class="text-sm font-medium px-2">CIA Triad (1=niski, 4=krytyczny)</legend>
        <p class="text-xs text-slate-500 mb-3">Krytyczność wyliczana automatycznie. Jeśli wszystkie ≥3, asset oznaczany jako Critical.</p>
        <div class="grid grid-cols-3 gap-3">
            @foreach(['confidentiality','integrity','availability'] as $key)
                <div>
                    <label class="block text-sm">{{ ucfirst($key) }}</label>
                    <select name="{{ $key }}_impact" class="w-full px-2 py-1.5 border border-slate-300 rounded">
                        @for($i=1;$i<=4;$i++)
                            <option value="{{ $i }}" @selected(old("{$key}_impact", $asset->{$key.'_impact'} ?? 2)==$i)>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            @endforeach
        </div>
    </fieldset>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Środowisko</label>
            <select name="environment" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($environments as $e)<option value="{{ $e }}" @selected(old('environment', $asset->environment ?? 'prod')===$e)>{{ $e }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Klasyfikacja danych</label>
            <select name="data_classification" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($classifications as $c)<option value="{{ $c }}" @selected(old('data_classification', $asset->data_classification ?? 'Internal')===$c)>{{ $c }}</option>@endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Owner</label>
            <select name="owner_id" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">— brak —</option>
                @foreach($users as $u)<option value="{{ $u->id }}" @selected(old('owner_id', $asset->owner_id)==$u->id)>{{ $u->name }} ({{ $u->email }})</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Custodian</label>
            <select name="custodian_id" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">— brak —</option>
                @foreach($users as $u)<option value="{{ $u->id }}" @selected(old('custodian_id', $asset->custodian_id)==$u->id)>{{ $u->name }}</option>@endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Business Unit</label>
            <select name="business_unit_id" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">—</option>
                @foreach($businessUnits as $bu)<option value="{{ $bu->id }}" @selected(old('business_unit_id', $asset->business_unit_id)==$bu->id)>{{ $bu->code }} – {{ $bu->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Klient</label>
            <select name="client_id" class="w-full px-3 py-2 border border-slate-300 rounded">
                <option value="">—</option>
                @foreach($clients as $c)<option value="{{ $c->id }}" @selected(old('client_id', $asset->client_id)==$c->id)>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Lifecycle</label>
            <select name="lifecycle_status" class="w-full px-3 py-2 border border-slate-300 rounded">
                @foreach($lifecycles as $l)<option value="{{ $l }}" @selected(old('lifecycle_status', $asset->lifecycle_status ?? 'active')===$l)>{{ $l }}</option>@endforeach
            </select>
        </div>
    </div>

    <div class="flex gap-2 pt-2">
        <button class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">{{ $asset->exists ? 'Zapisz' : 'Utwórz' }}</button>
        <a href="{{ route('assets.index') }}" class="px-4 py-2 border border-slate-300 rounded hover:bg-slate-50">Anuluj</a>
    </div>
</form>
@endsection
