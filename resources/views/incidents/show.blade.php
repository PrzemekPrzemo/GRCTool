@extends('layouts.app')
@section('content')
@php
    $sevBg = match($incident->severity) {
        'Critical' => 'bg-red-600 text-white',
        'High'     => 'bg-orange-500 text-white',
        'Medium'   => 'bg-amber-300 text-amber-900',
        default    => 'bg-slate-200 text-slate-700',
    };
    $stBg = match($incident->status) {
        'New'          => 'bg-slate-100 text-slate-700',
        'Investigating'=> 'bg-blue-100 text-blue-800',
        'Containment'  => 'bg-amber-100 text-amber-800',
        'Eradication'  => 'bg-orange-100 text-orange-800',
        'Recovery'     => 'bg-purple-100 text-purple-800',
        'Closed'       => 'bg-emerald-100 text-emerald-800',
        default        => 'bg-slate-100',
    };
@endphp

{{-- Header --}}
<div class="flex items-start justify-between mb-6 gap-4">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <a href="{{ route('incidents.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Incydenty</a>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-2xl font-semibold">{{ $incident->title }}</h1>
            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $sevBg }}">{{ $incident->severity }}</span>
            <span class="px-2 py-0.5 rounded text-xs {{ $stBg }}">{{ $incident->status }}</span>
            @if($incident->is_breach)
                <span class="px-2 py-0.5 rounded text-xs bg-red-600 text-white font-bold">⚠ BREACH</span>
            @endif
        </div>
        <div class="text-sm text-slate-500 mt-1">{{ $incident->code }} · Owner: {{ $incident->owner?->name ?? '—' }}</div>
    </div>
    <div class="flex gap-2 flex-shrink-0">
        @can('incident.update')
            <a href="{{ route('incidents.edit', $incident) }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm">Edytuj</a>
        @endcan
        @can('incident.delete')
            <form method="POST" action="{{ route('incidents.destroy', $incident) }}" onsubmit="return confirm('Usunąć incydent?')">
                @csrf @method('DELETE')
                <button class="px-3 py-1.5 border border-red-200 text-red-600 rounded text-sm">Usuń</button>
            </form>
        @endcan
    </div>
</div>

{{-- Breach + NIS2 deadline alert --}}
@if($incident->is_breach && $incident->enisa_is_significant && $incident->enisa_notification_deadline)
<div class="bg-red-50 border border-red-200 rounded p-4 mb-6">
    <h3 class="font-semibold text-red-800 mb-2">⚠ Incydent istotny NIS2 — wymagane zgłoszenie (Art. 23)</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
        <div class="bg-white rounded p-3 border border-red-100">
            <div class="text-xs text-red-500 mb-1">Wczesne ostrzeżenie (24h)</div>
            <div class="font-medium {{ $incident->enisa_early_warning_deadline->isPast() ? 'text-red-700' : 'text-slate-800' }}">
                {{ $incident->enisa_early_warning_deadline->format('Y-m-d H:i') }}
                @if($incident->enisa_early_warning_deadline->isPast())
                    <span class="text-xs text-red-600 font-normal ml-1">(PRZEKROCZONY)</span>
                @else
                    <span class="text-xs text-slate-500 font-normal ml-1">(za {{ $incident->enisa_early_warning_deadline->diffForHumans() }})</span>
                @endif
            </div>
        </div>
        <div class="bg-white rounded p-3 border border-red-100">
            <div class="text-xs text-red-500 mb-1">Powiadomienie właściwe (72h)</div>
            <div class="font-medium {{ $incident->enisa_notification_deadline->isPast() ? 'text-red-700' : 'text-slate-800' }}">
                {{ $incident->enisa_notification_deadline->format('Y-m-d H:i') }}
                @if($incident->enisa_notification_deadline->isPast())
                    <span class="text-xs text-red-600 font-normal ml-1">(PRZEKROCZONY)</span>
                @else
                    <span class="text-xs text-slate-500 font-normal ml-1">(za {{ $incident->enisa_notification_deadline->diffForHumans() }})</span>
                @endif
            </div>
        </div>
        <div class="bg-white rounded p-3 border border-red-100">
            <div class="text-xs text-red-500 mb-1">Raport końcowy (30 dni)</div>
            <div class="font-medium {{ $incident->enisa_final_report_deadline->isPast() ? 'text-red-700' : 'text-slate-800' }}">
                {{ $incident->enisa_final_report_deadline->format('Y-m-d H:i') }}
                @if($incident->enisa_final_report_deadline->isPast())
                    <span class="text-xs text-red-600 font-normal ml-1">(PRZEKROCZONY)</span>
                @else
                    <span class="text-xs text-slate-500 font-normal ml-1">(za {{ $incident->enisa_final_report_deadline->diffForHumans() }})</span>
                @endif
            </div>
        </div>
    </div>
    <p class="text-xs text-red-600 mt-2">Punkt kontaktowy: CSIRT NASK (cert@cert.pl) lub CSIRT GOV (gov@cert.gov.pl)</p>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left column --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Detail --}}
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Szczegóły</h2>
            @if($incident->description)
                <p class="text-sm text-slate-700 whitespace-pre-line mb-3">{{ $incident->description }}</p>
            @endif
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <dt class="text-slate-500">Kod</dt><dd class="font-mono">{{ $incident->code }}</dd>
                <dt class="text-slate-500">Źródło</dt><dd>{{ $incident->source ?? '—' }}</dd>
                <dt class="text-slate-500">Szacowany koszt</dt>
                <dd>{{ $incident->estimated_cost_eur ? number_format((float)$incident->estimated_cost_eur, 2).' EUR' : '—' }}</dd>
            </dl>
        </div>

        {{-- MTTD/MTTA/MTTR --}}
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Metryki czasu reakcji</h2>
            <div class="grid grid-cols-3 gap-3">
                @php
                    $mttd = $incident->mttdMinutes();
                    $mtta = $incident->mttaMinutes();
                    $mttr = $incident->mttrHours();
                @endphp
                <div class="bg-blue-50 rounded p-3 text-center">
                    <div class="text-xs text-blue-600 mb-1">MTTD (wykrycie)</div>
                    <div class="text-2xl font-bold text-blue-800">{{ $mttd ?? '—' }}</div>
                    <div class="text-xs text-blue-500">{{ $mttd !== null ? 'minut' : 'brak danych' }}</div>
                </div>
                <div class="bg-amber-50 rounded p-3 text-center">
                    <div class="text-xs text-amber-600 mb-1">MTTA (potwierdzenie)</div>
                    <div class="text-2xl font-bold text-amber-800">{{ $mtta ?? '—' }}</div>
                    <div class="text-xs text-amber-500">{{ $mtta !== null ? 'minut' : 'brak danych' }}</div>
                </div>
                <div class="bg-emerald-50 rounded p-3 text-center">
                    <div class="text-xs text-emerald-600 mb-1">MTTR (rozwiązanie)</div>
                    <div class="text-2xl font-bold text-emerald-800">{{ $mttr ?? '—' }}</div>
                    <div class="text-xs text-emerald-500">{{ $mttr !== null ? 'godzin' : 'brak danych' }}</div>
                </div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-4">Oś czasu incydentu</h2>
            <div class="flex items-start gap-0">
                @php
                    $steps = [
                        ['label' => 'Wystąpił',       'field' => 'occurred_at'],
                        ['label' => 'Wykryty',        'field' => 'detected_at'],
                        ['label' => 'Potwierdzony',   'field' => 'acknowledged_at'],
                        ['label' => 'Opanowany',      'field' => 'contained_at'],
                        ['label' => 'Rozwiązany',     'field' => 'resolved_at'],
                    ];
                @endphp
                @foreach($steps as $i => $step)
                <div class="flex-1 flex flex-col items-center relative">
                    @if($i < count($steps) - 1)
                        <div class="absolute top-3 left-1/2 right-0 h-0.5 {{ $incident->{$step['field']} ? 'bg-emerald-400' : 'bg-slate-200' }}"></div>
                    @endif
                    <div class="w-7 h-7 rounded-full border-2 flex items-center justify-center z-10
                        {{ $incident->{$step['field']} ? 'bg-emerald-500 border-emerald-500 text-white' : 'bg-white border-slate-300 text-slate-400' }}">
                        {{ $incident->{$step['field']} ? '✓' : ($i+1) }}
                    </div>
                    <div class="text-xs font-medium text-slate-700 mt-2 text-center">{{ $step['label'] }}</div>
                    <div class="text-xs text-slate-400 text-center mt-0.5">
                        {{ $incident->{$step['field']}?->format('Y-m-d H:i') ?? 'Oczekuje' }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ENISA scoring --}}
        @if($incident->enisa_severity_score !== null)
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Ocena wpływu ENISA (NIS2 Art. 23)</h2>
            @php
                $lvlBg = match($incident->enisa_severity_level) {
                    'Critical' => 'bg-red-600 text-white',
                    'High'     => 'bg-orange-500 text-white',
                    'Medium'   => 'bg-amber-300 text-amber-900',
                    default    => 'bg-emerald-200 text-emerald-800',
                };
                $userLabels    = ['lt100'=>'< 100','lt1k'=>'100–1K','lt10k'=>'1K–10K','lt100k'=>'10K–100K','ge100k'=>'> 100 000'];
                $serviceLabels = ['none'=>'Brak','minimal'=>'Minimalny','partial'=>'Częściowy','significant'=>'Znaczny','full'=>'Pełna awaria'];
                $geoLabels     = ['local'=>'Lokalny','regional'=>'Regionalny','national'=>'Krajowy','cross_border'=>'Transgraniczny'];
                $econLabels    = ['negligible'=>'Pomijalny','low'=>'Niski','moderate'=>'Umiarkowany','significant'=>'Znaczący','severe'=>'Poważny'];
            @endphp
            <div class="flex items-center gap-4 mb-4">
                <div class="text-3xl font-bold text-slate-800">{{ $incident->enisa_severity_score }} <span class="text-sm text-slate-400">/ 3.00</span></div>
                <span class="px-3 py-1 rounded text-sm font-medium {{ $lvlBg }}">{{ $incident->enisa_severity_level }}</span>
                @if($incident->enisa_is_significant)
                    <span class="px-3 py-1 rounded text-sm font-medium bg-red-100 text-red-700 border border-red-200">
                        Incydent istotny — NIS2 Art. 23
                    </span>
                @else
                    <span class="px-3 py-1 rounded text-sm bg-slate-100 text-slate-600">Poniżej progu istotności</span>
                @endif
            </div>
            <dl class="grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-2 text-sm">
                <dt class="text-slate-500">Liczba użytkowników</dt>
                <dd>{{ $userLabels[$incident->enisa_users_affected_band] ?? '—' }}</dd>
                <dt class="text-slate-500">Wpływ na usługę</dt>
                <dd>{{ $serviceLabels[$incident->enisa_service_impact] ?? '—' }}</dd>
                <dt class="text-slate-500">Zasięg geograficzny</dt>
                <dd>{{ $geoLabels[$incident->enisa_geographic_spread] ?? '—' }}</dd>
                <dt class="text-slate-500">Czas trwania</dt>
                <dd>{{ $incident->enisa_duration_hours ? $incident->enisa_duration_hours.' h' : '—' }}</dd>
                <dt class="text-slate-500">Wpływ ekonomiczny</dt>
                <dd>{{ $econLabels[$incident->enisa_economic_impact] ?? '—' }}</dd>
            </dl>
            <p class="text-xs text-slate-400 mt-3">Wagi ENISA: użytkownicy 25%, usługa 25%, zasięg 20%, czas 20%, ekonomia 10%. Próg istotności: wynik ≥ 1.5.</p>
        </div>
        @endif

        {{-- Post-mortem --}}
        @if($incident->post_mortem)
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Post-mortem / Wnioski</h2>
            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $incident->post_mortem }}</p>
        </div>
        @endif

        {{-- Linked objects --}}
        @if(($incident->affected_assets && count($incident->affected_assets)) ||
            ($incident->linked_risks && count($incident->linked_risks)) ||
            ($incident->linked_controls && count($incident->linked_controls)))
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Powiązania</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                @if($incident->affected_assets && count($incident->affected_assets))
                <div>
                    <div class="text-xs font-medium text-slate-500 mb-1 uppercase">Dotknięte aktywa</div>
                    @foreach($incident->affected_assets as $a)
                        <span class="inline-block bg-slate-100 rounded px-2 py-0.5 text-xs mr-1 mb-1">{{ $a }}</span>
                    @endforeach
                </div>
                @endif
                @if($incident->linked_risks && count($incident->linked_risks))
                <div>
                    <div class="text-xs font-medium text-slate-500 mb-1 uppercase">Powiązane ryzyka</div>
                    @foreach($incident->linked_risks as $r)
                        <span class="inline-block bg-amber-50 text-amber-800 rounded px-2 py-0.5 text-xs mr-1 mb-1">{{ $r }}</span>
                    @endforeach
                </div>
                @endif
                @if($incident->linked_controls && count($incident->linked_controls))
                <div>
                    <div class="text-xs font-medium text-slate-500 mb-1 uppercase">Powiązane kontrole</div>
                    @foreach($incident->linked_controls as $c)
                        <span class="inline-block bg-blue-50 text-blue-800 rounded px-2 py-0.5 text-xs mr-1 mb-1">{{ $c }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Right column — actions --}}
    <div class="space-y-4">
        {{-- Status workflow --}}
        @can('incident.update')
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold text-slate-700 mb-3 text-sm">Zmień status</h3>
            <form method="POST" action="{{ route('incidents.status', $incident) }}">
                @csrf
                <select name="status" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm mb-2">
                    @foreach(\App\Models\Incident::STATUSES as $s)
                        <option @selected($incident->status === $s) value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
                <button class="w-full px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Zaktualizuj status</button>
            </form>
        </div>

        {{-- Breach toggle --}}
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold text-slate-700 mb-2 text-sm">Breach / Notyfikacja</h3>
            <p class="text-xs text-slate-500 mb-3">
                {{ $incident->is_breach ? 'Incydent oznaczony jako naruszenie wymagające notyfikacji (RODO/NIS2/DORA).' : 'Incydent nie jest oznaczony jako naruszenie.' }}
            </p>
            <form method="POST" action="{{ route('incidents.breach', $incident) }}">
                @csrf
                <button class="w-full px-3 py-1.5 rounded text-sm {{ $incident->is_breach ? 'bg-slate-100 text-slate-700 border border-slate-300' : 'bg-red-600 text-white' }}">
                    {{ $incident->is_breach ? 'Odznacz BREACH' : 'Oznacz jako BREACH' }}
                </button>
            </form>
        </div>
        @endcan

        {{-- Metadata --}}
        <div class="bg-white rounded shadow p-4 text-xs text-slate-500 space-y-1">
            <div>Utworzony: {{ $incident->created_at->format('Y-m-d H:i') }}</div>
            <div>Zaktualizowany: {{ $incident->updated_at->format('Y-m-d H:i') }}</div>
            @if($incident->affected_clients && count($incident->affected_clients))
                <div class="pt-2">
                    <div class="font-medium text-slate-600 mb-1">Dotknięci klienci</div>
                    @foreach($incident->affected_clients as $c)
                        <span class="inline-block bg-slate-100 rounded px-2 py-0.5 mr-1 mb-1">{{ $c }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<x-comment-thread :model="$incident" />
@endsection
