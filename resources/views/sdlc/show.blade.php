@extends('layouts.app')
@section('content')

@php
    $riskColor = \App\Models\SdlcProject::RISK_COLORS[$sdlc->risk_level] ?? 'slate';
    $riskLabel = \App\Models\SdlcProject::RISK_LABELS[$sdlc->risk_level] ?? '—';
    $gateStatus = $sdlc->overallGateStatus();
    $gateColors = ['failed'=>'red','pending'=>'amber','waived'=>'slate','passed'=>'emerald'];
    $gateLabels = ['failed'=>'Nieudane','pending'=>'Oczekuje','waived'=>'Zwolnione','passed'=>'Zaliczone'];
    $gateColor = $gateColors[$gateStatus] ?? 'slate';
    $gateLabel = $gateLabels[$gateStatus] ?? '—';

    $phaseLabels = \App\Models\SdlcSecurityGate::PHASE_LABELS;
    $gateTypeLabels = \App\Models\SdlcSecurityGate::GATE_TYPE_LABELS;
    $gateStatusLabels = \App\Models\SdlcSecurityGate::STATUS_LABELS;
@endphp

<div class="mb-4">
    <a href="{{ route('sdlc.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Projekty SDLC</a>
</div>

{{-- Header --}}
<div class="flex items-start justify-between mb-4">
    <div>
        <span class="font-mono text-xs text-slate-500">{{ $sdlc->code }}</span>
        <h1 class="text-2xl font-semibold">{{ $sdlc->name }}</h1>
        <div class="flex items-center gap-2 mt-1">
            @if($sdlc->risk_level)
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $riskColor }}-100 text-{{ $riskColor }}-700">Ryzyko: {{ $riskLabel }}</span>
            @endif
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $gateColor }}-100 text-{{ $gateColor }}-700">Gate: {{ $gateLabel }}</span>
        </div>
    </div>
    @can('sdlc.update')
    <a href="{{ route('sdlc.edit', $sdlc) }}" class="px-3 py-1.5 bg-slate-100 rounded-lg text-sm hover:bg-slate-200 transition-colors">Edytuj</a>
    @endcan
</div>

