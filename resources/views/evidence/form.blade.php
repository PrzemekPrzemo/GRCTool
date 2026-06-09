@extends('layouts.app')
@section('content')

<div class="mb-4">
    <a href="{{ $evidence->exists ? route('evidence.show', $evidence) : route('evidence.index') }}" class="text-sm text-emerald-700 hover:underline">
        &larr; {{ $evidence->exists ? 'Wróć do dowodu' : 'Rejestr dowodów' }}
    </a>
    <h1 class="text-2xl font-semibold mt-1">{{ $evidence->exists ? 'Edytuj dowód' : 'Nowy dowód' }}</h1>
</div>

@if(session('error'))
<div class="mb-3 bg-red-50 border border-red-200 text-red-800 rounded px-3 py-2 text-sm">{{ session('error') }}</div>
@endif

<form
    method="POST"
    action="{{ $evidence->exists ? route('evidence.update', $evidence) : route('evidence.store') }}"
    class="space-y-4 max-w-3xl"
    x-data="{
        validFrom: '{{ old('valid_from', $evidence->valid_from?->format('Y-m-d') ?? '') }}',
        validUntil: '{{ old('valid_until', $evidence->valid_until?->format('Y-m-d') ?? '') }}',
        get dateError() {
            return this.validFrom && this.validUntil && this.validUntil < this.validFrom;
        }
    }"
>
    @csrf
    @if($evidence->exists) @method('PUT') @endif

    {{-- 1. Metadane --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 space-y-4">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Metadane</h2>

        <div>
            <label class="block text-sm font-medium mb-1">Tytuł <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $evidence->title) }}" required
                   class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Klasyfikacja <span class="text-red-500">*</span></label>
            <select name="classification" required class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @foreach(['Public', 'Internal', 'Confidential', 'Restricted'] as $cls)
                <option value="{{ $cls }}" @selected(old('classification', $evidence->classification) === $cls)>{{ $cls }}</option>
                @endforeach
            </select>
            @error('classification')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Opis</label>
            <textarea name="description" rows="3" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('description', $evidence->description) }}</textarea>
            @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- 2. Ważność --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 space-y-4">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Ważność</h2>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Ważne od</label>
                <input type="date" name="valid_from" x-model="validFrom"
                       class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @error('valid_from')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Ważne do</label>
                <input type="date" name="valid_until" x-model="validUntil"
                       :class="dateError ? 'border-red-400' : 'border-slate-300'"
                       class="w-full border rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <p x-show="dateError" x-transition class="mt-1 text-xs text-red-600">Data musi być równa lub późniejsza niż „Ważne od"</p>
                @error('valid_until')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Retencja do</label>
                <input type="date" name="retention_until" value="{{ old('retention_until', $evidence->retention_until?->format('Y-m-d')) }}"
                       class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @error('retention_until')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- 3. Dodatkowe --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 space-y-4">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Dodatkowe</h2>

        <div>
            <label class="block text-sm font-medium mb-1">Tagi</label>
            <input type="text" name="tags_raw" value="{{ old('tags_raw', implode(', ', (array)($evidence->tags ?? []))) }}"
                   placeholder="np. audyt, ISO27001, roczny"
                   class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <p class="text-xs text-slate-400 mt-1">Oddziel tagi przecinkami</p>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Klient</label>
            <select name="client_id" class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">— brak —</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}" @selected(old('client_id', $evidence->client_id) == $client->id)>{{ $client->name }}</option>
                @endforeach
            </select>
            @error('client_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        @unless($evidence->exists)
        <div class="flex items-center gap-2">
            <input type="hidden" name="is_immutable" value="0">
            <input type="checkbox" name="is_immutable" value="1" id="is_immutable"
                   @checked(old('is_immutable', false)) class="rounded border-slate-300 text-emerald-600">
            <label for="is_immutable" class="text-sm">Niezmienialny (blokada edycji po zapisaniu)</label>
        </div>
        @endunless
    </div>

    {{-- 4. Informacja o pliku --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 space-y-4">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Informacja o pliku</h2>
        <p class="text-xs text-slate-400">Pola opcjonalne — uzupełnij jeśli dowód jest powiązany z plikiem.</p>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nazwa pliku / ścieżka</label>
                <input type="text" name="original_filename" value="{{ old('original_filename', $evidence->original_filename) }}"
                       placeholder="np. raport_audytu_2025.pdf"
                       class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @error('original_filename')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">SHA-256 <span class="text-slate-400 font-normal">(opcjonalne)</span></label>
                <input type="text" name="sha256" value="{{ old('sha256', $evidence->sha256) }}"
                       placeholder="skrót pliku"
                       class="w-full border border-slate-300 rounded px-3 py-1.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @error('sha256')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Validation errors summary --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded p-3 text-sm">
        <ul class="list-disc ml-4 space-y-0.5">
            @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Actions --}}
    <div class="flex gap-2">
        <button type="submit" :disabled="dateError" :class="dateError ? 'opacity-50 cursor-not-allowed' : ''"
                class="px-4 py-2 bg-emerald-600 text-white rounded text-sm hover:bg-emerald-700 transition-colors">
            Zapisz
        </button>
        <a href="{{ $evidence->exists ? route('evidence.show', $evidence) : route('evidence.index') }}"
           class="px-4 py-2 bg-slate-200 rounded text-sm hover:bg-slate-300 transition-colors">
            Anuluj
        </a>
    </div>
</form>

@endsection
