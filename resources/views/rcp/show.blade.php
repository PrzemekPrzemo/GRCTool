@extends('layouts.app')
@section('content')

<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('rcp.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Rejestr Czynności</a>
    <h1 class="text-2xl font-semibold">{{ $rcp->name }}</h1>
    @php
        $statusBg = match($rcp->status) {
            'active'       => 'bg-emerald-100 text-emerald-800',
            'under_review' => 'bg-amber-100 text-amber-800',
            default        => 'bg-slate-100 text-slate-600',
        };
    @endphp
    <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">
        {{ match($rcp->status) { 'active' => 'Aktywna', 'under_review' => 'W przeglądzie', default => 'Archiwalna' } }}
    </span>
    @can('rcp.update')
        <a href="{{ route('rcp.edit', $rcp) }}" class="ml-auto px-3 py-1.5 border border-slate-300 rounded text-sm">Edytuj</a>
    @endcan
</div>

{{-- Alerts --}}
@if($rcp->hasSpecialCategories())
<div class="bg-red-50 border-l-4 border-red-400 px-4 py-3 rounded-r mb-4 text-sm text-red-800">
    <strong>Szczególne kategorie danych (Art. 9 RODO)</strong> — czynność przetwarza dane wrażliwe. Wymagana wyraźna podstawa z Art. 9 ust. 2.
</div>
@endif
@if($rcp->dpia_required && $rcp->dpias->isEmpty())
<div class="bg-amber-50 border-l-4 border-amber-400 px-4 py-3 rounded-r mb-4 text-sm text-amber-800">
    <strong>Brak DPIA</strong> — ta czynność wymaga oceny skutków dla ochrony danych.
    @can('dpia.create')
        <a href="{{ route('dpias.create', ['activity' => $rcp->id]) }}" class="underline ml-1">Przeprowadź DPIA</a>
    @endcan
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        {{-- Main details --}}
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Szczegóły czynności</h2>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <dt class="text-slate-500">Cel przetwarzania</dt>
                <dd>{{ $rcp->purpose ?? '—' }}</dd>
                <dt class="text-slate-500">Podstawa prawna</dt>
                <dd>{{ $rcp->legalBasisLabel() }}</dd>
                <dt class="text-slate-500">System informatyczny</dt>
                <dd>{{ $rcp->system_name ?? '—' }}</dd>
                <dt class="text-slate-500">Okres retencji</dt>
                <dd>{{ $rcp->retention_period ?? '—' }}</dd>
                <dt class="text-slate-500">Administrator danych</dt>
                <dd>{{ $rcp->controller?->name ?? '—' }}</dd>
                <dt class="text-slate-500">Podmiot przetwarzający</dt>
                <dd>{{ $rcp->processor?->name ?? '—' }}</dd>
                <dt class="text-slate-500">Transfer poza EOG</dt>
                <dd>{{ $rcp->cross_border_transfer ? 'Tak — '.$rcp->transfer_mechanism : 'Nie' }}</dd>
            </dl>
            @if($rcp->description)
            <div class="mt-3 pt-3 border-t border-slate-100">
                <p class="text-sm text-slate-700">{{ $rcp->description }}</p>
            </div>
            @endif
        </div>

        {{-- Data categories --}}
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Kategorie danych</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="text-xs font-medium text-slate-500 uppercase mb-2">Kategorie zwykłe</h3>
                    @if(!empty($rcp->data_categories))
                        <div class="flex flex-wrap gap-1">
                            @foreach($rcp->data_categories as $cat)
                                <span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-800">
                                    {{ \App\Models\ProcessingActivity::DATA_CATEGORIES[$cat] ?? $cat }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-slate-400 text-sm">—</span>
                    @endif
                </div>
                <div>
                    <h3 class="text-xs font-medium text-red-600 uppercase mb-2">Szczególne kategorie (Art. 9)</h3>
                    @if(!empty($rcp->special_categories))
                        <div class="flex flex-wrap gap-1">
                            @foreach($rcp->special_categories as $cat)
                                <span class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-800">
                                    {{ \App\Models\ProcessingActivity::SPECIAL_CATEGORIES[$cat] ?? $cat }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-slate-400 text-sm">—</span>
                    @endif
                </div>
            </div>
            @if(!empty($rcp->data_subjects))
            <div class="mt-3 pt-3 border-t border-slate-100">
                <h3 class="text-xs font-medium text-slate-500 uppercase mb-2">Osoby, których dane dotyczą</h3>
                <div class="flex flex-wrap gap-1">
                    @foreach($rcp->data_subjects as $sub)
                        <span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-700">
                            {{ \App\Models\ProcessingActivity::DATA_SUBJECTS[$sub] ?? $sub }}
                        </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Third parties --}}
        @if($rcp->thirdParties->isNotEmpty())
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Podmioty przetwarzające i strony trzecie</h2>
            <div class="space-y-2">
                @foreach($rcp->thirdParties as $tp)
                @php
                    $roleLabel = match($tp->pivot->role) {
                        'processor' => 'Podmiot przetwarzający',
                        'joint_controller' => 'Współadministrator',
                        'sub_processor' => 'Podprocesor',
                        default => $tp->pivot->role,
                    };
                    $roleBg = match($tp->pivot->role) {
                        'joint_controller' => 'bg-amber-100 text-amber-800',
                        'sub_processor' => 'bg-purple-100 text-purple-800',
                        default => 'bg-blue-100 text-blue-800',
                    };
                @endphp
                <div class="flex items-center justify-between p-2 rounded border border-slate-100">
                    <div>
                        <a href="{{ route('third-parties.show', $tp) }}" class="font-medium text-sm text-emerald-700 hover:underline">{{ $tp->name }}</a>
                        <span class="text-xs text-slate-500 ml-2">{{ $tp->service_provided }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="px-2 py-0.5 rounded {{ $roleBg }}">{{ $roleLabel }}</span>
                        <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">{{ $tp->tier }}</span>
                        @if($tp->dpa_url)
                            <a href="{{ $tp->dpa_url }}" target="_blank" class="text-emerald-600 hover:underline">DPA ↗</a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- DPIAs --}}
        @if($rcp->dpias->isNotEmpty())
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Powiązane DPIA</h2>
            <div class="space-y-2">
                @foreach($rcp->dpias as $dpia)
                <div class="flex items-center justify-between p-2 rounded border border-slate-100">
                    <a href="{{ route('dpias.show', $dpia) }}" class="text-sm text-emerald-700 hover:underline font-mono">{{ $dpia->code }}</a>
                    <span class="text-sm ml-2">{{ $dpia->title }}</span>
                    <span class="ml-auto px-2 py-0.5 rounded text-xs {{ $dpia->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $dpia->status === 'approved' ? 'Zatwierdzona' : ucfirst($dpia->status) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Right column --}}
    <div class="space-y-4">
        <div class="bg-white rounded shadow p-4 text-xs text-slate-500 space-y-1.5">
            <div>Kod: <span class="font-mono text-slate-700">{{ $rcp->code }}</span></div>
            <div>Administrator: <span class="text-slate-700">{{ $rcp->controller?->name ?? '—' }}</span></div>
            <div>Utworzono: {{ $rcp->created_at->format('Y-m-d H:i') }}</div>
            <div>Zmieniono: {{ $rcp->updated_at->format('Y-m-d H:i') }}</div>
        </div>

        @if(!empty($rcp->security_measures))
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold text-slate-700 text-sm mb-2">Środki bezpieczeństwa</h3>
            <div class="flex flex-wrap gap-1">
                @foreach($rcp->security_measures as $m)
                    <span class="px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-800">{{ $m }}</span>
                @endforeach
            </div>
        </div>
        @endif

        @can('dpia.create')
        @if($rcp->dpia_required)
        <div>
            <a href="{{ route('dpias.create', ['activity' => $rcp->id]) }}"
               class="block w-full text-center px-3 py-2 bg-blue-600 text-white rounded text-sm">
                + Nowa DPIA dla tej czynności
            </a>
        </div>
        @endif
        @endcan
    </div>
</div>
@endsection
