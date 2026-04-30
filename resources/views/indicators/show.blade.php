@extends('layouts.app')
@section('content')
<div class="flex justify-between items-start mb-4">
    <div>
        <div class="text-xs font-mono text-slate-500">{{ $indicator->code }} · {{ $indicator->type }}</div>
        <h1 class="text-2xl font-semibold">{{ $indicator->name }}</h1>
    </div>
    <a href="{{ route('indicators.edit', $indicator) }}" class="px-3 py-1.5 border border-slate-300 bg-white rounded text-sm">Edytuj</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-3">Trend</h2>
        @php
            $points = $measurements->map(fn($m) => ['x' => $m->measured_at?->format('Y-m-d'), 'y' => (float)$m->value])->toArray();
            $maxY = max(array_column($points, 'y') ?: [1]);
            $minY = min(array_column($points, 'y') ?: [0]);
            $range = max($maxY - $minY, 1);
        @endphp
        @if(count($points))
        <svg viewBox="0 0 600 180" class="w-full h-48">
            @php
                $w = 580; $h = 160; $stepX = count($points) > 1 ? $w / (count($points) - 1) : 0;
                $path = '';
                foreach ($points as $idx => $p) {
                    $x = 10 + $idx * $stepX;
                    $y = 10 + $h - (($p['y'] - $minY) / $range * $h);
                    $path .= ($idx === 0 ? 'M' : 'L')." $x $y ";
                }
            @endphp
            <path d="{{ $path }}" fill="none" stroke="#059669" stroke-width="2"/>
            @if($indicator->target_value)
                @php $ty = 10 + $h - ((float)$indicator->target_value - $minY) / $range * $h; @endphp
                <line x1="10" y1="{{ $ty }}" x2="{{ 10+$w }}" y2="{{ $ty }}" stroke="#3b82f6" stroke-dasharray="4 2" stroke-width="1"/>
            @endif
        </svg>
        @else
        <p class="text-sm text-slate-500">Brak danych. Zarejestruj pierwszy pomiar po prawej.</p>
        @endif

        <table class="w-full text-sm mt-4">
            <thead class="text-xs text-slate-500 text-left">
                <tr><th>Data</th><th class="text-right">Wartość</th><th>Status</th><th>Reporter</th><th>Notatka</th></tr>
            </thead>
            <tbody>
                @foreach($measurements->reverse()->take(20) as $m)
                <tr class="border-t border-slate-100">
                    <td class="py-1">{{ $m->measured_at?->format('Y-m-d') }}</td>
                    <td class="py-1 text-right font-medium">{{ rtrim(rtrim(number_format((float)$m->value, 2), '0'), '.') }} {{ $indicator->unit }}</td>
                    <td class="py-1">
                        @php $bg = ['green'=>'bg-emerald-500','amber'=>'bg-amber-500','red'=>'bg-red-500'][$m->status] ?? 'bg-slate-300'; @endphp
                        <span class="inline-block w-2.5 h-2.5 rounded-full {{ $bg }}"></span>
                    </td>
                    <td class="py-1 text-xs text-slate-500">{{ $m->reporter?->name }}</td>
                    <td class="py-1 text-xs text-slate-600">{{ $m->notes }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold mb-3">Konfiguracja</h2>
            <dl class="text-sm grid grid-cols-2 gap-y-1">
                <dt class="text-slate-500">Source</dt><dd>{{ $indicator->data_source ?? '—' }}</dd>
                <dt class="text-slate-500">Unit</dt><dd>{{ $indicator->unit }}</dd>
                <dt class="text-slate-500">Target</dt><dd>{{ $indicator->target_value }}</dd>
                <dt class="text-slate-500">Direction</dt><dd>{{ $indicator->direction }}</dd>
                <dt class="text-slate-500">Frequency</dt><dd>{{ $indicator->frequency }}</dd>
                <dt class="text-slate-500">Audience</dt><dd>{{ $indicator->consumer_audience }}</dd>
                <dt class="text-slate-500">Owner</dt><dd>{{ $indicator->owner?->name ?? '—' }}</dd>
            </dl>
            @if($indicator->formula)
                <p class="text-xs mt-2 pt-2 border-t border-slate-100"><strong>Formula:</strong> {{ $indicator->formula }}</p>
            @endif
            <div class="text-xs text-slate-500 mt-2 pt-2 border-t border-slate-100">
                Progi: G {{ $indicator->green_threshold }} / A {{ $indicator->amber_threshold }} / R {{ $indicator->red_threshold }}
            </div>
        </div>

        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold mb-3">Zarejestruj pomiar</h2>
            <form method="POST" action="{{ route('indicators.measurement', $indicator) }}" class="space-y-2">
                @csrf
                <input name="value" type="number" step="0.01" required placeholder="Wartość" class="w-full px-2 py-1 border border-slate-300 rounded text-sm">
                <input name="measured_at" type="datetime-local" class="w-full px-2 py-1 border border-slate-300 rounded text-sm">
                <textarea name="notes" rows="2" placeholder="Notatka" class="w-full px-2 py-1 border border-slate-300 rounded text-sm"></textarea>
                <button class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm w-full">Zapisz pomiar</button>
            </form>
        </div>

        <div class="bg-white rounded shadow p-4">
            <h2 class="font-semibold mb-3">Import CSV pomiarów</h2>
            <p class="text-xs text-slate-500 mb-2">Format: <code>measured_at,value,source_run_id</code></p>
            <form method="POST" action="{{ route('indicators.import', $indicator) }}" enctype="multipart/form-data" class="space-y-2">
                @csrf
                <input type="file" name="file" accept=".csv" required class="block text-sm">
                <button class="px-3 py-1 bg-slate-900 text-white rounded text-sm w-full">Import</button>
            </form>
        </div>
    </div>
</div>
@endsection
