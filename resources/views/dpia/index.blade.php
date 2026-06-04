@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">DPIA — Oceny Skutków dla Ochrony Danych</h1>
        <p class="text-sm text-slate-500 mt-1">Art. 35 RODO — Data Protection Impact Assessment</p>
    </div>
    @can('dpia.create')
        <a href="{{ route('dpias.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">+ Nowa DPIA</a>
    @endcan
</div>

<form method="GET" class="bg-white rounded shadow p-3 mb-4 flex gap-2">
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Status —</option>
        <option value="draft" @selected(request('status')==='draft')>Roboczy</option>
        <option value="in_review" @selected(request('status')==='in_review')>W przeglądzie</option>
        <option value="approved" @selected(request('status')==='approved')>Zatwierdzona</option>
        <option value="rejected" @selected(request('status')==='rejected')>Odrzucona</option>
    </select>
    <select name="risk_level" class="px-3 py-1.5 border border-slate-300 rounded text-sm">
        <option value="">— Ryzyko szczątkowe —</option>
        <option value="low" @selected(request('risk_level')==='low')>Niskie</option>
        <option value="medium" @selected(request('risk_level')==='medium')>Średnie</option>
        <option value="high" @selected(request('risk_level')==='high')>Wysokie</option>
        <option value="very_high" @selected(request('risk_level')==='very_high')>Bardzo wysokie</option>
    </select>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm">Filtruj</button>
</form>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Kod</th>
                <th class="px-3 py-2">Tytuł DPIA</th>
                <th class="px-3 py-2">Czynność przetwarzania</th>
                <th class="px-3 py-2">Data oceny</th>
                <th class="px-3 py-2">Ryzyko</th>
                <th class="px-3 py-2">Konsultacja DPO</th>
                <th class="px-3 py-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dpias as $d)
            @php
                $riskBg = match($d->overall_risk_level) {
                    'very_high' => 'bg-red-600 text-white',
                    'high'      => 'bg-orange-500 text-white',
                    'medium'    => 'bg-amber-300 text-amber-900',
                    'low'       => 'bg-emerald-100 text-emerald-800',
                    default     => 'bg-slate-100 text-slate-600',
                };
                $statusBg = match($d->status) {
                    'draft'     => 'bg-yellow-100 text-yellow-800',
                    'in_review' => 'bg-blue-100 text-blue-800',
                    'approved'  => 'bg-emerald-100 text-emerald-800',
                    'rejected'  => 'bg-red-100 text-red-800',
                    default     => 'bg-slate-100 text-slate-600',
                };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('dpias.show', $d) }}" class="text-emerald-700 hover:underline">{{ $d->code }}</a>
                </td>
                <td class="px-3 py-2">{{ $d->title }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">
                    @if($d->processingActivity)
                        <a href="{{ route('rcp.show', $d->processingActivity) }}" class="text-emerald-700 hover:underline">{{ $d->processingActivity->name }}</a>
                    @else —
                    @endif
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $d->assessment_date?->format('Y-m-d') ?? '—' }}</td>
                <td class="px-3 py-2">
                    @if($d->overall_risk_level)
                        <span class="px-2 py-0.5 rounded text-xs {{ $riskBg }}">{{ $d->riskLevelLabel() }}</span>
                    @else <span class="text-slate-400">—</span> @endif
                </td>
                <td class="px-3 py-2 text-center text-xs">
                    {{ $d->dpo_consulted ? '✓ Tak' : '—' }}
                </td>
                <td class="px-3 py-2">
                    <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">
                        {{ match($d->status) { 'draft' => 'Roboczy', 'in_review' => 'W przeglądzie', 'approved' => 'Zatwierdzona', 'rejected' => 'Odrzucona', default => $d->status } }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-3 py-8 text-center text-slate-500">
                    Brak przeprowadzonych ocen DPIA.
                    @can('dpia.create')
                        <a href="{{ route('dpias.create') }}" class="text-emerald-600 hover:underline ml-1">Przeprowadź pierwszą DPIA</a>.
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $dpias->links() }}</div>
@endsection
