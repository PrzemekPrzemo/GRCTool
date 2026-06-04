@extends('layouts.app')
@section('content')
@php
    $tierBg = match($party->tier) {
        'Critical' => 'bg-red-100 text-red-800',
        'High'     => 'bg-orange-100 text-orange-800',
        'Medium'   => 'bg-amber-100 text-amber-800',
        default    => 'bg-slate-100 text-slate-600',
    };
@endphp

<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('third-parties.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Strony trzecie</a>
    <h1 class="text-2xl font-semibold">{{ $party->name }}</h1>
    <span class="px-2 py-0.5 rounded text-xs {{ $tierBg }}">{{ $party->tier }}</span>
    @if(!$party->is_active)
        <span class="px-2 py-0.5 rounded text-xs bg-slate-200 text-slate-600">Nieaktywny</span>
    @endif
    @can('third_party.update')
        <a href="{{ route('third-parties.edit', $party) }}" class="ml-auto px-3 py-1.5 border border-slate-300 rounded text-sm">Edytuj</a>
    @endcan
</div>

@if($party->next_assessment_due?->isPast())
<div class="bg-amber-50 border-l-4 border-amber-400 px-4 py-2 rounded-r mb-4 text-sm text-amber-800">
    Termin oceny bezpieczeństwa minął: <strong>{{ $party->next_assessment_due->format('Y-m-d') }}</strong>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Dane podstawowe</h2>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <dt class="text-slate-500">Kod</dt>
                <dd class="font-mono">{{ $party->code }}</dd>
                <dt class="text-slate-500">Świadczona usługa</dt>
                <dd>{{ $party->service_provided ?? '—' }}</dd>
                <dt class="text-slate-500">Kraj przetwarzania</dt>
                <dd>{{ $party->country_of_processing ?? '—' }}</dd>
                <dt class="text-slate-500">Mechanizm transferu</dt>
                <dd>{{ $party->transfer_mechanism ?? '—' }}</dd>
                <dt class="text-slate-500">Podstawa prawna</dt>
                <dd>{{ $party->legal_basis ?? '—' }}</dd>
                <dt class="text-slate-500">Security Rating</dt>
                <dd>
                    @if($party->security_rating !== null)
                        <span class="px-2 py-0.5 rounded text-xs {{ $party->security_rating >= 70 ? 'bg-emerald-100 text-emerald-800' : ($party->security_rating >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                            {{ $party->security_rating }}/100
                        </span>
                    @else — @endif
                </dd>
                <dt class="text-slate-500">Ostatnia ocena</dt>
                <dd>{{ $party->last_assessment_date?->format('Y-m-d') ?? '—' }}</dd>
                <dt class="text-slate-500">Następna ocena</dt>
                <dd class="{{ $party->next_assessment_due?->isPast() ? 'text-red-700 font-semibold' : '' }}">
                    {{ $party->next_assessment_due?->format('Y-m-d') ?? '—' }}
                </dd>
                @if($party->dpa_url)
                <dt class="text-slate-500">DPA / SLA</dt>
                <dd><a href="{{ $party->dpa_url }}" target="_blank" class="text-emerald-700 hover:underline">Otwórz dokument ↗</a></dd>
                @endif
            </dl>
        </div>

        @if(!empty($party->certifications))
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold text-slate-700 text-sm mb-2">Certyfikacje</h3>
            <div class="flex flex-wrap gap-1">
                @foreach($party->certifications as $cert)
                    <span class="px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-800">{{ $cert }}</span>
                @endforeach
            </div>
        </div>
        @endif

        @if($party->processingActivities->isNotEmpty())
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Powiązane czynności przetwarzania</h2>
            <div class="space-y-2">
                @foreach($party->processingActivities as $act)
                @php
                    $roleLabel = match($act->pivot->role) {
                        'processor' => 'Podmiot przetwarzający',
                        'joint_controller' => 'Współadministrator',
                        'sub_processor' => 'Podprocesor',
                        default => $act->pivot->role,
                    };
                @endphp
                <div class="flex items-center justify-between p-2 rounded border border-slate-100">
                    <a href="{{ route('rcp.show', $act) }}" class="text-sm text-emerald-700 hover:underline">
                        <span class="font-mono text-xs">{{ $act->code }}</span> — {{ $act->name }}
                    </a>
                    <span class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-800">{{ $roleLabel }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="space-y-4">
        @if(!empty($party->data_categories))
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold text-slate-700 text-sm mb-2">Kategorie danych</h3>
            <div class="flex flex-wrap gap-1">
                @foreach($party->data_categories as $cat)
                    <span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-800">{{ $cat }}</span>
                @endforeach
            </div>
        </div>
        @endif

        <div class="bg-white rounded shadow p-4 text-xs text-slate-500 space-y-1.5">
            <div>Kod: <span class="font-mono text-slate-700">{{ $party->code }}</span></div>
            <div>Dodano: {{ $party->created_at->format('Y-m-d H:i') }}</div>
        </div>
    </div>
</div>
@endsection
