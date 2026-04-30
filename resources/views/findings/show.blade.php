@extends('layouts.app')
@section('content')
<div class="text-xs font-mono text-slate-500">{{ $finding->code }}</div>
<h1 class="text-2xl font-semibold mb-4">{{ $finding->title }}</h1>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-3">Profil</h2>
        <dl class="grid grid-cols-2 gap-y-2 text-sm">
            <dt class="text-slate-500">Severity</dt><dd>{{ $finding->severity }}</dd>
            <dt class="text-slate-500">Source</dt><dd>{{ $finding->source }}</dd>
            <dt class="text-slate-500">Engagement</dt><dd>{{ $finding->engagement?->code ?? '—' }}</dd>
            <dt class="text-slate-500">Framework ref</dt><dd>{{ $finding->framework_reference ?? '—' }}</dd>
            <dt class="text-slate-500">Linked control</dt><dd>{{ $finding->control?->code ?? '—' }}</dd>
            <dt class="text-slate-500">Linked risk</dt><dd>{{ $finding->risk?->code ?? '—' }}</dd>
            <dt class="text-slate-500">Discovered</dt><dd>{{ $finding->discovered_at?->format('Y-m-d') }}</dd>
            <dt class="text-slate-500">Due</dt><dd>{{ $finding->due_date?->format('Y-m-d') ?? '—' }}</dd>
            <dt class="text-slate-500">Status</dt><dd>{{ $finding->status }}</dd>
            @if($finding->verified_at)
                <dt class="text-slate-500">Verified</dt><dd>{{ $finding->verifier?->name ?? '?' }} · {{ $finding->verified_at?->format('Y-m-d') }}</dd>
            @endif
        </dl>
        <div class="mt-3 pt-3 border-t border-slate-100">
            <h3 class="font-medium text-sm mb-1">Opis</h3>
            <p class="text-sm whitespace-pre-line">{{ $finding->description }}</p>
        </div>
    </div>

    <div class="bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-3">Closure / Verification</h2>
        @if($finding->status === 'Verified' || $finding->status === 'Closed')
            <p class="text-emerald-700 text-sm">Finding zamknięty.</p>
            @if($finding->evidence_of_closure)
                <div class="mt-2 text-sm whitespace-pre-line">{{ $finding->evidence_of_closure }}</div>
            @endif
        @else
            <form method="POST" action="{{ route('findings.close', $finding) }}" class="space-y-2">
                @csrf
                <textarea name="evidence_of_closure" required rows="4" placeholder="Opisz evidence of closure (link do tickeu, ścieżka do evidence, wynik testu)" class="w-full px-2 py-1 border border-slate-300 rounded text-sm"></textarea>
                <button class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">Zatwierdź zamknięcie</button>
            </form>
        @endif
    </div>
</div>
@endsection
