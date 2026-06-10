@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <div class="text-xs text-slate-500 font-mono">{{ $risk->code }}</div>
        <h1 class="text-2xl font-semibold">{{ $risk->title }}</h1>
        <p class="text-slate-500 text-sm mt-1">{{ $risk->category_l1 }} / {{ $risk->category_l2 }}</p>
    </div>
    <div class="flex gap-2">
        @can('update', $risk)
        <form method="POST" action="{{ route('risks.review', $risk) }}">@csrf<button class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm">Zarejestruj review</button></form>
        <a href="{{ route('risks.edit', $risk) }}" class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm">Edytuj</a>
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold mb-3">Profil ryzyka</h2>
            <dl class="grid grid-cols-2 gap-y-2 text-sm">
                <dt class="text-slate-500">Inherent (L × I)</dt>
                <dd>{{ $risk->inherent_likelihood }} × {{ $risk->inherent_impact }} = <strong>{{ $risk->inherent_score }}</strong></dd>
                <dt class="text-slate-500">Residual (L × I)</dt>
                <dd>{{ $risk->residual_likelihood }} × {{ $risk->residual_impact }} = <strong>{{ $risk->residual_score }}</strong> ({{ $risk->riskLevel() }})</dd>
                <dt class="text-slate-500">Target</dt>
                <dd>{{ $risk->target_score ?? '—' }} do {{ $risk->target_date?->format('Y-m-d') ?? '—' }}</dd>
                <dt class="text-slate-500">Strategia</dt>
                <dd>{{ $risk->treatment_strategy ?? '—' }}</dd>
                <dt class="text-slate-500">Status</dt>
                <dd>{{ $risk->status }}</dd>
                <dt class="text-slate-500">Owner</dt>
                <dd>{{ $risk->owner?->name ?? '—' }}</dd>
                <dt class="text-slate-500">Last reviewed</dt>
                <dd>{{ $risk->last_reviewed_at?->format('Y-m-d') ?? '—' }} {{ $risk->lastReviewer?->name ? '— '.$risk->lastReviewer->name : '' }}</dd>
                <dt class="text-slate-500">Next review</dt>
                <dd>{{ $risk->next_review_date?->format('Y-m-d') ?? '—' }}</dd>
            </dl>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <h3 class="font-medium text-sm mb-1">Opis</h3>
                <p class="text-sm whitespace-pre-line text-slate-700">{{ $risk->description }}</p>
            </div>
            @if($risk->risk_scenario)
            <div class="mt-3 pt-3 border-t border-slate-100">
                <h3 class="font-medium text-sm mb-1">Scenariusz</h3>
                <p class="text-sm whitespace-pre-line text-slate-700">{{ $risk->risk_scenario }}</p>
            </div>
            @endif
            @if($risk->mitre_attack_techniques)
            <div class="mt-3 pt-3 border-t border-slate-100">
                <h3 class="font-medium text-sm mb-1">MITRE ATT&CK</h3>
                <p class="text-sm font-mono">{{ implode(', ', $risk->mitre_attack_techniques) }}</p>
            </div>
            @endif
        </div>

        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold mb-3">Risk Treatment Plans</h2>
            @forelse($risk->treatmentPlans as $plan)
                <div class="border border-slate-200 rounded p-3 mb-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-medium text-sm">RTP #{{ $plan->id }} — target {{ $plan->target_residual_score }} do {{ $plan->target_date?->format('Y-m-d') }}</div>
                            <div class="text-xs text-slate-500">Status: {{ $plan->status }} · Budget: {{ $plan->budget_eur ? number_format((float)$plan->budget_eur, 0).' EUR' : '—' }}</div>
                        </div>
                    </div>
                    @if($plan->actions->count())
                    <table class="w-full text-xs mt-2">
                        <thead class="text-slate-500"><tr><th class="text-left">Akcja</th><th>Owner</th><th>Due</th><th>Progress</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($plan->actions as $a)
                            <tr class="border-t border-slate-100">
                                <td class="py-1">{{ $a->title }}</td>
                                <td>{{ $a->owner?->name ?? '—' }}</td>
                                <td>{{ $a->due_date?->format('Y-m-d') ?? '—' }}</td>
                                <td>{{ $a->progress_percent }}%</td>
                                <td>{{ $a->status }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                    @can('update', $risk)
                    <form method="POST" action="{{ route('risks.rtp.action', $plan) }}" class="mt-2 flex gap-2">
                        @csrf
                        <input name="title" placeholder="+ akcja" class="flex-1 px-2 py-1 border border-slate-300 rounded text-sm">
                        <button class="px-2 py-1 bg-emerald-600 text-white rounded text-xs">Dodaj</button>
                    </form>
                    @endcan
                </div>
            @empty
                <p class="text-sm text-slate-500">Brak RTP.</p>
            @endforelse

            @can('update', $risk)
            <details class="mt-3">
                <summary class="text-sm text-emerald-700 cursor-pointer">+ Nowy plan</summary>
                <form method="POST" action="{{ route('risks.rtp.create', $risk) }}" class="mt-3 grid grid-cols-2 gap-3">
                    @csrf
                    <input name="target_residual_score" type="number" min="1" max="25" required placeholder="Target score" class="px-2 py-1 border border-slate-300 rounded text-sm">
                    <input name="target_date" type="date" required class="px-2 py-1 border border-slate-300 rounded text-sm">
                    <input name="budget_eur" type="number" step="0.01" placeholder="Budget (EUR)" class="px-2 py-1 border border-slate-300 rounded text-sm">
                    <select name="review_cadence" class="px-2 py-1 border border-slate-300 rounded text-sm">
                        @foreach(['monthly','quarterly','semiannual','annual'] as $f)<option>{{ $f }}</option>@endforeach
                    </select>
                    <button class="col-span-2 px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">Utwórz plan</button>
                </form>
            </details>
            @endcan
        </div>

        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold mb-3">Risk Acceptance (4-eyes)</h2>
            @foreach($risk->acceptances as $acc)
                <div class="border border-slate-200 rounded p-3 mb-2 text-sm">
                    <div class="flex justify-between"><span class="font-medium">{{ $acc->status }}</span> <span class="text-slate-500 text-xs">do {{ $acc->expiry_date?->format('Y-m-d') }}</span></div>
                    <div class="text-xs text-slate-600 mt-1">Zgłoszone przez {{ $acc->proposer?->name ?? '?' }} · {{ $acc->proposed_at?->format('Y-m-d H:i') }}</div>
                    @if($acc->accepted_by)
                        <div class="text-xs text-emerald-700 mt-1">Zatwierdzone przez {{ $acc->approver?->name ?? '?' }} · {{ $acc->accepted_at?->format('Y-m-d H:i') }}</div>
                    @elseif($acc->status === 'Pending')
                        @can('update', $risk)
                        @if($acc->proposed_by !== auth()->id() && auth()->user()->can('risk.accept'))
                        <form method="POST" action="{{ route('risks.acceptance.approve', [$risk, $acc]) }}" class="mt-2">@csrf
                            <button class="px-3 py-1 bg-emerald-600 text-white rounded text-xs">Zatwierdź (4-eyes)</button>
                        </form>
                        @else
                        <p class="text-xs text-amber-700 mt-1">Twoja własna propozycja — wymaga akceptacji innej osoby (SoD).</p>
                        @endif
                        @endcan
                    @endif
                    <div class="mt-2 text-slate-700 text-xs whitespace-pre-line">{{ $acc->rationale }}</div>
                </div>
            @endforeach

            @can('update', $risk)
            <details class="mt-3">
                <summary class="text-sm text-emerald-700 cursor-pointer">+ Zaproponuj akceptację</summary>
                <form method="POST" action="{{ route('risks.acceptance.propose', $risk) }}" class="mt-3 space-y-2">
                    @csrf
                    <textarea name="rationale" required rows="3" placeholder="Uzasadnienie biznesowe (min. 20 znaków)" class="w-full px-2 py-1 border border-slate-300 rounded text-sm"></textarea>
                    <input name="expiry_date" type="date" required class="px-2 py-1 border border-slate-300 rounded text-sm">
                    <textarea name="compensating_controls" rows="2" placeholder="Compensating controls (jedna na linię)" class="w-full px-2 py-1 border border-slate-300 rounded text-sm"></textarea>
                    <button class="px-3 py-1.5 bg-amber-600 text-white rounded text-sm">Zgłoś propozycję</button>
                </form>
            </details>
            @endcan
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold mb-3">Heat map pozycja</h2>
            <div class="grid grid-cols-6 gap-1 text-xs">
                <div></div>
                @for($i = 1; $i <= 5; $i++)<div class="text-center font-medium">I{{ $i }}</div>@endfor
                @for($l = 5; $l >= 1; $l--)
                    <div class="text-right font-medium pr-2">L{{ $l }}</div>
                    @for($i = 1; $i <= 5; $i++)
                        @php
                            $score = $l * $i;
                            $bg = match(true) {
                                $score >= 20 => 'bg-red-600',
                                $score >= 12 => 'bg-orange-400',
                                $score >= 6 => 'bg-yellow-300',
                                default => 'bg-emerald-300',
                            };
                            $isCurrent = ($l === $risk->residual_likelihood && $i === $risk->residual_impact);
                            $isInh = ($l === $risk->inherent_likelihood && $i === $risk->inherent_impact);
                            $isTgt = $risk->target_score && ($l*$i === $risk->target_score);
                        @endphp
                        <div class="aspect-square flex items-center justify-center rounded {{ $bg }} text-xs font-semibold {{ $isCurrent ? 'ring-2 ring-slate-900' : '' }}">
                            @if($isCurrent)R@elseif($isInh)I@elseif($isTgt)T@endif
                        </div>
                    @endfor
                @endfor
            </div>
            <p class="text-xs text-slate-500 mt-2">R=residual, I=inherent, T=target</p>
        </div>

        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold mb-3">Versions (audit trail)</h2>
            <ul class="text-xs space-y-1 max-h-64 overflow-y-auto">
                @foreach($risk->versions->reverse()->take(10) as $v)
                    <li class="border-b border-slate-100 py-1">
                        <span class="font-medium">v{{ $v->version_number }}</span>
                        <span class="text-slate-500"> · {{ $v->changed_at?->format('Y-m-d H:i') }}</span><br>
                        <span class="text-slate-600">{{ $v->author?->name ?? 'system' }}</span> — {{ $v->change_reason ?? '—' }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<x-comment-thread :model="$risk" />
@endsection