<div class="grid grid-cols-3 gap-4">
    <div class="col-span-2 space-y-4">

        {{-- Project details --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="font-semibold text-sm text-slate-700 mb-3">Szczegóły projektu</h2>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-slate-500">Zespół:</span> {{ $sdlc->team ?? '—' }}</div>
                <div><span class="text-slate-500">Typ:</span> {{ \App\Models\SdlcProject::TYPE_LABELS[$sdlc->project_type] ?? $sdlc->project_type }}</div>
                <div><span class="text-slate-500">Właściciel:</span> {{ $sdlc->owner?->name ?? '—' }}</div>
                <div><span class="text-slate-500">Stack:</span> {{ $sdlc->tech_stack ?? '—' }}</div>
                @if($sdlc->repo_url)
                <div class="col-span-2"><span class="text-slate-500">Repo:</span> <a href="{{ $sdlc->repo_url }}" target="_blank" class="text-emerald-600 hover:underline break-all">{{ $sdlc->repo_url }}</a></div>
                @endif
                @if($sdlc->prod_url)
                <div class="col-span-2"><span class="text-slate-500">Prod URL:</span> <a href="{{ $sdlc->prod_url }}" target="_blank" class="text-emerald-600 hover:underline break-all">{{ $sdlc->prod_url }}</a></div>
                @endif
            </div>
            @if($sdlc->description)
            <div class="mt-3 text-sm text-slate-600 bg-slate-50 rounded-lg p-3">{{ $sdlc->description }}</div>
            @endif
        </div>

        {{-- Security Gates Pipeline --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="font-semibold text-sm text-slate-700 mb-4">Pipeline bezpieczeństwa</h2>
            <div class="space-y-4">
                @foreach($phases as $phase)
                @php $phaseGates = $gatesByPhase->get($phase, collect()); @endphp
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="h-px bg-slate-200 flex-1"></div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide px-2">{{ $phaseLabels[$phase] ?? $phase }}</span>
                        <div class="h-px bg-slate-200 flex-1"></div>
                    </div>
                    @if($phaseGates->isEmpty())
                    <div class="text-xs text-slate-400 text-center py-1">Brak bramek w tej fazie</div>
                    @else
                    <div class="space-y-1.5">
                        @foreach($phaseGates as $gate)
                        @php
                            $gBg = ['pending'=>'bg-amber-50 border-amber-200','passed'=>'bg-emerald-50 border-emerald-200','failed'=>'bg-red-50 border-red-200','waived'=>'bg-slate-50 border-slate-200'][$gate->status] ?? 'bg-slate-50 border-slate-200';
                            $gBadge = ['pending'=>'bg-amber-100 text-amber-700','passed'=>'bg-emerald-100 text-emerald-700','failed'=>'bg-red-100 text-red-700','waived'=>'bg-slate-100 text-slate-600'][$gate->status] ?? '';
                        @endphp
                        <div class="flex items-center justify-between px-3 py-2 rounded-lg border {{ $gBg }}">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium">{{ $gateTypeLabels[$gate->gate_type] ?? $gate->gate_type }}</span>
                                @if($gate->tool)
                                <span class="text-xs text-slate-500">{{ $gate->tool }}</span>
                                @endif
                                @if($gate->critical_count > 0)
                                <span class="text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded">{{ $gate->critical_count }} kryt.</span>
                                @endif
                                @if($gate->high_count > 0)
                                <span class="text-xs bg-orange-100 text-orange-700 px-1.5 py-0.5 rounded">{{ $gate->high_count }} wys.</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                @if($gate->conductedBy)
                                <span class="text-xs text-slate-500">{{ $gate->conductedBy->name }}</span>
                                @endif
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $gBadge }}">{{ $gateStatusLabels[$gate->status] ?? $gate->status }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Add Gate form (collapsible) --}}
        @can('sdlc.update')
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5" x-data="{ open: false }">
            <button @click="open = !open" type="button"
                    class="w-full flex items-center justify-between text-sm font-semibold text-slate-700 hover:text-slate-900">
                <span>+ Dodaj bramkę bezpieczeństwa</span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-transition class="mt-4">
                <form method="POST" action="{{ route('sdlc.gate.add', $sdlc) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium mb-1">Faza *</label>
                            <select name="phase" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                                @foreach($phaseLabels as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Typ bramki *</label>
                            <select name="gate_type" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                                @foreach($gateTypeLabels as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium mb-1">Status *</label>
                            <select name="status" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                                @foreach($gateStatusLabels as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Narzędzie</label>
                            <input type="text" name="tool" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm" placeholder="np. SonarQube">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Przeprowadził</label>
                            <select name="conducted_by" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                                <option value="">— brak —</option>
                                @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 gap-2">
                        <div>
                            <label class="block text-xs font-medium mb-1 text-red-600">Krytyczne</label>
                            <input type="number" name="critical_count" value="0" min="0" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1 text-orange-600">Wysokie</label>
                            <input type="number" name="high_count" value="0" min="0" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1 text-amber-600">Średnie</label>
                            <input type="number" name="medium_count" value="0" min="0" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1 text-blue-600">Niskie</label>
                            <input type="number" name="low_count" value="0" min="0" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Podsumowanie wyników</label>
                        <textarea name="result_summary" rows="2" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Powód zwolnienia (waiver)</label>
                        <textarea name="waiver_reason" rows="1" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium mb-1">URL raportu</label>
                            <input type="url" name="report_url" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Data przeprowadzenia</label>
                            <input type="date" name="conducted_at" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                        </div>
                    </div>
                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm font-medium hover:bg-emerald-700 transition-colors">Dodaj bramkę</button>
                </form>
            </div>
        </div>
        @endcan

        {{-- Threat Models --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-sm text-slate-700">Modele zagrożeń</h2>
                <span class="text-xs text-slate-400">{{ $sdlc->threatModels->count() }}</span>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 text-left">
                    <tr>
                        <th class="px-4 py-2">Tytuł</th>
                        <th class="px-4 py-2">Metodologia</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Zagrożenia</th>
                        <th class="px-4 py-2">Przeprowadził</th>
                        <th class="px-4 py-2">Przegląd</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sdlc->threatModels as $tm)
                    @php
                        $tmBadge = ['draft'=>'bg-slate-100 text-slate-600','in_review'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700'][$tm->status] ?? '';
                        $pct = $tm->threats_identified > 0 ? round($tm->threats_mitigated / $tm->threats_identified * 100) : 0;
                    @endphp
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2 font-medium">{{ $tm->title }}</td>
                        <td class="px-4 py-2">
                            <span class="px-1.5 py-0.5 rounded text-xs bg-blue-100 text-blue-700">{{ \App\Models\SdlcThreatModel::METHODOLOGY_LABELS[$tm->methodology] ?? $tm->methodology }}</span>
                        </td>
                        <td class="px-4 py-2">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $tmBadge }}">{{ \App\Models\SdlcThreatModel::STATUS_LABELS[$tm->status] ?? $tm->status }}</span>
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-2">
                                <div class="w-16 bg-slate-200 rounded-full h-1.5">
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-slate-500">{{ $tm->threats_mitigated }}/{{ $tm->threats_identified }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-2 text-xs text-slate-600">{{ $tm->conductedBy?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-xs text-slate-600">{{ $tm->reviewed_at?->format('Y-m-d') ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400 text-sm">Brak modeli zagrożeń</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Add Threat Model form (collapsible) --}}
        @can('sdlc.update')
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5" x-data="{ open: false }">
            <button @click="open = !open" type="button"
                    class="w-full flex items-center justify-between text-sm font-semibold text-slate-700 hover:text-slate-900">
                <span>+ Dodaj model zagrożeń</span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-transition class="mt-4">
                <form method="POST" action="{{ route('sdlc.threat_model.add', $sdlc) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium mb-1">Tytuł *</label>
                        <input type="text" name="title" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium mb-1">Metodologia *</label>
                            <select name="methodology" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                                @foreach(\App\Models\SdlcThreatModel::METHODOLOGY_LABELS as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Status *</label>
                            <select name="status" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                                @foreach(\App\Models\SdlcThreatModel::STATUS_LABELS as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Przeprowadził</label>
                            <select name="conducted_by" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                                <option value="">— brak —</option>
                                @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium mb-1">Zagrożeń zidentyfikowanych</label>
                            <input type="number" name="threats_identified" value="0" min="0" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Zagrożeń zmitigowanych</label>
                            <input type="number" name="threats_mitigated" value="0" min="0" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Przejrzał</label>
                            <select name="reviewed_by" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                                <option value="">— brak —</option>
                                @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Data przeglądu</label>
                            <input type="date" name="reviewed_at" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">URL dokumentu</label>
                        <input type="url" name="document_url" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Notatki</label>
                        <textarea name="notes" rows="2" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm"></textarea>
                    </div>
                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm font-medium hover:bg-emerald-700 transition-colors">Dodaj model zagrożeń</button>
                </form>
            </div>
        </div>
        @endcan

    </div>

    {{-- Right sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h2 class="font-semibold text-sm text-slate-700 mb-3">Podsumowanie</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Bramki ogółem</span>
                    <span class="font-medium">{{ $sdlc->gates->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Zaliczone</span>
                    <span class="font-medium text-emerald-600">{{ $sdlc->gates->where('status','passed')->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Nieudane</span>
                    <span class="font-medium text-red-600">{{ $sdlc->gates->where('status','failed')->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Oczekujące</span>
                    <span class="font-medium text-amber-600">{{ $sdlc->gates->where('status','pending')->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Modele zagrożeń</span>
                    <span class="font-medium">{{ $sdlc->threatModels->count() }}</span>
                </div>
            </div>
        </div>

        @if($sdlc->notes)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h2 class="font-semibold text-sm text-slate-700 mb-2">Notatki</h2>
            <p class="text-sm text-slate-600">{{ $sdlc->notes }}</p>
        </div>
        @endif
    </div>
</div>

@endsection
