@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Asset Register</h1>
    <div class="flex gap-2">
        @can('create', App\Models\Asset::class)
            <a href="{{ route('assets.import.show') }}" class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm hover:bg-slate-50">Import CSV</a>
            <a href="{{ route('assets.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm hover:bg-emerald-700">+ Nowe aktywo</a>
        @endcan
    </div>
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex gap-2 flex-wrap items-end">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs text-slate-500 mb-1">Szukaj</label>
        <input name="q" value="{{ request('q') }}" class="w-full px-2 py-1 border border-slate-300 rounded text-sm" placeholder="kod, nazwa, opis">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Typ</label>
        <select name="type" class="px-2 py-1 border border-slate-300 rounded text-sm">
            <option value="">— wszystkie —</option>
            @foreach(['server','application','repository','saas','data_store','person','vendor','network_device','ai_model'] as $t)
                <option value="{{ $t }}" @selected(request('type')===$t)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Krytyczność</label>
        <select name="criticality" class="px-2 py-1 border border-slate-300 rounded text-sm">
            <option value="">— wszystkie —</option>
            @foreach(['Critical','High','Medium','Low'] as $c)
                <option value="{{ $c }}" @selected(request('criticality')===$c)>{{ $c }}</option>
            @endforeach
        </select>
    </div>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Nazwa</th>
                <th class="px-3 py-2">Typ</th>
                <th class="px-3 py-2">Krytyczność</th>
                <th class="px-3 py-2">CIA</th>
                <th class="px-3 py-2">Klient / BU</th>
                <th class="px-3 py-2">Owner</th>
                <th class="px-3 py-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $a)
                <tr class="border-t border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2 font-mono text-xs"><a href="{{ route('assets.show', $a) }}" class="text-emerald-700 hover:underline">{{ $a->code }}</a></td>
                    <td class="px-3 py-2">{{ $a->name }}</td>
                    <td class="px-3 py-2 text-slate-600">{{ $a->type }}</td>
                    <td class="px-3 py-2">
                        @php $bg = ['Critical'=>'bg-red-100 text-red-800','High'=>'bg-orange-100 text-orange-800','Medium'=>'bg-amber-100 text-amber-800','Low'=>'bg-emerald-100 text-emerald-800'][$a->criticality] ?? 'bg-slate-100'; @endphp
                        <span class="px-2 py-0.5 rounded text-xs {{ $bg }}">{{ $a->criticality }}</span>
                    </td>
                    <td class="px-3 py-2 text-xs text-slate-600">C{{ $a->confidentiality_impact }} I{{ $a->integrity_impact }} A{{ $a->availability_impact }}</td>
                    <td class="px-3 py-2 text-slate-600">{{ $a->client?->name ?? '—' }} / {{ $a->businessUnit?->code ?? '—' }}</td>
                    <td class="px-3 py-2 text-slate-600">{{ $a->owner?->name ?? '—' }}</td>
                    <td class="px-3 py-2"><span class="px-2 py-0.5 rounded bg-slate-100 text-xs">{{ $a->lifecycle_status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-3 py-6 text-center text-slate-500">Brak aktywów. <a href="{{ route('assets.create') }}" class="text-emerald-600 hover:underline">Dodaj pierwsze</a> lub zaimportuj CSV.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="mt-3">{{ $assets->links() }}</div>
@endsection
