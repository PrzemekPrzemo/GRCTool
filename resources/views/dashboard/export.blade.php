<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport bezpieczeństwa — {{ now()->format('d.m.Y') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 11px; color: #1e293b; background: #fff; padding: 24px; }
        h1 { font-size: 20px; font-weight: 700; color: #0f172a; }
        h2 { font-size: 13px; font-weight: 600; color: #334155; margin: 20px 0 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        h3 { font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.04em; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #0f172a; }
        .header-meta { text-align: right; font-size: 10px; color: #64748b; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 16px; }
        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 16px; }
        .card { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; }
        .card-label { font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
        .card-value { font-size: 24px; font-weight: 700; line-height: 1.1; margin-top: 2px; }
        .card-sub { font-size: 9px; color: #94a3b8; margin-top: 2px; }
        .red { color: #dc2626; }
        .amber { color: #d97706; }
        .emerald { color: #059669; }
        .slate { color: #475569; }
        .border-red { border-color: #fca5a5; background: #fef2f2; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th { text-align: left; padding: 4px 6px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 600; font-size: 9px; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; }
        td { padding: 4px 6px; border-bottom: 1px solid #f1f5f9; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 9999px; font-size: 9px; font-weight: 600; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .chart-area { height: 70px; }
        .trend-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
        .trend-card { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; }
        .footer { margin-top: 24px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; display: flex; justify-content: space-between; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 16mm; size: A4 landscape; }
        }
    </style>
</head>
<body>

{{-- Print button (hidden on print) --}}
<div class="no-print" style="margin-bottom:16px; display:flex; gap:8px;">
    <button onclick="window.print()" style="padding:6px 16px; background:#0f172a; color:white; border:none; border-radius:6px; font-size:11px; cursor:pointer;">Drukuj / Zapisz PDF</button>
    <a href="{{ route('dashboard') }}" style="padding:6px 16px; background:#f1f5f9; color:#475569; border:none; border-radius:6px; font-size:11px; text-decoration:none;">← Wróć</a>
</div>

<div class="header">
    <div>
        <h1>Raport bezpieczeństwa</h1>
        <div style="font-size:11px; color:#64748b; margin-top:2px;">{{ config('app.name') }}</div>
    </div>
    <div class="header-meta">
        <div>Data: <strong>{{ now()->format('d.m.Y') }}</strong></div>
        <div>Wygenerował: {{ auth()->user()->name }}</div>
        <div style="margin-top:4px; font-size:9px; color:#94a3b8;">POUFNE — do użytku wewnętrznego</div>
    </div>
</div>

<h2>Przegląd kluczowych wskaźników</h2>
<div class="grid-4">
    <div class="card {{ $stats['risks_over_appetite'] > 0 ? 'border-red' : '' }}">
        <div class="card-label">Ryzyka otwarte</div>
        <div class="card-value {{ $stats['risks_over_appetite'] > 0 ? 'red' : 'slate' }}">{{ $stats['risks_open'] }}</div>
        <div class="card-sub">{{ $stats['risks_over_appetite'] }} ponad apetyt</div>
    </div>
    <div class="card {{ $stats['vulns_overdue'] > 0 ? 'border-red' : '' }}">
        <div class="card-label">Podatności otwarte</div>
        <div class="card-value {{ $stats['vulns_overdue'] > 0 ? 'red' : 'slate' }}">{{ $stats['vulns_open'] }}</div>
        <div class="card-sub">{{ $stats['vulns_overdue'] }} po SLA</div>
    </div>
    <div class="card {{ $stats['incidents_open_p1_p2'] > 0 ? 'border-red' : '' }}">
        <div class="card-label">Incydenty P1/P2</div>
        <div class="card-value {{ $stats['incidents_open_p1_p2'] > 0 ? 'red' : 'emerald' }}">{{ $stats['incidents_open_p1_p2'] }}</div>
        <div class="card-sub">otwarte</div>
    </div>
    <div class="card">
        <div class="card-label">Kontrole skuteczne</div>
        <div class="card-value emerald">{{ $stats['controls_effective'] }}<span style="font-size:12px; color:#94a3b8;">/{{ $stats['controls_total'] }}</span></div>
        <div class="card-sub">{{ $stats['controls_total'] > 0 ? round($stats['controls_effective']/$stats['controls_total']*100) : 0 }}% effectiveness</div>
    </div>
</div>

<div class="grid-4">
    <div class="card">
        <div class="card-label">Szkolenia</div>
        <div class="card-value {{ $stats['trainings_completion_pct'] >= 80 ? 'emerald' : 'amber' }}">{{ $stats['trainings_completion_pct'] }}%</div>
        <div class="card-sub">ukończonych</div>
    </div>
    <div class="card {{ $stats['findings_open'] > 0 ? '' : '' }}">
        <div class="card-label">Findings audytowe</div>
        <div class="card-value slate">{{ $stats['findings_open'] }}</div>
        <div class="card-sub">otwarte</div>
    </div>
    <div class="card {{ $stats['rtp_actions_overdue'] > 0 ? 'border-red' : '' }}">
        <div class="card-label">Akcje leczenia ryzyk</div>
        <div class="card-value {{ $stats['rtp_actions_overdue'] > 0 ? 'red' : 'slate' }}">{{ $stats['rtp_actions_overdue'] }}</div>
        <div class="card-sub">po terminie</div>
    </div>
    <div class="card {{ $stats['evidence_expiring_30d'] > 0 ? '' : '' }}">
        <div class="card-label">Dowody wygasają</div>
        <div class="card-value {{ $stats['evidence_expiring_30d'] > 0 ? 'amber' : 'emerald' }}">{{ $stats['evidence_expiring_30d'] }}</div>
        <div class="card-sub">w ciągu 30 dni</div>
    </div>
</div>

<h2>Trendy — ostatnie 12 miesięcy</h2>
<div class="trend-grid">
    <div class="trend-card">
        <h3>Nowe ryzyka</h3>
        <div class="chart-area">
            <x-svg-line-chart :points="$trends['risksNew']->toArray()" color="#f59e0b" :height="70"/>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:9px;color:#94a3b8;margin-top:2px;">
            <span>Suma: {{ $trends['risksNew']->sum('y') }}</span>
            <span>Avg/mies: {{ round($trends['risksNew']->avg('y'), 1) }}</span>
        </div>
    </div>
    <div class="trend-card">
        <h3>Podatności odkryte (czerwony) / zamknięte (zielony)</h3>
        <div class="chart-area">
            <x-svg-line-chart :points="$trends['vulnsNew']->toArray()" color="#ef4444" :height="40"/>
            <x-svg-line-chart :points="$trends['vulnsClosed']->toArray()" color="#10b981" :height="40"/>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:9px;margin-top:2px;">
            <span style="color:#ef4444;">Odkryte: {{ $trends['vulnsNew']->sum('y') }}</span>
            <span style="color:#10b981;">Zamknięte: {{ $trends['vulnsClosed']->sum('y') }}</span>
        </div>
    </div>
    <div class="trend-card">
        <h3>Incydenty</h3>
        <div class="chart-area">
            <x-svg-line-chart :points="$trends['incidentsNew']->toArray()" color="#8b5cf6" :height="70"/>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:9px;color:#94a3b8;margin-top:2px;">
            <span>Suma: {{ $trends['incidentsNew']->sum('y') }}</span>
            <span>Szczyt: {{ $trends['incidentsNew']->max('y') }}</span>
        </div>
    </div>
    <div class="trend-card">
        <h3>Wyniki ocen zgodności (%)</h3>
        <div class="chart-area">
            @if($trends['complianceScores']->count() >= 2)
                <x-svg-line-chart :points="$trends['complianceScores']->toArray()" color="#059669" :height="70" :targetLine="80"/>
            @else
                <div style="height:70px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:10px;">Brak danych</div>
            @endif
        </div>
        <div style="font-size:9px;color:#94a3b8;margin-top:2px;">
            @if($trends['complianceScores']->isNotEmpty()) Ostatni wynik: {{ $trends['complianceScores']->last()['y'] }}% | Cel: 80% @endif
        </div>
    </div>
</div>

<h2>Top 10 ryzyk (residual score)</h2>
<table>
    <thead>
        <tr>
            <th>Kod</th>
            <th>Tytuł</th>
            <th>Kategoria</th>
            <th>Score</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($topRisks as $r)
        <tr>
            <td style="font-family:monospace;">{{ $r->code }}</td>
            <td>{{ $r->title }}</td>
            <td>{{ $r->category_l1 }}</td>
            <td>
                @php $level = match(true) { $r->residual_score >= 15 => 'red', $r->residual_score >= 8 => 'amber', default => 'green' }; @endphp
                <span class="badge badge-{{ $level }}">{{ $r->residual_score }}</span>
            </td>
            <td>{{ $r->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h2>Mapa ciepła ryzyk (likelihood × impact)</h2>
<div style="display:inline-grid; grid-template-columns: auto repeat(5, 36px); gap:3px; font-size:9px;">
    <div></div>
    @for($i=1;$i<=5;$i++)<div style="text-align:center;font-weight:600;color:#94a3b8;">I{{ $i }}</div>@endfor
    @for($l=5;$l>=1;$l--)
        <div style="font-weight:600;color:#94a3b8;text-align:right;padding-right:4px;line-height:36px;">L{{ $l }}</div>
        @for($i=1;$i<=5;$i++)
            @php
                $score = $l * $i;
                $count = $heatmap[$l][$i] ?? 0;
                $bg = match(true) { $score >= 20 => '#ef4444', $score >= 12 => '#f97316', $score >= 6 => '#fbbf24', default => '#6ee7b7' };
                $fg = $score >= 6 ? '#fff' : '#065f46';
            @endphp
            <div style="width:36px;height:36px;background:{{ $bg }};border-radius:4px;display:flex;align-items:center;justify-content:center;font-weight:700;color:{{ $fg }};">{{ $count ?: '' }}</div>
        @endfor
    @endfor
</div>

<div class="footer">
    <span>{{ config('app.name') }} — Raport wygenerowany {{ now()->format('d.m.Y H:i') }}</span>
    <span>POUFNE — tylko do użytku wewnętrznego</span>
</div>

</body>
</html>
