@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Polityki i procedury</h1>
        <p class="text-sm text-slate-500 mt-1">Rejestr dokumentów polityk bezpieczeństwa i ochrony danych</p>
    </div>
    @can('policy.create')
        <div class="flex gap-2">
            <a href="{{ route('policies.import.show') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm">Importuj CSV</a>
            <a href="{{ route('policies.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowa polityka</a>
        </div>
    @endcan
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex flex-wrap gap-2">
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Status —</option>
        @foreach(['Draft','Approved','Active','Retired'] as $s)
            <option @selected(request('status')===$s)>{{ $s }}</option>
        @endforeach
    </select>
    <select name="category" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Kategoria —</option>
        @foreach($categories as $cat)
            <option @selected(request('category')===$cat)>{{ $cat }}</option>
        @endforeach
    </select>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
    @if(request()->anyFilled(['status','category']))
        <a href="{{ route('policies.index') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600">Wyczyść</a>
    @endif
</form>

@can('policy.update')
<form method="GET" action="{{ route('policies.bulk-edit') }}" id="bulk-form">
@endcan
<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                @can('policy.update')
                <th class="px-3 py-2 w-8">
                    <input type="checkbox" onclick="document.querySelectorAll('.policy-check').forEach(c=>c.checked=this.checked)">
                </th>
                @endcan
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Tytuł</th>
                <th class="px-3 py-2">Kategoria</th>
                <th class="px-3 py-2">Wersja</th>
                <th class="px-3 py-2">Obowiązuje od</th>
                <th class="px-3 py-2">Przegląd</th>
                <th class="px-3 py-2">Właściciel</th>
                <th class="px-3 py-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($policies as $p)
            @php
                $statusBg = match($p->status) {
                    'Draft'    => 'bg-yellow-100 text-yellow-800',
                    'Approved' => 'bg-blue-100 text-blue-800',
                    'Active'   => 'bg-emerald-100 text-emerald-800',
                    'Retired'  => 'bg-slate-200 text-slate-600',
                    default    => 'bg-slate-100 text-slate-600',
                };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                @can('policy.update')
                <td class="px-3 py-2">
                    <input type="checkbox" name="ids[]" value="{{ $p->id }}" class="policy-check">
                </td>
                @endcan
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('policies.show', $p) }}" class="text-emerald-700 hover:underline">{{ $p->code }}</a>
                </td>
                <td class="px-3 py-2 font-medium">{{ $p->title }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $p->category ?? '—' }}</td>
                <td class="px-3 py-2 text-xs font-mono">v{{ $p->current_version }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $p->effective_from?->format('Y-m-d') ?? '—' }}</td>
                <td class="px-3 py-2 text-xs {{ $p->next_review_due?->isPast() ? 'text-red-700 font-semibold' : 'text-slate-600' }}">
                    {{ $p->next_review_due?->format('Y-m-d') ?? '—' }}
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $p->owner?->name ?? '—' }}</td>
                <td class="px-3 py-2">
                    <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">{{ $p->status }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-3 py-8 text-center text-slate-500">
                    Brak polityk.
                    @can('policy.create')
                        <a href="{{ route('policies.create') }}" class="text-emerald-600 hover:underline ml-1">Dodaj pierwszą politykę</a>.
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@can('policy.update')
    @if($policies->isNotEmpty())
    <div class="mt-3">
        <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Masowa aktualizacja zaznaczonych</button>
    </div>
    @endif
</form>
@endcan
<div class="mt-3">{{ $policies->links() }}</div>
@endsection
