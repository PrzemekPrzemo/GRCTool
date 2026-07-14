@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-5">
    <h1 class="text-2xl font-semibold">Plany leczenia ryzyk</h1>
    <a href="{{ route('risks.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 transition-colors">+ Nowe ryzyko</a>
</div>

{{-- Stat cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wide">Plany aktywne</div>
        <div class="text-3xl font-bold mt-1 text-slate-700">{{ $totalStats['plans_active'] }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border {{ $totalStats['actions_overdue'] > 0 ? 'border-red-300 bg-red-50' : 'border-slate-200' }} p-4">
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wide">Akcje po terminie</div>
        <div class="text-3xl font-bold mt-1 {{ $totalStats['actions_overdue'] > 0 ? 'text-red-700' : 'text-slate-700' }}">{{ $totalStats['actions_overdue'] }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wide">W toku</div>
        <div class="text-3xl font-bold mt-1 text-blue-600">{{ $totalStats['actions_in_progress'] }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wide">Ukończone</div>
        <div class="text-3xl font-bold mt-1 text-emerald-600">{{ $totalStats['actions_completed'] }}</div>
    </div>
</div>

{{-- Overdue actions alert --}}
@if($overdueActions->isNotEmpty())
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5">
    <h2 class="font-semibold text-red-800 mb-3 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        Akcje po terminie ({{ $overdueActions->count() }})
    </h2>
    <div class="space-y-1.5">
        @foreach($overdueActions as $a)
        <div class="flex items-center gap-3 bg-white border border-red-100 rounded-lg px-3 py-2 text-sm">
            <a href="{{ route('risk-treatment-plans.show', $a->plan) }}" class="font-medium text-red-800 hover:underline truncate flex-1">{{ $a->title }}</a>
            <span class="text-xs text-slate-500 flex-shrink-0">{{ $a->plan->risk->code ?? '' }} – {{ $a->plan->risk->title ?? '' }}</span>
            <span class="text-xs text-slate-500 flex-shrink-0">{{ $a->owner?->name ?? '—' }}</span>
            <span class="text-xs font-semibold text-red-700 flex-shrink-0">+{{ $a->daysOverdue() }}d</span>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Filters --}}
<form method="GET" class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 mb-4 flex gap-2">
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm">
        <option value="">— Status planu —</option>
        @foreach(['Draft','Approved','In Progress','Completed','On Hold','Cancelled','Rejected'] as $s)
            <option @selected(request('status') === $s)>{{ $s }}</option>
        @endforeach
    </select>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="overdue_only" value="1" @checked(request('overdue_only'))> Tylko z zaległościami</label>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-sm">Filtruj</button>
</form>

{{-- Plans table --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 border-b border-slate-200">
            <tr>
                <th class="px-3 py-2.5">Ryzyko</th>
                <th class="px-3 py-2.5">Cel score</th>
                <th class="px-3 py-2.5">Deadline</th>
                <th class="px-3 py-2.5">Postęp</th>
                <th class="px-3 py-2.5">Akcje</th>
                <th class="px-3 py-2.5">Budżet (EUR)</th>
                <th class="px-3 py-2.5">Status</th>
                <th class="px-3 py-2.5"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($plans as $plan)
            @php
                $progress = $plan->progressPercent();
                $overdueCnt = $plan->overdue_count ?? 0;
                $progColor = $progress >= 75 ? 'bg-emerald-500' : ($progress >= 40 ? 'bg-blue-500' : 'bg-amber-500');
                $isLate = $plan->target_date && $plan->target_date->isPast() && ! in_array($plan->status, ['Completed','Cancelled']);
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors {{ $overdueCnt > 0 ? 'bg-red-50' : '' }}">
                <td class="px-3 py-2">
                    <a href="{{ route('risks.show', $plan->risk) }}" class="text-xs font-mono text-slate-500 hover:underline">{{ $plan->risk?->code }}</a>
                    <div class="text-sm text-slate-800 truncate max-w-xs">{{ $plan->risk?->title }}</div>
                </td>
                <td class="px-3 py-2 text-xs text-center font-bold text-slate-700">{{ $plan->target_residual_score ?? '—' }}</td>
                <td class="px-3 py-2 text-xs {{ $isLate ? 'text-red-700 font-semibold' : 'text-slate-500' }}">
                    {{ $plan->target_date?->format('Y-m-d') ?? '—' }}
                </td>
                <td class="px-3 py-2">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 bg-slate-200 rounded-full h-2 min-w-16">
                            <div class="h-2 rounded-full {{ $progColor }}" style="width: {{ $progress }}%"></div>
                        </div>
                        <span class="text-xs text-slate-600 w-8 text-right">{{ $progress }}%</span>
                    </div>
                </td>
                <td class="px-3 py-2 text-xs text-center">
                    <span class="{{ $overdueCnt > 0 ? 'text-red-700 font-semibold' : 'text-slate-500' }}">{{ $overdueCnt }}</span>
                    <span class="text-slate-400">/{{ $plan->actions_count }}</span>
                </td>
                <td class="px-3 py-2 text-xs text-right">
                    @if($plan->budget_eur)
                        {{ number_format($plan->totalCostEur(), 0, ',', ' ') }} / {{ number_format($plan->budget_eur, 0, ',', ' ') }}
                        @if($plan->budgetVariance() < 0) <span class="text-red-600 font-semibold">↑</span> @endif
                    @else —
                    @endif
                </td>
                <td class="px-3 py-2 text-xs">{{ $plan->status }}</td>
                <td class="px-3 py-2 text-xs">
                    <a href="{{ route('risk-treatment-plans.show', $plan) }}" class="text-emerald-700 hover:underline">Szczegóły →</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-3 py-8 text-center text-slate-500">Brak planów leczenia ryzyk.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $plans->links() }}</div>

@endsection
