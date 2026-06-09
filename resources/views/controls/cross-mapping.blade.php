@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-semibold">Macierz pokrycia kontroli</h1>
        <p class="text-sm text-slate-500 mt-0.5">Które frameworki są pokryte przez każdą kontrolę</p>
    </div>
    <a href="{{ route('controls.soa') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm hover:bg-slate-200 transition-colors">SoA widok →</a>
</div>

{{-- Coverage summary bars --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
    @foreach($frameworks as $fw)
    @php $pct = $coverage[$fw->id] ?? 0; $barColor = $pct >= 75 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-400' : 'bg-red-500'); @endphp
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3">
        <div class="text-xs font-semibold text-slate-700 truncate">{{ $fw->code }}</div>
        <div class="text-xs text-slate-400 truncate mb-2">{{ $fw->name }}</div>
        <div class="flex items-center gap-2">
            <div class="flex-1 bg-slate-200 rounded-full h-2">
                <div class="h-2 rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
            </div>
            <span class="text-xs font-bold text-slate-600">{{ $pct }}%</span>
        </div>
    </div>
    @endforeach
</div>

{{-- Legend --}}
<div class="flex items-center gap-4 mb-4 text-xs text-slate-600">
    <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-4 bg-emerald-100 border border-emerald-300 rounded text-center text-emerald-700 font-bold text-xs leading-4">F</span> Pełne</span>
    <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-4 bg-amber-100 border border-amber-300 rounded text-center text-amber-700 font-bold text-xs leading-4">P</span> Częściowe</span>
    <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-4 bg-purple-100 border border-purple-300 rounded text-center text-purple-700 font-bold text-xs leading-4">K</span> Kompensujące</span>
    <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-4 bg-slate-100 border border-slate-200 rounded"></span> Brak mapowania</span>
</div>

{{-- Matrix table --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-x-auto">
    <table class="text-xs min-w-full">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-3 py-2.5 text-left text-slate-500 font-semibold uppercase tracking-wide sticky left-0 bg-slate-50 z-10 min-w-64">Kontrola</th>
                <th class="px-2 py-2.5 text-left text-slate-500 font-semibold uppercase tracking-wide min-w-20">Testowanie</th>
                @foreach($frameworks as $fw)
                <th class="px-2 py-2 text-center font-semibold text-slate-600 min-w-16 whitespace-nowrap">
                    <div>{{ $fw->code }}</div>
                    <div class="text-xs font-normal text-slate-400">{{ $coverage[$fw->id] ?? 0 }}%</div>
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($controls as $control)
            @php
                $testStatus = $control->testingStatus();
                $testBadge  = match($testStatus) { 'overdue' => 'bg-red-100 text-red-700', 'never_tested' => 'bg-amber-100 text-amber-700', default => 'bg-emerald-100 text-emerald-700' };
                $testLabel  = match($testStatus) { 'overdue' => 'Zaległy', 'never_tested' => 'Nigdy', default => 'OK' };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                <td class="px-3 py-2 sticky left-0 bg-white z-10 border-r border-slate-100">
                    <a href="{{ route('controls.show', $control) }}" class="font-mono text-emerald-700 hover:underline">{{ $control->code }}</a>
                    <span class="ml-2 text-slate-700">{{ $control->name }}</span>
                </td>
                <td class="px-2 py-2">
                    <span class="px-1.5 py-0.5 rounded {{ $testBadge }} font-medium">{{ $testLabel }}</span>
                    @if($control->next_test_due)
                    <div class="text-slate-400 mt-0.5">{{ $control->next_test_due->format('Y-m-d') }}</div>
                    @endif
                </td>
                @foreach($frameworks as $fw)
                @php
                    $mapping = $matrix[$control->id][$fw->id] ?? null;
                    $cell = match($mapping) {
                        'full'         => ['bg-emerald-100 border-emerald-300 text-emerald-700', 'F'],
                        'partial'      => ['bg-amber-100 border-amber-300 text-amber-700', 'P'],
                        'compensating' => ['bg-purple-100 border-purple-300 text-purple-700', 'K'],
                        default        => ['bg-slate-50 border-slate-200 text-slate-300', '—'],
                    };
                @endphp
                <td class="px-2 py-2 text-center">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded border {{ $cell[0] }} font-bold">{{ $cell[1] }}</span>
                </td>
                @endforeach
            </tr>
            @empty
            <tr><td colspan="{{ 2 + $frameworks->count() }}" class="px-3 py-8 text-center text-slate-500">Brak kontroli z mappowaniem frameworków.</td></tr>
            @endforelse
        </tbody>
        {{-- Summary row --}}
        <tfoot class="border-t-2 border-slate-200 bg-slate-50">
            <tr>
                <td class="px-3 py-2 font-semibold text-slate-600 sticky left-0 bg-slate-50 z-10" colspan="2">Pokrycie (%)</td>
                @foreach($frameworks as $fw)
                @php $pct = $coverage[$fw->id] ?? 0; $color = $pct >= 75 ? 'text-emerald-700' : ($pct >= 50 ? 'text-amber-700' : 'text-red-700'); @endphp
                <td class="px-2 py-2 text-center font-bold {{ $color }}">{{ $pct }}%</td>
                @endforeach
            </tr>
        </tfoot>
    </table>
</div>

@endsection
