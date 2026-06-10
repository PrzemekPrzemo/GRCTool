@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-semibold text-slate-800">Plany działań naprawczych (CAP)</h1>
        <p class="text-sm text-slate-500 mt-0.5">Zarządzanie planami działań korygujących</p>
    </div>
    @can('cap.create')
    <a href="{{ route('cap.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nowy CAP
    </a>
    @endcan
</div>

@if(session('status'))
<div class="mb-4 px-4 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('status') }}</div>
@endif

{{-- Karty statystyk --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wide">Łącznie</div>
        <div class="text-3xl font-bold text-slate-800 mt-1">{{ $stats['total'] }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-xs text-amber-600 font-medium uppercase tracking-wide">Szkice</div>
        <div class="text-3xl font-bold text-amber-700 mt-1">{{ $stats['draft'] }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-xs text-blue-600 font-medium uppercase tracking-wide">W realizacji</div>
        <div class="text-3xl font-bold text-blue-700 mt-1">{{ $stats['in_progress'] }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-xs text-red-600 font-medium uppercase tracking-wide">Zaległe akcje</div>
        <div class="text-3xl font-bold text-red-700 mt-1">{{ $stats['overdue'] }}</div>
    </div>
</div>

{{-- Filtr --}}
<form method="GET" class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 mb-4 flex gap-2 items-center">
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Wszystkie statusy —</option>
        @foreach(['Draft','Approved','In Progress','Completed','Cancelled'] as $s)
            <option @selected(request('status') === $s)>{{ $s }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm hover:bg-slate-700 transition-colors">Filtruj</button>
    @if(request('status'))
        <a href="{{ route('cap.index') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">Wyczyść</a>
    @endif
</form>

{{-- Tabela --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Kod</th>
                <th class="px-4 py-3">Tytuł</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Findings</th>
                <th class="px-4 py-3">Akcje</th>
                <th class="px-4 py-3">Zatwierdzający</th>
                <th class="px-4 py-3">Przegląd skuteczności</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($caps as $cap)
            @php
                $statusColor = match($cap->status) {
                    'Approved'    => 'bg-blue-100 text-blue-800',
                    'In Progress' => 'bg-amber-100 text-amber-800',
                    'Completed'   => 'bg-emerald-100 text-emerald-800',
                    'Cancelled'   => 'bg-slate-200 text-slate-600',
                    default       => 'bg-slate-100 text-slate-700',
                };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3">
                    <a href="{{ route('cap.show', $cap) }}" class="font-mono text-xs text-emerald-700 hover:underline font-semibold">{{ $cap->code }}</a>
                </td>
                <td class="px-4 py-3 font-medium text-slate-800 max-w-xs">
                    <a href="{{ route('cap.show', $cap) }}" class="hover:text-emerald-700 hover:underline">{{ $cap->title }}</a>
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">{{ $cap->status }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-slate-500">
                    {{ count($cap->finding_ids ?? []) }}
                </td>
                <td class="px-4 py-3 text-xs text-slate-500">
                    {{ $cap->actions_count ?? 0 }}
                    @if(($cap->overdue_count ?? 0) > 0)
                        <span class="ml-1 text-red-600 font-semibold">({{ $cap->overdue_count }} po terminie)</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-slate-500">{{ $cap->approver?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-xs text-slate-500">{{ $cap->effectiveness_review_date?->format('d.m.Y') ?? '—' }}</td>
                <td class="px-4 py-3">
                    @can('cap.update')
                    <a href="{{ route('cap.edit', $cap) }}" class="text-xs text-slate-500 hover:text-emerald-700 hover:underline">Edytuj</a>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-slate-500 text-sm">Brak planów działań naprawczych.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $caps->links() }}</div>

@endsection
