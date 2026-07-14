@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Ocena stosowania NIS2</h1>
        <p class="text-sm text-slate-500 mt-1">Sprawdź, czy organizacja podlega pod Dyrektywę NIS2 (Dyrektywa 2022/2555)</p>
    </div>
    @can('nis2.create')
        <a href="{{ route('nis2.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowa ocena</a>
    @endcan
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex gap-2">
    <select name="result" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Wynik —</option>
        <option value="not_subject"      @selected(request('result')==='not_subject')>Nie podlega</option>
        <option value="important_entity" @selected(request('result')==='important_entity')>Podmiot Ważny</option>
        <option value="essential_entity" @selected(request('result')==='essential_entity')>Podmiot Kluczowy</option>
    </select>
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Status —</option>
        <option value="draft" @selected(request('status')==='draft')>Roboczy</option>
        <option value="final" @selected(request('status')==='final')>Sfinalizowany</option>
    </select>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Organizacja</th>
                <th class="px-3 py-2">Data oceny</th>
                <th class="px-3 py-2">Wynik NIS2</th>
                <th class="px-3 py-2">Rozmiar</th>
                <th class="px-3 py-2">Załącznik</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Przeprowadzono przez</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assessments as $a)
            @php
                $resultBg = match($a->result) {
                    'not_subject'      => 'bg-emerald-100 text-emerald-800',
                    'important_entity' => 'bg-amber-100 text-amber-800',
                    'essential_entity' => 'bg-red-100 text-red-800',
                    default            => 'bg-slate-100 text-slate-600',
                };
                $resultLabel = match($a->result) {
                    'not_subject'      => 'Nie podlega',
                    'important_entity' => 'Podmiot Ważny',
                    'essential_entity' => 'Podmiot Kluczowy',
                    default            => $a->result ?? '—',
                };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('nis2.show', $a) }}" class="text-emerald-700 hover:underline">{{ $a->code }}</a>
                </td>
                <td class="px-3 py-2">{{ $a->organization_name }}</td>
                <td class="px-3 py-2 text-slate-600">{{ $a->assessment_date?->format('Y-m-d') }}</td>
                <td class="px-3 py-2">
                    @if($a->result)
                        <span class="px-2 py-0.5 rounded text-xs {{ $resultBg }}">{{ $resultLabel }}</span>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $a->entity_size ?? '—' }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $a->annexLabel() }}</td>
                <td class="px-3 py-2">
                    <span class="px-2 py-0.5 rounded text-xs {{ $a->isFinal() ? 'bg-slate-800 text-white' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $a->isFinal() ? 'Sfinalizowany' : 'Roboczy' }}
                    </span>
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $a->conductedBy?->name ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-3 py-8 text-center text-slate-500">
                    Brak ocen NIS2.
                    @can('nis2.create')
                        <a href="{{ route('nis2.create') }}" class="text-emerald-600 hover:underline ml-1">Przeprowadź pierwszą ocenę</a>.
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $assessments->links() }}</div>
@endsection
