@extends('layouts.app')
@section('content')

@php $isEdit = isset($assessment); @endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Edytuj ocenę' : 'Nowa ocena compliance' }}</h1>
        @if($isEdit)
        <p class="text-sm text-slate-500 mt-0.5">{{ $assessment->code }}</p>
        @endif
    </div>
    <a href="{{ $isEdit ? route('compliance.show', $assessment) : route('compliance.index') }}"
       class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
        ← Anuluj
    </a>
</div>

<div class="max-w-2xl">
    <form method="POST"
          action="{{ $isEdit ? route('compliance.update', $assessment) : route('compliance.store') }}"
          class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        @csrf
        @if($isEdit) @method('PUT') @endif

        {{-- Framework --}}
        <div x-data="{ selectedFw: {{ $selected ?? 'null' }}, reqCount: 0, frameworks: {{ $frameworks->map(fn($f) => ['id' => $f->id, 'name' => $f->name, 'short_name' => $f->short_name, 'req_count' => $f->requirementsCount()])->toJson() }} }"
             x-init="if (selectedFw) { let fw = frameworks.find(f => f.id == selectedFw); if (fw) reqCount = fw.req_count; }">

            <label class="block text-sm font-medium text-slate-700 mb-1">Framework / Norma <span class="text-red-500">*</span></label>
            <select name="framework_id" required
                    x-model="selectedFw"
                    @change="let fw = frameworks.find(f => f.id == parseInt(selectedFw)); reqCount = fw ? fw.req_count : 0"
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('framework_id') border-red-500 @enderror">
                <option value="">— Wybierz framework —</option>
                @foreach($frameworks as $fw)
                <option value="{{ $fw->id }}" @selected(old('framework_id', $selected) == $fw->id)>{{ $fw->short_name }} — {{ $fw->name }}</option>
                @endforeach
            </select>
            <p x-show="selectedFw && reqCount > 0" class="text-xs text-emerald-600 mt-1">
                Ten framework zawiera <strong x-text="reqCount"></strong> wymagań do oceny.
            </p>
            @error('framework_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Title --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tytuł oceny <span class="text-red-500">*</span></label>
            <input type="text" name="title" required maxlength="256"
                   value="{{ old('title', $assessment->title ?? '') }}"
                   placeholder="np. Ocena ISO 27001 Q1 2026"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('title') border-red-500 @enderror">
            @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Scope --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Zakres oceny</label>
            <textarea name="scope" rows="3"
                      placeholder="Opisz zakres oceny (systemy, procesy, jednostki organizacyjne...)"
                      class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none">{{ old('scope', $assessment->scope ?? '') }}</textarea>
        </div>

        {{-- Date & Conducted by --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Data oceny</label>
                <input type="date" name="assessment_date"
                       value="{{ old('assessment_date', isset($assessment) ? $assessment->assessment_date?->format('Y-m-d') : '') }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Przeprowadzający</label>
                <select name="conducted_by" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                    <option value="">— Wybierz osobę —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(old('conducted_by', $assessment->conducted_by ?? null) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Notes --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Notatki</label>
            <textarea name="notes" rows="3"
                      placeholder="Dodatkowe informacje o ocenie..."
                      class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none">{{ old('notes', $assessment->notes ?? '') }}</textarea>
        </div>

        <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
            <button type="submit" class="px-5 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                {{ $isEdit ? 'Zapisz zmiany' : 'Utwórz ocenę' }}
            </button>
            <a href="{{ $isEdit ? route('compliance.show', $assessment) : route('compliance.index') }}"
               class="px-5 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
                Anuluj
            </a>
        </div>
    </form>
</div>

@endsection
