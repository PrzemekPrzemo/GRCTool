@extends('layouts.app')
@section('content')

<div class="flex justify-between items-center mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Wskaźniki organizacji</h1>
        <p class="text-slate-500 text-sm">Zestawienie KPI, KRI i KCI — status na dziś</p>
    </div>
    <a href="{{ route('indicators.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowy wskaźnik</a>
</div>

{{-- RAG Summary --}}
<div class="grid grid-cols-4 gap-3 mb-6">
    <div class="bg-emerald-50 border border-emerald-200 rounded shadow p-4 text-center">
        <div class="text-3xl font-bold text-emerald-700">{{ $ragCounts['green'] }}</div>
        <div class="text-sm text-emerald-600">Zielony</div>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded shadow p-4 text-center">
        <div class="text-3xl font-bold text-amber-700">{{ $ragCounts['amber'] }}</div>
        <div class="text-sm text-amber-600">Żółty</div>
    </div>
    <div class="bg-red-50 border border-red-200 rounded shadow p-4 text-center">
        <div class="text-3xl font-bold text-red-700">{{ $ragCounts['red'] }}</div>
        <div class="text-sm text-red-600">Czerwony</div>
    </div>
    <div class="bg-slate-50 border border-slate-200 rounded shadow p-4 text-center">
        <div class="text-3xl font-bold text-slate-500">{{ $ragCounts['no_data'] }}</div>
        <div class="text-sm text-slate-500">Brak danych</div>
    </div>
</div>

{{-- KRI Alerts --}}
@if($kriAlerts->isNotEmpty())
<div class="bg-red-50 border border-red-200 rounded shadow p-4 mb-6">
    <h2 class="font-semibold text-red-800 mb-2">Alerty KRI — przekroczone progi</h2>
    <div class="space-y-1">
    @foreach($kriAlerts as $ind)
    <div class="flex items-center justify-between text-sm py-1 border-b border-red-100 last:border-0">
        <div><a href="{{ route('indicators.show', $ind) }}" class="font-mono text-red-700 hover:underline">{{ $ind->code }}</a> — {{ $ind->name }}</div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-500">{{ $ind->latestMeasurement->measured_at->format('Y-m-d') }}</span>
            <span class="font-semibold text-red-700">{{ $ind->latestMeasurement->value }} {{ $ind->unit }}</span>
        </div>
    </div>
    @endforeach
    </div>
</div>
@endif

{{-- KPI / KRI / KCI tables grouped --}}
@foreach(['KRI' => 'Kluczowe wskaźniki ryzyka (KRI)', 'KPI' => 'Kluczowe wskaźniki wydajności (KPI)', 'KCI' => 'Kluczowe wskaźniki kontroli (KCI)'] as $type => $label)
@if(isset($byType[$type]) && $byType[$type]->isNotEmpty())
<div class="bg-white rounded shadow mb-4">
    <div class="px-4 py-2 border-b border-slate-100 font-semibold text-sm text-slate-700">{{ $label }} ({{ $byType[$type]->count() }})</div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500 text-left">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Nazwa</th>
                <th class="px-3 py-2">Właściciel</th>
                <th class="px-3 py-2 text-right">Ostatnia wartość</th>
                <th class="px-3 py-2">Cel</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Data pomiaru</th>
            </tr>
        </thead>
        <tbody>
        @foreach($byType[$type] as $ind)
        @php
            $m = $ind->latestMeasurement;
            $status = $m ? $ind->classify((float)$m->value) : 'no_data';
            $statusBg = ['green'=>'bg-emerald-100 text-emerald-800','amber'=>'bg-amber-100 text-amber-800','red'=>'bg-red-100 text-red-800','no_data'=>'bg-slate-100 text-slate-500'][$status];
            $statusLabel = ['green'=>'OK','amber'=>'Uwaga','red'=>'Alert','no_data'=>'Brak'][$status];
        @endphp
        <tr class="border-t border-slate-100 hover:bg-slate-50">
            <td class="px-3 py-2 font-mono text-xs"><a href="{{ route('indicators.show', $ind) }}" class="text-emerald-700 hover:underline">{{ $ind->code }}</a></td>
            <td class="px-3 py-2">{{ $ind->name }}</td>
            <td class="px-3 py-2 text-xs text-slate-500">{{ $ind->owner?->name ?? '—' }}</td>
            <td class="px-3 py-2 text-right font-semibold {{ $status === 'red' ? 'text-red-700' : '' }}">{{ $m ? $m->value.' '.$ind->unit : '—' }}</td>
            <td class="px-3 py-2 text-xs text-slate-500">{{ $ind->target_value ? $ind->target_value.' '.$ind->unit : '—' }}</td>
            <td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">{{ $statusLabel }}</span></td>
            <td class="px-3 py-2 text-xs text-slate-500">{{ $m?->measured_at->format('Y-m-d') ?? '—' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
@endforeach
@endsection
