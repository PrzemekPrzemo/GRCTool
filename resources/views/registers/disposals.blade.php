@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">Rejestr utylizacji</h1>
        <p class="text-sm text-slate-500 mt-0.5">Sanityzacja i utylizacja nośników / urządzeń (PROC-209)</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-2.5">Kod</th>
                <th class="text-left px-4 py-2.5">Urządzenie</th>
                <th class="text-left px-4 py-2.5">Klasyfikacja</th>
                <th class="text-left px-4 py-2.5">Metoda sanityzacji</th>
                <th class="text-left px-4 py-2.5">Data</th>
                <th class="text-left px-4 py-2.5">Metoda utylizacji</th>
                <th class="text-left px-4 py-2.5">Potwierdził</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($disposals as $d)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $d->code }}</td>
                <td class="px-4 py-3 text-slate-800">{{ $d->device_type }} <span class="text-xs text-slate-400">({{ $d->asset_ref }})</span></td>
                <td class="px-4 py-3 text-xs">{{ $d->data_classification }}</td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $d->sanitisation_method }}</td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $d->sanitised_at?->format('Y-m-d') ?? '—' }}</td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $d->disposal_method }}</td>
                <td class="px-4 py-3 text-xs">{{ $d->confirmed_by }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endsection
