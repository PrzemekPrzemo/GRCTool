<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $template->name }} — {{ $reportCode }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #0f172a; }
        h1 { color: #0f172a; border-bottom: 3px solid #059669; padding-bottom: 5px; }
        h2 { color: #1e293b; margin-top: 25px; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; font-size: 13pt; }
        h3 { font-size: 11pt; color: #334155; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { padding: 5px 8px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 9pt; }
        th { background: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 8pt; color: #64748b; }
        .meta { color: #64748b; font-size: 8pt; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 60pt; color: rgba(15, 23, 42, 0.05); z-index: -1; }
        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; color: #94a3b8; font-size: 7pt; }
        .tile { display: inline-block; width: 22%; padding: 8px; margin: 4px 0.5%; border: 1px solid #e2e8f0; border-radius: 4px; vertical-align: top; }
        .tile .label { font-size: 7pt; color: #64748b; text-transform: uppercase; }
        .tile .value { font-size: 18pt; font-weight: bold; color: #059669; }
        .tile .sub { font-size: 8pt; color: #64748b; }
        .severity-Major { color: #b91c1c; font-weight: bold; }
        .severity-Minor { color: #d97706; }
        .residual-high { color: #b91c1c; font-weight: bold; }
    </style>
</head>
<body>
<div class="watermark">CONFIDENTIAL</div>
<div class="footer">{{ $watermark }} · Strona <span class="pagenum"></span></div>

<h1>{{ $template->name }}</h1>
<p class="meta">Okres: <strong>{{ $periodStart->format('Y-m-d') }} — {{ $periodEnd->format('Y-m-d') }}</strong> · Wygenerowany: {{ $generatedAt->format('Y-m-d H:i') }} przez {{ $generatedBy }} · Kod raportu: <strong>{{ $reportCode }}</strong></p>

<h2>1. Streszczenie wykonawcze</h2>
<div>
    <div class="tile"><div class="label">Otwarte ryzyka</div><div class="value">{{ $data['risks_total'] }}</div><div class="sub">{{ $data['risks_over_appetite'] }} powyżej apetytu</div></div>
    <div class="tile"><div class="label">Kontrole effective</div><div class="value">{{ $data['controls_effective'] }}/{{ $data['controls_total'] }}</div><div class="sub">{{ $data['controls_total'] > 0 ? round($data['controls_effective'] / $data['controls_total'] * 100) : 0 }}% pokrycia</div></div>
    <div class="tile"><div class="label">Critical vuln otwarte</div><div class="value">{{ $data['vulns_open_critical'] }}</div><div class="sub">+{{ $data['vulns_open_high'] }} High</div></div>
    <div class="tile"><div class="label">Findings otwarte</div><div class="value">{{ $data['findings_open'] }}</div><div class="sub">{{ $data['engagements_active']->count() }} aktywne audyty</div></div>
</div>

<h2>2. Top 10 ryzyk (residual)</h2>
<table>
    <thead><tr><th>Kod</th><th>Tytuł</th><th>Kategoria</th><th>Inh.</th><th>Res.</th><th>Tgt.</th><th>Strategia</th><th>Owner</th><th>Status</th></tr></thead>
    <tbody>
        @foreach($data['risks_top10'] as $r)
        <tr>
            <td>{{ $r->code }}</td>
            <td>{{ $r->title }}</td>
            <td class="meta">{{ $r->category_l1 }}/{{ $r->category_l2 }}</td>
            <td>{{ $r->inherent_score }}</td>
            <td class="residual-high">{{ $r->residual_score }}</td>
            <td>{{ $r->target_score ?? '—' }}</td>
            <td>{{ $r->treatment_strategy ?? '—' }}</td>
            <td class="meta">{{ $r->owner?->name ?? '—' }}</td>
            <td>{{ $r->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h2>3. Kluczowe wskaźniki strategiczne</h2>
<table>
    <thead><tr><th>Kod</th><th>Typ</th><th>Nazwa</th><th>Wartość</th><th>Target</th><th>Status</th></tr></thead>
    <tbody>
        @foreach($data['indicators_executive'] as $i)
        @php $latest = $i->latestMeasurement; @endphp
        <tr>
            <td>{{ $i->code }}</td>
            <td>{{ $i->type }}</td>
            <td>{{ $i->name }}</td>
            <td>{{ $latest ? rtrim(rtrim(number_format((float)$latest->value, 2), '0'), '.').' '.$i->unit : '—' }}</td>
            <td>{{ $i->target_value }} {{ $i->unit }}</td>
            <td>{{ $latest?->status ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h2>4. Aktywne audyty</h2>
<table>
    <thead><tr><th>Kod</th><th>Nazwa</th><th>Framework</th><th>Auditor</th><th>Okres</th><th>Status</th></tr></thead>
    <tbody>
        @forelse($data['engagements_active'] as $e)
        <tr>
            <td>{{ $e->code }}</td><td>{{ $e->name }}</td><td>{{ $e->framework }}</td>
            <td>{{ $e->auditor_org ?? '—' }}</td>
            <td class="meta">{{ $e->audit_period_start?->format('Y-m-d') }}-{{ $e->audit_period_end?->format('Y-m-d') }}</td>
            <td>{{ $e->status }}</td>
        </tr>
        @empty
        <tr><td colspan="6" class="meta">Brak aktywnych audytów.</td></tr>
        @endforelse
    </tbody>
</table>

<p class="meta" style="margin-top: 30px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
    Raport wygenerowany automatycznie z platformy GRC. Dokument tamper-evident — SHA-256 hash dostępny w metadanych raportu w systemie. W razie wątpliwości weryfikuj integralność przez kod {{ $reportCode }}.
</p>
</body>
</html>
