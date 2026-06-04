@extends('layouts.app')
@section('content')
@php
    $resultBg = match($nis2->result) {
        'not_subject'      => 'bg-emerald-500',
        'important_entity' => 'bg-amber-500',
        'essential_entity' => 'bg-red-600',
        default            => 'bg-slate-500',
    };
    $resultTitle = match($nis2->result) {
        'not_subject'      => 'NIE PODLEGASZ pod NIS2',
        'important_entity' => 'Podmiot Ważny — Załącznik II',
        'essential_entity' => 'Podmiot Kluczowy — Załącznik I',
        default            => 'Wynik nieznany',
    };
@endphp

<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('nis2.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Oceny NIS2</a>
    <h1 class="text-2xl font-semibold">{{ $nis2->code }}</h1>
    <span class="px-2 py-0.5 rounded text-xs {{ $nis2->isFinal() ? 'bg-slate-800 text-white' : 'bg-yellow-100 text-yellow-800' }}">
        {{ $nis2->isFinal() ? 'Sfinalizowany' : 'Roboczy' }}
    </span>
</div>

{{-- Result banner --}}
<div class="{{ $resultBg }} text-white rounded-lg p-6 mb-6 shadow">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <div class="text-xs font-medium uppercase tracking-wider opacity-80 mb-1">Wynik oceny NIS2 dla: {{ $nis2->organization_name }}</div>
            <h2 class="text-3xl font-bold mb-2">{{ $resultTitle }}</h2>
            @if($nis2->annex_classification && $nis2->annex_classification !== 'not_applicable')
                <div class="opacity-90 text-sm">{{ $nis2->annexLabel() }} NIS2 · Sektor: {{ $nis2->sectorLabel() }}</div>
            @endif
            @if($nis2->entity_size)
                <div class="opacity-75 text-xs mt-1">Klasyfikacja rozmiaru: {{ $nis2->entity_size }}</div>
            @endif
        </div>
        @if(!$nis2->isFinal())
            <div class="flex gap-2">
                @can('nis2.update')
                    <a href="{{ route('nis2.edit', $nis2) }}" class="px-3 py-1.5 bg-white bg-opacity-20 hover:bg-opacity-30 rounded text-sm">Edytuj</a>
                    <form method="POST" action="{{ route('nis2.finalize', $nis2) }}" onsubmit="return confirm('Sfinalizować ocenę? Nie będzie można jej edytować.')">
                        @csrf
                        <button class="px-3 py-1.5 bg-white text-slate-900 rounded text-sm font-medium">Finalizuj ocenę</button>
                    </form>
                @endcan
            </div>
        @else
            @can('nis2.update')
                <a href="{{ route('nis2.edit', $nis2) }}" class="px-3 py-1.5 bg-white bg-opacity-20 hover:bg-opacity-30 rounded text-sm">Edytuj</a>
            @endcan
        @endif
    </div>
</div>

{{-- Justification --}}
@if($nis2->justification)
<div class="bg-white rounded shadow p-5 mb-6">
    <h2 class="font-semibold text-slate-700 mb-2">Uzasadnienie</h2>
    <p class="text-sm text-slate-700">{{ $nis2->justification }}</p>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        {{-- Obligations --}}
        @if($nis2->result === 'essential_entity' || $nis2->result === 'important_entity')
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Obowiązki wynikające z NIS2</h2>
            @if($nis2->result === 'essential_entity')
            <div class="bg-red-50 border-l-4 border-red-400 px-4 py-2 text-sm text-red-800 mb-4 rounded-r">
                Jako <strong>Podmiot Kluczowy (Annex I)</strong> podlegasz pod rygorystyczny nadzór ex-ante CSIRT i organu nadzorczego.
            </div>
            @else
            <div class="bg-amber-50 border-l-4 border-amber-400 px-4 py-2 text-sm text-amber-800 mb-4 rounded-r">
                Jako <strong>Podmiot Ważny (Annex II)</strong> podlegasz pod nadzór reaktywny (ex-post) organu nadzorczego.
            </div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <h3 class="font-medium text-slate-700 mb-2">Art. 21 — Środki zarządzania ryzykiem</h3>
                    <ul class="space-y-1 text-slate-600">
                        <li class="flex gap-2"><span class="text-slate-400 flex-shrink-0">•</span>Polityka bezpieczeństwa informacji i zarządzania ryzykiem</li>
                        <li class="flex gap-2"><span class="text-slate-400 flex-shrink-0">•</span>Zarządzanie incydentami (SIEM, procedury IR)</li>
                        <li class="flex gap-2"><span class="text-slate-400 flex-shrink-0">•</span>Ciągłość działania (BCP/DRP)</li>
                        <li class="flex gap-2"><span class="text-slate-400 flex-shrink-0">•</span>Bezpieczeństwo łańcucha dostaw</li>
                        <li class="flex gap-2"><span class="text-slate-400 flex-shrink-0">•</span>Bezpieczeństwo sieci i systemów informacyjnych</li>
                        <li class="flex gap-2"><span class="text-slate-400 flex-shrink-0">•</span>Polityki i procedury dotyczące kryptografii</li>
                        <li class="flex gap-2"><span class="text-slate-400 flex-shrink-0">•</span>Cyber higiena i szkolenia pracowników</li>
                        <li class="flex gap-2"><span class="text-slate-400 flex-shrink-0">•</span>Bezpieczeństwo HR i kontrola dostępu</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-medium text-slate-700 mb-2">Art. 23 — Zgłaszanie incydentów</h3>
                    <div class="space-y-2">
                        <div class="bg-red-50 rounded p-2">
                            <div class="text-xs font-semibold text-red-700">24 godziny</div>
                            <div class="text-xs text-red-600">Wczesne ostrzeżenie do CSIRT (od momentu wykrycia)</div>
                        </div>
                        <div class="bg-amber-50 rounded p-2">
                            <div class="text-xs font-semibold text-amber-700">72 godziny</div>
                            <div class="text-xs text-amber-600">Powiadomienie właściwe z wstępną oceną incydentu</div>
                        </div>
                        <div class="bg-slate-50 rounded p-2">
                            <div class="text-xs font-semibold text-slate-700">1 miesiąc</div>
                            <div class="text-xs text-slate-600">Raport końcowy z analizą przyczyn i działaniami naprawczymi</div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Obowiązek dotyczy incydentów "istotnych" (score ENISA ≥ 1.5).</p>
                    <h3 class="font-medium text-slate-700 mt-3 mb-1">Art. 24 — Rejestracja</h3>
                    <p class="text-xs text-slate-600">Podmiot musi być wpisany do rejestru prowadzonego przez organ nadzorczy (w Polsce: UODO / CSIRT NASK).</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Significant incidents cross-link --}}
        @if($significantBreaches > 0 && in_array($nis2->result, ['essential_entity','important_entity']))
        <div class="bg-white rounded shadow p-4 border-l-4 border-red-400">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-slate-700 text-sm">Incydenty wymagające zgłoszenia NIS2</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        W rejestrze widnieje {{ $significantBreaches }} incydent(ów) oznaczonych jako naruszenie z wynikiem ENISA ≥ 1.5.
                    </p>
                </div>
                <a href="{{ route('incidents.index', ['breach' => 1]) }}" class="px-3 py-1.5 bg-red-600 text-white rounded text-xs flex-shrink-0">
                    Pokaż incydenty
                </a>
            </div>
        </div>
        @endif

        {{-- Input data summary --}}
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Dane wejściowe oceny</h2>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <dt class="text-slate-500">Liczba pracowników</dt>
                <dd>{{ $nis2->employee_count ? number_format($nis2->employee_count) : '—' }}</dd>
                <dt class="text-slate-500">Obrót roczny</dt>
                <dd>{{ $nis2->annual_turnover_eur ? number_format((float)$nis2->annual_turnover_eur / 1_000_000, 2).' M€' : '—' }}</dd>
                <dt class="text-slate-500">Suma bilansowa</dt>
                <dd>{{ $nis2->balance_sheet_eur ? number_format((float)$nis2->balance_sheet_eur / 1_000_000, 2).' M€' : '—' }}</dd>
                <dt class="text-slate-500">Sektor</dt>
                <dd>{{ $nis2->sectorLabel() }}{{ $nis2->subsector ? ' — '.$nis2->subsector : '' }}</dd>
                <dt class="text-slate-500">Administracja publiczna</dt>
                <dd>{{ $nis2->is_public_administration ? 'Tak' : 'Nie' }}</dd>
                <dt class="text-slate-500">Infrastruktura krytyczna</dt>
                <dd>{{ $nis2->is_critical_infrastructure ? 'Tak' : 'Nie' }}</dd>
            </dl>
            @php
                $digitalActive = collect([
                    'provides_dns' => 'DNS', 'provides_tld' => 'TLD', 'provides_ixp' => 'IXP',
                    'provides_trust_services' => 'Usługi zaufania', 'provides_cloud' => 'Cloud',
                    'provides_datacentre' => 'DC', 'provides_cdn' => 'CDN',
                    'provides_msp_mssp' => 'MSP/MSSP', 'provides_ecomms' => 'e-Communications',
                ])->filter(fn($label, $field) => $nis2->$field);
            @endphp
            @if($digitalActive->isNotEmpty())
            <div class="mt-3 pt-3 border-t border-slate-100">
                <div class="text-xs font-medium text-slate-500 mb-2 uppercase">Świadczone usługi cyfrowe</div>
                <div class="flex flex-wrap gap-1">
                    @foreach($digitalActive as $label)
                        <span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-800">{{ $label }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        @if($nis2->notes)
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-2">Uwagi</h2>
            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $nis2->notes }}</p>
        </div>
        @endif
    </div>

    {{-- Right column --}}
    <div class="space-y-4">
        {{-- CSIRT contact --}}
        @if($nis2->result !== 'not_subject' && $nis2->result !== null)
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold text-slate-700 text-sm mb-3">Punkt kontaktowy CSIRT</h3>
            <div class="space-y-2 text-xs">
                <div class="p-2 bg-slate-50 rounded">
                    <div class="font-medium">CSIRT NASK</div>
                    <div class="text-slate-500">cert@cert.pl · +48 22 380 82 74</div>
                    <div class="text-slate-500">Dla sektora prywatnego</div>
                </div>
                <div class="p-2 bg-slate-50 rounded">
                    <div class="font-medium">CSIRT GOV</div>
                    <div class="text-slate-500">gov@cert.gov.pl</div>
                    <div class="text-slate-500">Dla administracji publicznej</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Metadata --}}
        <div class="bg-white rounded shadow p-4 text-xs text-slate-500 space-y-1.5">
            <div>Kod: <span class="font-mono text-slate-700">{{ $nis2->code }}</span></div>
            <div>Data oceny: <span class="text-slate-700">{{ $nis2->assessment_date?->format('Y-m-d') }}</span></div>
            <div>Przeprowadzona przez: <span class="text-slate-700">{{ $nis2->conductedBy?->name ?? '—' }}</span></div>
            @if($nis2->isFinal())
                <div>Sfinalizował: <span class="text-slate-700">{{ $nis2->reviewer?->name ?? '—' }}</span></div>
                <div>Data finalizacji: <span class="text-slate-700">{{ $nis2->reviewed_at?->format('Y-m-d H:i') }}</span></div>
            @endif
            <div class="pt-1">Utworzono: {{ $nis2->created_at->format('Y-m-d H:i') }}</div>
        </div>

        {{-- Legal note --}}
        <div class="bg-amber-50 border border-amber-200 rounded p-3 text-xs text-amber-800">
            <strong>Uwaga prawna:</strong> Niniejsza ocena ma charakter pomocniczy. Obowiązki wynikające z NIS2 mogą być rozszerzone przez krajowe przepisy implementacyjne (KSC). Skonsultuj się z prawnikiem specjalizującym się w regulacjach cyberbezpieczeństwa.
        </div>
    </div>
</div>
@endsection
