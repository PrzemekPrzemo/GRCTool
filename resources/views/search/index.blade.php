@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold mb-4">Wyniki wyszukiwania</h1>
    <form method="GET" action="{{ route('search.index') }}" class="flex gap-2 items-center">
        <input
            name="q"
            value="{{ $q }}"
            placeholder="Szukaj..."
            autofocus
            class="border border-slate-300 rounded px-3 py-2 w-full max-w-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
        >
        <button
            type="submit"
            class="px-4 py-2 bg-emerald-600 text-white rounded text-sm hover:bg-emerald-700 transition-colors whitespace-nowrap"
        >
            Szukaj
        </button>
    </form>
</div>

@if($q && strlen($q) >= 2 && empty(array_filter($results, fn($c) => $c->isNotEmpty())))
    <p class="text-slate-500 text-sm">Brak wyników dla „{{ $q }}"</p>
@elseif(!$q || strlen($q) < 2)
    <p class="text-slate-400 text-sm">Wpisz co najmniej 2 znaki, aby wyszukać.</p>
@endif

@php
$sections = [
    'risks'           => 'Ryzyka',
    'controls'        => 'Kontrolki',
    'vulnerabilities' => 'Podatności',
    'incidents'       => 'Incydenty',
    'findings'        => 'Ustalenia',
    'policies'        => 'Polityki',
    'evidence'        => 'Dowody',
];

$routes = [
    'risks'           => 'risks.show',
    'controls'        => 'controls.show',
    'vulnerabilities' => 'vulnerabilities.show',
    'incidents'       => 'incidents.show',
    'findings'        => 'findings.show',
    'policies'        => 'policies.show',
    'evidence'        => 'evidence.show',
];
@endphp

@foreach($sections as $key => $label)
    @if(!empty($results[$key]) && $results[$key]->isNotEmpty())
    <div class="mb-6">
        <h2 class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-2">
            {{ $label }}
            <span class="ml-1 text-slate-400 font-normal normal-case tracking-normal">({{ $results[$key]->count() }})</span>
        </h2>
        <div class="bg-white rounded shadow divide-y divide-slate-100">
            @foreach($results[$key] as $item)
            <div class="px-4 py-3">
                <a href="{{ route($routes[$key], $item) }}" class="font-medium text-emerald-700 hover:underline">
                    @if(!empty($item->code))
                        <span class="text-slate-400 text-xs mr-1">{{ $item->code }}</span>
                    @endif
                    {{ $item->title ?? $item->name ?? '—' }}
                </a>
                @if(!empty($item->description))
                <p class="text-sm text-slate-500 mt-0.5">
                    {{ Str::limit($item->description, 120) }}
                </p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
@endforeach

@endsection
