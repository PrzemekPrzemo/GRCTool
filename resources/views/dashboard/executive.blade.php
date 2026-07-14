@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">Dashboard CSO / Zarządu</h1>
        <p class="text-sm text-slate-500 mt-0.5">Zunifikowany widok na potrzeby kwartalnego raportu zarządu (REG-015 Q-05, szablon CLT-410)</p>
    </div>
</div>

{{-- RAG summary --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    @foreach(['KPI' => 'Wskaźniki KPI', 'KRI' => 'Wskaźniki ryzyka KRI'] as $type => $label)
    @php $c = $ragCounts[$type]; $total = array_sum($c); @endphp
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <div class="text-sm font-semibold text-slate-700 mb-3">{{ $label }} <span class="text-slate-400 font-normal">({{ $total }})</span></div>
        <div class="flex items-center gap-1 h-3 rounded-full overflow-hidden bg-slate-100">
            @if($total > 0)
                <div class="h-full bg-emerald-500" style="width: {{ $c['green'] / $total * 100 }}%"></div>
                <div class="h-full bg-amber-500" style="width: {{ $c['amber'] / $total * 100 }}%"></div>
                <div class="h-full bg-red-500" style="width: {{ $c['red'] / $total * 100 }}%"></div>
                <div class="h-full bg-slate-300" style="width: {{ $c['none'] / $total * 100 }}%"></div>
            @endif
        </div>
        <div class="flex gap-4 mt-3 text-xs text-slate-600">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Green {{ $c['green'] }}</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span>Amber {{ $c['amber'] }}</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span>Red {{ $c['red'] }}</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-slate-300"></span>Brak pomiaru {{ $c['none'] }}</span>
        </div>
    </div>
    @endforeach
</div>

{{-- Indicators requiring attention --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
    <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between">
        <span class="text-sm font-semibold text-slate-700">Wskaźniki wymagające uwagi (Amber/Red)</span>
        <a href="{{ route('indicators.index') }}" class="text-xs text-emerald-700 hover:underline">Zobacz wszystkie →</a>
    </div>
    @if($attentionIndicators->isEmpty())
        <div class="px-4 py-6 text-sm text-slate-400 text-center">Brak wskaźników w statusie Amber/Red.</div>
    @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <tbody class="divide-y divide-slate-100">
                @foreach($attentionIndicators as $indicator)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2 w-3">
                        <span class="inline-block w-2.5 h-2.5 rounded-full {{ $indicator->latestMeasurement->status === 'red' ? 'bg-red-500' : 'bg-amber-500' }}"></span>
                    </td>
                    <td class="px-4 py-2 font-mono text-xs text-slate-500 w-32">{{ $indicator->code }}</td>
                    <td class="px-4 py-2 text-slate-800"><a href="{{ route('indicators.show', $indicator) }}" class="hover:underline">{{ $indicator->name }}</a></td>
                    <td class="px-4 py-2 text-xs text-slate-600 w-32 text-right">{{ $indicator->latestMeasurement->value }} {{ $indicator->unit }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Critical/high compliance gaps --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between">
            <span class="text-sm font-semibold text-slate-700">Luki compliance — krytyczne / wysokie</span>
            <a href="{{ route('compliance.gaps') }}" class="text-xs text-emerald-700 hover:underline">Zobacz wszystkie →</a>
        </div>
        @if($criticalGaps->isEmpty())
            <div class="px-4 py-6 text-sm text-slate-400 text-center">Brak otwartych luk krytycznych/wysokich.</div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                    @foreach($criticalGaps as $gap)
                    <tr class="hover:bg-slate-50 align-top">
                        <td class="px-4 py-2 w-20">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $gap->severity === 'critical' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">{{ ucfirst($gap->severity) }}</span>
                        </td>
                        <td class="px-4 py-2 text-slate-800">
                            {{ $gap->title }}
                            @if($gap->target_date)
                                <div class="text-xs text-slate-500 mt-0.5">Termin: {{ $gap->target_date->format('Y-m-d') }}</div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>

    {{-- Exceptions expiring soon --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between">
            <span class="text-sm font-semibold text-slate-700">Wyjątki wygasające w ≤14 dni</span>
            <a href="{{ route('exceptions.index') }}" class="text-xs text-emerald-700 hover:underline">Zobacz wszystkie →</a>
        </div>
        @if($expiringExceptions->isEmpty())
            <div class="px-4 py-6 text-sm text-slate-400 text-center">Brak wyjątków wygasających w ciągu 14 dni.</div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                    @foreach($expiringExceptions as $exception)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2 text-slate-800">{{ $exception->title }}</td>
                        <td class="px-4 py-2 text-xs text-amber-700 w-28 text-right">{{ $exception->expires_at->format('Y-m-d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Calendar tasks this cycle --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between">
            <span class="text-sm font-semibold text-slate-700">Zadania kalendarza compliance — bieżący cykl</span>
            <a href="{{ route('registers.compliance-calendar') }}" class="text-xs text-emerald-700 hover:underline">Zobacz wszystkie →</a>
        </div>
        @if($calendarTasksThisCycle->isEmpty())
            <div class="px-4 py-6 text-sm text-slate-400 text-center">Brak zadań przypadających na bieżący miesiąc/kwartał.</div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                    @foreach($calendarTasksThisCycle as $task)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2 font-mono text-xs text-slate-500 w-14">{{ $task->ref }}</td>
                        <td class="px-4 py-2 text-slate-800">{{ $task->task }}</td>
                        <td class="px-4 py-2 text-xs text-slate-600 w-28">{{ $task->owner_role }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>

    {{-- Top risks --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between">
            <span class="text-sm font-semibold text-slate-700">Najwyższe ryzyka rezydualne</span>
            <a href="{{ route('risks.index') }}" class="text-xs text-emerald-700 hover:underline">Zobacz wszystkie →</a>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <tbody class="divide-y divide-slate-100">
                @foreach($topRisks as $risk)
                @php $level = match(true) { $risk->residual_score >= 15 => 'red', $risk->residual_score >= 8 => 'amber', default => 'emerald' }; @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2 w-10">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold bg-{{ $level }}-100 text-{{ $level }}-700">{{ $risk->residual_score }}</span>
                    </td>
                    <td class="px-4 py-2 text-slate-800"><a href="{{ route('risks.show', $risk) }}" class="hover:underline">{{ $risk->title }}</a></td>
                    <td class="px-4 py-2 text-xs text-slate-500 w-32">{{ $risk->owner?->name ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
