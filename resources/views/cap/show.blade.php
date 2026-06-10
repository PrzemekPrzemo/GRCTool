@extends('layouts.app')
@section('content')

@php
    $statusColor = match($cap->status) {
        'Approved'    => 'bg-blue-100 text-blue-800',
        'In Progress' => 'bg-amber-100 text-amber-800',
        'Completed'   => 'bg-emerald-100 text-emerald-800',
        'Cancelled'   => 'bg-slate-200 text-slate-600',
        default       => 'bg-slate-100 text-slate-700',
    };
    $avgProgress = $cap->actions->count()
        ? (int) round($cap->actions->avg('progress_percent'))
        : 0;
    $progressColor = $avgProgress >= 75 ? 'bg-emerald-500' : ($avgProgress >= 40 ? 'bg-blue-500' : 'bg-amber-400');
@endphp

{{-- Nagłówek --}}
<div class="flex items-start gap-3 mb-5">
    <a href="{{ route('cap.index') }}" class="mt-1 text-slate-400 hover:text-slate-600">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
    </a>
    <div class="flex-1 min-w-0">
        <div class="text-xs text-slate-500 mb-1">Plan działań naprawczych</div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="font-mono text-sm font-bold text-slate-600">{{ $cap->code }}</span>
            <h1 class="text-xl font-semibold text-slate-800">{{ $cap->title }}</h1>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">{{ $cap->status }}</span>
        </div>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
        @can('cap.update')
        <a href="{{ route('cap.edit', $cap) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-slate-300 rounded-lg text-sm text-slate-700 hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edytuj
        </a>
        @if($cap->status === 'Draft')
        <form method="POST" action="{{ route('cap.approve', $cap) }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Zatwierdź
            </button>
        </form>
        @endif
        @endcan
    </div>
</div>

@if(session('status'))
<div class="mb-4 px-4 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('status') }}</div>
@endif

