<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $template->name }} — {{ $reportCode }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #0f172a; }
        h1 { color: #0f172a; border-bottom: 3px solid #059669; padding-bottom: 5px; }
        h2 { color: #1e293b; margin-top: 25px; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; font-size: 13pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { padding: 5px 8px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 9pt; }
        th { background: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 8pt; color: #64748b; }
        .meta { color: #64748b; font-size: 8pt; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 60pt; color: rgba(15, 23, 42, 0.05); z-index: -1; }
        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; color: #94a3b8; font-size: 7pt; }
        .low { color: #b91c1c; font-weight: bold; }
        .mid { color: #d97706; font-weight: bold; }
        .high { color: #059669; font-weight: bold; }
    </style>
</head>
<body>
<div class="watermark">CONFIDENTIAL</div>
<div class="footer">{{ $watermark }} · Strona <span class="pagenum"></span></div>

<h1>{{ $template->name }}</h1>
<p class="meta">Wygenerowany: {{ $generatedAt->format('Y-m-d H:i') }} przez {{ $generatedBy }} · Kod raportu: <strong>{{ $reportCode }}</strong></p>

@php
    $level = fn (?float $pct) => $pct === null ? '' : ($pct < 50 ? 'low' : ($pct < 80 ? 'mid' : 'high'));
@endphp

<h2>1. Zgodność wg ostatniej przeprowadzonej oceny</h2>
<p class="meta">Wynik oceny (self-assessment) dla każdego aktywnego frameworka — na podstawie odpowiedzi udzielonych na poszczególne wymagania w module Compliance → Oceny.</p>
<table>
    <thead><tr><th>Framework</th><th>Liczba wymagań</th><th>Ostatnia ocena</th><th>% zgodności</th></tr></thead>
    <tbody>
        @forelse($data['compliance_posture'] as $row)
        <tr>
            <td>{{ $row['framework']->short_name }} — {{ $row['framework']->name }}</td>
            <td class="meta">{{ $row['requirements_count'] }}</td>
            <td class="meta">{{ $row['assessment']?->assessment_date?->format('Y-m-d') ?? '—' }}</td>
            <td class="{{ $level($row['score']) }}">{{ $row['score'] !== null ? $row['score'].'%' : 'Brak przeprowadzonej oceny' }}</td>
        </tr>
        @empty
        <tr><td colspan="4" class="meta">Brak aktywnych frameworków.</td></tr>
        @endforelse
    </tbody>
</table>

<h2>2. Pokrycie wymagań kontrolami wewnętrznymi</h2>
<p class="meta">Niezależnie od ocen: jaki procent wymagań/klauzul danego standardu ma przypisaną choć jedną obowiązującą kontrolę wewnętrzną (mapowanie Kontrole → Frameworki).</p>
<table>
    <thead><tr><th>Framework</th><th>Wymagania frameworka</th><th>Pokryte kontrolą</th><th>% pokrycia</th></tr></thead>
    <tbody>
        @forelse($data['control_framework_coverage'] as $row)
        <tr>
            <td>{{ $row['framework']->short_name }} — {{ $row['framework']->name }}</td>
            <td class="meta">{{ $row['total'] }}</td>
            <td class="meta">{{ $row['mapped'] }}</td>
            <td class="{{ $level($row['percent']) }}">{{ $row['percent'] }}%</td>
        </tr>
        @empty
        <tr><td colspan="4" class="meta">Brak zaimportowanych treści standardowych frameworków.</td></tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
