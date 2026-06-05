@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('compliance.admin.frameworks') }}" class="text-sm text-slate-500 hover:text-slate-700 transition-colors">Frameworki</a>
            <span class="text-slate-300">/</span>
            <a href="{{ route('compliance.admin.frameworks.domains', $framework) }}" class="text-sm text-slate-500 hover:text-slate-700 transition-colors">{{ $framework->short_name }}</a>
            <span class="text-slate-300">/</span>
            <a href="{{ route('compliance.admin.requirements.index', [$framework, $domain]) }}" class="text-sm text-slate-500 hover:text-slate-700 transition-colors">{{ $domain->code }}</a>
            <span class="text-slate-300">/</span>
            <span class="text-sm text-slate-700 font-medium">{{ $requirement ? 'Edytuj' : 'Nowe wymaganie' }}</span>
        </div>
        <h1 class="text-2xl font-semibold">
            {{ $requirement ? 'Edytuj wymaganie' : 'Nowe wymaganie' }}
        </h1>
        <p class="text-sm text-slate-500 mt-0.5">{{ $domain->name }} — {{ $framework->name }}</p>
    </div>
    <a href="{{ route('compliance.admin.requirements.index', [$framework, $domain]) }}"
       class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
        ← Powrót
    </a>
</div>

<div class="max-w-2xl">
    <form method="POST"
          action="{{ $requirement
            ? route('compliance.admin.requirements.update', [$framework, $domain, $requirement])
            : route('compliance.admin.requirements.store', [$framework, $domain]) }}"
          class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        @csrf
        @if($requirement) @method('PUT') @endif

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="code">Kod wymagania <span class="text-red-500">*</span></label>
                <input type="text" id="code" name="code"
                       value="{{ old('code', $requirement?->code) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('code') border-red-400 @enderror"
                       placeholder="np. A.5.1.1">
                @error('code')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="sort_order">Kolejność</label>
                <input type="number" id="sort_order" name="sort_order" min="0"
                       value="{{ old('sort_order', $requirement?->sort_order ?? 0) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="name">Nazwa / Treść wymagania <span class="text-red-500">*</span></label>
            <textarea id="name" name="name" rows="3"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none @error('name') border-red-400 @enderror"
                      placeholder="Pełna treść wymagania...">{{ old('name', $requirement?->name) }}</textarea>
            @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="description">Opis</label>
            <textarea id="description" name="description" rows="3"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"
                      placeholder="Dodatkowy opis lub kontekst wymagania...">{{ old('description', $requirement?->description) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="guidance">Wskazówki implementacji</label>
            <textarea id="guidance" name="guidance" rows="3"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"
                      placeholder="Praktyczne wskazówki dotyczące spełnienia wymagania...">{{ old('guidance', $requirement?->guidance) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="control_type">Typ kontroli <span class="text-red-500">*</span></label>
                <select id="control_type" name="control_type"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('control_type') border-red-400 @enderror">
                    <option value="">— wybierz —</option>
                    <option value="technical" {{ old('control_type', $requirement?->control_type) === 'technical' ? 'selected' : '' }}>Techniczne</option>
                    <option value="organisational" {{ old('control_type', $requirement?->control_type) === 'organisational' ? 'selected' : '' }}>Organizacyjne</option>
                    <option value="physical" {{ old('control_type', $requirement?->control_type) === 'physical' ? 'selected' : '' }}>Fizyczne</option>
                    <option value="procedural" {{ old('control_type', $requirement?->control_type) === 'procedural' ? 'selected' : '' }}>Proceduralne</option>
                    <option value="legal" {{ old('control_type', $requirement?->control_type) === 'legal' ? 'selected' : '' }}>Prawne</option>
                </select>
                @error('control_type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3 pt-6">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_mandatory" value="0">
                    <input type="checkbox" name="is_mandatory" value="1" id="is_mandatory"
                           {{ old('is_mandatory', $requirement?->is_mandatory ?? false) ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-emerald-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
                <label for="is_mandatory" class="text-sm font-medium text-slate-700 cursor-pointer">Obowiązkowe</label>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
            <button type="submit"
                    class="px-5 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                {{ $requirement ? 'Zapisz zmiany' : 'Dodaj wymaganie' }}
            </button>
            <a href="{{ route('compliance.admin.requirements.index', [$framework, $domain]) }}"
               class="px-5 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
                Anuluj
            </a>
        </div>
    </form>
</div>

@endsection
