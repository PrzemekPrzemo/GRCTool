@extends('layouts.app')
@section('content')
<div class="flex items-start justify-between mb-4">
    <div>
        <div class="text-xs font-mono text-slate-500">{{ $vulnerability->cve_id ?? $vulnerability->code }}</div>
        <h1 class="text-2xl font-semibold">{{ $vulnerability->title }}</h1>
    </div>
    @if(in_array($vulnerability->status, ['Open','In Progress','Reopened']))
    <form method="POST" action="{{ route('vulnerabilities.close', $vulnerability) }}">@csrf
        <button class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">Zamknij</button>
    </form>
    @endif
</div>
<div class="bg-white rounded shadow p-6">
    <dl class="grid grid-cols-2 md:grid-cols-3 gap-y-2 text-sm">
        <dt class="text-slate-500">Severity</dt><dd>{{ $vulnerability->severity }}</dd>
        <dt class="text-slate-500">CVSS</dt><dd>{{ $vulnerability->cvss_score ?? '—' }} {{ $vulnerability->cvss_vector }}</dd>
        <dt class="text-slate-500">Source</dt><dd>{{ $vulnerability->source }}</dd>
        <dt class="text-slate-500">Discovered</dt><dd>{{ $vulnerability->discovered_at?->format('Y-m-d') }}</dd>
        <dt class="text-slate-500">Due (SLA)</dt><dd class="{{ $vulnerability->isOverdue() ? 'text-red-700 font-bold' : '' }}">{{ $vulnerability->due_date?->format('Y-m-d') }}</dd>
        <dt class="text-slate-500">Status</dt><dd>{{ $vulnerability->status }}</dd>
    </dl>
    @if($vulnerability->description)
    <div class="mt-4 pt-4 border-t border-slate-100">
        <h3 class="font-medium text-sm mb-1">Opis</h3>
        <p class="text-sm whitespace-pre-line">{{ $vulnerability->description }}</p>
    </div>
    @endif
    <div class="mt-4 pt-4 border-t border-slate-100">
        <h3 class="font-medium text-sm mb-1">Dotknięte aktywa</h3>
        <ul class="text-sm">
            @forelse($vulnerability->assets as $a)
                <li><a href="{{ route('assets.show', $a) }}" class="text-emerald-700 hover:underline">{{ $a->code }} — {{ $a->name }}</a></li>
            @empty
                <li class="text-slate-500">Brak.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
