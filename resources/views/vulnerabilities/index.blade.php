@extends('layouts.app')
@section('content')

<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Zarządzanie podatnościami</h1>
    <div class="flex gap-2">
        <a href="{{ route('vulnerabilities.import.show') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-sm hover:bg-slate-200 transition-colors">Import CSV</a>
        <a href="{{ route('export.vulnerabilities') }}" class="px-3 py-1.5 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">↓ CSV</a>
        @can('vulnerability.create')
        <a href="{{ route('vulnerabilities.create') }}" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 transition-colors">+ Dodaj ręcznie</a>
        @endcan
    </div>
</div>

{{-- SLA stat cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    @php
    $slaCards = [
        ['Critical', $stats['critical_overdue'], $stats['critical_total'], 'red'],
        ['High', $stats['high_overdue'], $stats['high_total'], 'orange'],
        ['Medium', $stats['medium_overdue'], $stats['medium_total'], 'amber'],
        ['SLA compliance', $stats['sla_compliance'].'%', '(zamknięte)', 'emerald'],
    ];
    @endphp
    @foreach($slaCards as [$label, $val, $sub, $color])
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 {{ $color === 'red' && $val > 0 ? 'border-red-300 bg-red-50' : '' }}">
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wide">{{ $label }}</div>
        <div class="text-3xl font-bold mt-1 text-{{ $color }}-600">{{ $val }}</div>
        <div class="text-xs text-slate-400 mt-0.5">{{ $sub === $stats['critical_total'] || is_int($sub) ? $sub.' otwarte' : $sub }}</div>
    </div>
    @endforeach
</div>

<form method="GET" class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 mb-4 flex flex-wrap gap-2">
    <select name="severity" class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm">
        <option value="">— Severity —</option>
        @foreach(['Critical','High','Medium','Low','Info'] as $s)<option @selected(request('severity')===$s)>{{ $s }}</option>@endforeach
    </select>
    <select name="status" class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm">
        <option value="">— Status —</option>
        @foreach(['Open','In Progress','Mitigated','Resolved','Closed','False Positive','Risk Accepted','Reopened'] as $s)<option @selected(request('status')===$s)>{{ $s }}</option>@endforeach
    </select>
    <select name="source_type" class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm">
        <option value="">— Źródło —</option>
        @foreach(\App\Models\Vulnerability::SOURCE_TYPES as $t)<option @selected(request('source_type')===$t)>{{ $t }}</option>@endforeach
    </select>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="overdue" value="1" @checked(request('overdue'))> Po SLA</label>
    <button class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-sm">Filtruj</button>
    @if(request()->anyFilled(['severity','status','source_type','overdue']))
    <a href="{{ route('vulnerabilities.index') }}" class="px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-sm">Wyczyść</a>
    @endif
</form>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 border-b border-slate-200">
            <tr>
                <th class="px-3 py-2.5">CVE / Kod</th>
                <th class="px-3 py-2.5">Tytuł</th>
                <th class="px-3 py-2.5">Severity</th>
                <th class="px-3 py-2.5">CVSS</th>
                <th class="px-3 py-2.5">OWASP</th>
                <th class="px-3 py-2.5">Źródło</th>
                <th class="px-3 py-2.5">Dni otwarte</th>
                <th class="px-3 py-2.5">SLA deadline</th>
                <th class="px-3 py-2.5">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vulns as $v)
            @php
                $days   = $v->daysOpen();
                $ageBg  = match(true) { $days > 90 => 'text-red-700 font-bold', $days > 30 => 'text-amber-700', default => 'text-slate-500' };
                $sevBg  = ['Critical'=>'bg-red-600 text-white','High'=>'bg-orange-500 text-white','Medium'=>'bg-amber-300 text-amber-900','Low'=>'bg-emerald-200 text-emerald-800','Info'=>'bg-slate-200 text-slate-700'][$v->severity] ?? 'bg-slate-200';
                $sla    = $v->slaStatus();
                $slaBg  = match($sla) { 'breach' => 'text-red-700 font-bold', 'warning' => 'text-amber-700', default => 'text-slate-500' };
            @endphp
            <tr class="border-t border-slate-100 hover:bg-slate-50 transition-colors">
                <td class="px-3 py-2 font-mono text-xs">
                    <a href="{{ route('vulnerabilities.show', $v) }}" class="text-emerald-700 hover:underline">{{ $v->cve_id ?? $v->code }}</a>
                    @if($v->is_kev) <span class="ml-1 px-1 py-0.5 bg-red-100 text-red-700 text-xs rounded font-semibold">KEV</span> @endif
                </td>
                <td class="px-3 py-2 max-w-xs truncate">{{ $v->title }}</td>
                <td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs {{ $sevBg }}">{{ $v->severity }}</span></td>
                <td class="px-3 py-2 text-xs">{{ $v->cvss_score ?? '—' }}</td>
                <td class="px-3 py-2 text-xs font-mono">{{ $v->owasp_category ?? '—' }}</td>
                <td class="px-3 py-2 text-xs text-slate-500">{{ $v->source_type }}</td>
                <td class="px-3 py-2 text-xs {{ $ageBg }}">{{ $days }}d</td>
                <td class="px-3 py-2 text-xs {{ $slaBg }}">{{ $v->due_date?->format('Y-m-d') ?? '—' }}</td>
                <td class="px-3 py-2 text-xs text-slate-600">{{ $v->status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-3 py-8 text-center text-slate-500">
                    Brak podatności.
                    <a href="{{ route('vulnerabilities.create') }}" class="text-emerald-600 hover:underline">Dodaj ręcznie</a>
                    lub <a href="{{ route('vulnerabilities.import.show') }}" class="text-emerald-600 hover:underline">zaimportuj CSV</a>.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $vulns->links() }}</div>

@endsection
