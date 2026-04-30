@extends('layouts.app')
@section('content')
<div class="flex justify-between items-start mb-4">
    <div>
        <div class="text-xs font-mono text-slate-500">{{ $mcr->code }}</div>
        <h1 class="text-2xl font-semibold">{{ $mcr->name }}</h1>
    </div>
    <a href="{{ route('mcr.edit', $mcr) }}" class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm">Edytuj</a>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-2">Profil</h2>
        <dl class="grid grid-cols-2 gap-y-2 text-sm">
            <dt class="text-slate-500">Kategoria</dt><dd>{{ $mcr->category }}{{ $mcr->subcategory ? ' / '.$mcr->subcategory : '' }}</dd>
            <dt class="text-slate-500">Severity</dt><dd>{{ str_replace('_', ' ', $mcr->severity) }}</dd>
            <dt class="text-slate-500">Tier dostawcy</dt><dd>{{ $mcr->vendor_tier_applicability ? implode(', ', $mcr->vendor_tier_applicability) : 'wszystkie' }}</dd>
            <dt class="text-slate-500">Wymaga evidence</dt><dd>{{ $mcr->requires_evidence ? 'Tak' : 'Nie' }}</dd>
            <dt class="text-slate-500">Internal control</dt><dd>{{ $mcr->linkedControl?->code ?? '—' }}</dd>
            <dt class="text-slate-500">Aktywny</dt><dd>{{ $mcr->is_active ? 'Tak' : 'Nie' }}</dd>
        </dl>
        <div class="mt-3 pt-3 border-t border-slate-100">
            <h3 class="font-medium text-sm mb-1">Opis (internal)</h3>
            <p class="text-sm whitespace-pre-line">{{ $mcr->description }}</p>
        </div>
        @if($mcr->vendor_facing_text)
        <div class="mt-3 pt-3 border-t border-slate-100">
            <h3 class="font-medium text-sm mb-1">Pytanie dla dostawcy</h3>
            <p class="text-sm whitespace-pre-line text-slate-700">{{ $mcr->vendor_facing_text }}</p>
        </div>
        @endif
        @if($mcr->evaluation_criteria)
        <div class="mt-3 pt-3 border-t border-slate-100">
            <h3 class="font-medium text-sm mb-1">Kryteria oceny</h3>
            <p class="text-sm">{{ $mcr->evaluation_criteria }}</p>
        </div>
        @endif
    </div>

    <div class="bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-2">Mapowania</h2>
        <div class="text-xs">
            <div class="text-slate-500 mb-1">Frameworks</div>
            <ul class="font-mono space-y-0.5">@foreach($mcr->framework_mappings ?? [] as $f)<li>· {{ $f }}</li>@endforeach</ul>
        </div>
        @if($mcr->expected_evidence_types)
        <div class="mt-3 pt-3 border-t border-slate-100 text-xs">
            <div class="text-slate-500 mb-1">Oczekiwane typy evidence</div>
            <ul class="space-y-0.5">@foreach($mcr->expected_evidence_types as $e)<li>· {{ $e }}</li>@endforeach</ul>
        </div>
        @endif
    </div>
</div>
@endsection
