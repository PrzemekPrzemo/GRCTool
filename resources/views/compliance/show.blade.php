@extends('layouts.app')
@section('content')

@php
$statusBadge = [
    'draft'       => 'bg-slate-100 text-slate-600',
    'in_progress' => 'bg-blue-100 text-blue-700',
    'completed'   => 'bg-emerald-100 text-emerald-700',
    'archived'    => 'bg-slate-200 text-slate-500',
][$compliance->status] ?? 'bg-slate-100 text-slate-600';
$statusLabel = \App\Models\ComplianceAssessment::STATUS_LABELS[$compliance->status] ?? $compliance->status;

$total = $compliance->compliant_count + $compliance->partial_count + $compliance->non_compliant_count + $compliance->not_assessed_count;
$totalWithNa = $total + $compliance->na_count;
@endphp

{{-- Header --}}
<div class="flex items-start justify-between mb-6 gap-4">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <span class="font-mono text-sm text-emerald-700 font-semibold">{{ $compliance->code }}</span>
            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge }}">{{ $statusLabel }}</span>
            @if($compliance->is_published)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-600 text-white">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                SoA opublikowana
            </span>
            @endif
        </div>
        <h1 class="text-2xl font-semibold">{{ $compliance->title }}</h1>
        <div class="flex items-center gap-3 mt-1 text-sm text-slate-500">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-slate-900 text-slate-100">{{ $compliance->framework->short_name }}</span>
            <span>{{ $compliance->framework->name }}</span>
            @if($compliance->assessment_date)
            <span>·</span>
            <span>{{ $compliance->assessment_date->format('d.m.Y') }}</span>
            @endif
        </div>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
        @if(in_array($compliance->status, ['draft', 'in_progress']))
        <a href="{{ route('compliance.respond', $compliance) }}"
           class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Przejdź do odpowiedzi
        </a>
        @endif
        @can('compliance.update')
        @if($compliance->status !== 'completed' && $compliance->status !== 'archived')
        <form method="POST" action="{{ route('compliance.complete', $compliance) }}" class="inline">
            @csrf
            <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors"
                    onclick="return confirm('Zakończyć ocenę i obliczyć wynik?')">
                Finalizuj
            </button>
        </form>
        @endif
        @if($compliance->status === 'completed' && !$compliance->is_published)
        <form method="POST" action="{{ route('compliance.publish', $compliance) }}" class="inline">
            @csrf
            <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-sm font-medium hover:bg-slate-700 transition-colors"
                    onclick="return confirm('Opublikować Deklarację Stosowalności (SoA)?')">
                Opublikuj SoA
            </button>
        </form>
        @endif
        @endcan
        <div class="flex gap-2">
            <a href="{{ route('compliance.export.soa', $compliance) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                SoA
            </a>
            <a href="{{ route('compliance.export.gap', $compliance) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-700 hover:bg-red-600 text-white text-sm rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Analiza Luk
            </a>
            <a href="{{ route('compliance.export.csv', $compliance) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-700 hover:bg-emerald-600 text-white text-sm rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                CSV
            </a>
        </div>
    </div>
</div>

{{-- Score summary --}}
<div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-slate-700">Wynik ogólny</span>
            @if($compliance->overall_score !== null)
            <span class="text-2xl font-bold {{ $compliance->overall_score >= 80 ? 'text-emerald-600' : ($compliance->overall_score >= 50 ? 'text-amber-600' : 'text-red-600') }}">
                {{ number_format($compliance->overall_score, 1) }}%
            </span>
            @else
            <span class="text-2xl font-bold text-slate-400">—</span>
            @endif
        </div>
        @if($compliance->overall_score !== null)
        <div class="w-full bg-slate-200 rounded-full h-2">
            <div class="h-2 rounded-full transition-all {{ $compliance->overall_score >= 80 ? 'bg-emerald-500' : ($compliance->overall_score >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                 style="width: {{ $compliance->overall_score }}%"></div>
        </div>
        @endif
        <div class="text-xs text-slate-500 mt-2">{{ $totalWithNa }} wymagań łącznie</div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-emerald-200 p-4">
        <div class="text-2xl font-bold text-emerald-700">{{ $compliance->compliant_count }}</div>
        <div class="text-xs text-slate-500 mt-0.5">Zgodne</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-4">
        <div class="text-2xl font-bold text-amber-600">{{ $compliance->partial_count }}</div>
        <div class="text-xs text-slate-500 mt-0.5">Częściowe</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-red-200 p-4">
        <div class="text-2xl font-bold text-red-600">{{ $compliance->non_compliant_count }}</div>
        <div class="text-xs text-slate-500 mt-0.5">Niezgodne</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="text-2xl font-bold text-slate-500">{{ $compliance->na_count }}</div>
        <div class="text-xs text-slate-500 mt-0.5">Nie dotyczy</div>
    </div>
