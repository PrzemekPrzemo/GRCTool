@props(['points', 'color' => '#059669', 'targetLine' => null, 'height' => 80])
@php
$pts    = collect($points)->values();
$count  = $pts->count();
$width  = 580;
$h      = (int) $height - 20;

if ($count < 2) {
    $hasData = false;
} else {
    $hasData = true;
    $values  = $pts->pluck('y')->map(fn ($v) => (float) $v);
    $minY    = $values->min();
    $maxY    = $values->max();
    $range   = max($maxY - $minY, 1);
    $stepX   = $width / ($count - 1);

    $pathPoints = $pts->map(function ($pt, $idx) use ($stepX, $minY, $range, $h) {
        return [
            'x' => 10 + $idx * $stepX,
            'y' => 10 + $h - (($pt['y'] - $minY) / $range * $h),
        ];
    });

    $d = $pathPoints->map(fn ($pt, $i) => ($i === 0 ? 'M' : 'L') . round($pt['x'], 1) . ',' . round($pt['y'], 1))->implode(' ');

    if ($targetLine !== null) {
        $ty = 10 + $h - (((float) $targetLine - $minY) / $range * $h);
    }
}
@endphp

@if($hasData)
<svg viewBox="0 0 600 {{ $height }}" class="w-full" aria-hidden="true">
    {{-- Grid lines --}}
    @for($i = 0; $i <= 3; $i++)
    <line x1="10" x2="590" y1="{{ 10 + ($h / 3) * $i }}" y2="{{ 10 + ($h / 3) * $i }}" stroke="#e2e8f0" stroke-width="1"/>
    @endfor

    {{-- Target line --}}
    @if(isset($ty))
    <line x1="10" x2="590" y1="{{ $ty }}" y2="{{ $ty }}" stroke="#3b82f6" stroke-width="1.5" stroke-dasharray="5,4" opacity="0.7"/>
    @endif

    {{-- Data line --}}
    <path d="{{ $d }}" fill="none" stroke="{{ $color }}" stroke-width="2" stroke-linejoin="round"/>

    {{-- Data points --}}
    @foreach($pathPoints as $pt)
    <circle cx="{{ round($pt['x'], 1) }}" cy="{{ round($pt['y'], 1) }}" r="3" fill="{{ $color }}" stroke="white" stroke-width="1.5"/>
    @endforeach

    {{-- X-axis labels (first and last) --}}
    @if($pts->first())
    <text x="10" y="{{ $height }}" font-size="9" fill="#94a3b8" text-anchor="start">{{ $pts->first()['x'] }}</text>
    @endif
    @if($pts->last())
    <text x="590" y="{{ $height }}" font-size="9" fill="#94a3b8" text-anchor="end">{{ $pts->last()['x'] }}</text>
    @endif
</svg>
@else
<div class="flex items-center justify-center text-slate-400 text-xs" style="height: {{ $height }}px">Brak danych</div>
@endif
