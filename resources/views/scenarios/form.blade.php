@extends('layouts.app')
@section('content')

@php $edit = $scenario->exists; @endphp

<div class="flex items-center justify-between mb-5">
    <h1 class="text-2xl font-semibold">{{ $edit ? 'Edytuj scenariusz: '.$scenario->code : 'Nowy scenariusz' }}</h1>
    <a href="{{ $edit ? route('scenarios.show', $scenario) : route('scenarios.index') }}"
       class="px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-sm hover:bg-slate-200 transition-colors">← Anuluj</a>
</div>

<form method="POST" action="{{ $edit ? route('scenarios.update', $scenario) : route('scenarios.store') }}">
    @csrf
    @if($edit) @method('PUT') @endif

    <div class="space-y-5">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Identyfikacja</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kod <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $scenario->code) }}"
                           class="w-full rounded-lg border-slate-300 text-sm @error('code') border-red-400 @enderror"
                           placeholder="np. CYBER-001" required>
                    @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nazwa <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $scenario->name) }}"
                           class="w-full rounded-lg border-slate-300 text-sm @error('name') border-red-400 @enderror" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kategoria główna <span class="text-red-500">*</span></label>
                    <select name="category_l1" class="w-full rounded-lg border-slate-300 text-sm" required>
                        @foreach(['Cyber','Compliance','Operational'] as $c)
                        <option value="{{ $c }}" @selected(old('category_l1', $scenario->category_l1) === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Podkategoria <span class="text-red-500">*</span></label>
                    <input type="text" name="category_l2" value="{{ old('category_l2', $scenario->category_l2) }}"
                           class="w-full rounded-lg border-slate-300 text-sm" placeholder="np. Ransomware, Data Breach, IAM" required>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Opis</label>
                    <textarea name="description" rows="3"
                              class="w-full rounded-lg border-slate-300 text-sm">{{ old('description', $scenario->description) }}</textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           {{ old('is_active', $scenario->is_active ?? true) ? 'checked' : '' }}
                           class="rounded border-slate-300">
                    <label for="is_active" class="text-sm text-slate-700">Aktywny (widoczny w bibliotece)</label>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Aktorzy i techniki (opcjonalne)</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Domyślni aktorzy zagrożeń</label>
                    <textarea name="default_threat_actors_raw" rows="3"
                              class="w-full rounded-lg border-slate-300 text-sm font-mono text-xs"
                              placeholder="JSON array, np. [&quot;APT28&quot;, &quot;Insider&quot;]">{{ old('default_threat_actors_raw', json_encode($scenario->default_threat_actors ?? [], JSON_UNESCAPED_UNICODE)) }}</textarea>
                    <p class="text-xs text-slate-400 mt-1">Format JSON: ["APT28", "Insider"]</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Techniki MITRE ATT&CK</label>
                    <textarea name="default_mitre_techniques_raw" rows="3"
                              class="w-full rounded-lg border-slate-300 text-sm font-mono text-xs"
                              placeholder="JSON array, np. [&quot;T1566&quot;, &quot;T1078&quot;]">{{ old('default_mitre_techniques_raw', json_encode($scenario->default_mitre_techniques ?? [], JSON_UNESCAPED_UNICODE)) }}</textarea>
                    <p class="text-xs text-slate-400 mt-1">Format JSON: ["T1566", "T1078"]</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-3 mt-5">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">Zapisz</button>
        <a href="{{ $edit ? route('scenarios.show', $scenario) : route('scenarios.index') }}"
           class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm hover:bg-slate-200 transition-colors">Anuluj</a>
    </div>
</form>
@endsection
