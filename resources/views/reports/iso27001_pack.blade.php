<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $template->name }} — {{ $reportCode }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #0f172a; }
        h1 { color: #0f172a; border-bottom: 3px solid #2563eb; padding-bottom: 5px; }
        h2 { color: #1e3a8a; margin-top: 22px; font-size: 13pt; }
        h3 { font-size: 11pt; color: #334155; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { padding: 4px 6px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 8pt; vertical-align: top; }
        th { background: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 7pt; color: #64748b; }
        .meta { color: #64748b; font-size: 7pt; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 50pt; color: rgba(37, 99, 235, 0.04); z-index: -1; }
        .footer { position: fixed; bottom: 10px; text-align: center; color: #94a3b8; font-size: 7pt; left: 0; right: 0; }
    </style>
</head>
<body>
<div class="watermark">ISO 27001 PACK</div>
<div class="footer">{{ $watermark }}</div>

<h1>{{ $template->name }}</h1>
<p class="meta">ISO/IEC 27001:2022 · Audit period: {{ $periodStart->format('Y-m-d') }} — {{ $periodEnd->format('Y-m-d') }} · Generated: {{ $generatedAt->format('Y-m-d H:i') }} · Code: {{ $reportCode }}</p>

<h2>1. Statement of Applicability (extract)</h2>
<table>
    <thead><tr><th>Control code</th><th>Name</th><th>Type</th><th>Owner</th><th>Effectiveness</th><th>Last tested</th><th>Frameworks</th></tr></thead>
    <tbody>
        @foreach($data['controls_iso'] ?? [] as $c)
        <tr>
            <td>{{ $c->code }}</td>
            <td>{{ $c->name }}</td>
            <td>{{ $c->control_type }}</td>
            <td>{{ $c->owner?->name ?? '—' }}</td>
            <td>{{ $c->effectiveness_status }}</td>
            <td>{{ $c->last_tested_at?->format('Y-m-d') ?? '—' }}</td>
            <td class="meta">@foreach($c->frameworkControls as $fc){{ $fc->reference }}@if(!$loop->last); @endif @endforeach</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h2>2. Risk Register (top 10)</h2>
<table>
    <thead><tr><th>Code</th><th>Title</th><th>Inh.</th><th>Res.</th><th>Strategy</th><th>Status</th><th>Owner</th></tr></thead>
    <tbody>
        @foreach($data['risks_top10'] as $r)
        <tr>
            <td>{{ $r->code }}</td>
            <td>{{ $r->title }}</td>
            <td>{{ $r->inherent_score }}</td>
            <td>{{ $r->residual_score }}</td>
            <td>{{ $r->treatment_strategy ?? '—' }}</td>
            <td>{{ $r->status }}</td>
            <td>{{ $r->owner?->name ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h2>3. KCI — Key Control Indicators</h2>
<table>
    <thead><tr><th>Code</th><th>Name</th><th>Latest value</th><th>Target</th><th>Status</th><th>Source</th></tr></thead>
    <tbody>
        @foreach($data['indicators_kci'] as $i)
        @php $latest = $i->latestMeasurement; @endphp
        <tr>
            <td>{{ $i->code }}</td>
            <td>{{ $i->name }}</td>
            <td>{{ $latest ? $latest->value.' '.$i->unit : '—' }}</td>
            <td>{{ $i->target_value }} {{ $i->unit }}</td>
            <td>{{ $latest?->status ?? '—' }}</td>
            <td class="meta">{{ $i->data_source }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h2>4. Aktywne audyty zewnętrzne</h2>
<table>
    <thead><tr><th>Code</th><th>Auditor</th><th>Period</th><th>Status</th></tr></thead>
    <tbody>
        @foreach($data['engagements_active'] as $e)
        <tr><td>{{ $e->code }}</td><td>{{ $e->auditor_org }}</td><td>{{ $e->audit_period_start?->format('Y-m-d') }}-{{ $e->audit_period_end?->format('Y-m-d') }}</td><td>{{ $e->status }}</td></tr>
        @endforeach
    </tbody>
</table>

<p class="meta" style="margin-top: 30px;">Pełna ewidencja kontroli, planów testów i evidence dostępna w platformie GRC. Audytor zewnętrzny otrzymuje czasowy dostęp do swojego scope przez Auditor Portal.</p>
</body>
</html>
