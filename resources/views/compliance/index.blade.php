@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Oceny Compliance</h1>
        <p class="text-sm text-slate-500 mt-0.5">Zarządzaj ocenami zgodności z normami i regulacjami</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('compliance.frameworks') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
            Frameworki
        </a>
        @can('compliance.create')
        <a href="{{ route('compliance.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
            + Nowa ocena
        </a>
        @endcan
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-slate-500 mb-1">Framework</label>
        <select name="framework_id" class="px-3 py-1.5 border border-slate-300 rounded text-sm bg-white">
            <option value="">— Wszystkie —</option>
            @foreach($frameworks as $fw)
            <option value="{{ $fw->id }}" @selected(request('framework_id') == $fw->id)>{{ $fw->short_name }} — {{ $fw->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Status</label>
        <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm bg-white">
            <option value="">— Wszystkie —</option>
            @foreach(\App\Models\ComplianceAssessment::STATUS_LABELS as $val => $label)
            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm hover:bg-slate-700">Filtruj</button>
    @if(request('framework_id') || request('status'))
    <a href="{{ route('compliance.index') }}" class="px-3 py-1.5 bg-slate-200 rounded text-sm hover:bg-slate-300">Wyczyść</a>
    @endif
</form>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 border-b border-slate-200">
            <tr>
                <th class="px-4 py-3">Kod</th>
                <th class="px-4 py-3">Framework</th>
                <th class="px-4 py-3">Tytuł</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Wynik</th>
                <th class="px-4 py-3">Data</th>
                <th class="px-4 py-3">Wykonawca</th>
                <th class="px-4 py-3">Akcje</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assessments as $a)
            @php
                $statusBadge = [
                    'draft'       => 'bg-slate-100 text-slate-600',
                    'in_progress' => 'bg-blue-100 text-blue-700',
                    'completed'   => 'bg-emerald-100 text-emerald-700',
                    'archived'    => 'bg-slate-200 text-slate-500',
                ][$a->status] ?? 'bg-slate-100 text-slate-600';
                $statusLabel = \App\Models\ComplianceAssessment::STATUS_LABELS[$a->status] ?? $a->status;
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3">
                    <a href="{{ route('compliance.show', $a) }}" class="font-mono text-xs text-emerald-700 hover:underline">{{ $a->code }}</a>
                    @if($a->is_published)
                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700">SoA</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-slate-900 text-slate-100">{{ $a->framework?->short_name }}</span>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('compliance.show', $a) }}" class="font-medium hover:underline">{{ $a->title }}</a>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadge }}">{{ $statusLabel }}</span>
                </td>
                <td class="px-4 py-3">
                    @if($a->overall_score !== null)
                    <div class="flex items-center gap-2">
                        <div class="w-20 bg-slate-200 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full {{ $a->overall_score >= 80 ? 'bg-emerald-500' : ($a->overall_score >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                                 style="width: {{ $a->overall_score }}%"></div>
                        </div>
                        <span class="text-xs font-medium text-slate-700">{{ number_format($a->overall_score, 1) }}%</span>
                    </div>
                    @else
                    <span class="text-xs text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $a->assessment_date?->format('Y-m-d') ?? '—' }}</td>
                <td class="px-4 py-3 text-xs text-slate-600">{{ $a->conductedBy?->name ?? '—' }}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('compliance.show', $a) }}" class="text-xs text-emerald-600 hover:underline">Podgląd</a>
                        @if(in_array($a->status, ['draft', 'in_progress']))
                        <a href="{{ route('compliance.respond', $a) }}" class="text-xs text-blue-600 hover:underline">Odpowiedz</a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-12 text-center">
                    <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-slate-400 mb-2">Brak ocen compliance</p>
                    @can('compliance.create')
                    <a href="{{ route('compliance.create') }}" class="text-sm text-emerald-600 hover:underline">Utwórz pierwszą ocenę</a>
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $assessments->links() }}</div>

@endsection