</div>

{{-- Meta info --}}
@if($compliance->scope || $compliance->conductedBy || $compliance->reviewedBy || $compliance->notes)
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
    @if($compliance->scope)
    <div>
        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Zakres</dt>
        <dd class="text-slate-700">{{ $compliance->scope }}</dd>
    </div>
    @endif
    @if($compliance->conductedBy)
    <div>
        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Przeprowadzający</dt>
        <dd class="text-slate-700">{{ $compliance->conductedBy->name }}</dd>
    </div>
    @endif
    @if($compliance->reviewedBy)
    <div>
        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Weryfikator</dt>
        <dd class="text-slate-700">{{ $compliance->reviewedBy->name }} · {{ $compliance->reviewed_at?->format('d.m.Y H:i') }}</dd>
    </div>
    @endif
    @if($compliance->notes)
    <div class="md:col-span-2">
        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Notatki</dt>
        <dd class="text-slate-700">{{ $compliance->notes }}</dd>
    </div>
    @endif
</div>
@endif

{{-- Requirements grouped by domain --}}
<div class="space-y-3">
    @foreach($compliance->framework->domains as $domain)
    @php
        $domainReqs = $domain->requirements;
        $domainCompliant = 0;
        $domainTotal = $domainReqs->count();
        foreach ($domainReqs as $req) {
            $resp = $responses->get($req->id);
            if ($resp && $resp->status === 'compliant') $domainCompliant++;
        }
        $domainPct = $domainTotal > 0 ? round($domainCompliant / $domainTotal * 100) : 0;
    @endphp
    <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <button @click="open = !open"
                class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-slate-50 transition-colors">
            <div class="flex items-center gap-3">
                <span class="font-mono text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded">{{ $domain->code }}</span>
                <span class="font-semibold text-slate-800">{{ $domain->name }}</span>
                <span class="text-xs text-slate-400">({{ $domainTotal }} wymagań)</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-24 bg-slate-200 rounded-full h-1.5 hidden sm:block">
                        <div class="h-1.5 rounded-full {{ $domainPct >= 80 ? 'bg-emerald-500' : ($domainPct >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                             style="width: {{ $domainPct }}%"></div>
                    </div>
                    <span class="text-xs text-slate-500">{{ $domainPct }}%</span>
                </div>
                <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': open }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>

        <div x-show="open" x-transition class="border-t border-slate-100">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-left text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-2 w-24">Kod</th>
                        <th class="px-4 py-2">Wymaganie</th>
                        <th class="px-4 py-2 w-32">Status</th>
                        <th class="px-4 py-2 w-20">Priorytet</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($domainReqs as $req)
                    @php
                        $resp = $responses->get($req->id);
                        $rStatus = $resp?->status ?? 'not_assessed';
                        $statusColor = match($rStatus) {
                            'compliant'      => 'bg-emerald-100 text-emerald-700',
                            'partial'        => 'bg-amber-100 text-amber-700',
                            'non_compliant'  => 'bg-red-100 text-red-700',
                            'not_applicable' => 'bg-slate-100 text-slate-500',
                            default          => 'bg-slate-50 text-slate-400',
                        };
                        $statusLabels = [
                            'compliant'      => 'Zgodne',
                            'partial'        => 'Częściowe',
                            'non_compliant'  => 'Niezgodne',
                            'not_applicable' => 'Nie dotyczy',
                            'not_assessed'   => 'Nie oceniono',
                        ];
                        $rowBg = match($rStatus) {
                            'non_compliant'  => 'bg-red-50/30',
                            'partial'        => 'bg-amber-50/30',
                            'compliant'      => 'bg-emerald-50/20',
                            default          => '',
                        };
                    @endphp
                    <tr class="border-t border-slate-100 {{ $rowBg }} hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-2.5">
                            <span class="font-mono font-medium text-slate-700">{{ $req->code }}</span>
                        </td>
                        <td class="px-4 py-2.5">
                            <div class="font-medium text-slate-800">{{ $req->name }}</div>
                            @if($resp?->gap_description)
                            <div class="text-slate-500 mt-0.5 truncate max-w-md">{{ $resp->gap_description }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium {{ $statusColor }}">
                                {{ $statusLabels[$rStatus] }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5">
                            @if($resp?->priority)
                            @php
                                $prioColor = match($resp->priority) {
                                    'high'   => 'text-red-600',
                                    'medium' => 'text-amber-600',
                                    default  => 'text-slate-500',
                                };
                                $prioLabel = ['high' => 'Wysoki', 'medium' => 'Średni', 'low' => 'Niski'][$resp->priority];
                            @endphp
                            <span class="font-medium {{ $prioColor }}">{{ $prioLabel }}</span>
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>

@endsection
