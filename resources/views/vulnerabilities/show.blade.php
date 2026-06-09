@extends('layouts.app')
@section('content')

@php
$sevBg  = ['Critical'=>'bg-red-600 text-white','High'=>'bg-orange-500 text-white','Medium'=>'bg-amber-300 text-amber-900','Low'=>'bg-emerald-200 text-emerald-800','Info'=>'bg-slate-200 text-slate-700'][$vulnerability->severity] ?? 'bg-slate-200';
$sla    = $vulnerability->slaStatus();
$slaBadge = match($sla) { 'breach' => 'bg-red-100 text-red-800', 'warning' => 'bg-amber-100 text-amber-800', default => 'bg-emerald-100 text-emerald-800' };
$slaLabel = match($sla) { 'breach' => 'SLA Przekroczony', 'warning' => 'SLA < 50%', default => 'W terminie' };
@endphp

<div class="flex items-start justify-between mb-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('vulnerabilities.index') }}" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-mono text-xs text-slate-500">{{ $vulnerability->cve_id ?? $vulnerability->code }}</span>
                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $sevBg }}">{{ $vulnerability->severity }}</span>
                @if($vulnerability->is_kev) <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded text-xs font-semibold">KEV</span> @endif
                @if($vulnerability->exploit_available) <span class="px-2 py-0.5 bg-orange-100 text-orange-800 rounded text-xs">Exploit</span> @endif
                <span class="px-2 py-0.5 rounded text-xs {{ $slaBadge }}">{{ $slaLabel }}</span>
            </div>
            <h1 class="text-xl font-semibold text-slate-800 mt-1">{{ $vulnerability->title }}</h1>
        </div>
    </div>
    <div class="flex gap-2 flex-shrink-0">
        @can('vulnerability.update')
        <a href="{{ route('vulnerabilities.edit', $vulnerability) }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm hover:bg-slate-200 transition-colors">Edytuj</a>
        @endcan
        @if(in_array($vulnerability->status, ['Open','In Progress','Reopened']))
        @can('vulnerability.update')
        <form method="POST" action="{{ route('vulnerabilities.close', $vulnerability) }}">@csrf
            <button class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 transition-colors">Zamknij</button>
        </form>
        @endcan
        @endif
    </div>
</div>

