@extends('layouts.app')
@section('content')
<div class="flex justify-between items-start mb-4">
    <div>
        <div class="text-xs font-mono text-slate-500">{{ $report->code }}</div>
        <h1 class="text-2xl font-semibold">{{ $report->template?->name }}</h1>
        <p class="text-slate-500 text-sm">{{ $report->period_start?->format('Y-m-d') }} – {{ $report->period_end?->format('Y-m-d') }} · {{ $report->classification }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('reports.download', $report) }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-sm">Pobierz PDF</a>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-3">Tamper evidence</h2>
        <dl class="text-sm grid grid-cols-2 gap-y-2">
            <dt class="text-slate-500">Wygenerowany</dt><dd>{{ $report->generated_at?->format('Y-m-d H:i') }}</dd>
            <dt class="text-slate-500">Przez</dt><dd>{{ $report->generator?->name ?? '?' }}</dd>
            <dt class="text-slate-500">Watermark</dt><dd class="text-xs">{{ $report->watermark_text }}</dd>
            @foreach($report->output_files ?? [] as $f)
                <dt class="text-slate-500">SHA-256 ({{ $f['format'] }})</dt><dd class="text-xs font-mono break-all">{{ $f['sha256'] }}</dd>
                <dt class="text-slate-500">Rozmiar</dt><dd>{{ number_format($f['size']) }} B</dd>
            @endforeach
            <dt class="text-slate-500">Status</dt><dd>{{ $report->revoked ? 'REVOKED ('.$report->revoked_at?->format('Y-m-d').')' : 'Active' }}</dd>
        </dl>
        @if(! $report->revoked)
            <form method="POST" action="{{ route('reports.revoke', $report) }}" class="mt-4 space-y-2">
                @csrf
                <input name="reason" required placeholder="Powód revoke" class="w-full px-2 py-1 border border-slate-300 rounded text-sm">
                <button class="px-3 py-1.5 bg-red-600 text-white rounded text-sm" onclick="return confirm('Revoke raport?')">Revoke</button>
            </form>
        @endif
    </div>

    <div class="bg-white rounded shadow p-4">
        <h2 class="font-semibold mb-3">Distribution log</h2>
        @if(empty($report->distribution_log))
            <p class="text-sm text-slate-500">Brak pobrań.</p>
        @else
            <ul class="text-xs space-y-1">
                @foreach($report->distribution_log as $entry)
                    <li class="border-b border-slate-100 py-1">
                        <strong>{{ $entry['user'] ?? '?' }}</strong>
                        <span class="text-slate-500"> · {{ $entry['ts'] ?? '?' }} · IP: {{ $entry['ip'] ?? '?' }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
