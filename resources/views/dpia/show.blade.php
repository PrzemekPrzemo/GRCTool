@extends('layouts.app')
@section('content')
@php
    $riskBg = match($dpia->overall_risk_level) {
        'very_high' => 'bg-red-600 text-white',
        'high'      => 'bg-orange-500 text-white',
        'medium'    => 'bg-amber-400 text-amber-900',
        'low'       => 'bg-emerald-500 text-white',
        default     => 'bg-slate-400 text-white',
    };
    $statusBg = match($dpia->status) {
        'draft'     => 'bg-yellow-100 text-yellow-800',
        'in_review' => 'bg-blue-100 text-blue-800',
        'approved'  => 'bg-emerald-100 text-emerald-800',
        'rejected'  => 'bg-red-100 text-red-800',
        default     => 'bg-slate-100 text-slate-600',
    };
@endphp

<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('dpias.index') }}" class="text-slate-500 hover:text-slate-700 text-sm">← DPIA</a>
    <h1 class="text-2xl font-semibold">{{ $dpia->code }}</h1>
    <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">
        {{ match($dpia->status) { 'draft' => 'Roboczy', 'in_review' => 'W przeglądzie', 'approved' => 'Zatwierdzona', 'rejected' => 'Odrzucona', default => $dpia->status } }}
    </span>
    @can('dpia.update')
    <div class="ml-auto flex gap-2">
        <a href="{{ route('dpias.edit', $dpia) }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm">Edytuj</a>
        @if($dpia->status !== 'approved')
        <form method="POST" action="{{ route('dpias.approve', $dpia) }}" onsubmit="return confirm('Zatwierdzić DPIA?')">
            @csrf
            <button class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">Zatwierdź</button>
        </form>
        @endif
    </div>
    @endcan
</div>

{{-- Authority consultation alert --}}
@if($dpia->authority_consultation_required && !$dpia->authority_consulted_at)
<div class="bg-amber-50 border-l-4 border-amber-400 px-4 py-3 rounded-r mb-4 text-sm text-amber-800">
    <strong>Art. 36 RODO:</strong> Ta ocena wymaga uprzedniej konsultacji z UODO przed wdrożeniem przetwarzania (wysokie ryzyko szczątkowe).
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-2">{{ $dpia->title }}</h2>
            @if($dpia->description)
                <p class="text-sm text-slate-700 mb-4">{{ $dpia->description }}</p>
            @endif
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <dt class="text-slate-500">Powiązana czynność</dt>
                <dd>
                    @if($dpia->processingActivity)
                        <a href="{{ route('rcp.show', $dpia->processingActivity) }}" class="text-emerald-700 hover:underline">{{ $dpia->processingActivity->name }}</a>
                    @else — @endif
                </dd>
                <dt class="text-slate-500">Data oceny</dt>
                <dd>{{ $dpia->assessment_date?->format('Y-m-d') ?? '—' }}</dd>
                <dt class="text-slate-500">Przeprowadzona przez</dt>
                <dd>{{ $dpia->conductedBy?->name ?? '—' }}</dd>
                <dt class="text-slate-500">Ryzyko szczątkowe</dt>
                <dd>
                    @if($dpia->overall_risk_level)
                        <span class="px-2 py-0.5 rounded text-xs {{ $riskBg }}">{{ $dpia->riskLevelLabel() }}</span>
                    @else — @endif
                </dd>
            </dl>
        </div>

        @if($dpia->necessity_assessment || $dpia->proportionality_assessment)
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Niezbędność i proporcjonalność</h2>
            @if($dpia->necessity_assessment)
                <h3 class="text-sm font-medium text-slate-600 mb-1">Ocena niezbędności</h3>
                <p class="text-sm text-slate-700 whitespace-pre-line mb-3">{{ $dpia->necessity_assessment }}</p>
            @endif
            @if($dpia->proportionality_assessment)
                <h3 class="text-sm font-medium text-slate-600 mb-1">Ocena proporcjonalności</h3>
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $dpia->proportionality_assessment }}</p>
            @endif
        </div>
        @endif

        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Konsultacja DPO</h2>
            <div class="flex items-center gap-2 text-sm mb-2">
                <span class="{{ $dpia->dpo_consulted ? 'text-emerald-600' : 'text-slate-400' }}">
                    {{ $dpia->dpo_consulted ? '✓ DPO skonsultowany' : '✗ Brak konsultacji DPO' }}
                </span>
                @if($dpia->dpo_consulted_at)
                    <span class="text-slate-500">— {{ $dpia->dpo_consulted_at->format('Y-m-d') }}</span>
                @endif
            </div>
            @if($dpia->dpo_opinion)
                <p class="text-sm text-slate-700 bg-slate-50 rounded p-3">{{ $dpia->dpo_opinion }}</p>
            @endif

            @if($dpia->authority_consultation_required)
            <div class="mt-3 pt-3 border-t border-slate-100">
                <div class="flex items-center gap-2 text-sm mb-2 text-amber-700 font-medium">
                    Art. 36 — Konsultacja z organem nadzorczym (UODO)
                </div>
                @if($dpia->authority_consulted_at)
                    <div class="text-sm text-slate-600 mb-1">Data konsultacji: {{ $dpia->authority_consulted_at->format('Y-m-d') }}</div>
                @endif
                @if($dpia->authority_response)
                    <p class="text-sm text-slate-700 bg-amber-50 rounded p-3">{{ $dpia->authority_response }}</p>
                @endif
            </div>
            @endif
        </div>

        @if($dpia->review_notes)
        <div class="bg-white rounded shadow p-5">
            <h2 class="font-semibold text-slate-700 mb-2">Notatki z przeglądu</h2>
            <p class="text-sm text-slate-700">{{ $dpia->review_notes }}</p>
        </div>
        @endif
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded shadow p-4 text-xs text-slate-500 space-y-1.5">
            <div>Kod: <span class="font-mono text-slate-700">{{ $dpia->code }}</span></div>
            <div>Data oceny: <span class="text-slate-700">{{ $dpia->assessment_date?->format('Y-m-d') ?? '—' }}</span></div>
            @if($dpia->isApproved())
                <div>Zatwierdził: <span class="text-slate-700">{{ $dpia->reviewer?->name ?? '—' }}</span></div>
                <div>Data zatwierdzenia: <span class="text-slate-700">{{ $dpia->reviewed_at?->format('Y-m-d H:i') }}</span></div>
            @endif
            <div>Utworzono: {{ $dpia->created_at->format('Y-m-d H:i') }}</div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded p-3 text-xs text-blue-800">
            <strong>Art. 35 RODO:</strong> DPIA jest wymagana, gdy przetwarzanie może powodować wysokie ryzyko dla praw i wolności osób fizycznych, w szczególności przy użyciu nowych technologii.
        </div>
    </div>
</div>
@endsection
