@extends('layouts.app')
@section('content')

@php $progress = $plan->progressPercent(); @endphp

<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('risk-treatment-plans.index') }}" class="text-slate-400 hover:text-slate-600">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <div class="text-xs text-slate-500">Plan leczenia ryzyka</div>
        <h1 class="text-xl font-semibold text-slate-800">
            <a href="{{ route('risks.show', $plan->risk) }}" class="hover:underline">{{ $plan->risk?->code }} — {{ $plan->risk?->title }}</a>
        </h1>
    </div>
</div>

@if(session('status'))
<div class="mb-4 px-4 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('status') }}</div>
@endif

{{-- Plan header --}}
<div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wide">Cel residual score</div>
        <div class="text-3xl font-bold mt-1 text-emerald-700">{{ $plan->target_residual_score ?? '—' }}</div>
        <div class="text-xs text-slate-400 mt-1">Obecny: {{ $plan->risk?->residual_score ?? '—' }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wide">Deadline</div>
        @php $isLate = $plan->target_date && $plan->target_date->isPast() && ! in_array($plan->status, ['Completed','Cancelled']); @endphp
        <div class="text-xl font-bold mt-1 {{ $isLate ? 'text-red-700' : 'text-slate-700' }}">{{ $plan->target_date?->format('d.m.Y') ?? '—' }}</div>
        @if($isLate) <div class="text-xs text-red-600 mt-1">Po terminie!</div> @endif
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wide">Budżet</div>
        @php $costEur = $plan->totalCostEur(); $budgetEur = (float)($plan->budget_eur ?? 0); $overBudget = $budgetEur > 0 && $costEur > $budgetEur; @endphp
        <div class="text-xl font-bold mt-1 {{ $overBudget ? 'text-red-700' : 'text-slate-700' }}">
            {{ $budgetEur > 0 ? number_format($costEur, 0, ',', ' ').' / '.number_format($budgetEur, 0, ',', ' ').' EUR' : '—' }}
        </div>
        @if($overBudget) <div class="text-xs text-red-600 mt-1">Przekroczony budżet!</div> @endif
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-2">Postęp ogólny</div>
        <div class="flex items-center gap-3">
            <div class="flex-1 bg-slate-200 rounded-full h-3">
                <div class="h-3 rounded-full {{ $progress >= 75 ? 'bg-emerald-500' : ($progress >= 40 ? 'bg-blue-500' : 'bg-amber-500') }}" style="width: {{ $progress }}%"></div>
            </div>
            <span class="text-xl font-bold text-slate-700">{{ $progress }}%</span>
        </div>
        <div class="text-xs text-slate-400 mt-1">{{ $plan->overdueActionsCount() }} akcji po terminie</div>
    </div>
</div>

{{-- Actions table --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-semibold text-slate-700">Akcje planu ({{ $plan->actions->count() }})</h2>
        <span class="text-xs text-slate-400">Status: {{ $plan->status }}</span>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-2.5">Akcja</th>
                <th class="px-4 py-2.5">Właściciel</th>
                <th class="px-4 py-2.5">Termin</th>
                <th class="px-4 py-2.5">Postęp</th>
                <th class="px-4 py-2.5">Status</th>
                <th class="px-4 py-2.5">Koszt EUR</th>
                @can('risk.update') <th class="px-4 py-2.5">Aktualizuj</th> @endcan
            </tr>
        </thead>
        <tbody>
            @forelse($plan->actions as $action)
            @php
                $overdue   = $action->isOverdue();
                $progColor = $action->progress_percent >= 75 ? 'bg-emerald-500' : ($action->progress_percent >= 40 ? 'bg-blue-500' : 'bg-amber-400');
            @endphp
            <tr class="border-t border-slate-100 {{ $overdue ? 'bg-red-50' : 'hover:bg-slate-50' }} transition-colors" x-data="{ editing: false }">
                <td class="px-4 py-3">
                    <div class="font-medium text-slate-800">{{ $action->title }}</div>
                    @if($action->description)<div class="text-xs text-slate-400 mt-0.5">{{ Str::limit($action->description, 80) }}</div>@endif
                </td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $action->owner?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-xs {{ $overdue ? 'text-red-700 font-bold' : 'text-slate-500' }}">
                    {{ $action->due_date?->format('Y-m-d') ?? '—' }}
                    @if($overdue) <span class="text-red-600 font-semibold">(+{{ $action->daysOverdue() }}d)</span> @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2 min-w-24">
                        <div class="flex-1 bg-slate-200 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $progColor }}" style="width: {{ $action->progress_percent }}%"></div>
                        </div>
                        <span class="text-xs text-slate-500 w-7 text-right">{{ $action->progress_percent }}%</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-xs">
                    @php $statusColor = match($action->status) { 'Completed' => 'bg-emerald-100 text-emerald-800', 'Cancelled' => 'bg-slate-200 text-slate-600', 'Overdue' => 'bg-red-100 text-red-800', 'In Progress' => 'bg-blue-100 text-blue-800', default => 'bg-slate-100 text-slate-700' }; @endphp
                    <span class="px-2 py-0.5 rounded-full {{ $statusColor }} font-medium">{{ $action->status }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-right text-slate-500">{{ $action->cost_eur ? number_format($action->cost_eur, 0, ',', ' ') : '—' }}</td>
                @can('risk.update')
                <td class="px-4 py-3">
                    @if(! in_array($action->status, ['Completed','Cancelled']))
                    <div x-show="!editing">
                        <button @click="editing = true" class="text-xs text-emerald-700 hover:underline">Aktualizuj</button>
                    </div>
                    <form x-show="editing" method="POST" action="{{ route('rtp-actions.update', $action) }}" class="flex items-end gap-2 min-w-56">
                        @csrf @method('PATCH')
                        <div>
                            <label class="block text-xs text-slate-500 mb-0.5">Postęp %</label>
                            <input type="number" name="progress_percent" value="{{ $action->progress_percent }}" min="0" max="100"
                                class="w-16 border border-slate-300 rounded px-2 py-1 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-0.5">Status</label>
                            <select name="status" class="border border-slate-300 rounded px-2 py-1 text-xs">
                                @foreach(['Open','In Progress','Completed','Cancelled'] as $s)
                                    <option @selected($action->status === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="px-2 py-1 bg-emerald-600 text-white rounded text-xs hover:bg-emerald-700">Zapisz</button>
                        <button type="button" @click="editing = false" class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs">Anuluj</button>
                    </form>
                    @endif
                </td>
                @endcan
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500 text-sm">Brak akcji w planie.</td></tr>
            @endforelse
        </tbody>
        @if($plan->budget_eur)
        <tfoot class="border-t-2 border-slate-200 bg-slate-50">
            <tr>
                <td colspan="5" class="px-4 py-2 text-xs font-semibold text-slate-600 text-right">Suma kosztów:</td>
                <td class="px-4 py-2 text-xs font-bold {{ $plan->budgetVariance() < 0 ? 'text-red-700' : 'text-slate-700' }} text-right">
                    {{ number_format($plan->totalCostEur(), 0, ',', ' ') }} EUR
                </td>
                @can('risk.update') <td></td> @endcan
            </tr>
        </tfoot>
        @endif
    </table>
    </div>
</div>

@endsection
