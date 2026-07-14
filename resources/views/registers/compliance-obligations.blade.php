@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">Rejestr obowiązków compliance</h1>
        <p class="text-sm text-slate-500 mt-0.5">Obowiązki regulacyjne i prawne — GDPR, NIS2, DORA, KSA PDPL/SAMA, prawo polskie, ISO 27001 (REG-014)</p>
    </div>
</div>

@php
$statusBadge = [
    'compliant' => 'bg-green-100 text-green-700',
    'monitor' => 'bg-amber-100 text-amber-700',
    'not_applicable' => 'bg-slate-100 text-slate-500',
];
$statusLabel = [
    'compliant' => 'Compliant',
    'monitor' => 'Monitor',
    'not_applicable' => 'N/A',
];
@endphp

@foreach($obligations as $category => $items)
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
    <div class="bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-600 uppercase">{{ $category }}</div>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <tbody class="divide-y divide-slate-100">
            @foreach($items as $obligation)
            <tr class="hover:bg-slate-50 align-top">
                <td class="px-4 py-2 font-mono text-xs text-slate-500 w-14">{{ $obligation->ref }}</td>
                <td class="px-4 py-2 text-xs text-slate-600 w-40">{{ $obligation->regulation }}</td>
                <td class="px-4 py-2 text-slate-800">
                    {{ $obligation->obligation }}
                    @if($obligation->applies_when)
                        <div class="text-xs text-slate-500 mt-0.5">{{ $obligation->applies_when }}</div>
                    @endif
                </td>
                <td class="px-4 py-2 text-xs text-blue-700 w-40">{{ $obligation->related_documents }}</td>
                <td class="px-4 py-2 text-xs text-slate-600 w-28">{{ $obligation->owner?->name ?? '—' }}</td>
                <td class="px-4 py-2 w-24">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadge[$obligation->status] ?? 'bg-slate-100 text-slate-500' }}">
                        {{ $statusLabel[$obligation->status] ?? $obligation->status }}
                    </span>
                </td>
                <td class="px-4 py-2 text-xs text-slate-500 w-28">{{ optional($obligation->next_review_at)->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endforeach
@endsection
