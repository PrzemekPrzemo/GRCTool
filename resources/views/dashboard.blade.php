@extends('layouts.app')
@section('content')

{{-- Alert banners --}}
@if(count($alerts) > 0)
<div class="space-y-2 mb-6">
    @foreach($alerts as $alert)
    @php
        $classes = match($alert['level']) {
            'critical' => 'bg-red-50 border-red-300 text-red-900',
            'warning'  => 'bg-amber-50 border-amber-300 text-amber-900',
            default    => 'bg-blue-50 border-blue-300 text-blue-900',
        };
        $icon = match($alert['level']) {
            'critical' => '🔴',
            'warning'  => '🟡',
            default    => 'ℹ️',
        };
    @endphp
    <a href="{{ $alert['href'] }}" class="flex items-center gap-3 px-4 py-2.5 rounded border {{ $classes }} hover:opacity-90 transition">
        <span>{{ $icon }}</span>
        <span class="text-sm font-medium flex-1">{{ $alert['msg'] }}</span>
        <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
    @endforeach
</div>
@endif

{{-- Page header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p class="text-slate-500 text-sm mt-0.5">Witaj, <strong>{{ auth()->user()->name ?? auth()->user()->email }}</strong>. Przegląd stanu bezpieczeństwa.</p>
    </div>
</div>

{{-- Primary KPI row --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-4">
    @php
        $kpis = [
            [
                'label' => 'Otwarte ryzyka',
                'value' => $stats['risks_open'],
                'sub'   => $stats['risks_over_appetite'].' powyżej apetytu',
                'color' => $stats['risks_over_appetite'] > 0 ? 'amber' : 'slate',
                'href'  => route('risks.index'),
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
            ],
            [
                'label' => 'Incydenty',
                'value' => $stats['incidents_open'],
                'sub'   => $stats['incidents_p1_p2'].' P1/P2 krytyczne',
                'color' => $stats['incidents_p1_p2'] > 0 ? 'red' : 'slate',
                'href'  => route('incidents.index'),
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
            ],
            [
                'label' => 'Podatności',
                'value' => $stats['vulns_open'],
                'sub'   => $stats['vulns_overdue'].' po SLA',
                'color' => $stats['vulns_overdue'] > 0 ? 'orange' : 'slate',
                'href'  => route('vulnerabilities.index'),
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
            ],
            [
                'label' => 'Findings',
                'value' => $stats['findings_open'],
                'sub'   => $stats['engagements_active'].' audytów aktywnych',
                'color' => 'slate',
                'href'  => route('findings.index'),
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
            ],
            [
                'label' => 'Aktywa',
                'value' => $stats['assets_total'],
                'sub'   => $stats['assets_critical'].' krytycznych',
                'color' => 'emerald',
                'href'  => route('assets.index'),
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>',
            ],
            [
                'label' => 'Kontrole',
                'value' => $stats['controls_total'],
                'sub'   => $stats['controls_effective'].' Effective',
                'color' => 'blue',
                'href'  => route('controls.index'),
                'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            ],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <a href="{{ $kpi['href'] }}" class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 hover:shadow-md hover:border-slate-300 transition group">
        <div class="flex items-start justify-between">
            <div class="text-xs text-slate-500 uppercase tracking-wide font-medium leading-tight">{{ $kpi['label'] }}</div>
            <svg class="w-4 h-4 text-{{ $kpi['color'] }}-400 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $kpi['icon'] !!}</svg>
        </div>
        <div class="text-3xl font-bold text-{{ $kpi['color'] }}-700 mt-2">{{ $kpi['value'] }}</div>
        <div class="text-xs text-slate-400 mt-1">{{ $kpi['sub'] }}</div>
    </a>
    @endforeach
</div>

{{-- Secondary KPI row --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('certificates.index') }}" class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 hover:shadow-md transition">
        <div class="text-xs text-slate-500 uppercase tracking-wide font-medium">Certyfikaty</div>
        <div class="flex items-end gap-3 mt-2">
            <div class="{{ $stats['certs_expired'] > 0 ? 'text-red-700' : 'text-slate-700' }} text-2xl font-bold">{{ $stats['certs_expired'] }}</div>
            <div class="text-slate-400 text-xs pb-0.5">wygasłe</div>
            <div class="{{ $stats['certs_expiring_30d'] > 0 ? 'text-amber-600' : 'text-slate-400' }} text-xl font-semibold ml-auto">{{ $stats['certs_expiring_30d'] }}</div>
            <div class="text-slate-400 text-xs pb-0.5">w 30 dni</div>
        </div>
    </a>
    <a href="{{ route('trainings.index') }}" class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 hover:shadow-md transition">
        <div class="text-xs text-slate-500 uppercase tracking-wide font-medium">Szkolenia</div>
        <div class="mt-2 flex items-end gap-2">
            <div class="text-2xl font-bold {{ $stats['trainings_completion'] >= 80 ? 'text-emerald-700' : ($stats['trainings_completion'] >= 50 ? 'text-amber-600' : 'text-red-600') }}">
                {{ $stats['trainings_completion'] }}%
            </div>
            <div class="text-slate-400 text-xs pb-0.5">ukończenia</div>
        </div>
        <div class="mt-1.5 w-full bg-slate-100 rounded-full h-1.5">
            <div class="h-1.5 rounded-full {{ $stats['trainings_completion'] >= 80 ? 'bg-emerald-500' : ($stats['trainings_completion'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                 style="width: {{ $stats['trainings_completion'] }}%"></div>
        </div>
    </a>
    <a href="{{ route('exceptions.index') }}" class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 hover:shadow-md transition">
        <div class="text-xs text-slate-500 uppercase tracking-wide font-medium">Wyjątki oczekujące</div>
        <div class="text-2xl font-bold {{ $stats['exceptions_pending'] > 0 ? 'text-amber-600' : 'text-slate-700' }} mt-2">
            {{ $stats['exceptions_pending'] }}
        </div>
        <div class="text-xs text-slate-400 mt-1">do zatwierdzenia</div>
    </a>
    <a href="{{ route('bcp.index') }}" class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 hover:shadow-md transition">
        <div class="text-xs text-slate-500 uppercase tracking-wide font-medium">BCP / DR</div>
        <div class="text-2xl font-bold {{ $stats['bcp_untested'] > 0 ? 'text-amber-600' : 'text-slate-700' }} mt-2">
            {{ $stats['bcp_untested'] }}
        </div>
        <div class="text-xs text-slate-400 mt-1">planów bez testu</div>
    </a>
</div>

{{-- Charts row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- Risk heatmap --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-800">Heat Map ryzyk</h2>
            <a href="{{ route('risks.index') }}" class="text-xs text-emerald-600 hover:underline">Zobacz wszystkie →</a>
        </div>
        <div class="grid grid-cols-6 gap-1 text-xs">
            <div></div>
            @for($i = 1; $i <= 5; $i++)
                <div class="text-center text-slate-400 font-medium">I{{ $i }}</div>
            @endfor
            @for($l = 5; $l >= 1; $l--)
                <div class="text-right text-slate-400 font-medium pr-1 leading-7">L{{ $l }}</div>
                @for($i = 1; $i <= 5; $i++)
                    @php
                        $score = $l * $i;
                        $count = $heatmap[$l][$i] ?? 0;
                        $bg = match(true) {
                            $score >= 20 => 'bg-red-600 text-white',
                            $score >= 12 => 'bg-orange-400 text-white',
                            $score >= 6  => 'bg-yellow-300 text-slate-800',
                            default      => 'bg-emerald-200 text-slate-700',
                        };
                    @endphp
                    <div class="aspect-square flex items-center justify-center rounded text-xs font-bold {{ $bg }}">
                        {{ $count ?: '' }}
                    </div>
                @endfor
            @endfor
        </div>
        <div class="flex items-center gap-3 mt-3 text-xs text-slate-400">
            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-red-600 inline-block"></span>Krytyczne</span>
            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-orange-400 inline-block"></span>Wysokie</span>
            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-yellow-300 inline-block"></span>Średnie</span>
            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-200 inline-block"></span>Niskie</span>
        </div>
    </div>

    {{-- Top risks --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-800">Top 10 ryzyk (residual score)</h2>
            <a href="{{ route('risks.create') }}" class="text-xs text-emerald-600 hover:underline">+ Dodaj ryzyko</a>
        </div>
        @if($topRisks->isEmpty())
        <div class="py-8 text-center text-slate-400 text-sm">
            Brak ryzyk.
            <a href="{{ route('risks.create') }}" class="text-emerald-600 hover:underline">Dodaj pierwsze</a>
            lub <a href="{{ route('scenarios.index') }}" class="text-emerald-600 hover:underline">adoptuj z biblioteki</a>.
        </div>
        @else
        <div class="space-y-1">
            @foreach($topRisks as $r)
            @php
                $pct = min(100, ($r->residual_score / 25) * 100);
                $barColor = match(true) {
                    $r->residual_score >= 20 => 'bg-red-500',
                    $r->residual_score >= 12 => 'bg-orange-400',
                    $r->residual_score >= 6  => 'bg-yellow-400',
                    default                   => 'bg-emerald-400',
                };
            @endphp
            <div class="flex items-center gap-3 py-1.5 group hover:bg-slate-50 rounded px-1 -mx-1">
                <a href="{{ route('risks.show', $r) }}" class="font-mono text-xs text-emerald-700 w-28 flex-shrink-0 hover:underline">{{ $r->code }}</a>
                <div class="flex-1 min-w-0">
                    <div class="text-sm truncate">{{ $r->title }}</div>
                    <div class="h-1 bg-slate-100 rounded-full mt-1">
                        <div class="h-1 {{ $barColor }} rounded-full" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                <span class="text-sm font-bold w-6 text-right flex-shrink-0 {{ $r->residual_score >= 20 ? 'text-red-600' : ($r->residual_score >= 12 ? 'text-orange-500' : 'text-slate-600') }}">{{ $r->residual_score }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Indicators grid --}}
<div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-slate-800">Wskaźniki KCI / KPI / KRI</h2>
        <a href="{{ route('org-metrics.index') }}" class="text-xs text-emerald-600 hover:underline">Pełne metryki →</a>
    </div>
    @if($indicators->isEmpty())
    <p class="text-slate-400 text-sm py-4 text-center">Brak aktywnych wskaźników. <a href="{{ route('indicators.create') }}" class="text-emerald-600 hover:underline">Dodaj wskaźnik</a>.</p>
    @else
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
        @foreach($indicators as $ind)
            @php
                $latest = $ind->latestMeasurement;
                $status = $latest?->status ?? 'gray';
                $dotColor = match($status) {
                    'green' => 'bg-emerald-500',
                    'amber' => 'bg-amber-500',
                    'red'   => 'bg-red-500',
                    default => 'bg-slate-300',
                };
                $cardBorder = match($status) {
                    'red'   => 'border-red-200 bg-red-50',
                    'amber' => 'border-amber-200 bg-amber-50',
                    default => 'border-slate-200 bg-slate-50',
                };
            @endphp
            <a href="{{ route('indicators.show', $ind) }}" class="block rounded-lg border {{ $cardBorder }} p-3 hover:shadow-sm transition">
                <div class="flex items-center justify-between gap-1">
                    <span class="text-xs text-slate-500 font-mono truncate">{{ $ind->code }}</span>
                    <span class="inline-block w-2.5 h-2.5 rounded-full {{ $dotColor }} flex-shrink-0"></span>
                </div>
                <div class="text-xs font-medium mt-1 line-clamp-2 text-slate-700">{{ $ind->name }}</div>
                <div class="text-xl font-bold mt-1 text-slate-900">
                    @if($latest)
                        {{ rtrim(rtrim(number_format((float)$latest->value, 2), '0'), '.') }}<span class="text-xs font-normal text-slate-500"> {{ $ind->unit }}</span>
                    @else
                        <span class="text-slate-400 text-xs font-normal">brak danych</span>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
    @endif
</div>

@endsection
