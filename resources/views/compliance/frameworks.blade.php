@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">Frameworki Compliance</h1>
        <p class="text-sm text-slate-500 mt-0.5">Biblioteka norm i regulacji dostępnych do oceny</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('compliance.coverage') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
            Pokrycie regulacyjne
        </a>
        <a href="{{ route('compliance.gaps') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
            Luki compliance
        </a>
        <a href="{{ route('compliance.index') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
            ← Oceny
        </a>
    </div>
</div>

@if($frameworks->isEmpty())
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    <p class="text-slate-400 mb-2">Brak frameworków w bazie</p>
    <p class="text-xs text-slate-400">Uruchom seeder: <code class="bg-slate-100 px-1 rounded">php artisan db:seed --class=ComplianceFrameworkSeeder</code></p>
</div>
@else
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @foreach($frameworks as $fw)
    @php
        $regionColors = [
            'EU'     => 'bg-blue-100 text-blue-700',
            'US'     => 'bg-indigo-100 text-indigo-700',
            'Global' => 'bg-emerald-100 text-emerald-700',
            'KSA'    => 'bg-amber-100 text-amber-700',
        ];
        $regionColor = $regionColors[$fw->region] ?? 'bg-slate-100 text-slate-600';
    @endphp
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between gap-2">
            <div>
                <h2 class="font-semibold text-slate-900 leading-tight">{{ $fw->name }}</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-slate-900 text-slate-100">{{ $fw->short_name }}</span>
                    @if($fw->version)
                    <span class="text-xs text-slate-400">v{{ $fw->version }}</span>
                    @endif
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $regionColor }}">{{ $fw->region }}</span>
                </div>
            </div>
            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
        </div>

        @if($fw->description)
        <p class="text-xs text-slate-500 leading-relaxed line-clamp-2">{{ $fw->description }}</p>
        @endif

        <div class="flex items-center gap-4 text-xs text-slate-500 border-t border-slate-100 pt-3">
            <div>
                <span class="font-semibold text-slate-800 text-base">{{ $fw->requirements_count_cached }}</span>
                <span class="ml-0.5">wymagań</span>
            </div>
            <div>
                <span class="font-semibold {{ $fw->active_assessments > 0 ? 'text-blue-700' : 'text-slate-800' }} text-base">{{ $fw->assessments_count }}</span>
                <span class="ml-0.5">ocen</span>
            </div>
            @if($fw->active_assessments > 0)
            <div>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ $fw->active_assessments }} aktywnych</span>
            </div>
            @endif
        </div>

        @if($fw->issuer)
        <div class="text-xs text-slate-400">{{ $fw->issuer }}</div>
        @endif

        <div class="mt-auto pt-1">
            @can('compliance.create')
            <a href="{{ route('compliance.create', ['framework' => $fw->id]) }}"
               class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Nowa ocena
            </a>
            @endcan
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
