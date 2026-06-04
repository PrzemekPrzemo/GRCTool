@extends('layouts.app')
@section('content')
@php
    $riskBg = match($breach->risk_level) {
        'very_high' => 'bg-red-600',
        'high'      => 'bg-orange-500',
        'medium'    => 'bg-amber-500',
        'low'       => 'bg-slate-400',
        default     => 'bg-slate-500',
    };
    $statusBg = match($breach->status) {
        'open'      => 'bg-red-100 text-red-800',
        'contained' => 'bg-amber-100 text-amber-800',
        'reported'  => 'bg-blue-100 text-blue-800',
        'closed'    => 'bg-emerald-100 text-emerald-800',
        default     => 'bg-slate-100 text-slate-600',
    };
@endphp

<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('gdpr-breaches.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← Naruszenia RODO</a>
    <h1 class="text-2xl font-semibold">{{ $breach->code }}</h1>
    <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">
        {{ match($breach->status) { 'open' => 'Otwarte', 'contained' => 'Opanowane', 'reported' => 'Zgłoszone', 'closed' => 'Zamknięte', default => $breach->status } }}
    </span>
    @can('gdpr_breach.update')
        <a href="{{ route('gdpr-breaches.edit', $breach) }}" class="ml-auto px-3 py-1.5 border border-slate-300 rounded text-sm">Edytuj</a>
    @endcan
</div>

{{-- Deadline alert --}}
@if($breach->notification_required && !$breach->uodo_notified_at)
@php $hours = $breach->hoursUntilDeadline(); @endphp
<div class="rounded-lg p-4 mb-4 {{ $breach->isOverdue() ? 'bg-red-600 text-white' : 'bg-amber-50 border border-amber-300 text-amber-900' }}">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="font-semibold text-sm">{{ $breach->isOverdue() ? '⚠ PRZEKROCZONY termin zgłoszenia do UODO' : 'Wymaga zgłoszenia do UODO (Art. 33 RODO)' }}</div>
            @if($breach->uodo_notification_deadline)
                <div class="text-sm mt-1">Deadline: <strong>{{ $breach->uodo_notification_deadline->format('Y-m-d H:i') }}</strong>
                    @if(!$breach->isOverdue() && $hours !== null)
                        (pozostało {{ abs($hours) }}h)
                    @endif
                </div>
            @endif
        </div>
        @can('gdpr_breach.update')
        <form method="POST" action="{{ route('gdpr-breaches.notify-uodo', $breach) }}" class="flex gap-2 items-center flex-shrink-0">
            @csrf
            <input type="text" name="uodo_reference_number" placeholder="Nr ref. UODO" class="px-2 py-1 border rounded text-xs text-slate-800">
            <button class="px-3 py-1.5 bg-white text-slate-900 rounded text-xs font-medium">Oznacz jako zgłoszone</button>
        </form>
        @endcan
    </div>
</div>
@elseif($breach->uodo_notified_at)
<div class="bg-emerald-50 border border-emerald-200 rounded p-3 mb-4 text-sm text-emerald-800">
    Zgłoszono do UODO: <strong>{{ $breach->uodo_notified_at->format('Y-m-d H:i') }}</strong>
    @if($breach->uodo_reference_number) · Nr ref.: <strong>{{ $breach->uodo_reference_number }}</strong> @endif
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        {{-- Main --}}
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">{{ $breach->title }}</h2>
            @if($breach->description)
                <p class="text-sm text-slate-700 mb-4">{{ $breach->description }}</p>
            @endif
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <dt class="text-slate-500">Rodzaj naruszenia</dt>
                <dd>{{ \App\Models\GdprBreach::BREACH_TYPES[$breach->breach_type] ?? '—' }}</dd>
                <dt class="text-slate-500">Poziom ryzyka</dt>
                <dd>{{ $breach->riskLevelLabel() }}</dd>
                <dt class="text-slate-500">Zaistniało</dt>
                <dd>{{ $breach->occurred_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                <dt class="text-slate-500">Wykryto</dt>
                <dd>{{ $breach->discovered_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                <dt class="text-slate-500">Opanowano</dt>
                <dd>{{ $breach->contained_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                <dt class="text-slate-500">Odpowiedzialny</dt>
                <dd>{{ $breach->responsibleUser?->name ?? '—' }}</dd>
            </dl>
            @if($breach->incident)
            <div class="mt-3 pt-3 border-t border-slate-100 text-sm">
                Powiązany incydent IT:
                <a href="{{ route('incidents.show', $breach->incident) }}" class="text-emerald-700 hover:underline font-mono">{{ $breach->incident->code }}</a>
                — {{ $breach->incident->title }}
            </div>
            @endif
        </div>

        {{-- Affected data --}}
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Zakres naruszenia</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="text-xs font-medium text-slate-500 uppercase mb-2">Kategorie danych</h3>
                    @if(!empty($breach->data_categories_affected))
                        <div class="flex flex-wrap gap-1">
                            @foreach($breach->data_categories_affected as $cat)
                                <span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-800">
                                    {{ \App\Models\ProcessingActivity::DATA_CATEGORIES[$cat] ?? $cat }}
                                </span>
                            @endforeach
                        </div>
                    @else <span class="text-slate-400 text-sm">—</span> @endif
                </div>
                <div>
                    <h3 class="text-xs font-medium text-red-600 uppercase mb-2">Szczególne kategorie (Art. 9)</h3>
                    @if(!empty($breach->special_categories_affected))
                        <div class="flex flex-wrap gap-1">
                            @foreach($breach->special_categories_affected as $cat)
                                <span class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-800">
                                    {{ \App\Models\ProcessingActivity::SPECIAL_CATEGORIES[$cat] ?? $cat }}
                                </span>
                            @endforeach
                        </div>
                    @else <span class="text-slate-400 text-sm">—</span> @endif
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-slate-500">Liczba dotkniętych osób:</span>
                    <strong class="ml-1">{{ $breach->data_subjects_count ? number_format($breach->data_subjects_count) : '—' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500">Kategorie osób:</span>
                    @if(!empty($breach->data_subjects_types))
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach($breach->data_subjects_types as $type)
                                <span class="px-1.5 py-0.5 rounded text-xs bg-slate-100 text-slate-700">
                                    {{ \App\Models\ProcessingActivity::DATA_SUBJECTS[$type] ?? $type }}
                                </span>
                            @endforeach
                        </div>
                    @else — @endif
                </div>
            </div>
            @if($breach->risk_description)
            <div class="mt-3 pt-3 border-t border-slate-100">
                <h3 class="text-sm font-medium text-slate-600 mb-1">Opis ryzyka</h3>
                <p class="text-sm text-slate-700">{{ $breach->risk_description }}</p>
            </div>
            @endif
        </div>

        {{-- Remediation --}}
        @if($breach->remediation_actions || $breach->preventive_measures)
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Działania naprawcze</h2>
            @if($breach->remediation_actions)
                <h3 class="text-sm font-medium text-slate-600 mb-1">Podjęte działania</h3>
                <p class="text-sm text-slate-700 whitespace-pre-line mb-3">{{ $breach->remediation_actions }}</p>
            @endif
            @if($breach->preventive_measures)
                <h3 class="text-sm font-medium text-slate-600 mb-1">Środki zapobiegawcze</h3>
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $breach->preventive_measures }}</p>
            @endif
        </div>
        @endif
    </div>

    {{-- Right column --}}
    <div class="space-y-4">
        {{-- Notification status --}}
        <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold text-slate-700 text-sm mb-3">Status zgłoszeń</h3>
            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Zgłoszenie do UODO (Art. 33)</span>
                    @if($breach->notification_required)
                        <span class="{{ $breach->uodo_notified_at ? 'text-emerald-600' : 'text-red-600 font-semibold' }}">
                            {{ $breach->uodo_notified_at ? '✓ Zgłoszono' : '✗ Wymagane' }}
                        </span>
                    @else
                        <span class="text-slate-400">Nie wymaga</span>
                    @endif
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Zawiadomienie osób (Art. 34)</span>
                    @if($breach->data_subject_notification_required)
                        <span class="{{ $breach->data_subjects_notified ? 'text-emerald-600' : 'text-red-600 font-semibold' }}">
                            {{ $breach->data_subjects_notified ? '✓ Zawiadomiono' : '✗ Wymagane' }}
                        </span>
                    @else
                        <span class="text-slate-400">Nie wymaga</span>
                    @endif
                </div>
                @if($breach->uodo_notification_deadline)
                <div class="pt-1 border-t border-slate-100">
                    <span class="text-slate-500">Deadline UODO:</span>
                    <span class="{{ $breach->isOverdue() ? 'text-red-700 font-bold' : 'text-slate-700' }}">
                        {{ $breach->uodo_notification_deadline->format('Y-m-d H:i') }}
                    </span>
                </div>
                @endif
            </div>
        </div>

        {{-- Metadata --}}
        <div class="bg-white rounded shadow p-4 text-xs text-slate-500 space-y-1.5">
            <div>Kod: <span class="font-mono text-slate-700">{{ $breach->code }}</span></div>
            <div>Odpowiedzialny: <span class="text-slate-700">{{ $breach->responsibleUser?->name ?? '—' }}</span></div>
            <div>Utworzono: {{ $breach->created_at->format('Y-m-d H:i') }}</div>
        </div>

        {{-- Legal note --}}
        <div class="bg-amber-50 border border-amber-200 rounded p-3 text-xs text-amber-800">
            <strong>Art. 33 RODO:</strong> Zgłoszenie do UODO w ciągu 72 godzin od stwierdzenia naruszenia, chyba że jest mało prawdopodobne, by naruszenie to skutkowało ryzykiem naruszenia praw lub wolności osób fizycznych.
        </div>
    </div>
</div>
@endsection
