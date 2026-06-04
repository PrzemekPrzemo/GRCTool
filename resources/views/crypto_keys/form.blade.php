@extends('layouts.app')
@section('content')

<div class="mb-4">
    <a href="{{ route('crypto-keys.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Klucze</a>
    <h1 class="text-2xl font-semibold mt-1">{{ $cryptoKey->exists ? 'Edytuj klucz' : 'Nowy klucz kryptograficzny' }}</h1>
</div>

<form method="POST" action="{{ $cryptoKey->exists ? route('crypto-keys.update', $cryptoKey) : route('crypto-keys.store') }}" class="space-y-4 max-w-2xl">
    @csrf
    @if($cryptoKey->exists) @method('PUT') @endif

    <div class="bg-white rounded shadow p-4 space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Nazwa *</label>
            <input type="text" name="name" value="{{ old('name', $cryptoKey->name) }}" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Typ klucza *</label>
                <select name="key_type" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                    @foreach(['AES', 'RSA', 'EC', 'HMAC', 'EdDSA'] as $kt)
                    <option value="{{ $kt }}" @selected(old('key_type', $cryptoKey->key_type) === $kt)>{{ $kt }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Algorytm</label>
                <input type="text" name="algorithm" value="{{ old('algorithm', $cryptoKey->algorithm) }}" placeholder="AES-256-GCM, RSA-2048..." class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Rozmiar klucza (bit)</label>
                <input type="number" name="key_size" value="{{ old('key_size', $cryptoKey->key_size) }}" min="0" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Lokalizacja przechowywania *</label>
                <select name="storage_location" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                    @foreach(['HSM', 'KMS', 'Vault', 'filesystem', 'TPM'] as $loc)
                    <option value="{{ $loc }}" @selected(old('storage_location', $cryptoKey->storage_location) === $loc)>{{ $loc }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">ID klucza (zewnętrzny)</label>
                <input type="text" name="key_id" value="{{ old('key_id', $cryptoKey->key_id) }}" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Przeznaczenie</label>
                <input type="text" name="purpose" value="{{ old('purpose', $cryptoKey->purpose) }}" placeholder="encryption, signing..." class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Rotacja (dni) *</label>
                <input type="number" name="rotation_days" value="{{ old('rotation_days', $cryptoKey->rotation_days ?? 365) }}" min="1" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Ostatnia rotacja</label>
                <input type="date" name="last_rotated_at" value="{{ old('last_rotated_at', $cryptoKey->last_rotated_at?->format('Y-m-d')) }}" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Właściciel</label>
            <select name="owner_id" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">
                <option value="">— brak —</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" @selected(old('owner_id', $cryptoKey->owner_id) == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $cryptoKey->is_active ?? true)) id="is_active" class="rounded">
            <label for="is_active" class="text-sm">Aktywny</label>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Uwagi</label>
            <textarea name="notes" rows="2" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm">{{ old('notes', $cryptoKey->notes) }}</textarea>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded p-3 text-sm">
        <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded text-sm">Zapisz</button>
        <a href="{{ route('crypto-keys.index') }}" class="px-4 py-2 bg-slate-200 rounded text-sm">Anuluj</a>
    </div>
</form>
@endsection
