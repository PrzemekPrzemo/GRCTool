@extends('layouts.app')
@section('content')

@php
    $statusBg = match($project->status) {
        'active'   => 'bg-emerald-100 text-emerald-800',
        'on_hold'  => 'bg-amber-100 text-amber-800',
        default    => 'bg-slate-100 text-slate-600',
    };
    $statusLabel = match($project->status) {
        'active'   => 'Aktywny',
        'on_hold'  => 'Wstrzymany',
        'archived' => 'Zarchiwizowany',
        default    => $project->status,
    };
@endphp

<div class="flex items-start justify-between mb-5">
    <div>
        <div class="flex items-center gap-3">
            <span class="font-mono text-slate-400 text-sm">{{ $project->code }}</span>
            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $statusBg }}">{{ $statusLabel }}</span>
        </div>
        <h1 class="text-2xl font-semibold mt-1">{{ $project->name }}</h1>
    </div>
    <div class="flex gap-2">
        @can('project.update')
        <a href="{{ route('projects.edit', $project) }}"
           class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm hover:bg-slate-200 transition-colors">Edytuj</a>
        @endcan
        <a href="{{ route('projects.index') }}"
           class="px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-sm hover:bg-slate-200 transition-colors">← Wróć</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Dane projektu</h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Klient</dt>
                    <dd class="mt-0.5 font-medium">
                        @if($project->client)
                            <a href="{{ route('clients.show', $project->client) }}" class="text-emerald-700 hover:underline">{{ $project->client->name }}</a>
                        @else <span class="text-slate-400">—</span> @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Jednostka biznesowa</dt>
                    <dd class="mt-0.5 font-medium">{{ $project->businessUnit?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Właściciel</dt>
                    <dd class="mt-0.5 font-medium">{{ $project->owner?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Rozpoczęcie</dt>
                    <dd class="mt-0.5">{{ $project->start_date?->format('d.m.Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Zakończenie</dt>
                    <dd class="mt-0.5 {{ $project->end_date?->isPast() && $project->status === 'active' ? 'text-red-600 font-medium' : '' }}">
                        {{ $project->end_date?->format('d.m.Y') ?? '—' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">
                Powiązane aktywa <span class="ml-2 px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full text-xs font-normal">{{ $project->assets->count() }}</span>
            </h2>
            @if($project->assets->isNotEmpty())
            <table class="w-full text-sm">
                <thead class="text-xs uppercase text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="pb-2 text-left">Kod</th><th class="pb-2 text-left">Nazwa</th>
                        <th class="pb-2 text-left">Typ</th><th class="pb-2 text-left">Krytyczność</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->assets as $asset)
                    @php $cc = match($asset->criticality) { 'Critical'=>'bg-red-100 text-red-700','High'=>'bg-orange-100 text-orange-700','Medium'=>'bg-amber-100 text-amber-700',default=>'bg-slate-100 text-slate-600' }; @endphp
                    <tr class="border-t border-slate-50 hover:bg-slate-50">
                        <td class="py-2 font-mono text-xs"><a href="{{ route('assets.show', $asset) }}" class="text-emerald-700 hover:underline">{{ $asset->code }}</a></td>
                        <td class="py-2 font-medium">{{ $asset->name }}</td>
                        <td class="py-2 text-slate-500">{{ $asset->type }}</td>
                        <td class="py-2"><span class="px-2 py-0.5 rounded text-xs {{ $cc }}">{{ $asset->criticality }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-sm text-slate-400">Brak aktywów przypisanych do tego projektu.</p>
            @endif
        </div>
    </div>
</div>
@endsection
