@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">Macierz RACI</h1>
        <p class="text-sm text-slate-500 mt-0.5">Odpowiedzialność za działania bezpieczeństwa wg ról (R=Responsible, A=Accountable, C=Consulted, I=Informed)</p>
    </div>
</div>

@php
    $badge = fn($v) => match($v) {
        'R' => 'bg-blue-100 text-blue-800',
        'A' => 'bg-emerald-100 text-emerald-800',
        'C' => 'bg-amber-100 text-amber-800',
        'I' => 'bg-slate-100 text-slate-500',
        default => 'bg-slate-50 text-slate-300',
    };
@endphp

@foreach($entries as $domain => $items)
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
    <div class="bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-600 uppercase">{{ $domain }}</div>
    <div class="overflow-x-auto">
    <table class="w-full text-xs">
        <tbody class="divide-y divide-slate-100">
            @foreach($items as $entry)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-2 text-slate-800 w-64">{{ $entry->activity }}</td>
                <td class="px-4 py-2">
                    <div class="flex flex-wrap gap-1">
                        @foreach($entry->assignments as $role => $value)
                            <span class="px-1.5 py-0.5 rounded {{ $badge($value) }}" title="{{ $role }}">{{ \Illuminate\Support\Str::limit($role, 12, '') }}: {{ $value }}</span>
                        @endforeach
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endforeach
@endsection
