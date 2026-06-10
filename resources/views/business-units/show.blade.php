@extends('layouts.app')
@section('content')

<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('business-units.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Jednostki biznesowe</a>
    <h1 class="text-2xl font-semibold">{{ $unit->name }}</h1>
    @if($unit->is_active)
        <span class="px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-800">Aktywna</span>
    @else
        <span class="px-2 py-0.5 rounded text-xs bg-slate-200 text-slate-600">Nieaktywna</span>
    @endif
    @can('business_unit.update')
        <a href="{{ route('business-units.edit', $unit) }}" class="ml-auto px-3 py-1.5 border border-slate-300 rounded text-sm hover:bg-slate-50">Edytuj</a>
    @endcan
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        {{-- Dane podstawowe --}}
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Dane podstawowe</h2>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <dt class="text-slate-500">Kod</dt>
                <dd class="font-mono">{{ $unit->code }}</dd>
                <dt class="text-slate-500">Nazwa</dt>
                <dd class="font-medium">{{ $unit->name }}</dd>
                <dt class="text-slate-500">Nadrzędna jednostka</dt>
                <dd>
                    @if($unit->parent)
                        <a href="{{ route('business-units.show', $unit->parent) }}" class="text-emerald-700 hover:underline">{{ $unit->parent->name }}</a>
                    @else
                        <span class="text-slate-400">Jednostka główna</span>
                    @endif
                </dd>
                <dt class="text-slate-500">Właściciel</dt>
                <dd>{{ $unit->owner?->name ?? '—' }}</dd>
            </dl>
            @if($unit->description)
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <h3 class="font-medium text-sm mb-1">Opis</h3>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $unit->description }}</p>
                </div>
            @endif
        </div>

        {{-- Podjednostki --}}
        @if($unit->children->isNotEmpty())
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Podjednostki ({{ $unit->children->count() }})</h2>
            <div class="space-y-1.5">
                @foreach($unit->children as $child)
                <div class="flex items-center justify-between p-2 rounded border border-slate-100 hover:bg-slate-50">
                    <div>
                        <a href="{{ route('business-units.show', $child) }}" class="text-sm text-emerald-700 hover:underline font-medium">{{ $child->name }}</a>
                        <span class="text-xs text-slate-500 ml-2 font-mono">{{ $child->code }}</span>
                    </div>
                    @if($child->is_active)
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    @else
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Projekty --}}
        @if($unit->projects->isNotEmpty())
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Przypisane projekty</h2>
            <table class="w-full text-sm">
                <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Kod</th>
                        <th class="px-3 py-2 text-left">Nazwa</th>
                        <th class="px-3 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unit->projects as $project)
                    @php
                        $statusBg = match($project->status) {
                            'active'    => 'bg-emerald-100 text-emerald-800',
                            'on_hold'   => 'bg-amber-100 text-amber-800',
                            default     => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-3 py-2 font-mono text-xs">
                            <a href="{{ route('projects.show', $project) }}" class="text-emerald-700 hover:underline">{{ $project->code }}</a>
                        </td>
                        <td class="px-3 py-2">{{ $project->name }}</td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">{{ $project->status }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>

    <div class="space-y-4">

        {{-- Użytkownicy --}}
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold text-slate-700 text-sm mb-2">Użytkownicy ({{ $unit->users->count() }})</h3>
            @if($unit->users->isNotEmpty())
            <div class="space-y-1.5">
                @foreach($unit->users->take(10) as $user)
                <div class="text-xs text-slate-700">
                    <span class="font-medium">{{ $user->name }}</span>
                    <span class="text-slate-500"> — {{ $user->email }}</span>
                </div>
                @endforeach
                @if($unit->users->count() > 10)
                    <p class="text-xs text-slate-400">… i {{ $unit->users->count() - 10 }} więcej</p>
                @endif
            </div>
            @else
            <p class="text-xs text-slate-500">Brak przypisanych użytkowników.</p>
            @endif
        </div>

        <div class="bg-white rounded shadow p-4 text-xs text-slate-500 space-y-1.5">
            <div>Kod: <span class="font-mono text-slate-700">{{ $unit->code }}</span></div>
            <div>Dodano: {{ $unit->created_at->format('Y-m-d H:i') }}</div>
            <div>Zaktualizowano: {{ $unit->updated_at->format('Y-m-d H:i') }}</div>
        </div>
    </div>
</div>
@endsection
