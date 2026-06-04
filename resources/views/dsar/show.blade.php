@extends('layouts.app')
@section('content')
@php
    $days = $dsar->daysUntilDeadline();
    $deadline = $dsar->deadline_extended ? $dsar->extended_deadline_at : $dsar->deadline_at;
    $statusBg = match($dsar->status) {
        'pending'     => 'bg-slate-100 text-slate-700',
        'in_progress' => 'bg-blue-100 text-blue-800',
        'on_hold'     => 'bg-amber-100 text-amber-800',
        'completed'   => 'bg-emerald-100 text-emerald-800',
        'rejected'    => 'bg-red-100 text-red-800',
        'withdrawn'   => 'bg-slate-200 text-slate-600',
        default       => 'bg-slate-100 text-slate-600',
    };
@endphp

<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('dsar.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Wnioski DSAR</a>
    <h1 class="text-2xl font-semibold">{{ $dsar->code }}</h1>
    <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">
        {{ match($dsar->status) { 'pending' => 'Oczekujący', 'in_progress' => 'W trakcie', 'on_hold' => 'Wstrzymany', 'completed' => 'Zakończony', 'rejected' => 'Odrzucony', 'withdrawn' => 'Wycofany', default => $dsar->status } }}
    </span>
    @can('dsar.update')
        <a href="{{ route('dsar.edit', $dsar) }}" class="ml-auto px-3 py-1.5 border border-slate-300 rounded text-sm">Edytuj</a>
    @endcan
</div>

{{-- Deadline alert --}}
@if($dsar->isOverdue())
<div class="bg-red-600 text-white rounded-lg p-4 mb-4">
    <strong>Przekroczono termin odpowiedzi!</strong>
    Deadline upłynął: {{ $deadline?->format('Y-m-d H:i') }}
</div>
@elseif($days !== null && $days <= 5 && !in_array($dsar->status, ['completed','rejected','withdrawn']))
<div class="bg-amber-50 border border-amber-300 rounded p-3 mb-4 text-sm text-amber-800">
    <strong>Zbliża się termin!</strong> Pozostało <strong>{{ $days }} dni</strong> na odpowiedź (do {{ $deadline?->format('Y-m-d') }}).
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Szczegóły wniosku</h2>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm mb-4">
                <dt class="text-slate-500">Typ wniosku</dt>
                <dd class="font-medium">{{ $dsar->requestTypeLabel() }}</dd>
                <dt class="text-slate-500">Wnioskodawca</dt>
                <dd>{{ $dsar->requester_name }}</dd>
                <dt class="text-slate-500">Email</dt>
                <dd>{{ $dsar->requester_email ?? '—' }}</dd>
                <dt class="text-slate-500">Data wpłynięcia</dt>
                <dd>{{ $dsar->received_at->format('Y-m-d H:i') }}</dd>
                <dt class="text-slate-500">Termin odpowiedzi</dt>
                <dd class="{{ $dsar->isOverdue() ? 'text-red-700 font-bold' : '' }}">
                    {{ $deadline?->format('Y-m-d') ?? '—' }}
                    @if($dsar->deadline_extended) <span class="text-amber-600 text-xs ml-1">(przedłużony do 90 dni)</span> @endif
                </dd>
                <dt class="text-slate-500">Weryfikacja tożsamości</dt>
                <dd>{{ $dsar->identity_verified ? '✓ Zweryfikowana' : '✗ Niezweryfikowana' }}</dd>
            </dl>
            <h3 class="text-sm font-medium text-slate-600 mb-1">Treść wniosku</h3>
            <p class="text-sm text-slate-700 bg-slate-50 rounded p-3 whitespace-pre-line">{{ $dsar->request_description }}</p>
            @if($dsar->requester_details)
                <div class="mt-3">
                    <h3 class="text-sm font-medium text-slate-600 mb-1">Dane dodatkowe</h3>
                    <p class="text-sm text-slate-700">{{ $dsar->requester_details }}</p>
                </div>
            @endif
        </div>

        @if($dsar->handling_notes)
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-2">Notatki z obsługi</h2>
            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $dsar->handling_notes }}</p>
        </div>
        @endif

        @if($dsar->status === 'completed' && $dsar->outcome)
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Wynik rozpatrzenia</h2>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2 py-0.5 rounded text-sm bg-emerald-100 text-emerald-800 font-medium">{{ $dsar->outcomeLabel() }}</span>
                @if($dsar->completed_at)
                    <span class="text-xs text-slate-500">{{ $dsar->completed_at->format('Y-m-d H:i') }}</span>
                @endif
            </div>
            @if($dsar->outcome_notes)
                <p class="text-sm text-slate-700">{{ $dsar->outcome_notes }}</p>
            @endif
        </div>
        @endif
    </div>

    <div class="space-y-4">
        @can('dsar.update')
        @if(!in_array($dsar->status, ['completed', 'rejected', 'withdrawn']))
        <div class="bg-white rounded shadow p-4 space-y-3">
            <h3 class="font-semibold text-slate-700 text-sm">Akcje</h3>

            <form method="POST" action="{{ route('dsar.complete', $dsar) }}" class="space-y-2">
                @csrf
                <select name="outcome" class="w-full px-3 py-1.5 border border-slate-300 rounded text-xs">
                    <option value="">— Wynik —</option>
                    @foreach(\App\Models\DsarRequest::OUTCOMES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <textarea name="outcome_notes" rows="2" placeholder="Notatki (opcjonalnie)" class="w-full px-3 py-1.5 border border-slate-300 rounded text-xs"></textarea>
                <button class="w-full px-3 py-1.5 bg-emerald-600 text-white rounded text-xs">Zakończ wniosek</button>
            </form>

            @if(!$dsar->deadline_extended)
            <form method="POST" action="{{ route('dsar.extend', $dsar) }}" class="space-y-2">
                @csrf
                <input type="text" name="extension_reason" placeholder="Powód przedłużenia…" required
                    class="w-full px-3 py-1.5 border border-slate-300 rounded text-xs">
                <button class="w-full px-3 py-1.5 bg-amber-500 text-white rounded text-xs">Przedłuż termin do 90 dni</button>
            </form>
            @endif
        </div>
        @endif
        @endcan

        <div class="bg-white rounded shadow p-4 text-xs text-slate-500 space-y-1.5">
            <div>Kod: <span class="font-mono text-slate-700">{{ $dsar->code }}</span></div>
            <div>Przypisany: <span class="text-slate-700">{{ $dsar->assignedUser?->name ?? '—' }}</span></div>
            <div>Wpłynął: <span class="text-slate-700">{{ $dsar->received_at->format('Y-m-d H:i') }}</span></div>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded p-3 text-xs text-slate-600">
            <strong>Terminy DSAR:</strong> 30 dni na odpowiedź (liczone od daty wpłynięcia). Możliwe przedłużenie do 90 dni z uwagi na złożoność wniosku lub dużą liczbę wniosków — z poinformowaniem wnioskodawcy.
        </div>
    </div>
</div>
@endsection
