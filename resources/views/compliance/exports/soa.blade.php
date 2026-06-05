<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoA — {{ $assessment->code }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1e293b; background: #fff; }
        .container { max-width: 1100px; margin: 0 auto; padding: 24px 32px; }
        .header { border-bottom: 3px solid #059669; padding-bottom: 16px; margin-bottom: 24px; }
        .header h1 { font-size: 22px; font-weight: 700; color: #059669; margin-bottom: 4px; }
        .header .meta { display: flex; gap: 24px; flex-wrap: wrap; margin-top: 12px; color: #475569; font-size: 11px; }
        .meta-item strong { color: #1e293b; }
        .summary { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin-bottom: 24px; }
        .summary-card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; text-align: center; }
        .summary-card .num { font-size: 22px; font-weight: 700; }
        .summary-card .lbl { font-size: 10px; color: #64748b; margin-top: 2px; }
        .summary-card.total .num { color: #1e293b; }
        .summary-card.compliant .num { color: #059669; border-color: #bbf7d0; background: #f0fdf4; }
        .summary-card.partial .num { color: #d97706; border-color: #fde68a; background: #fffbeb; }
        .summary-card.non-compliant .num { color: #dc2626; border-color: #fecaca; background: #fef2f2; }
        .summary-card.na .num { color: #64748b; }
        .summary-card.score .num { color: #059669; }
        h2 { font-size: 14px; font-weight: 700; color: #1e293b; margin: 20px 0 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 24px; }
        thead th { background: #f8fafc; font-weight: 600; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em; padding: 8px 10px; border-bottom: 2px solid #e2e8f0; text-align: left; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        tbody tr:hover { background: #f8fafc; }
        .domain-header td { background: #f1f5f9; font-weight: 700; color: #1e293b; padding: 8px 10px; font-size: 11px; border-top: 2px solid #e2e8f0; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 10px; font-weight: 600; }
        .badge-compliant { background: #d1fae5; color: #065f46; }
        .badge-partial { background: #fef3c7; color: #92400e; }
        .badge-non-compliant { background: #fee2e2; color: #991b1b; }
        .badge-na { background: #f1f5f9; color: #64748b; }
        .badge-not-assessed { background: #f8fafc; color: #94a3b8; }
        .code-mono { font-family: monospace; font-size: 10px; background: #f1f5f9; padding: 1px 5px; border-radius: 3px; }
        .no-print { }
        .actions { display: flex; gap: 12px; margin-bottom: 20px; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; }
        .btn-print { background: #059669; color: #fff; }
        .btn-close { background: #e2e8f0; color: #475569; }
        .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; text-align: center; }
        @media print {
            .no-print { display: none !important; }
            .container { padding: 12px 16px; }
            body { font-size: 11px; }
            .summary { grid-template-columns: repeat(6, 1fr); }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="no-print actions">
        <button onclick="window.print()" class="btn btn-print">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Drukuj
        </button>
        <button onclick="window.close()" class="btn btn-close">
            Zamknij
        </button>
    </div>

    <div class="header">
        <h1>Deklaracja Stosowania (SoA)</h1>
        <div style="font-size:13px; color:#475569; margin-top:6px; font-weight:600;">
            {{ $assessment->framework->name }}
            @if($assessment->framework->version) v{{ $assessment->framework->version }}@endif
        </div>
        <div style="font-size:14px; font-weight:700; color:#1e293b; margin-top:4px;">{{ $assessment->title }}</div>
        <div class="meta">
            <div class="meta-item"><strong>Kod:</strong> {{ $assessment->code }}</div>
            @if($assessment->assessment_date)
            <div class="meta-item"><strong>Data oceny:</strong> {{ $assessment->assessment_date->format('d.m.Y') }}</div>
            @endif
            @if($assessment->conductedBy)
            <div class="meta-item"><strong>Przeprowadzający:</strong> {{ $assessment->conductedBy->name }}</div>
            @endif
            @if($assessment->scope)
            <div class="meta-item"><strong>Zakres:</strong> {{ $assessment->scope }}</div>
            @endif
            <div class="meta-item"><strong>Status:</strong> {{ \App\Models\ComplianceAssessment::STATUS_LABELS[$assessment->status] ?? $assessment->status }}</div>
        </div>
    </div>

    <h2>Podsumowanie</h2>
    <div class="summary">
        <div class="summary-card total">
            <div class="num">{{ $totalWithNa }}</div>
            <div class="lbl">Łącznie</div>
        </div>
        <div class="summary-card compliant">
            <div class="num">{{ $assessment->compliant_count }}</div>
            <div class="lbl">Zgodne</div>
        </div>
        <div class="summary-card partial">
            <div class="num">{{ $assessment->partial_count }}</div>
            <div class="lbl">Częściowe</div>
        </div>
        <div class="summary-card non-compliant">
            <div class="num">{{ $assessment->non_compliant_count }}</div>
            <div class="lbl">Niezgodne</div>
        </div>
        <div class="summary-card na">
            <div class="num">{{ $assessment->na_count }}</div>
            <div class="lbl">Nie dotyczy</div>
        </div>
        <div class="summary-card score">
            <div class="num">{{ $assessment->overall_score !== null ? number_format($assessment->overall_score, 1) . '%' : '—' }}</div>
            <div class="lbl">Wynik</div>
        </div>
    </div>

    <h2>Wymagania według domen</h2>
    <table>
        <thead>
            <tr>
                <th style="width:90px">Kod</th>
                <th>Wymaganie</th>
                <th style="width:80px">Stosowalność</th>
                <th style="width:110px">Status</th>
                <th style="width:160px">Dowód (skrót)</th>
                <th style="width:160px">Uzasadnienie</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assessment->framework->domains as $domain)
            <tr class="domain-header">
                <td colspan="6">
                    <span class="code-mono">{{ $domain->code }}</span>
                    {{ $domain->name }}
                </td>
            </tr>
            @foreach($domain->requirements as $req)
            @php
                $resp    = $responses->get($req->id);
                $rStatus = $resp?->status ?? 'not_assessed';
                $badgeClass = match($rStatus) {
                    'compliant'      => 'badge-compliant',
                    'partial'        => 'badge-partial',
                    'non_compliant'  => 'badge-non-compliant',
                    'not_applicable' => 'badge-na',
                    default          => 'badge-not-assessed',
                };
                $statusLabel = [
                    'compliant'      => 'Zgodne',
                    'partial'        => 'Częściowe',
                    'non_compliant'  => 'Niezgodne',
                    'not_applicable' => 'Nie dotyczy',
                    'not_assessed'   => 'Nie oceniono',
                ][$rStatus] ?? $rStatus;
                $applicability = $rStatus === 'not_applicable' ? 'Wyłączone' : 'Włączone';
            @endphp
            <tr>
                <td><span class="code-mono">{{ $req->code }}</span></td>
                <td>{{ $req->name }}</td>
                <td>{{ $applicability }}</td>
                <td><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                <td>{{ $resp?->evidence ? Str::limit($resp->evidence, 100) : '—' }}</td>
                <td>{{ $resp?->gap_description ? Str::limit($resp->gap_description, 100) : '—' }}</td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Wygenerowano: {{ now()->format('d.m.Y H:i') }} | System: GRC Tool
        @if($assessment->is_published)
        | <strong>Dokument opublikowany</strong>
        @endif
    </div>
</div>
</body>
</html>
