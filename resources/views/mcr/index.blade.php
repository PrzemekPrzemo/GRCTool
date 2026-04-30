@extends('layouts.app')
@section('content')
<div class="flex justify-between items-center mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Minimum Control Requirements (MCR)</h1>
        <p class="text-slate-500 text-sm">Wymagania bezpieczeństwa stawiane dostawcom · {{ $mcrs->total() }} wymagań</p>
    </div>
    <a href="{{ route('mcr.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowy MCR</a>
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex gap-2">
    <select name="category" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Kategoria —</option>
        @foreach($categories as $c)<option @selected(request('category')===$c)>{{ $c }}</option>@endforeach
    </select>
    <select name="severity" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Severity —</option>
        @foreach(['Mandatory','Highly_Recommended','Recommended'] as $s)<option @selected(request('severity')===$s)>{{ $s }}</option>@endforeach
    </select>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th><th class="px-3 py-2">Nazwa</th>
                <th class="px-3 py-2">Kategoria</th><th class="px-3 py-2">Severity</th>
                <th class="px-3 py-2">Tier dostawcy</th><th class="px-3 py-2">Internal control</th>
                <th class="px-3 py-2">Frameworks</th>
            </tr>
        </thead>
        <tbody>
        @foreach($mcrs as $mcr)
        <tr class="border-t border-slate-100 hover:bg-slate-50">
            <td class="px-3 py-2 font-mono text-xs"><a href="{{ route('mcr.show', $mcr) }}" class="text-emerald-700 hover:underline">{{ $mcr->code }}</a></td>
            <td class="px-3 py-2">{{ $mcr->name }}</td>
            <td class="px-3 py-2 text-xs">{{ $mcr->category }}</td>
            <td class="px-3 py-2">
                @php $bg = ['Mandatory'=>'bg-red-100 text-red-800','Highly_Recommended'=>'bg-amber-100 text-amber-800','Recommended'=>'bg-slate-100'][$mcr->severity]; @endphp
                <span class="text-xs px-2 py-0.5 rounded {{ $bg }}">{{ str_replace('_', ' ', $mcr->severity) }}</span>
            </td>
            <td class="px-3 py-2 text-xs">{{ $mcr->vendor_tier_applicability ? implode(', ', $mcr->vendor_tier_applicability) : 'wszystkie' }}</td>
            <td class="px-3 py-2 text-xs">{{ $mcr->linkedControl?->code ?? '—' }}</td>
            <td class="px-3 py-2 text-xs font-mono">@foreach(array_slice($mcr->framework_mappings ?? [], 0, 2) as $f){{ $f }}@if(!$loop->last); @endif @endforeach</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $mcrs->links() }}</div>
@endsection
