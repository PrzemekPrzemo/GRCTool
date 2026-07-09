@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">Luki compliance</h1>
        <p class="text-sm text-slate-500 mt-0.5">Zidentyfikowane luki wymagające remediacji, z bazy wiedzy polityk TSH</p>
    </div>
    <a href="{{ route('compliance.coverage') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
        ← Pokrycie regulacyjne
    </a>
</div>

<form method="GET" class="flex gap-2 mb-4">
    <select name="severity" class="text-sm border border-slate-300 rounded px-2 py-1.5" onchange="this.form.submit()">
        <option value="">Wszystkie priorytety</option>
        <option value="critical" @selected(request('severity') === 'critical')>Critical</option>
        <option value="high" @selected(request('severity') === 'high')>High</option>
    </select>
    <select name="status" class="text-sm border border-slate-300 rounded px-2 py-1.5" onchange="this.form.submit()">
        <option value="">Wszystkie statusy</option>
        <option value="open" @selected(request('status') === 'open')>Open</option>
        <option value="in_progress" @selected(request('status') === 'in_progress')>In progress</option>
        <option value="closed" @selected(request('status') === 'closed')>Closed</option>
    </select>
</form>

@if($gaps->isEmpty())
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
    <p class="text-slate-400">Brak luk spełniających kryteria</p>
</div>
@else
<div class="space-y-3">
    @foreach($gaps as $gap)
    @php
        $sevColor = $gap->severity === 'critical' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800';
        $statusColor = match($gap->status) {
            'closed' => 'bg-emerald-100 text-emerald-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            default => 'bg-slate-100 text-slate-600',
        };
    @endphp
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="flex items-start justify-between gap-3 flex-wrap">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-xs text-slate-500">{{ $gap->gap_code }}</span>
                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $sevColor }}">{{ $gap->severity }}</span>
                    <span class="px-2 py-0.5 rounded text-xs {{ $statusColor }}">{{ $gap->status }}</span>
                    @if($gap->isOverdue())
                        <span class="px-2 py-0.5 rounded text-xs bg-red-600 text-white">po terminie</span>
                    @endif
                </div>
                <h3 class="font-semibold text-slate-900">{{ $gap->title }}</h3>
                <p class="text-sm text-slate-600 mt-1">{{ $gap->description }}</p>
                @if($gap->remediation)
                <p class="text-xs text-slate-500 mt-2"><span class="font-medium">Remediacja:</span> {{ $gap->remediation }}</p>
                @endif
                <div class="flex flex-wrap gap-1 mt-2">
                    @foreach($gap->affected_frameworks ?? [] as $fw)
                        <span class="px-1.5 py-0.5 rounded text-[11px] bg-blue-50 text-blue-700 font-mono">{{ $fw }}</span>
                    @endforeach
                </div>
            </div>
            <div class="text-right text-xs text-slate-500 shrink-0">
                <div>Termin</div>
                <div class="font-medium {{ $gap->isOverdue() ? 'text-red-700' : 'text-slate-700' }}">
                    {{ $gap->target_date?->format('Y-m-d') ?? '—' }}
                </div>
                @can('compliance.update')
                <form method="POST" action="{{ route('compliance.gaps.status', $gap) }}" class="mt-2">
                    @csrf
                    <select name="status" class="text-xs border border-slate-300 rounded px-1 py-1" onchange="this.form.submit()">
                        <option value="open" @selected($gap->status === 'open')>Open</option>
                        <option value="in_progress" @selected($gap->status === 'in_progress')>In progress</option>
                        <option value="closed" @selected($gap->status === 'closed')>Closed</option>
                    </select>
                </form>
                @endcan
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
