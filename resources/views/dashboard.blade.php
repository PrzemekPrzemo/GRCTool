@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Dzień dobry, {{ auth()->user()->name ?? 'użytkowniku' }}</h1>
        <p class="text-slate-500 text-sm mt-0.5">Przegląd bezpieczeństwa na {{ now()->format('d.m.Y') }}</p>
    </div>
</div>

{{-- Alert banner: critical issues --}}
@php
$alerts = [];
if($stats['incidents_open_p1_p2'] > 0) $alerts[] = ['Aktywne incydenty P1/P2: '.$stats['incidents_open_p1_p2'], 'red', route('incidents.index')];
if($stats['gdpr_breach_overdue'] > 0) $alerts[] = ['Naruszenia RODO po terminie: '.$stats['gdpr_breach_overdue'], 'red', route('gdpr-breaches.index')];
if($stats['dsar_overdue'] > 0) $alerts[] = ['Przeterminowane DSAR: '.$stats['dsar_overdue'], 'red', route('dsar.index')];
if($stats['certs_expired'] > 0) $alerts[] = ['Wygasłe certyfikaty: '.$stats['certs_expired'], 'red', route('certificates.index')];
if($stats['certs_expiring_30d'] > 0) $alerts[] = ['Certyfikaty wygasające ≤30 dni: '.$stats['certs_expiring_30d'], 'amber', route('certificates.index')];
if($stats['exceptions_pending'] > 0) $alerts[] = ['Wyjątki do zatwierdzenia: '.$stats['exceptions_pending'], 'amber', route('exceptions.index')];
@endphp
@if(count($alerts))
<div class="space-y-2 mb-6">
@foreach($alerts as [$msg, $level, $href])
<a href="{{ $href }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg border text-sm font-medium transition-colors
    {{ $level === 'red' ? 'bg-red-50 border-red-200 text-red-800 hover:bg-red-100' : 'bg-amber-50 border-amber-200 text-amber-800 hover:bg-amber-100' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    {{ $msg }}
    <span class="ml-auto text-xs">Zobacz →</span>
</a>
@endforeach
</div>
@endif

{{-- Main KPI tiles --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
@php
$tiles = [
    ['Aktywa', $stats['assets_total'], $stats['assets_critical'].' kryt.', 'emerald', route('assets.index'),
     '<path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>'],
    ['Ryzyka otwarte', $stats['risks_open'], $stats['risks_over_appetite'].' ponad apetyt', 'amber', route('risks.index'),
     '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>'],
    ['Kontrole', $stats['controls_total'], $stats['controls_effective'].' skutecznych', 'blue', route('controls.index'),
     '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>'],
    ['Podatności', $stats['vulns_open'], $stats['vulns_overdue'].' po SLA', 'red', route('vulnerabilities.index'),
     '<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>'],
    ['Incydenty P1/P2', $stats['incidents_open_p1_p2'], 'otwarte', $stats['incidents_open_p1_p2'] > 0 ? 'red' : 'slate', route('incidents.index'),
     '<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>'],
    ['Szkolenia', $stats['trainings_completion_pct'].'%', 'ukończonych', $stats['trainings_completion_pct'] >= 80 ? 'emerald' : ($stats['trainings_completion_pct'] >= 60 ? 'amber' : 'red'), route('trainings.index'),
     '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'],
];
@endphp
@foreach($tiles as [$label, $value, $sub, $color, $href, $icon])
<a href="{{ $href }}" class="block bg-white rounded-xl shadow-sm border border-slate-200 p-4 hover:shadow-md hover:border-{{ $color }}-300 transition-all group">
    <div class="flex items-start justify-between">
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wide leading-tight">{{ $label }}</div>
        <div class="w-7 h-7 rounded-lg bg-{{ $color }}-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-{{ $color }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">{!! $icon !!}</svg>
        </div>
    </div>
    <div class="text-3xl font-bold text-{{ $color }}-700 mt-2 leading-none">{{ $value }}</div>
    <div class="text-xs text-slate-400 mt-1.5">{{ $sub }}</div>
</a>
@endforeach
</div>

{{-- Second row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <a href="{{ route('gdpr-breaches.index') }}" class="bg-white rounded-xl shadow-sm border {{ $stats['gdpr_breach_overdue'] > 0 ? 'border-red-300 bg-red-50' : 'border-slate-200' }} p-4 hover:shadow-md transition-all">
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wide">Naruszenia RODO</div>
        <div class="text-3xl font-bold mt-2 {{ $stats['gdpr_breach_overdue'] > 0 ? 'text-red-700' : 'text-slate-700' }}">{{ $stats['gdpr_breach_overdue'] }}</div>
        <div class="text-xs text-slate-400 mt-1.5">po terminie zgłoszenia</div>
    </a>
    <a href="{{ route('certificates.index') }}" class="bg-white rounded-xl shadow-sm border {{ $stats['certs_expiring_30d'] > 0 ? 'border-amber-300' : 'border-slate-200' }} p-4 hover:shadow-md transition-all">
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wide">Certyfikaty</div>
        <div class="text-3xl font-bold mt-2 {{ $stats['certs_expiring_30d'] > 0 ? 'text-amber-700' : 'text-slate-700' }}">{{ $stats['certs_expiring_30d'] }}</div>
        <div class="text-xs text-slate-400 mt-1.5">wygasają ≤ 30 dni</div>
    </a>
    <a href="{{ route('bcp.index') }}" class="bg-white rounded-xl shadow-sm border {{ $stats['bcp_untested'] > 0 ? 'border-amber-300' : 'border-slate-200' }} p-4 hover:shadow-md transition-all">
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wide">BCP/DR</div>
        <div class="text-3xl font-bold mt-2 {{ $stats['bcp_untested'] > 0 ? 'text-amber-700' : 'text-slate-700' }}">{{ $stats['bcp_untested'] }}</div>
        <div class="text-xs text-slate-400 mt-1.5">plany bez testów</div>
    </a>
    <a href="{{ route('exceptions.index') }}" class="bg-white rounded-xl shadow-sm border {{ $stats['exceptions_pending'] > 0 ? 'border-purple-300' : 'border-slate-200' }} p-4 hover:shadow-md transition-all">
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wide">Wyjątki</div>
        <div class="text-3xl font-bold mt-2 {{ $stats['exceptions_pending'] > 0 ? 'text-purple-700' : 'text-slate-700' }}">{{ $stats['exceptions_pending'] }}</div>
        <div class="text-xs text-slate-400 mt-1.5">do zatwierdzenia</div>
    </a>
</div>

{{-- Heat map + Top risks --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h2 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
            Heat Map ryzyk
        </h2>
        <div class="grid grid-cols-6 gap-1 text-xs">
            <div></div>
            @for($i = 1; $i <= 5; $i++)
                <div class="text-center font-semibold text-slate-400">I{{ $i }}</div>
            @endfor
            @for($l = 5; $l >= 1; $l--)
                <div class="text-right font-semibold text-slate-400 pr-1 self-center">L{{ $l }}</div>
                @for($i = 1; $i <= 5; $i++)
                    @php
                        $score = $l * $i;
                        $count = $heatmap[$l][$i] ?? 0;
                        $bg = match(true) {
                            $score >= 20 => 'bg-red-500 text-white',
                            $score >= 12 => 'bg-orange-400 text-white',
                            $score >= 6  => 'bg-yellow-300 text-yellow-900',
                            default      => 'bg-emerald-200 text-emerald-800',
                        };
                    @endphp
                    <div class="aspect-square flex items-center justify-center rounded-md {{ $bg }} font-bold text-xs">
                        {{ $count ?: '' }}
                    </div>
                @endfor
            @endfor
        </div>
        <p class="text-xs text-slate-400 mt-3">L = prawdopodobieństwo, I = wpływ</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 lg:col-span-2">
        <h2 class="font-semibold text-slate-700 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            Top 10 ryzyk (residual score)
        </h2>
        <div class="space-y-1">
        @forelse($topRisks as $r)
        @php
            $level = match(true) { $r->residual_score >= 15 => 'red', $r->residual_score >= 8 => 'amber', default => 'emerald' };
        @endphp
        <a href="{{ route('risks.show', $r) }}" class="flex items-center gap-3 px-2 py-1.5 rounded-lg hover:bg-slate-50 transition-colors group text-sm">
            <span class="w-10 font-mono text-xs text-slate-400">{{ $r->code }}</span>
            <span class="flex-1 text-slate-700 truncate group-hover:text-slate-900">{{ $r->title }}</span>
            <span class="w-6 h-6 rounded-full bg-{{ $level }}-100 text-{{ $level }}-700 text-xs flex items-center justify-center font-bold flex-shrink-0">{{ $r->residual_score }}</span>
        </a>
        @empty
        <p class="text-sm text-slate-400 py-4 text-center">Brak ryzyk. <a href="{{ route('risks.create') }}" class="text-emerald-600 hover:underline">Dodaj pierwsze</a></p>
        @endforelse
        </div>
    </div>
</div>

{{-- Indicators RAG --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            Wskaźniki KCI / KPI / KRI
        </h2>
        <a href="{{ route('org-metrics.index') }}" class="text-xs text-emerald-600 hover:underline">Widok pełny →</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
        @foreach($indicators as $ind)
            @php
                $latest = $ind->latestMeasurement;
                $status = $latest ? $ind->classify((float)$latest->value) : 'no_data';
                $dot = ['green' => 'bg-emerald-500', 'amber' => 'bg-amber-500', 'red' => 'bg-red-500', 'no_data' => 'bg-slate-300'][$status];
                $badge = ['green' => 'bg-emerald-50 border-emerald-200', 'amber' => 'bg-amber-50 border-amber-200', 'red' => 'bg-red-50 border-red-200', 'no_data' => 'bg-slate-50 border-slate-200'][$status];
            @endphp
            <a href="{{ route('indicators.show', $ind) }}" class="block rounded-lg border {{ $badge }} p-3 hover:shadow-sm transition-all">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs text-slate-500 font-mono leading-tight truncate">{{ $ind->code }}</span>
                    <span class="w-2.5 h-2.5 rounded-full {{ $dot }} flex-shrink-0 ml-1"></span>
                </div>
                <div class="text-xs font-medium text-slate-700 leading-tight line-clamp-2 mb-2">{{ $ind->name }}</div>
                <div class="text-xl font-bold text-slate-800">
                    @if($latest)
                        {{ rtrim(rtrim(number_format((float)$latest->value, 2), '0'), '.') }}<span class="text-xs font-normal text-slate-400 ml-0.5">{{ $ind->unit }}</span>
                    @else
                        <span class="text-slate-300 text-sm">—</span>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
</div>

@endsection
