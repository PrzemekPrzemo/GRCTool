<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analiza Luk — {{ $assessment->code }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1e293b; background: #fff; }
        .container { max-width: 1100px; margin: 0 auto; padding: 24px 32px; }
        .header { border-bottom: 3px solid #dc2626; padding-bottom: 16px; margin-bottom: 24px; }
        .header h1 { font-size: 22px; font-weight: 700; color: #dc2626; margin-bottom: 4px; }
        .header .subtitle { font-size: 14px; font-weight: 700; color: #1e293b; margin-top: 4px; }
        .header .meta { display: flex; gap: 24px; flex-wrap: wrap; margin-top: 12px; color: #475569; font-size: 11px; }
        .meta-item strong { color: #1e293b; }
        .summary-bar { display: flex; gap: 16px; margin-bottom: 24px; }
        .summary-pill { display: flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 13px; }
        .pill-critical { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .pill-partial { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .pill-label { font-size: 11px; font-weight: 400; color: inherit; opacity: 0.8; }
        h2 { font-size: 14px; font-weight: 700; color: #1e293b; margin: 20px 0 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 24px; }
        thead th { background: #f8fafc; font-weight: 600; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em; padding: 8px 10px; border-bottom: 2px solid #e2e8f0; text-align: left; }
        tbody td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: top; line-height: 1.5; }
        tbody tr.row-non-compliant { background: #fef2f2; }
        tbody tr.row-non-compliant:hover { background: #fee2e2; }
        tbody tr.row-partial { background: #fffbeb; }
        tbody tr.row-partial:hover { background: #fef3c7; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 10px; font-weight: 600; }
        .badge-non-compliant { background: #fee2e2; color: #991b1b; }
        .badge-partial { background: #fef3c7; color: #92400e; }
        .badge-prio-high { background: #fecaca; color: #991b1b; }
        .badge-prio-medium { background: #fde68a; color: #92400e; }
        .badge-prio-low { background: #d1fae5; color: #065f46; }
        .code-mono { font-family: monospace; font-size: 10px; background: #f1f5f9; padding: 1px 5px; border-radius: 3px; }
        .no-print { }
        .actions { display: flex; gap: 12px; margin-bottom: 20px; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; }
        .btn-print { background: #dc2626; color: #fff; }
        .btn-close { background: #e2e8f0; color: #475569; }
        .empty-state { text-align: center; padding: 40px; color: #94a3b8; font-size: 14px; }
        .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; text-align: center; }
        @media print {
            .no-print { display: none !important; }
            .container { padding: 12px 16px; }
            body { font-size: 11px; }
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
        <h1>Analiza Luk</h1>
        <div class="subtitle">
            {{ $assessment->framework->name }}
            @if($assessment->framework->version) v{{ $assessment->framework->version }}@endif
            — {{ $assessment->title }}
        </div>
        <div class="meta">
            <div class="meta-item"><strong>Kod:</strong> {{ $assessment->code }}</div>
            @if($assessment->assessment_date)
            <div class="meta-item"><strong>Data oceny:</strong> {{ $assessment->assessment_date->format('d.m.Y') }}</div>
            @endif
            @if($assessment->conductedBy)
            <div class="meta-item"><strong>Przeprowadzający:</strong> {{ $assessment->conductedBy->name }}</div>
            @endif
        </div>
    </div>

    <div class="summary-bar">
        <div class="summary-pill pill-critical">
            <span style="font-size:18px;">{{ $nonCompliantCount }}</span>
            <span class="pill-label">luk krytycznych (niezgodne)</span>
        </div>
        <div class="summary-pill pill-partial">
            <span style="font-size:18px;">{{ $partialCount }}</span>
            <span class="pill-label">częściowych (do uzupełnienia)</span>
        </div>
    </div>

    @if($gaps->isEmpty())
    <div class="empty-state">
        Brak luk do wyświetlenia. Wszystkie wymagania są zgodne lub nie ocenione.
    </div>
    @else
    <h2>Lista luk i planów remediacji</h2>
    <table>
        <thead>
            <tr>
                <th style="width:90px">Kod</th>
                <th style="width:200px">Wymaganie</th>
                <th style="width:90px">Status</th>
                <th>Opis luki</th>
                <th>Plan remediacji</th>
                <th style="width:75px">Priorytet</th>
                <th style="width:90px">Termin</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gaps as $gap)
            @php
                $req  = $gap['requirement'];
                $resp = $gap['response'];
                $rowClass = $resp->status === 'non_compliant' ? 'row-non-compliant' : 'row-partial';
                $statusBadge = $resp->status === 'non_compliant' ? 'badge-non-compliant' : 'badge-partial';
                $statusLabel = $resp->status === 'non_compliant' ? 'Niezgodne' : 'Częściowe';
                $prioBadge = match($resp->priority) {
                    'high'   => 'badge-prio-high',
                    'medium' => 'badge-prio-medium',
                    'low'    => 'badge-prio-low',
                    default  => '',
                };
                $prioLabel = match($resp->priority) {
                    'high'   => 'Wysoki',
                    'medium' => 'Średni',
                    'low'    => 'Niski',
                    default  => '—',
                };
            @endphp
            <tr class="{{ $rowClass }}">
                <td><span class="code-mono">{{ $req->code }}</span></td>
                <td>{{ Str::limit($req->name, 100) }}</td>
                <td><span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span></td>
                <td>{{ $resp->gap_description ?: '—' }}</td>
                <td>{{ $resp->remediation_plan ?: '—' }}</td>
                <td>
                    @if($resp->priority)
                    <span class="badge {{ $prioBadge }}">{{ $prioLabel }}</span>
                    @else
                    <span style="color:#94a3b8">—</span>
                    @endif
                </td>
                <td>{{ $resp->target_date?->format('d.m.Y') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        Wygenerowano: {{ now()->format('d.m.Y H:i') }} | System: GRC Tool
    </div>
</div>
</body>
</html>
