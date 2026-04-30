@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">Dashboard</h1>
        <p class="text-slate-500 text-sm">Witaj, {{ auth()->user()->name }}. Stan na {{ now()->format('Y-m-d H:i') }}</p>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
    @php
        $tiles = [
            ['Aktywa', $stats['assets_total'], $stats['assets_critical'].' krytycznych', 'emerald', route('assets.index')],
            ['Otwarte ryzyka', $stats['risks_open'], $stats['risks_over_appetite'].' powyżej apetytu', 'amber', route('risks.index')],
            ['Kontrole', $stats['controls_total'], $stats['controls_effective'].' Effective', 'blue', route('controls.index')],
            ['Otwarte podatności', $stats['vulns_open'], $stats['vulns_overdue'].' po SLA', 'red', route('vulnerabilities.index')],
            ['Findings', $stats['findings_open'], $stats['engagements_active'].' aktywnych audytów', 'slate', route('engagements.index')],
        ];
    @endphp
    @foreach($tiles as [$label, $value, $sub, $color, $href])
    <a href="{{ $href }}" class="block bg-white rounded shadow p-4 hover:shadow-md transition">
        <div class="text-xs text-slate-500 uppercase tracking-wide">{{ $label }}</div>
        <div class="text-3xl font-bold text-{{ $color }}-700 mt-1">{{ $value }}</div>
        <div class="text-xs text-slate-500 mt-1">{{ $sub }}</div>
    </a>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded shadow p-4 lg:col-span-1">
        <h2 class="font-semibold mb-3">Heat Map ryzyk (residual)</h2>
        <div class="grid grid-cols-6 gap-1 text-xs">
            <div></div>
            @for($i = 1; $i <= 5; $i++)
                <div class="text-center font-medium">I{{ $i }}</div>
            @endfor
            @for($l = 5; $l >= 1; $l--)
                <div class="text-right font-medium pr-2">L{{ $l }}</div>
                @for($i = 1; $i <= 5; $i++)
                    @php
                        $score = $l * $i;
                        $count = $heatmap[$l][$i] ?? 0;
                        $bg = match(true) {
                            $score >= 20 => 'bg-red-600 text-white',
                            $score >= 12 => 'bg-orange-400 text-white',
                            $score >= 6 => 'bg-yellow-300',
                            default => 'bg-emerald-300',
                        };
                    @endphp
                    <div class="aspect-square flex items-center justify-center rounded {{ $bg }} font-semibold">
                        {{ $count }}
                    </div>
                @endfor
            @endfor
        </div>
        <p class="text-xs text-slate-500 mt-3">L = likelihood, I = impact. Liczba = otwarte ryzyka w komórce.</p>
    </div>

    <div class="bg-white rounded shadow p-4 lg:col-span-2">
        <h2 class="font-semibold mb-3">Top 10 ryzyk (residual score)</h2>
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-500 uppercase">
                <tr>
                    <th class="py-1">Kod</th><th>Nazwa</th><th>Kategoria</th>
                    <th class="text-right">Inh.</th><th class="text-right">Res.</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topRisks as $r)
                <tr class="border-t border-slate-100">
                    <td class="py-1.5 font-mono text-xs"><a href="{{ route('risks.show', $r) }}" class="text-emerald-700 hover:underline">{{ $r->code }}</a></td>
                    <td class="py-1.5">{{ $r->title }}</td>
                    <td class="py-1.5 text-slate-600">{{ $r->category_l1 }} / {{ $r->category_l2 }}</td>
                    <td class="py-1.5 text-right">{{ $r->inherent_score }}</td>
                    <td class="py-1.5 text-right font-semibold">{{ $r->residual_score }}</td>
                    <td class="py-1.5"><span class="px-2 py-0.5 rounded bg-slate-100 text-xs">{{ $r->status }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-4 text-center text-slate-500">Brak ryzyk. <a href="{{ route('risks.create') }}" class="text-emerald-600 hover:underline">Dodaj pierwsze</a> lub <a href="{{ route('scenarios.index') }}" class="text-emerald-600 hover:underline">adoptuj z biblioteki</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded shadow p-4">
    <h2 class="font-semibold mb-3">Wskaźniki KCI / KPI / KRI</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach($indicators as $ind)
            @php
                $latest = $ind->latestMeasurement;
                $status = $latest?->status ?? 'gray';
                $bgs = ['green' => 'bg-emerald-500', 'amber' => 'bg-amber-500', 'red' => 'bg-red-500', 'gray' => 'bg-slate-300'];
            @endphp
            <a href="{{ route('indicators.show', $ind) }}" class="block bg-slate-50 rounded p-3 hover:bg-slate-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-mono">{{ $ind->code }}</span>
                    <span class="inline-block w-3 h-3 rounded-full {{ $bgs[$status] }}"></span>
                </div>
                <div class="text-sm font-medium mt-1 line-clamp-2">{{ $ind->name }}</div>
                <div class="text-2xl font-bold mt-1">
                    @if($latest)
                        {{ rtrim(rtrim(number_format((float)$latest->value, 2), '0'), '.') }}<span class="text-sm font-normal text-slate-500"> {{ $ind->unit }}</span>
                    @else
                        <span class="text-slate-400 text-sm">brak danych</span>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection
