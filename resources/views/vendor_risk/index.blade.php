@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Ryzyko dostawców</h1>
        <p class="text-sm text-slate-500 mt-1">Ranking stron trzecich wg złożonego wyniku ryzyka (tier, rating bezpieczeństwa, wyniki ocen, przeterminowanie).</p>
    </div>
    @can('third_party.view')
    <form method="POST" action="{{ route('vendor-risk.snapshot') }}">
        @csrf
        <button class="px-3 py-1.5 border border-slate-300 rounded text-sm hover:bg-slate-50">Zapisz migawkę</button>
    </form>
    @endcan
</div>

@if(session('status'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded text-sm">{{ session('status') }}</div>
@endif

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
    @foreach(['Critical' => 'bg-red-600', 'High' => 'bg-orange-500', 'Medium' => 'bg-amber-400', 'Low' => 'bg-slate-400'] as $tier => $color)
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="flex items-center gap-2 mb-1">
            <span class="w-2 h-2 rounded-full {{ $color }}"></span>
            <span class="text-xs text-slate-500">{{ $tier }}</span>
        </div>
        <div class="text-2xl font-bold text-slate-900">{{ $tierCounts[$tier] ?? 0 }}</div>
    </div>
    @endforeach
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-3 py-2">Dostawca</th>
                <th class="px-3 py-2">Tier</th>
                <th class="px-3 py-2">Rating bezp.</th>
                <th class="px-3 py-2">Ostatnia ocena</th>
                <th class="px-3 py-2">Kryt. luki</th>
                <th class="px-3 py-2">Przegląd</th>
                <th class="px-3 py-2">Wynik ryzyka</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendors as $row)
            @php
                $tp = $row['vendor'];
                $risk = $row['risk'];
                $tierBg = match($risk['tier']) {
                    'Critical' => 'bg-red-600 text-white',
                    'High'     => 'bg-orange-500 text-white',
                    'Medium'   => 'bg-amber-300 text-amber-900',
                    default    => 'bg-slate-200 text-slate-700',
                };
                $overdue = $tp->next_assessment_due?->isPast();
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50">
                <td class="px-3 py-2">
                    <a href="{{ route('third-parties.show', $tp) }}" class="font-medium text-emerald-700 hover:underline">{{ $tp->name }}</a>
                    <div class="text-xs text-slate-400 font-mono">{{ $tp->code }}</div>
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $tp->tier ?? '—' }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $tp->security_rating ?? '—' }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">
                    {{ $risk['latest_assessment']?->compliance_percentage !== null ? $risk['latest_assessment']->compliance_percentage.'%' : '—' }}
                </td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $risk['latest_assessment']?->critical_gaps_count ?? '—' }}</td>
                <td class="px-3 py-2 text-xs {{ $overdue ? 'text-red-700 font-semibold' : 'text-slate-600' }}">
                    {{ $tp->next_assessment_due?->format('Y-m-d') ?? '—' }}
                </td>
                <td class="px-3 py-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $tierBg }}">{{ $risk['score'] }}</span>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-3 py-8 text-center text-slate-500">Brak aktywnych dostawców do oceny.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