@if(session('status'))
<div class="mb-4 px-4 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('status') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-2 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    {{-- Główne dane --}}
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-700 mb-4">Szczegóły</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <dt class="text-slate-500">Status</dt><dd class="font-medium">{{ $vulnerability->status }}</dd>
                <dt class="text-slate-500">Źródło</dt><dd>{{ $vulnerability->source_type }} {{ $vulnerability->source ? '('.$vulnerability->source.')' : '' }}</dd>
                <dt class="text-slate-500">CVSS</dt><dd class="font-mono">{{ $vulnerability->cvss_score ?? '—' }} {{ $vulnerability->cvss_vector ? '| '.$vulnerability->cvss_vector : '' }}</dd>
                <dt class="text-slate-500">EPSS</dt><dd>{{ $vulnerability->epss_score ? number_format($vulnerability->epss_score * 100, 2).'%' : '—' }}</dd>
                <dt class="text-slate-500">OWASP</dt><dd class="font-mono">
                    @if($vulnerability->owasp_category)
                        {{ $vulnerability->owasp_category }} – {{ \App\Models\Vulnerability::OWASP_CATEGORIES[$vulnerability->owasp_category] ?? '' }}
                    @else —
                    @endif
                </dd>
                <dt class="text-slate-500">CWE</dt><dd class="font-mono">{{ $vulnerability->cwe_id ?? '—' }}</dd>
                <dt class="text-slate-500">Wykryto</dt><dd>{{ $vulnerability->discovered_at?->format('d.m.Y') }}</dd>
                <dt class="text-slate-500">SLA deadline</dt>
                <dd class="{{ $vulnerability->isOverdue() ? 'text-red-700 font-bold' : '' }}">
                    {{ $vulnerability->due_date?->format('d.m.Y') ?? '—' }}
                    ({{ $vulnerability->daysOpen() }} dni od wykrycia)
                </dd>
                @if($vulnerability->closed_at)
                <dt class="text-slate-500">Zamknięto</dt><dd>{{ $vulnerability->closed_at->format('d.m.Y') }}</dd>
                @endif
                <dt class="text-slate-500">Właściciel</dt><dd>{{ $vulnerability->owner?->name ?? '—' }}</dd>
                @if($vulnerability->jira_ticket)
                <dt class="text-slate-500">Jira</dt><dd class="font-mono">{{ $vulnerability->jira_ticket }}</dd>
                @endif
            </dl>

            @if($vulnerability->repeatOf)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <div class="flex items-center gap-2 px-3 py-2 bg-amber-50 border border-amber-200 rounded-lg text-sm">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    <span class="text-amber-800">Powtórzenie podatności: </span>
                    <a href="{{ route('vulnerabilities.show', $vulnerability->repeatOf) }}" class="text-emerald-700 hover:underline font-mono">{{ $vulnerability->repeatOf->cve_id ?? $vulnerability->repeatOf->code }}</a>
                </div>
            </div>
            @endif

            @if($vulnerability->description)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <h3 class="font-medium text-sm text-slate-700 mb-2">Opis</h3>
                <p class="text-sm text-slate-600 whitespace-pre-line">{{ $vulnerability->description }}</p>
            </div>
            @endif

            @if($vulnerability->remediation_notes)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <h3 class="font-medium text-sm text-slate-700 mb-2">Plan remediacji</h3>
                <p class="text-sm text-slate-600 whitespace-pre-line">{{ $vulnerability->remediation_notes }}</p>
            </div>
            @endif
        </div>

        {{-- Dotknięte aktywa --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Dotknięte aktywa</h2>
            @forelse($vulnerability->assets as $a)
            <div class="flex items-center gap-3 py-1.5 border-b border-slate-100 last:border-0 text-sm">
                <a href="{{ route('assets.show', $a) }}" class="text-emerald-700 hover:underline font-mono text-xs">{{ $a->code }}</a>
                <span class="text-slate-700">{{ $a->name }}</span>
                <span class="ml-auto text-xs text-slate-400">{{ $a->pivot->status }}</span>
            </div>
            @empty
            <p class="text-sm text-slate-400">Brak powiązanych aktywów.</p>
            @endforelse
        </div>

        {{-- Repeat findings --}}
        @if($vulnerability->repeatFindings->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-5">
            <h2 class="font-semibold text-amber-700 mb-3">Ponowne wystąpienia tej podatności ({{ $vulnerability->repeatFindings->count() }})</h2>
            @foreach($vulnerability->repeatFindings as $rf)
            <div class="flex items-center gap-3 text-sm py-1.5 border-b border-slate-100 last:border-0">
                <a href="{{ route('vulnerabilities.show', $rf) }}" class="font-mono text-xs text-emerald-700 hover:underline">{{ $rf->cve_id ?? $rf->code }}</a>
                <span class="text-slate-700">{{ $rf->title }}</span>
                <span class="ml-auto text-xs text-slate-400">{{ $rf->discovered_at?->format('Y-m-d') }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Boczna kolumna: wyjątki --}}
    <div class="space-y-5">
        {{-- Wyjątki SLA --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Wyjątki od remediacji</h2>
            @forelse($vulnerability->exceptions as $ex)
            <div class="border border-slate-200 rounded-lg p-3 mb-3 text-xs space-y-1">
                <div class="flex items-center justify-between">
                    <span class="font-semibold">
                        @php $badge = ['Pending'=>'bg-amber-100 text-amber-800','Approved'=>'bg-emerald-100 text-emerald-800','Rejected'=>'bg-red-100 text-red-800','Expired'=>'bg-slate-200 text-slate-600','Revoked'=>'bg-slate-200 text-slate-600'][$ex->status] ?? 'bg-slate-200'; @endphp
                        <span class="px-2 py-0.5 rounded {{ $badge }}">{{ $ex->status }}</span>
                    </span>
                    <span class="text-slate-400">do {{ $ex->expiry_date?->format('Y-m-d') }}</span>
                </div>
                <p class="text-slate-600">{{ $ex->rationale }}</p>
                @if($ex->status === 'Pending' && (auth()->user()->hasRole('ciso') || auth()->user()->hasRole('admin')))
                <form method="POST" action="{{ route('vulnerabilities.exceptions.approve', $ex) }}">@csrf
                    <button class="mt-1 px-2 py-1 bg-emerald-600 text-white rounded text-xs hover:bg-emerald-700">Zatwierdź</button>
                </form>
                @endif
            </div>
            @empty
            <p class="text-xs text-slate-400">Brak wyjątków.</p>
            @endforelse

            @can('vulnerability.update')
            @if(in_array($vulnerability->status, ['Open','In Progress','Reopened']))
            <div class="mt-4 pt-4 border-t border-slate-100" x-data="{ open: false }">
                <button @click="open = !open" class="text-xs text-emerald-700 hover:underline font-medium">+ Proponuj wyjątek</button>
                <div x-show="open" x-transition class="mt-3 space-y-2">
                    <form method="POST" action="{{ route('vulnerabilities.exceptions.propose', $vulnerability) }}" class="space-y-2">
                        @csrf
                        <textarea name="rationale" rows="2" required placeholder="Uzasadnienie..." class="w-full border border-slate-300 rounded px-2 py-1.5 text-xs focus:ring-1 focus:ring-emerald-500"></textarea>
                        <input type="date" name="expiry_date" required min="{{ now()->addDay()->format('Y-m-d') }}" class="w-full border border-slate-300 rounded px-2 py-1.5 text-xs">
                        <button class="px-3 py-1.5 bg-emerald-600 text-white rounded text-xs hover:bg-emerald-700">Wyślij</button>
                    </form>
                </div>
            </div>
            @endif
            @endcan
        </div>
    </div>
</div>

@endsection
