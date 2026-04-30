@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Biblioteka scenariuszy ryzyka</h1>
        <p class="text-slate-500 text-sm">{{ $scenarios->count() }} scenariuszy dostosowanych do software house. Kliknij <em>Adopt</em>, aby utworzyć ryzyko.</p>
    </div>
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex gap-2 flex-wrap">
    <input name="q" value="{{ request('q') }}" placeholder="Szukaj..." class="flex-1 px-3 py-1.5 border border-slate-300 rounded text-sm">
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Szukaj</button>
</form>

@foreach($byCategory as $category => $items)
<section class="mb-6">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-2">{{ $category }} ({{ $items->count() }})</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($items as $s)
        <div class="bg-white rounded shadow p-4 flex flex-col">
            <div class="text-xs font-mono text-slate-500">{{ $s->code }}</div>
            <h3 class="font-medium mt-1">{{ $s->name }}</h3>
            <p class="text-xs text-slate-600 mt-2 line-clamp-3">{{ $s->description }}</p>
            <div class="mt-2 flex flex-wrap gap-1">
                @foreach($s->default_threat_actors ?? [] as $ta)
                    <span class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded">{{ $ta }}</span>
                @endforeach
            </div>
            <div class="mt-3 flex justify-between items-center">
                <a href="{{ route('scenarios.show', $s) }}" class="text-xs text-slate-600 hover:underline">Szczegóły</a>
                @can('create', App\Models\Risk::class)
                <form method="POST" action="{{ route('scenarios.adopt', $s) }}">@csrf
                    <button class="px-3 py-1 bg-emerald-600 text-white rounded text-xs hover:bg-emerald-700">Adopt →</button>
                </form>
                @endcan
            </div>
        </div>
        @endforeach
    </div>
</section>
@endforeach
@endsection
