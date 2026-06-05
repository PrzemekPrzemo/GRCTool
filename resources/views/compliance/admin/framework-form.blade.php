@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">
            {{ $framework ? 'Edytuj framework' : 'Nowy własny framework' }}
        </h1>
        @if($framework)
        <p class="text-sm text-slate-500 mt-0.5">{{ $framework->name }}</p>
        @endif
    </div>
    <a href="{{ route('compliance.admin.frameworks') }}"
       class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
        ← Powrót
    </a>
</div>

<div class="max-w-2xl">
    <form method="POST"
          action="{{ $framework ? route('compliance.admin.frameworks.update', $framework) : route('compliance.admin.frameworks.store') }}"
          class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        @csrf
        @if($framework) @method('PUT') @endif

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="name">Pełna nazwa <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name"
                       value="{{ old('name', $framework?->name) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('name') border-red-400 @enderror"
                       placeholder="np. ISO/IEC 27001:2022">
                @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="short_name">Skrót (Short name) <span class="text-red-500">*</span></label>
                <input type="text" id="short_name" name="short_name"
                       value="{{ old('short_name', $framework?->short_name) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('short_name') border-red-400 @enderror"
                       placeholder="np. ISO27001">
                @error('short_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="version">Wersja</label>
                <input type="text" id="version" name="version"
                       value="{{ old('version', $framework?->version) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                       placeholder="np. 2022">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="issuer">Wydawca / Organizacja</label>
                <input type="text" id="issuer" name="issuer"
                       value="{{ old('issuer', $framework?->issuer) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                       placeholder="np. ISO/IEC">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="region">Region <span class="text-red-500">*</span></label>
                <select id="region" name="region"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('region') border-red-400 @enderror">
                    <option value="">— wybierz —</option>
                    @foreach(['EU','US','Global','KSA','ME','Other'] as $r)
                    <option value="{{ $r }}" {{ old('region', $framework?->region) === $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
                @error('region')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="description">Opis</label>
            <textarea id="description" name="description" rows="4"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"
                      placeholder="Krótki opis standardu i jego zastosowania...">{{ old('description', $framework?->description) }}</textarea>
        </div>

        <div class="flex items-center gap-3">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" id="is_active"
                       {{ old('is_active', $framework?->is_active ?? true) ? 'checked' : '' }}
                       class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-emerald-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
            </label>
            <label for="is_active" class="text-sm font-medium text-slate-700 cursor-pointer">Framework aktywny</label>
        </div>

        <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
            <button type="submit"
                    class="px-5 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                {{ $framework ? 'Zapisz zmiany' : 'Utwórz framework' }}
            </button>
            <a href="{{ route('compliance.admin.frameworks') }}"
               class="px-5 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
                Anuluj
            </a>
        </div>
    </form>
</div>

@endsection
