@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-semibold">Poziom zgodności wg frameworku</h1>
        <p class="text-sm text-slate-500 mt-1">% zgodności z najnowszej zakończonej oceny per framework.</p>
    </div>
    <a href="{{ route('compliance.frameworks') }}" class="text-sm text-slate-500 hover:text-slate-700">← Frameworki</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-6">
    <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-slate-700">Średnia ważona wszystkich frameworków</span>
        <span class="text-3xl font-bold {{ $overallAverage === null ? 'text-slate-400' : ($overallAverage >= 80 ? 'text-emerald-600' : ($overallAverage >= 50 ? 'text-amber-600' : 'text-red-600')) }}">
            {{ $overallAverage !== null ? $overallAverage.'%' : '—' }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach($frameworks as $row)
    @php
        $fw = $row['framework'];
        $score = $row['score'];
        $barColor = $score === null ? 'bg-slate-300' : ($score >= 80 ? 'bg-emerald-500' : ($score >= 50 ? 'bg-amber-400' : 'bg-red-500'));
    @endphp
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-2">
            <div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-slate-900 text-slate-100">{{ $fw->short_name }}</span>
                <span class="ml-2 font-medium text-slate-800">{{ $fw->name }}</span>
            </div>
            <span class="text-xl font-bold {{ $score === null ? 'text-slate-400' : 'text-slate-900' }}">{{ $score !== null ? $score.'%' : 'Brak oceny' }}</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden mb-3">
            <div class="{{ $barColor }} h-2.5 rounded-full" style="width: {{ $score ?? 0 }}%"></div>
        </div>
        <div class="flex items-center justify-between text-xs text-slate-500">
            <span>{{ $row['requirements_count'] }} wymagań</span>
            @if($row['assessment'])
                <span>Ocena: {{ $row['assessment']->code }} · {{ $row['assessment']->assessment_date?->format('Y-m-d') }}</span>
                <a href="{{ route('compliance.show', $row['assessment']) }}" class="text-emerald-600 hover:underline">Szczegóły →</a>
            @else
                <a href="{{ route('compliance.create', ['framework' => $fw->id]) }}" class="text-emerald-600 hover:underline">Rozpocznij ocenę →</a>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection
