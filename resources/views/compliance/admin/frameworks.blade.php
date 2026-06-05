@extends('layouts.app')
@section('content')

@php
$regionColors = [
    'EU'     => 'bg-blue-100 text-blue-700',
    'US'     => 'bg-green-100 text-green-700',
    'Global' => 'bg-slate-100 text-slate-600',
    'KSA'    => 'bg-amber-100 text-amber-700',
    'ME'     => 'bg-orange-100 text-orange-700',
    'Other'  => 'bg-slate-100 text-slate-500',
];
@endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">Zarządzanie frameworkami</h1>
        <p class="text-sm text-slate-500 mt-0.5">Przeglądaj, edytuj i twórz własne frameworki compliance</p>
    </div>
    @can('compliance.create')
    <a href="{{ route('compliance.admin.frameworks.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Dodaj własny framework
    </a>
    @endcan
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    @if($frameworks->isEmpty())
    <div class="p-12 text-center">
        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <p class="text-slate-400">Brak frameworków w bazie</p>
    </div>
    @else
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200 text-left text-xs text-slate-500 uppercase tracking-wide">
            <tr>
                <th class="px-5 py-3">Framework</th>
                <th class="px-5 py-3">Region</th>
                <th class="px-5 py-3">Typ</th>
                <th class="px-5 py-3 text-right">Domeny</th>
                <th class="px-5 py-3 text-right">Wymagania</th>
                <th class="px-5 py-3 text-right">Akcje</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($frameworks as $fw)
            @php
                $regionColor = $regionColors[$fw->region] ?? 'bg-slate-100 text-slate-500';
            @endphp
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-slate-900 text-slate-100">{{ $fw->short_name }}</span>
                        <div>
                            <div class="font-medium text-slate-900">{{ $fw->name }}</div>
                            @if($fw->version)
                            <div class="text-xs text-slate-400">v{{ $fw->version }}@if($fw->issuer) · {{ $fw->issuer }}@endif</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $regionColor }}">{{ $fw->region }}</span>
                </td>
                <td class="px-5 py-3.5">
                    @if($fw->is_custom)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">Własny</span>
                    @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-500">Wbudowany</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right text-slate-700 font-semibold">{{ $fw->domains_count }}</td>
                <td class="px-5 py-3.5 text-right text-slate-700 font-semibold">{{ $fw->requirements_count_cached }}</td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('compliance.admin.frameworks.edit', $fw) }}"
                           class="px-2.5 py-1 text-xs bg-slate-100 text-slate-700 rounded hover:bg-slate-200 transition-colors">
                            Edytuj
                        </a>
                        <a href="{{ route('compliance.admin.frameworks.domains', $fw) }}"
                           class="px-2.5 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors">
                            Domeny
                        </a>
                        @if($fw->is_custom)
                        <form method="POST" action="{{ route('compliance.admin.frameworks.destroy', $fw) }}" class="inline"
                              onsubmit="return confirm('Usunąć framework {{ addslashes($fw->name) }}? Tej operacji nie można cofnąć.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="px-2.5 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors">
                                Usuń
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection
