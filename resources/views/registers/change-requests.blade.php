@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">Rejestr zmian</h1>
        <p class="text-sm text-slate-500 mt-0.5">Change requests (PROC-208 Change Management)</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-2.5">Kod</th>
                <th class="text-left px-4 py-2.5">Typ</th>
                <th class="text-left px-4 py-2.5">Tytuł</th>
                <th class="text-left px-4 py-2.5">Ryzyko</th>
                <th class="text-left px-4 py-2.5">Wdrożenie</th>
                <th class="text-left px-4 py-2.5">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($changes as $cr)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $cr->code }}</td>
                <td class="px-4 py-3 text-xs">{{ $cr->change_type }}</td>
                <td class="px-4 py-3 text-slate-800">{{ $cr->title }}</td>
                <td class="px-4 py-3 text-xs">{{ $cr->risk_level }}</td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $cr->implementation_date?->format('Y-m-d') ?? '—' }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded text-xs {{ $cr->status === 'Closed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $cr->status }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
