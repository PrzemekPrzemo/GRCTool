@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Plany BCP / DR</h1>
    <a href="{{ route('bcp.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowy plan</a>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Tytuł</th>
                <th class="px-3 py-2">Typ</th>
                <th class="px-3 py-2">RTO</th>
                <th class="px-3 py-2">RPO</th>
                <th class="px-3 py-2">Ostatni test</th>
                <th class="px-3 py-2">Wynik testu</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Właściciel</th>
                <th class="px-3 py-2">Akcje</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plans as $plan)
            @php
                $latestTest = $plan->latestTest();
                $typeBg = ['bcp'=>'bg-blue-100 text-blue-800','dr'=>'bg-orange-100 text-orange-800','coop'=>'bg-purple-100 text-purple-800','crisis'=>'bg-red-100 text-red-800'][$plan->plan_type] ?? 'bg-slate-100';
                $typeLabel = \App\Models\BcpPlan::TYPE_LABELS[$plan->plan_type] ?? $plan->plan_type;
                $statusBg = ['draft'=>'bg-slate-100 text-slate-600','active'=>'bg-emerald-100 text-emerald-700','under_review'=>'bg-amber-100 text-amber-700','retired'=>'bg-slate-200 text-slate-500'][$plan->status] ?? 'bg-slate-100';
                $statusLabel = \App\Models\BcpPlan::STATUS_LABELS[$plan->status] ?? $plan->status;
                $resultBg = $latestTest ? ['pass'=>'bg-emerald-100 text-emerald-700','pass_with_gaps'=>'bg-amber-100 text-amber-700','fail'=>'bg-red-100 text-red-700'][$latestTest->result] ?? '' : '';
                $resultLabel = $latestTest ? ['pass'=>'Zaliczony','pass_with_gaps'=>'Częściowy','fail'=>'Niezaliczony'][$latestTest->result] ?? $latestTest->result : '—';
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('bcp.show', $plan) }}" class="text-emerald-700 hover:underline">{{ $plan->code }}</a>
                </td>
                <td class="px-3 py-2 font-medium">
                    <a href="{{ route('bcp.show', $plan) }}" class="hover:underline">{{ $plan->title }}</a>
                </td>
                <td class="px-3 py-2">
                    <span class="px-1.5 py-0.5 rounded text-xs {{ $typeBg }}">{{ $typeLabel }}</span>
                </td>
                <td class="px-3 py-2 text-xs">{{ $plan->rtoLabel() }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $plan->rpo_minutes !== null ? $plan->rpo_minutes.'min' : '—' }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $latestTest?->tested_at->format('Y-m-d') ?? '—' }}</td>
                <td class="px-3 py-2">
                    @if($latestTest)
                    <span class="px-1.5 py-0.5 rounded text-xs {{ $resultBg }}">{{ $resultLabel }}</span>
                    @else
                    <span class="text-xs text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-3 py-2">
                    <span class="px-1.5 py-0.5 rounded text-xs {{ $statusBg }}">{{ $statusLabel }}</span>
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $plan->owner?->name ?? '—' }}</td>
                <td class="px-3 py-2">
                    <a href="{{ route('bcp.edit', $plan) }}" class="text-xs text-slate-600 hover:underline">Edytuj</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" class="px-3 py-8 text-center text-slate-400">Brak planów BCP/DR</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $plans->links() }}</div>
@endsection
