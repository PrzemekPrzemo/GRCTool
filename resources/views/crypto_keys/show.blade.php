@extends('layouts.app')
@section('content')

<div class="mb-4">
    <a href="{{ route('crypto-keys.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Klucze</a>
</div>

<div class="grid grid-cols-3 gap-4">
    <div class="col-span-2 space-y-4">
        <div class="bg-white rounded shadow p-4">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <span class="font-mono text-xs text-slate-500">{{ $cryptoKey->code }}</span>
                    <h1 class="text-xl font-semibold">{{ $cryptoKey->name }}</h1>
                </div>
                @if($cryptoKey->is_active)
                <span class="px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700">Aktywny</span>
                @else
                <span class="px-2 py-0.5 rounded text-xs bg-slate-200 text-slate-500">Nieaktywny</span>
                @endif
            </div>

            @if($cryptoKey->isOverdueForRotation())
            <div class="bg-red-50 border border-red-200 rounded p-3 mb-4 text-sm text-red-700">
                Rotacja klucza jest zaległa od {{ $cryptoKey->next_rotation_due->format('Y-m-d') }}!
            </div>
            @endif

            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-slate-500">Typ:</span> {{ $cryptoKey->key_type }}</div>
                <div><span class="text-slate-500">Algorytm:</span> {{ $cryptoKey->algorithm ?? '—' }}</div>
                <div><span class="text-slate-500">Rozmiar:</span> {{ $cryptoKey->key_size ? $cryptoKey->key_size.' bit' : '—' }}</div>
                <div><span class="text-slate-500">Lokalizacja:</span> {{ $cryptoKey->storage_location }}</div>
                <div><span class="text-slate-500">ID klucza:</span> {{ $cryptoKey->key_id ?? '—' }}</div>
                <div><span class="text-slate-500">Przeznaczenie:</span> {{ $cryptoKey->purpose ?? '—' }}</div>
                <div><span class="text-slate-500">Rotacja co:</span> {{ $cryptoKey->rotation_days }} dni</div>
                <div><span class="text-slate-500">Ostatnia rotacja:</span> {{ $cryptoKey->last_rotated_at?->format('Y-m-d') ?? '—' }}</div>
                <div><span class="text-slate-500">Następna rotacja:</span>
                    <span class="{{ $cryptoKey->isOverdueForRotation() ? 'text-red-600 font-medium' : '' }}">
                        {{ $cryptoKey->next_rotation_due?->format('Y-m-d') ?? '—' }}
                    </span>
                </div>
                <div><span class="text-slate-500">Właściciel:</span> {{ $cryptoKey->owner?->name ?? '—' }}</div>
            </div>

            @if($cryptoKey->notes)
            <div class="mt-3 text-sm text-slate-600 bg-slate-50 rounded p-3">{{ $cryptoKey->notes }}</div>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold text-sm mb-3">Akcje</h2>
            <a href="{{ route('crypto-keys.edit', $cryptoKey) }}" class="block w-full text-center px-3 py-1.5 bg-slate-100 rounded text-sm hover:bg-slate-200 mb-2">Edytuj</a>
            <form method="POST" action="{{ route('crypto-keys.rotate', $cryptoKey) }}">
                @csrf
                <button type="submit" class="w-full px-3 py-1.5 bg-amber-500 text-white rounded text-sm hover:bg-amber-600">Zarejestruj rotację</button>
            </form>
        </div>
    </div>
</div>
@endsection
