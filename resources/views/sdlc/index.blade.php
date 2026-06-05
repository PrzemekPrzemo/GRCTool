@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Projekty AppSec / SDLC</h1>
    @can('sdlc.create')
    <a href="{{ route('sdlc.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">+ Nowy projekt AppSec</a>
    @endcan
</div>

<form method="GET" class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4 flex flex-wrap gap-2 items-end">
    <div>
        <label class="block text-xs text-slate-500 mb-1">Status</label>
        <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
            <option value="">— Wszystkie —</option>
            @foreach(\App\Models\SdlcProject::STATUS_LABELS as $val => $label)
            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Poziom ryzyka</label>
        <select name="risk_level" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
            <option value="">— Wszystkie —</option>
            @foreach(\App\Models\SdlcProject::RISK_LABELS as $val => $label)
            <option value="{{ $val }}" @selected(request('risk_level') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Zespół</label>
        <input type="text" name="team" value="{{ request('team') }}" placeholder="Szukaj zespołu..." class="px-3 py-1.5 border border-slate-300 rounded text-sm w-40">
    </div>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
    @if(request()->hasAny(['status','risk_level','team']))
    <a href="{{ route('sdlc.index') }}" class="px-3 py-1.5 bg-slate-200 rounded text-sm">Wyczyść</a>
    @endif
</form>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 border-b border-slate-200">
            <tr>
                <th class="px-4 py-3">Kod</th>
                <th class="px-4 py-3">Nazwa</th>
                <th class="px-4 py-3">Zespół</th>
                <th class="px-4 py-3">Typ</th>
                <th class="px-4 py-3">Ryzyko</th>
                <th class="px-4 py-3">Gate</th>
                <th class="px-4 py-3">Właściciel</th>
                <th class="px-4 py-3">Akcje</th>
            </tr>
        </thead>
        <tbody>
            @forelse($projects as $project)
            @php
                $riskColor = \App\Models\SdlcProject::RISK_COLORS[$project->risk_level] ?? 'slate';
                $riskLabel = \App\Models\SdlcProject::RISK_LABELS[$project->risk_level] ?? '—';
                $typeLabel = \App\Models\SdlcProject::TYPE_LABELS[$project->project_type] ?? $project->project_type;
                $gateStatus = $project->overallGateStatus();
                $gateDot = match($gateStatus) {
                    'failed'  => 'bg-red-500',
                    'pending' => 'bg-amber-400',
                    'waived'  => 'bg-slate-400',
                    'passed'  => 'bg-emerald-500',
                    default   => 'bg-slate-300',
                };
                $gateLabel = match($gateStatus) {
                    'failed'  => 'Nieudane',
                    'pending' => 'Oczekuje',
                    'waived'  => 'Zwolnione',
                    'passed'  => 'Zaliczone',
                    default   => '—',
                };
                $statusBg = ['active'=>'bg-emerald-100 text-emerald-700','completed'=>'bg-blue-100 text-blue-700','archived'=>'bg-slate-100 text-slate-600'][$project->status] ?? 'bg-slate-100';
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3">
                    <a href="{{ route('sdlc.show', $project) }}" class="font-mono text-xs text-emerald-700 hover:underline">{{ $project->code }}</a>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('sdlc.show', $project) }}" class="font-medium hover:underline">{{ $project->name }}</a>
                    <div class="text-xs text-slate-400 mt-0.5">{{ $project->tech_stack }}</div>
                </td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $project->team ?? '—' }}</td>
                <td class="px-4 py-3">
                    <span class="px-1.5 py-0.5 rounded text-xs bg-slate-100 text-slate-700">{{ $typeLabel }}</span>
                </td>
                <td class="px-4 py-3">
                    @if($project->risk_level)
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $riskColor }}-100 text-{{ $riskColor }}-700">{{ $riskLabel }}</span>
                    @else
                    <span class="text-slate-400 text-xs">—</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $gateDot }} flex-shrink-0"></span>
                        <span class="text-xs text-slate-600">{{ $gateLabel }}</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $project->owner?->name ?? '—' }}</td>
                <td class="px-4 py-3">
                    <div class="flex gap-2">
                        <a href="{{ route('sdlc.show', $project) }}" class="text-xs text-emerald-600 hover:underline">Podgląd</a>
                        @can('sdlc.update')
                        <a href="{{ route('sdlc.edit', $project) }}" class="text-xs text-slate-500 hover:underline">Edytuj</a>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-12 text-center">
                    <div class="text-slate-400 mb-2">Brak projektów AppSec</div>
                    @can('sdlc.create')
                    <a href="{{ route('sdlc.create') }}" class="text-sm text-emerald-600 hover:underline">Utwórz pierwszy projekt</a>
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $projects->links() }}</div>

@endsection
