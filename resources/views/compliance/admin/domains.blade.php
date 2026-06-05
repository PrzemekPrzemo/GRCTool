@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('compliance.admin.frameworks') }}" class="text-sm text-slate-500 hover:text-slate-700 transition-colors">Frameworki</a>
            <span class="text-slate-300">/</span>
            <span class="text-sm text-slate-700 font-medium">{{ $framework->short_name }}</span>
        </div>
        <h1 class="text-2xl font-semibold">Domeny: <span class="text-emerald-700">{{ $framework->name }}</span></h1>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('compliance.admin.frameworks') }}"
           class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
            ← Powrót
        </a>
        @can('compliance.create')
        <a href="{{ route('compliance.admin.domains.create', $framework) }}"
           class="inline-flex items-center gap-2 px-4 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Dodaj domenę
        </a>
        @endcan
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    @if($domains->isEmpty())
    <div class="p-12 text-center">
        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <p class="text-slate-400 mb-2">Brak domen w tym frameworku</p>
        @can('compliance.create')
        <a href="{{ route('compliance.admin.domains.create', $framework) }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors mt-2">
            Dodaj pierwszą domenę
        </a>
        @endcan
    </div>
    @else
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-left text-xs text-slate-500 uppercase tracking-wide">
            <tr>
                <th class="px-5 py-3 w-16">#</th>
                <th class="px-5 py-3 w-28">Kod</th>
                <th class="px-5 py-3">Nazwa domeny</th>
                <th class="px-5 py-3 text-right">Wymagania</th>
                <th class="px-5 py-3 text-right">Akcje</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($domains as $domain)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-3.5 text-slate-400 text-xs">{{ $domain->sort_order }}</td>
                <td class="px-5 py-3.5">
                    <span class="font-mono text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded">{{ $domain->code }}</span>
                </td>
                <td class="px-5 py-3.5">
                    <div class="font-medium text-slate-900">{{ $domain->name }}</div>
                    @if($domain->description)
                    <div class="text-xs text-slate-400 mt-0.5 truncate max-w-md">{{ $domain->description }}</div>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right font-semibold text-slate-700">{{ $domain->requirements_count }}</td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('compliance.admin.domains.edit', [$framework, $domain]) }}"
                           class="px-2.5 py-1 text-xs bg-slate-100 text-slate-700 rounded hover:bg-slate-200 transition-colors">
                            Edytuj
                        </a>
                        <a href="{{ route('compliance.admin.requirements.index', [$framework, $domain]) }}"
                           class="px-2.5 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors">
                            Wymagania
                        </a>
                        <form method="POST" action="{{ route('compliance.admin.domains.destroy', [$framework, $domain]) }}" class="inline"
                              onsubmit="return confirm('Usunąć domenę {{ addslashes($domain->name) }} i wszystkie jej wymagania?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="px-2.5 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors">
                                Usuń
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection
