@extends('layouts.app')
@section('content')
@php $isEdit = !is_null($procedure); @endphp

<div class="flex items-center gap-3 mb-6">
    <a href="{{ $isEdit ? route('procedures.show', $procedure) : route('procedures.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Procedury</a>
    <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Edytuj procedurę' : 'Nowa procedura' }}</h1>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4 text-sm">
    <ul class="list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('warning'))
<div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 rounded mb-4 text-sm">{{ session('warning') }}</div>
@endif

<form method="POST" action="{{ $isEdit ? route('procedures.update', $procedure) : route('procedures.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white rounded shadow p-5 border-2 border-dashed border-emerald-200">
        <h2 class="font-semibold text-slate-700 mb-1">Import z pliku Word</h2>
        <p class="text-xs text-slate-500 mb-3">Wgraj plik .docx, aby automatycznie wypełnić pole „Pełna treść” jego treścią. Oryginalny plik zostanie zapisany jako załącznik do pobrania. Wgranie nowego pliku nadpisze obecną treść. Kroki procedury (tabela poniżej) trzeba uzupełnić ręcznie.</p>
        <input type="file" name="source_document" accept=".docx" class="block text-sm">
        @error('source_document')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="bg-white rounded shadow p-5">
        <h2 class="font-semibold text-slate-700 mb-4">Informacje podstawowe</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Kod <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $procedure?->code) }}" required
                    placeholder="np. PROC-2026-001"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm font-mono">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Wersja <span class="text-red-500">*</span></label>
                <input type="text" name="version" value="{{ old('version', $procedure?->version ?? '1.0') }}" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Tytuł <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $procedure?->title) }}" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Pełna treść</label>
                <textarea name="description" rows="6" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">{{ old('description', $procedure?->description) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Powiązana polityka</label>
                <input type="text" name="policy_ref" value="{{ old('policy_ref', $procedure?->policy_ref) }}"
                    placeholder="np. TSH-SEC-POL-003"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Encja GRC</label>
                <input type="text" name="related_model" value="{{ old('related_model', $procedure?->related_model) }}"
                    placeholder="np. Incident, Risk, Asset (opcjonalnie)"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Właściciel (rola)</label>
                <input type="text" name="owner_role" value="{{ old('owner_role', $procedure?->owner_role) }}"
                    placeholder="np. CSO, IT Lead"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
                    @foreach(['Draft','Approved','Active','Retired'] as $s)
                        <option @selected(old('status', $procedure?->status ?? 'Approved') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Data wejścia w życie</label>
                <input type="date" name="effective_from" value="{{ old('effective_from', $procedure?->effective_from?->format('Y-m-d')) }}"
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm">
            </div>
        </div>
    </div>

    @php
        $initialSteps = old('steps', $procedure?->steps?->map(fn ($s) => [
            'description' => $s->description, 'owner_role' => $s->owner_role, 'sla' => $s->sla, 'tool' => $s->tool,
        ])->all() ?? []);
    @endphp
    <div class="bg-white rounded shadow p-5" x-data="{ steps: {{ Illuminate\Support\Js::from($initialSteps ?: []) }} }">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-slate-700">Kroki</h2>
            <button type="button" @click="steps.push({description: '', owner_role: '', sla: '', tool: ''})"
                class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded text-xs font-medium">+ Dodaj krok</button>
        </div>

        <template x-if="steps.length === 0">
            <p class="text-sm text-slate-400">Brak kroków — dodaj przynajmniej jeden, jeśli procedura ma być operacyjnym playbookiem.</p>
        </template>

        <div class="space-y-3">
            <template x-for="(step, i) in steps" :key="i">
                <div class="border border-slate-200 rounded p-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-slate-500" x-text="'Krok ' + (i + 1)"></span>
                        <button type="button" @click="steps.splice(i, 1)" class="text-xs text-red-600 hover:underline">Usuń</button>
                    </div>
                    <textarea :name="'steps[' + i + '][description]'" x-model="step.description" rows="2" required
                        placeholder="Opis kroku" class="w-full px-2 py-1.5 border border-slate-300 rounded text-sm mb-2"></textarea>
                    <div class="grid grid-cols-3 gap-2">
                        <input type="text" :name="'steps[' + i + '][owner_role]'" x-model="step.owner_role" placeholder="Rola/odpowiedzialny" class="px-2 py-1.5 border border-slate-300 rounded text-xs">
                        <input type="text" :name="'steps[' + i + '][sla]'" x-model="step.sla" placeholder="SLA, np. 24h" class="px-2 py-1.5 border border-slate-300 rounded text-xs">
                        <input type="text" :name="'steps[' + i + '][tool]'" x-model="step.tool" placeholder="Narzędzie" class="px-2 py-1.5 border border-slate-300 rounded text-xs">
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded text-sm font-medium">
            {{ $isEdit ? 'Zapisz zmiany' : 'Dodaj procedurę' }}
        </button>
        <a href="{{ $isEdit ? route('procedures.show', $procedure) : route('procedures.index') }}" class="px-4 py-2 border border-slate-300 rounded text-sm text-slate-700">Anuluj</a>
    </div>
</form>
@endsection
