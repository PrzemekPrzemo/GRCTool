@extends('layouts.app')
@section('content')
@php
    $statusBg = match($policy->status) {
        'Draft'    => 'bg-yellow-100 text-yellow-800',
        'Approved' => 'bg-blue-100 text-blue-800',
        'Active'   => 'bg-emerald-100 text-emerald-800',
        'Retired'  => 'bg-slate-200 text-slate-600',
        default    => 'bg-slate-100 text-slate-600',
    };
@endphp

<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('policies.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Polityki</a>
    <h1 class="text-2xl font-semibold">{{ $policy->title }}</h1>
    <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">{{ $policy->status }}</span>
    <span class="text-xs font-mono text-slate-500">v{{ $policy->current_version }}</span>
    @can('policy.update')
    <div class="ml-auto flex gap-2">
        <a href="{{ route('policies.edit', $policy) }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm">Edytuj</a>
        @if($policy->status === 'Approved' || $policy->status === 'Draft')
        <form method="POST" action="{{ route('policies.approve', $policy) }}" onsubmit="return confirm('Zatwierdzić i aktywować politykę?')">
            @csrf
            <button class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">Zatwierdź</button>
        </form>
        @endif
    </div>
    @endcan
</div>

@if($policy->next_review_due?->isPast())
<div class="bg-amber-50 border-l-4 border-amber-400 px-4 py-2 rounded-r mb-4 text-sm text-amber-800">
    Termin przeglądu polityki minął: <strong>{{ $policy->next_review_due->format('Y-m-d') }}</strong>
</div>
@endif

{{-- Attestation CTA --}}
@if($policy->attestation_required && $policy->status === 'Active')
<div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4 flex items-center justify-between">
    <div class="text-sm text-blue-800">
        @if($userAttestation)
            ✓ Potwierdziłeś zapoznanie się z tą polityką ({{ $userAttestation->attested_at->format('Y-m-d') }})
        @else
            Wymagane jest potwierdzenie zapoznania się z polityką v{{ $policy->current_version }}
        @endif
    </div>
    @if(!$userAttestation)
    <form method="POST" action="{{ route('policies.attest', $policy) }}">
        @csrf
        <button class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm">Potwierdzam zapoznanie</button>
    </form>
    @endif
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Szczegóły polityki</h2>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <dt class="text-slate-500">Kod</dt>
                <dd class="font-mono">{{ $policy->code }}</dd>
                <dt class="text-slate-500">Kategoria</dt>
                <dd>{{ $policy->category ?? '—' }}</dd>
                <dt class="text-slate-500">Właściciel</dt>
                <dd>{{ $policy->owner?->name ?? '—' }}</dd>
                <dt class="text-slate-500">Obowiązuje od</dt>
                <dd>{{ $policy->effective_from?->format('Y-m-d') ?? '—' }}</dd>
                <dt class="text-slate-500">Termin przeglądu</dt>
                <dd class="{{ $policy->next_review_due?->isPast() ? 'text-red-700 font-semibold' : '' }}">
                    {{ $policy->next_review_due?->format('Y-m-d') ?? '—' }}
                </dd>
                <dt class="text-slate-500">Zatwierdził</dt>
                <dd>{{ $policy->approver?->name ?? '—' }}</dd>
                <dt class="text-slate-500">Data zatwierdzenia</dt>
                <dd>{{ $policy->approved_at?->format('Y-m-d') ?? '—' }}</dd>
            </dl>
            @if($policy->description)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $policy->description }}</p>
            </div>
            @endif
        </div>

        @if(!empty($policy->framework_mappings))
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold text-slate-700 text-sm mb-2">Mapowania standardów</h3>
            <div class="flex flex-wrap gap-1">
                @foreach($policy->framework_mappings as $m)
                    <span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-800">{{ $m }}</span>
                @endforeach
            </div>
        </div>
        @endif

        @if($policy->attestations->isNotEmpty())
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Potwierdzenia zapoznania ({{ $policy->attestations->count() }})</h2>
            <div class="space-y-1.5 max-h-60 overflow-y-auto">
                @foreach($policy->attestations->sortByDesc('attested_at') as $att)
                <div class="flex items-center justify-between text-xs p-1.5 rounded hover:bg-slate-50">
                    <span>{{ $att->user?->name ?? '—' }}</span>
                    <span class="text-slate-500">v{{ $att->policy_version }} · {{ $att->attested_at->format('Y-m-d H:i') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded shadow p-4 text-xs text-slate-500 space-y-1.5">
            <div>Kod: <span class="font-mono text-slate-700">{{ $policy->code }}</span></div>
            <div>Wersja: <span class="font-mono text-slate-700">v{{ $policy->current_version }}</span></div>
            <div>Właściciel: <span class="text-slate-700">{{ $policy->owner?->name ?? '—' }}</span></div>
            <div>Attestation wymagane: <span class="text-slate-700">{{ $policy->attestation_required ? 'Tak' : 'Nie' }}</span></div>
            <div>Utworzono: {{ $policy->created_at->format('Y-m-d H:i') }}</div>
        </div>
    </div>
</div>
@endsection