{{-- Metadane --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Opis</h2>
        <p class="text-sm text-slate-700 leading-relaxed">{{ $cap->description ?: '—' }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 space-y-3">
        <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Zarządzanie</h2>
        <div class="flex items-center justify-between text-sm">
            <span class="text-slate-500">Zatwierdzający</span>
            <span class="font-medium text-slate-700">{{ $cap->approver?->name ?? '—' }}</span>
        </div>
        <div class="flex items-center justify-between text-sm">
            <span class="text-slate-500">Data zatwierdzenia</span>
            <span class="font-medium text-slate-700">{{ $cap->approved_at?->format('d.m.Y H:i') ?? '—' }}</span>
        </div>
        <div class="flex items-center justify-between text-sm">
            <span class="text-slate-500">Przegląd skuteczności</span>
            @php $reviewDate = $cap->effectiveness_review_date; $reviewPast = $reviewDate && $reviewDate->isPast() && $cap->status !== 'Completed'; @endphp
            <span class="font-medium {{ $reviewPast ? 'text-red-700' : 'text-slate-700' }}">
                {{ $reviewDate?->format('d.m.Y') ?? '—' }}
                @if($reviewPast) <span class="text-xs">(po terminie)</span> @endif
            </span>
        </div>
        <div class="flex items-center justify-between text-sm">
            <span class="text-slate-500">Liczba akcji</span>
            <span class="font-medium text-slate-700">{{ $cap->actions->count() }}</span>
        </div>
    </div>
</div>

{{-- Powiązane findings --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="font-semibold text-slate-700">Powiązane findings ({{ $findings->count() }})</h2>
    </div>
    @if($findings->isEmpty())
    <div class="px-5 py-6 text-sm text-slate-500 text-center">Brak powiązanych findings.</div>
    @else
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-2.5">Kod</th>
                <th class="px-4 py-2.5">Tytuł</th>
                <th class="px-4 py-2.5">Severity</th>
                <th class="px-4 py-2.5">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($findings as $finding)
            @php
                $sevColor = match($finding->severity ?? '') {
                    'Major'          => 'bg-red-100 text-red-800',
                    'Minor'          => 'bg-amber-100 text-amber-800',
                    'Observation'    => 'bg-blue-100 text-blue-800',
                    'Recommendation' => 'bg-slate-100 text-slate-700',
                    default          => 'bg-slate-100 text-slate-700',
                };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-4 py-3 font-mono text-xs">
                    <a href="{{ route('findings.show', $finding) }}" class="text-emerald-700 hover:underline">{{ $finding->code }}</a>
                </td>
                <td class="px-4 py-3 text-slate-700">{{ $finding->title }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $sevColor }}">{{ $finding->severity }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-slate-500">{{ $finding->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Akcje naprawcze --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-semibold text-slate-700">Akcje naprawcze ({{ $cap->actions->count() }})</h2>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 min-w-36">
                <div class="flex-1 bg-slate-200 rounded-full h-2.5">
                    <div class="h-2.5 rounded-full {{ $progressColor }}" style="width: {{ $avgProgress }}%"></div>
                </div>
                <span class="text-sm font-semibold text-slate-700 w-9 text-right">{{ $avgProgress }}%</span>
            </div>
            <span class="text-xs text-slate-400">postęp ogólny</span>
        </div>
    </div>

    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-2.5">Tytuł akcji</th>
                <th class="px-4 py-2.5">Właściciel</th>
                <th class="px-4 py-2.5">Termin</th>
                <th class="px-4 py-2.5">Postęp</th>
                <th class="px-4 py-2.5">Status</th>
                @can('cap.update') <th class="px-4 py-2.5">Aktualizuj</th> @endcan
            </tr>
        </thead>
        <tbody>
            @forelse($cap->actions as $action)
            @php
                $overdue = ! in_array($action->status, ['Completed','Cancelled']) && $action->due_date?->isPast();
                $progColor = $action->progress_percent >= 75 ? 'bg-emerald-500' : ($action->progress_percent >= 40 ? 'bg-blue-500' : 'bg-amber-400');
                $actionStatusColor = match($action->status) {
                    'Completed'   => 'bg-emerald-100 text-emerald-800',
                    'Cancelled'   => 'bg-slate-200 text-slate-600',
                    'Overdue'     => 'bg-red-100 text-red-800',
                    'In Progress' => 'bg-blue-100 text-blue-800',
                    default       => 'bg-slate-100 text-slate-700',
                };
            @endphp
            <tr class="border-t border-slate-100 {{ $overdue ? 'bg-red-50' : 'hover:bg-slate-50' }} transition-colors"
                x-data="{ editing: false }">
                <td class="px-4 py-3">
                    <div class="font-medium text-slate-800">{{ $action->title }}</div>
                    @if($action->description)
                        <div class="text-xs text-slate-400 mt-0.5">{{ Str::limit($action->description, 90) }}</div>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $action->owner?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-xs {{ $overdue ? 'text-red-700 font-bold' : 'text-slate-500' }}">
                    {{ $action->due_date?->format('d.m.Y') ?? '—' }}
                    @if($overdue) <div class="text-red-600">po terminie</div> @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2 min-w-24">
                        <div class="flex-1 bg-slate-200 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $progColor }}" style="width: {{ $action->progress_percent ?? 0 }}%"></div>
                        </div>
                        <span class="text-xs text-slate-500 w-7 text-right">{{ $action->progress_percent ?? 0 }}%</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-xs">
                    <span class="px-2 py-0.5 rounded-full {{ $actionStatusColor }} font-medium">{{ $action->status }}</span>
                </td>
                @can('cap.update')
                <td class="px-4 py-3">
                    @if(! in_array($action->status, ['Completed','Cancelled']))
                    <div x-show="!editing">
                        <button @click="editing = true" class="text-xs text-emerald-700 hover:underline">Edytuj</button>
                    </div>
                    <form x-show="editing"
                          method="POST"
                          action="{{ route('cap-actions.update', $action) }}"
                          class="flex flex-wrap items-end gap-2">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="block text-xs text-slate-500 mb-0.5">Postęp %</label>
                            <input type="number" name="progress_percent"
                                   value="{{ $action->progress_percent ?? 0 }}"
                                   min="0" max="100"
                                   class="w-16 border border-slate-300 rounded px-2 py-1 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-0.5">Status</label>
                            <select name="status" class="border border-slate-300 rounded px-2 py-1 text-xs">
                                @foreach(['Open','In Progress','Completed','Overdue','Cancelled'] as $s)
                                    <option @selected($action->status === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-0.5">Ukończono</label>
                            <input type="date" name="completed_at"
                                   value="{{ $action->completed_at?->format('Y-m-d') }}"
                                   class="border border-slate-300 rounded px-2 py-1 text-xs">
                        </div>
                        <button type="submit"
                                class="px-2 py-1 bg-emerald-600 text-white rounded text-xs hover:bg-emerald-700">Zapisz</button>
                        <button type="button" @click="editing = false"
                                class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs hover:bg-slate-200">Anuluj</button>
                    </form>
                    @endif
                </td>
                @endcan
            </tr>
            @empty
            <tr>
                <td colspan="{{ auth()->user()->can('cap.update') ? 6 : 5 }}"
                    class="px-4 py-6 text-center text-slate-500 text-sm">Brak akcji naprawczych.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Formularz dodania nowej akcji --}}
    @can('cap.update')
    <div class="border-t border-slate-100 px-5 py-4" x-data="{ open: false }">
        <button @click="open = !open"
                class="inline-flex items-center gap-2 text-sm font-medium text-emerald-700 hover:text-emerald-900 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            <span x-text="open ? 'Ukryj formularz' : 'Dodaj akcję'">Dodaj akcję</span>
        </button>

        <div x-show="open" x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mt-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
            <form method="POST" action="{{ route('cap.action', $cap) }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Tytuł akcji <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Opis akcji naprawczej...">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" required
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            @foreach(['Open','In Progress','Completed','Overdue','Cancelled'] as $s)
                                <option>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Opis</label>
                        <textarea name="description" rows="2"
                                  class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                  placeholder="Szczegółowy opis..."></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Właściciel <span class="text-red-500">*</span></label>
                        <select name="owner_id" required
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">— wybierz —</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Termin realizacji <span class="text-red-500">*</span></label>
                        <input type="date" name="due_date" required
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Postęp (%)</label>
                        <input type="number" name="progress_percent" value="0" min="0" max="100"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                        Dodaj akcję
                    </button>
                    <button type="button" @click="open = false"
                            class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm hover:bg-slate-50 transition-colors">
                        Anuluj
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcan
</div>

<x-comment-thread :model="$cap" />
@endsection
