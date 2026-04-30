<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $template->name }} — {{ $reportCode }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #0f172a; }
        h1 { color: #0f172a; border-bottom: 3px solid #7c3aed; padding-bottom: 5px; }
        h2 { color: #5b21b6; margin-top: 25px; font-size: 13pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { padding: 5px 8px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 9pt; }
        th { background: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 8pt; color: #64748b; }
        .meta { color: #64748b; font-size: 8pt; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 50pt; color: rgba(124, 58, 237, 0.04); z-index: -1; }
        .footer { position: fixed; bottom: 10px; text-align: center; color: #94a3b8; font-size: 7pt; left: 0; right: 0; }
    </style>
</head>
<body>
<div class="watermark">CUSTOMER · NDA</div>
<div class="footer">{{ $watermark }}</div>

<h1>{{ $template->name }}</h1>
<p class="meta">Confidential — for customer security review · Generated: {{ $generatedAt->format('Y-m-d H:i') }} · Code: {{ $reportCode }}</p>

<h2>1. Security posture overview</h2>
<p>This report summarizes our security program based on continuous monitoring data and the latest control effectiveness assessments.</p>
<ul>
    <li>Active risks tracked: <strong>{{ $data['risks_total'] }}</strong> ({{ $data['risks_over_appetite'] }} above appetite, all with documented mitigations).</li>
    <li>Controls assessed effective: <strong>{{ $data['controls_effective'] }}/{{ $data['controls_total'] }}</strong>.</li>
    <li>Critical vulnerabilities open: <strong>{{ $data['vulns_open_critical'] }}</strong>; SLA target: 7 days.</li>
    <li>Active independent audits: <strong>{{ $data['engagements_active']->count() }}</strong>.</li>
</ul>

<h2>2. Active certifications &amp; assessments</h2>
<table>
    <thead><tr><th>Engagement</th><th>Framework</th><th>Auditor</th><th>Period</th><th>Status</th></tr></thead>
    <tbody>
        @forelse($data['engagements_active'] as $e)
        <tr><td>{{ $e->code }}</td><td>{{ $e->framework }}</td><td>{{ $e->auditor_org }}</td><td>{{ $e->audit_period_start?->format('Y-m-d') }}-{{ $e->audit_period_end?->format('Y-m-d') }}</td><td>{{ $e->status }}</td></tr>
        @empty
        <tr><td colspan="5" class="meta">No active engagements in this period.</td></tr>
        @endforelse
    </tbody>
</table>

<h2>3. Active policies</h2>
<table>
    <thead><tr><th>Code</th><th>Title</th><th>Version</th><th>Effective from</th><th>Next review</th></tr></thead>
    <tbody>
        @forelse($data['policies'] ?? [] as $p)
        <tr><td>{{ $p->code }}</td><td>{{ $p->title }}</td><td>{{ $p->current_version }}</td><td>{{ $p->effective_from?->format('Y-m-d') }}</td><td>{{ $p->next_review_due?->format('Y-m-d') }}</td></tr>
        @empty
        <tr><td colspan="5" class="meta">Policies are managed in the platform; subset shown on request.</td></tr>
        @endforelse
    </tbody>
</table>

<h2>4. Subprocessors (public listing)</h2>
<table>
    <thead><tr><th>Name</th><th>Service</th><th>Country</th><th>Tier</th><th>Certifications</th></tr></thead>
    <tbody>
        @forelse($data['subprocessors'] ?? [] as $s)
        <tr><td>{{ $s->name }}</td><td>{{ $s->service_provided }}</td><td>{{ $s->country_of_processing }}</td><td>{{ $s->tier }}</td><td class="meta">{{ implode(', ', $s->certifications ?? []) }}</td></tr>
        @empty
        <tr><td colspan="5" class="meta">Subprocessor list available upon NDA-signed request.</td></tr>
        @endforelse
    </tbody>
</table>

<h2>5. Customer-facing security KCI</h2>
<table>
    <thead><tr><th>Indicator</th><th>Value</th><th>Status</th></tr></thead>
    <tbody>
        @foreach($data['indicators_kci']->take(10) as $i)
        @php $latest = $i->latestMeasurement; @endphp
        <tr>
            <td>{{ $i->name }}</td>
            <td>{{ $latest ? $latest->value.' '.$i->unit : '—' }}</td>
            <td>{{ $latest?->status ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<p class="meta" style="margin-top: 30px;">This document is provided for security review purposes under NDA. Distribution is logged. For verification of integrity, contact security@... and reference report code <strong>{{ $reportCode }}</strong>.</p>
</body>
</html>
