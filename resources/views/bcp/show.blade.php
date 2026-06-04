@extends('layouts.app')
@section('content')

<div class="mb-4">
    <a href="{{ route('bcp.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; BCP/DR</a>
</div>

@php
    $statusBg = ['draft'=>'bg-slate-100 text-slate-600','active'=>'bg-emerald-100 text-emerald-700','under_review'=>'bg-amber-100 text-amber-700','retired'=>'bg-slate-200 text-slate-500'][$bcp->status] ?? 'bg-slate-100';
    $statusLabel = \App\Models\BcpPlan::STATUS_LABELS[$bcp->status] ?? $bcp->status;
    $typeLabel = \App\Models\BcpPlan::TYPE_LABELS[$bcp->plan_type] ?? $bcp->plan_type;
@endphp

<div class="grid grid-cols-3 gap-4">
    <div class="col-span-2 space-y-4">
        <div class="bg-white rounded shadow p-4">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <span class="font-mono text-xs text-slate-500">{{ $bcp->code }} · v{{ $bcp->version }}</span>
                    <h1 class="text-xl font-semibold">{{ $bcp->title }}</h1>
                </div>
                <span class="px-2 py-0.5 rounded text-xs {{ $statusBg }}">{{ $statusLabel }}</span>
            </div>

            @if($bcp->description)
            <p class="text-sm text-slate-600 mb-3">{{ $bcp->description }}</p>
            @endif

            {{-- RTO / RPO / MTD cards --}}
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="bg-blue-50 border border-blue-200 rounded p-3 text-center">
                    <div class="text-2xl font-bold text-blue-700">{{ $bcp->rtoLabel() }}</div>
                    <div class="text-xs text-blue-600">RTO</div>
                    <div class="text-xs text-slate-400">Recovery Time Objective</div>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded p-3 text-center">
                    <div class="text-2xl font-bold text-purple-700">{{ $bcp->rpo_minutes !== null ? $bcp->rpo_minutes.'min' : '—' }}</div>
                    <div class="text-xs text-purple-600">RPO</div>
                    <div class="text-xs text-slate-400">Recovery Point Objective</div>
                </div>
                <div class="bg-orange-50 border border-orange-200 rounded p-3 text-center">
                    @php $mtdLabel = $bcp->mtd_hours !== null ? (($bcp->mtd_hours >= 24 ? round($bcp->mtd_hours/24,1).'d' : $bcp->mtd_hours.'h')) : '—'; @endphp
                    <div class="text-2xl font-bold text-orange-700">{{ $mtdLabel }}</div>
                    <div class="text-xs text-orange-600">MTD</div>
                    <div class="text-xs text-slate-400">Max Tolerable Downtime</div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-slate-500">Typ:</span> {{ $typeLabel }}</div>
                <div><span class="text-slate-500">Właściciel:</span> {{ $bcp->owner?->name ?? '—' }}</div>
                <div><span class="text-slate-500">Zatwierdził:</span> {{ $bcp->approver?->name ?? '—' }}</div>
                <div><span class="text-slate-500">Data zatwierdzenia:</span> {{ $bcp->approved_at?->format('Y-m-d') ?? '—' }}</div>
                <div><span class="text-slate-500">Ostatni przegląd:</span> {{ $bcp->last_reviewed_at?->format('Y-m-d') ?? '—' }}</div>
                <div class="{{ $bcp->isReviewOverdue() ? 'text-red-600 font-medium' : '' }}">
                    <span class="text-slate-500">Następny przegląd:</span> {{ $bcp->next_review_due?->format('Y-m-d') ?? '—' }}
                </div>
            </div>

            @if($bcp->scope)
            <div class="mt-3">
                <div class="text-sm font-medium text-slate-700 mb-1">Zakres</div>
                <p class="text-sm text-slate-600 bg-slate-50 rounded p-3">{{ $bcp->scope }}</p>
            </div>
            @endif
        </div>

        {{-- Test history --}}
        <div class="bg-white rounded shadow overflow-hidden">
            <div class="px-4 py-2 border-b border-slate-100 font-semibold text-sm">Historia testów</div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 text-left">
                    <tr>
                        <th class="px-3 py-2">Kod</th>
                        <th class="px-3 py-2">Typ</th>
                        <th class="px-3 py-2">Data</th>
                        <th class="px-3 py-2">Przeprowadził</th>
                        <th class="px-3 py-2">Wynik</th>
                        <th class="px-3 py-2">Luki</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tests as $test)
                    @php
                        $resBg = ['pass'=>'bg-emerald-100 text-emerald-700','pass_with_gaps'=>'bg-amber-100 text-amber-700','fail'=>'bg-red-100 text-red-700'][$test->result] ?? '';
                        $resLabel = ['pass'=>'Zaliczony','pass_with_gaps'=>'Częściowy','fail'=>'Niezaliczony'][$test->result] ?? $test->result;
                        $testTypeLabels = ['tabletop'=>'Tabletop','walkthrough'=>'Walkthrough','simulation'=>'Symulacja','partial_interruption'=>'Częściowe','full_interruption'=>'Pełne'];
                    @endphp
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2 font-mono text-xs">{{ $test->code }}</td>
                        <td class="px-3 py-2 text-xs">{{ $testTypeLabels[$test->test_type] ?? $test->test_type }}</td>
                        <td class="px-3 py-2 text-xs">{{ $test->tested_at->format('Y-m-d') }}</td>
                        <td class="px-3 py-2 text-xs text-slate-600">{{ $test->conductor?->name ?? '—' }}</td>
                        <td class="px-3 py-2"><span class="px-1.5 py-0.5 rounded text-xs {{ $resBg }}">{{ $resLabel }}</span></td>
                        <td class="px-3 py-2 text-xs text-slate-600 max-w-xs truncate" title="{{ $test->gaps_identified }}">{{ $test->gaps_identified ? Str::limit($test->gaps_identified, 60) : '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-3 py-4 text-center text-slate-400 text-sm">Brak testów</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Add test form --}}
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold text-sm mb-3">Dodaj test</h2>
            <form method="POST" action="{{ route('bcp.test', $bcp) }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs mb-1">Typ testu *</label>
                        <select name="test_type" required class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                            <option value="tabletop">Tabletop</option>
                            <option value="walkthrough">Walkthrough</option>
                            <option value="simulation">Symulacja</option>
                            <option value="partial_interruption">Częściowe przerwanie</option>
                            <option value="full_interruption">Pełne przerwanie</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs mb-1">Data testu *</label>
                        <input type="date" name="tested_at" value="{{ date('Y-m-d') }}" required class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs mb-1">Wynik *</label>
                        <select name="result" required class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                            <option value="pass">Zaliczony</option>
                            <option value="pass_with_gaps">Częściowy (z lukami)</option>
                            <option value="fail">Niezaliczony</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs mb-1">Przeprowadził</label>
                        <select name="conducted_by" class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                            <option value="">— brak —</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs mb-1">Zidentyfikowane luki</label>
                    <textarea name="gaps_identified" rows="2" class="w-full border border-slate-300 rounded px-2 py-1 text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-xs mb-1">Podjęte działania</label>
                    <textarea name="actions_taken" rows="2" class="w-full border border-slate-300 rounded px-2 py-1 text-sm"></textarea>
                </div>
                <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">Zapisz test</button>
            </form>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold text-sm mb-3">Akcje</h2>
            <a href="{{ route('bcp.edit', $bcp) }}" class="block w-full text-center px-3 py-1.5 bg-slate-100 rounded text-sm hover:bg-slate-200 mb-2">Edytuj</a>
            @if($bcp->status !== 'active')
            <form method="POST" action="{{ route('bcp.approve', $bcp) }}">
                @csrf
                <button type="submit" class="w-full px-3 py-1.5 bg-emerald-600 text-white rounded text-sm hover:bg-emerald-700">Zatwierdź plan</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
