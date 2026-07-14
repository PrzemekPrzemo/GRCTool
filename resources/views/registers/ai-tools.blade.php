@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">Rejestr narzędzi AI</h1>
        <p class="text-sm text-slate-500 mt-0.5">Zatwierdzone / warunkowe / zabronione narzędzia AI (POL-013)</p>
    </div>
</div>

@php
    $statusColor = fn($s) => match(true) {
        str_contains($s, 'Unrestricted') => 'bg-emerald-100 text-emerald-800',
        str_contains($s, 'Conditional') => 'bg-amber-100 text-amber-800',
        str_contains($s, 'Prohibited') => 'bg-red-100 text-red-800',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-2.5">Narzędzie</th>
                <th class="text-left px-4 py-2.5">Dostawca</th>
                <th class="text-left px-4 py-2.5">Status</th>
                <th class="text-left px-4 py-2.5">DPA</th>
                <th class="text-left px-4 py-2.5">Dozwolone dane</th>
                <th class="text-left px-4 py-2.5">Zabronione dane</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($tools as $tool)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-medium text-slate-900">{{ $tool->name }}</td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $tool->vendor }}</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs {{ $statusColor($tool->approval_status) }}">{{ $tool->approval_status }}</span></td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $tool->dpa_status ?? '—' }}</td>
                <td class="px-4 py-3 text-xs text-slate-600 max-w-xs">{{ $tool->permitted_data }}</td>
                <td class="px-4 py-3 text-xs text-red-700 max-w-xs">{{ $tool->prohibited_data }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endsection
