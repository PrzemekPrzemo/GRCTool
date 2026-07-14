@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Wnioski DSAR</h1>
        <p class="text-sm text-slate-500 mt-1">Art. 15–22 RODO — wnioski osób, których dane dotyczą</p>
    </div>
    @can('dsar.create')
        <a href="{{ route('dsar.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowy wniosek</a>
    @endcan
</div>

@php $overdueCount = $requests->filter(fn($r) => $r->isOverdue())->count(); @endphp
@if($overdueCount > 0)
<div class="bg-red-50 border border-red-300 rounded p-3 mb-4 text-sm text-red-800">
    <strong>{{ $overdueCount }} wniosek(ów)</strong> przekroczyło termin odpowiedzi (30 lub 90 dni).
</div>
@endif

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex flex-wrap gap-2">
    <select name="type" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Typ wniosku —</option>
        @foreach($requestTypes as $key => $label)
            <option value="{{ $key }}" @selected(request('type')===$key)>{{ $label }}</option>
        @endforeach
    </select>
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Status —</option>
        <option value="pending" @selected(request('status')==='pending')>Oczekujący</option>
        <option value="in_progress" @selected(request('status')==='in_progress')>W trakcie</option>
        <option value="on_hold" @selected(request('status')==='on_hold')>Wstrzymany</option>
        <option value="completed" @selected(request('status')==='completed')>Zakończony</option>
        <option value="rejected" @selected(request('status')==='rejected')>Odrzucony</option>
        <option value="withdrawn" @selected(request('status')==='withdrawn')>Wycofany</option>
    </select>
    <label class="flex items-center gap-2 text-sm px-2">
        <input type="checkbox" name="overdue" value="1" @checked(request('overdue'))>
        Tylko przeterminowane
    </label>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
    @if(request()->anyFilled(['type','status','overdue']))
        <a href="{{ route('dsar.index') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600">Wyczyść</a>
    @endif
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Typ wniosku</th>
                <th class="px-3 py-2">Wnioskodawca</th>
                <th class="px-3 py-2">Wpłynął</th>
                <th class="px-3 py-2">Termin</th>
                <th class="px-3 py-2">Dni pozostało</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Przypisany do</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $r)
            @php
                $days = $r->daysUntilDeadline();
                $statusBg = match($r->status) {
                    'pending'     => 'bg-slate-100 text-slate-700',
                    'in_progress' => 'bg-blue-100 text-blue-800',
                    'on_hold'     => 'bg-amber-100 text-amber-800',
                    'completed'   => 'bg-emerald-100 text-emerald-800',
                    'rejected'    => 'bg-red-100 text-red-800',
                    'withdrawn'   => 'bg-slate-200 text-slate-600',
                    default       => 'bg-slate-100 text-slate-600',
                };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50 {{ $r->isOverdue() ? 'bg-red-50' : '' }}">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('dsar.show', $r) }}" class="text-emerald-700 hover:underline">{{ $r->code }}</a>
                </td>
                <td class="px-3 py-2 text-xs text-slate-700">{{ $r->requestTypeLabel() }}</td>
                <td class="px-3 py-2">{{ $r->requester_name }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $r->received_at->format('Y-m-d') }}</td>
                <td class="px-3 py-2 text-xs {{ $r->isOverdue() ? 'text-red-700 font-semibold' : 'text-slate-600' }}">
                    {{ ($r->deadline_extended ? $r->extended_deadline_at : $r->deadline_at)?->format('Y-m-d') ?? '—' }}
                    @if($r->deadline_extended) <span class="text-amber-600">(+90d)</span> @endif
                </td>
                <td class="px-3 py-2 text-center text-xs">
                    @if($days !== null && !in_array($r->status, ['completed','rejected','withdrawn']))
                        <span class="{{ $days < 0 ? 'text-red-700 font-bold' : ($days <= 5 ? 'text-amber-700 font-semibold' : 'text-slate-600') }}">
                            {{ $days < 0 ? 'PRZEKROCZONY' : $days.' dni' }}
                        </span>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-3 py-2">
                    <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">
                        {{ match($r->status) { 'pending' => 'Oczekujący', 'in_progress' => 'W trakcie', 'on_hold' => 'Wstrzymany', 'completed' => 'Zakończony', 'rejected' => 'Odrzucony', 'withdrawn' => 'Wycofany', default => $r->status } }}
                    </span>
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $r->assignedUser?->name ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-3 py-8 text-center text-slate-500">
                    Brak wniosków DSAR.
                    @can('dsar.create')
                        <a href="{{ route('dsar.create') }}" class="text-emerald-600 hover:underline ml-1">Zarejestruj wniosek</a>.
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $requests->links() }}</div>
@endsection
