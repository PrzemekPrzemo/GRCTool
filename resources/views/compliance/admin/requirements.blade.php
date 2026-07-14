@extends('layouts.app')
@section('content')

@php
$controlTypeColors = [
    'technical'      => 'bg-blue-100 text-blue-700',
    'organisational' => 'bg-purple-100 text-purple-700',
    'physical'       => 'bg-amber-100 text-amber-700',
    'procedural'     => 'bg-teal-100 text-teal-700',
    'legal'          => 'bg-red-100 text-red-700',
];
$controlTypeLabels = [
    'technical'      => 'Techniczne',
    'organisational' => 'Organizacyjne',
    'physical'       => 'Fizyczne',
    'procedural'     => 'Proceduralne',
    'legal'          => 'Prawne',
];
@endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('compliance.admin.frameworks') }}" class="text-sm text-slate-500 hover:text-slate-700 transition-colors">Frameworki</a>
            <span class="text-slate-300">/</span>
            <a href="{{ route('compliance.admin.frameworks.domains', $framework) }}" class="text-sm text-slate-500 hover:text-slate-700 transition-colors">{{ $framework->short_name }}</a>
            <span class="text-slate-300">/</span>
            <span class="text-sm text-slate-700 font-medium">{{ $domain->code }}</span>
        </div>
        <h1 class="text-2xl font-semibold">
            Wymagania: <span class="text-emerald-700">{{ $domain->name }}</span>
            <span class="text-lg text-slate-400 font-normal">({{ $framework->short_name }})</span>
        </h1>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('compliance.admin.frameworks.domains', $framework) }}"
           class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
            ← Powrót
        </a>
        @can('compliance.create')
        <a href="{{ route('compliance.admin.requirements.create', [$framework, $domain]) }}"
           class="inline-flex items-center gap-2 px-4 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Dodaj wymaganie
        </a>
        @endcan
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    @if($requirements->isEmpty())
    <div class="p-12 text-center">
        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-slate-400 mb-2">Brak wymagań w tej domenie</p>
        @can('compliance.create')
        <a href="{{ route('compliance.admin.requirements.create', [$framework, $domain]) }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors mt-2">
            Dodaj pierwsze wymaganie
        </a>
        @endcan
    </div>
    @else
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-left text-xs text-slate-500 uppercase tracking-wide">
            <tr>
                <th class="px-4 py-3 w-12">#</th>
                <th class="px-4 py-3 w-32">Kod</th>
                <th class="px-4 py-3">Nazwa</th>
                <th class="px-4 py-3">Typ kontroli</th>
                <th class="px-4 py-3">Obowiązkowe</th>
                <th class="px-4 py-3">Typ</th>
                <th class="px-4 py-3 text-right">Akcje</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($requirements as $req)
            @php
                $typeColor = $controlTypeColors[$req->control_type] ?? 'bg-slate-100 text-slate-500';
                $typeLabel = $controlTypeLabels[$req->control_type] ?? $req->control_type;
            @endphp
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3 text-xs text-slate-400">{{ $req->sort_order }}</td>
                <td class="px-4 py-3">
                    <span class="font-mono text-xs font-medium text-slate-800 bg-slate-100 px-1.5 py-0.5 rounded">{{ $req->code }}</span>
                </td>
                <td class="px-4 py-3">
                    <div class="font-medium text-slate-800 leading-tight">{{ Str::limit($req->name, 80) }}</div>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $typeColor }}">{{ $typeLabel }}</span>
                </td>
                <td class="px-4 py-3">
                    @if($req->is_mandatory)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Tak</span>
                    @else
                    <span class="text-xs text-slate-400">Nie</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if($req->is_custom)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">Własne</span>
                    @else
                    <span class="text-xs text-slate-400">Wbudowane</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('compliance.admin.requirements.edit', [$framework, $domain, $req]) }}"
                           class="px-2.5 py-1 text-xs bg-slate-100 text-slate-700 rounded hover:bg-slate-200 transition-colors">
                            Edytuj
                        </a>
                        <form method="POST" action="{{ route('compliance.admin.requirements.destroy', [$framework, $domain, $req]) }}" class="inline"
                              onsubmit="return confirm('Usunąć wymaganie {{ addslashes($req->code) }}?')">
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
    </div>
    @endif
</div>

@endsection
