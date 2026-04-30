@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <div class="text-xs text-slate-500 font-mono">{{ $asset->code }}</div>
        <h1 class="text-2xl font-semibold">{{ $asset->name }}</h1>
    </div>
    <div class="flex gap-2">
        @can('update', $asset)
            <a href="{{ route('assets.edit', $asset) }}" class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm">Edytuj</a>
        @endcan
        @can('delete', $asset)
            <form method="POST" action="{{ route('assets.destroy', $asset) }}" onsubmit="return confirm('Archiwizować aktywo?')">
                @csrf @method('DELETE')
                <button class="px-3 py-1.5 border border-red-300 bg-red-50 text-red-700 rounded text-sm">Archiwizuj</button>
            </form>
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-2 bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-3">Podstawowe</h2>
        <dl class="grid grid-cols-2 gap-y-2 text-sm">
            <dt class="text-slate-500">Typ</dt><dd>{{ $asset->type }}</dd>
            <dt class="text-slate-500">Środowisko</dt><dd>{{ $asset->environment }}</dd>
            <dt class="text-slate-500">Krytyczność</dt><dd class="font-semibold">{{ $asset->criticality }}</dd>
            <dt class="text-slate-500">CIA</dt><dd>C{{ $asset->confidentiality_impact }} · I{{ $asset->integrity_impact }} · A{{ $asset->availability_impact }}</dd>
            <dt class="text-slate-500">Klasyfikacja</dt><dd>{{ $asset->data_classification }}</dd>
            <dt class="text-slate-500">Lifecycle</dt><dd>{{ $asset->lifecycle_status }}</dd>
            <dt class="text-slate-500">Owner</dt><dd>{{ $asset->owner?->name ?? '—' }}</dd>
            <dt class="text-slate-500">Custodian</dt><dd>{{ $asset->custodian?->name ?? '—' }}</dd>
            <dt class="text-slate-500">Business Unit</dt><dd>{{ $asset->businessUnit?->code ?? '—' }}</dd>
            <dt class="text-slate-500">Klient</dt><dd>{{ $asset->client?->name ?? '—' }}</dd>
        </dl>
        @if($asset->description)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <h3 class="font-medium text-sm mb-1">Opis</h3>
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $asset->description }}</p>
            </div>
        @endif
    </div>
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-3">Powiązane podatności</h2>
        @forelse($asset->vulnerabilities as $v)
            <div class="text-sm border-b border-slate-100 py-1">
                <a href="{{ route('vulnerabilities.show', $v) }}" class="text-emerald-700 hover:underline">{{ $v->cve_id ?? $v->code }}</a>
                <span class="text-slate-500"> · {{ $v->severity }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-500">Brak.</p>
        @endforelse
    </div>
</div>
@endsection
