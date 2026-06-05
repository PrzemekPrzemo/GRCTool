@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('compliance.admin.frameworks') }}" class="text-sm text-slate-500 hover:text-slate-700 transition-colors">Frameworki</a>
            <span class="text-slate-300">/</span>
            <a href="{{ route('compliance.admin.frameworks.domains', $framework) }}" class="text-sm text-slate-500 hover:text-slate-700 transition-colors">{{ $framework->short_name }}</a>
            <span class="text-slate-300">/</span>
            <span class="text-sm text-slate-700 font-medium">{{ $domain ? 'Edytuj domenę' : 'Nowa domena' }}</span>
        </div>
        <h1 class="text-2xl font-semibold">
            {{ $domain ? 'Edytuj domenę' : 'Nowa domena' }}
        </h1>
        <p class="text-sm text-slate-500 mt-0.5">{{ $framework->name }}</p>
    </div>
    <a href="{{ route('compliance.admin.frameworks.domains', $framework) }}"
       class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
        ← Powrót
    </a>
</div>

<div class="max-w-xl">
    <form method="POST"
          action="{{ $domain ? route('compliance.admin.domains.update', [$framework, $domain]) : route('compliance.admin.domains.store', $framework) }}"
          class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        @csrf
        @if($domain) @method('PUT') @endif

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
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="code">Kod domeny <span class="text-red-500">*</span></label>
                <input type="text" id="code" name="code"
                       value="{{ old('code', $domain?->code) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('code') border-red-400 @enderror"
                       placeholder="np. A.5">
                @error('code')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5" for="sort_order">Kolejność</label>
                <input type="number" id="sort_order" name="sort_order" min="0"
                       value="{{ old('sort_order', $domain?->sort_order ?? 0) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="name">Nazwa domeny <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name"
                   value="{{ old('name', $domain?->name) }}"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('name') border-red-400 @enderror"
                   placeholder="np. Polityki bezpieczeństwa informacji">
            @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5" for="description">Opis</label>
            <textarea id="description" name="description" rows="3"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"
                      placeholder="Krótki opis zakresu domeny...">{{ old('description', $domain?->description) }}</textarea>
        </div>

        <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
            <button type="submit"
                    class="px-5 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                {{ $domain ? 'Zapisz zmiany' : 'Dodaj domenę' }}
            </button>
            <a href="{{ route('compliance.admin.frameworks.domains', $framework) }}"
               class="px-5 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
                Anuluj
            </a>
        </div>
    </form>
</div>

@endsection
